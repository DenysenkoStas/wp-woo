<?php
/**
 * Plugin Name: Import Products API
 * Description: REST API endpoint for importing WooCommerce products with Polylang translations
 * Version: 1.0.0
 * Author: Stas Denysenko
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('test/v1', '/import-products', [
        'methods' => 'POST',
        'callback' => 'handle_import_products',
        'permission_callback' => '__return_true',
    ]);
});

function handle_import_products(WP_REST_Request $request) {
    $products = $request->get_json_params();

    if (empty($products) || !is_array($products)) {
        return new WP_Error('invalid_data', 'Invalid or empty JSON', ['status' => 400]);
    }

    $created = 0;
    $updated = 0;
    $skipped = 0;
    $log = [];

    foreach ($products as $item) {
        $sku = sanitize_text_field($item['sku'] ?? '');
        $name_uk = sanitize_text_field($item['name'] ?? '');
        $price = floatval($item['price'] ?? 0);
        $stock = intval($item['stock'] ?? 0);
        $name_en = sanitize_text_field($item['translations']['en']['name'] ?? $name_uk);

        if (empty($sku) || empty($name_uk)) {
            $skipped++;
            $log[] = "Skipped: missing SKU or name";
            continue;
        }

        // Search for existing product by SKU
        $existing_id = wc_get_product_id_by_sku($sku);

        if ($existing_id) {
            // Update existing product
            $product = wc_get_product($existing_id);
            $product->set_name($name_uk);
            $product->set_regular_price($price);
            $product->set_stock_quantity($stock);
            $product->set_manage_stock(true);
            $product->save();

            // Update EN translation
            update_en_translation($existing_id, $name_en, $price, $stock, $sku);

            $updated++;
            $log[] = "Updated: {$sku} ({$name_uk})";
        } else {
            // Create new UA product
            $product = new WC_Product_Simple();
            $product->set_name($name_uk);
            $product->set_regular_price($price);
            $product->set_sku($sku);
            $product->set_stock_quantity($stock);
            $product->set_manage_stock(true);
            $product->set_status('publish');
            $product_id = $product->save();

            // Set Ukrainian language for the product
            if (function_exists('pll_set_post_language')) {
                pll_set_post_language($product_id, 'uk');
            }

            // Create EN translation
            $en_id = create_en_translation($product_id, $name_en, $price, $stock, $sku);

            // Link translations together
            if (function_exists('pll_save_post_translations')) {
                pll_save_post_translations([
                    'uk' => $product_id,
                    'en' => $en_id,
                ]);
            }

            $created++;
            $log[] = "Created: {$sku} ({$name_uk})";
        }
    }

    return rest_ensure_response([
        'created' => $created,
        'updated' => $updated,
        'skipped' => $skipped,
        'meta' => [
            'total' => count($products),
            'processed' => $created + $updated + $skipped,
            'timestamp' => current_time('mysql'),
            'log' => $log,
        ],
    ]);
}

function create_en_translation($uk_product_id, $name_en, $price, $stock, $sku) {
    $product_en = new WC_Product_Simple();
    $product_en->set_name($name_en);
    $product_en->set_regular_price($price);
    $product_en->set_sku($sku . '-EN');
    $product_en->set_stock_quantity($stock);
    $product_en->set_manage_stock(true);
    $product_en->set_status('publish');
    $en_id = $product_en->save();

    // Set English language for the product
    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($en_id, 'en');
    }

    return $en_id;
}

function update_en_translation($uk_product_id, $name_en, $price, $stock, $sku) {
    // Get existing EN translation if available
    $translations = function_exists('pll_get_post_translations')
        ? pll_get_post_translations($uk_product_id)
        : [];

    $en_id = $translations['en'] ?? wc_get_product_id_by_sku($sku . '-EN');

    if ($en_id) {
        // Update existing EN translation
        $product_en = wc_get_product($en_id);
        if ($product_en) {
            $product_en->set_name($name_en);
            $product_en->set_regular_price($price);
            $product_en->set_stock_quantity($stock);
            $product_en->save();
        }
    } else {
        // Create new EN translation if not found
        $en_id = create_en_translation($uk_product_id, $name_en, $price, $stock, $sku);

        if (function_exists('pll_save_post_translations')) {
            pll_save_post_translations([
                'uk' => $uk_product_id,
                'en' => $en_id,
            ]);
        }
    }
}