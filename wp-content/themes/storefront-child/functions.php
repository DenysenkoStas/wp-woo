<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'child-theme-style',
        get_stylesheet_uri(),
        ['storefront-style'],
        wp_get_theme()->get('Version')
    );
});

// Add language switcher to header as dropdown
add_action('storefront_header', function () {
    if (!function_exists('pll_the_languages')) return;

    $languages = pll_the_languages([
        'show_flags' => 0,
        'show_names' => 1,
        'hide_current' => 0,
        'echo' => 0,
        'raw' => 1,
    ]);

    $current_name = '';
    foreach ($languages as $lang) {
        if ($lang['current_lang']) {
            $current_name = $lang['name'];
            break;
        }
    }

    echo '<div class="header-lang-dropdown">';
    echo '<button class="lang-toggle" onclick="this.parentNode.classList.toggle(\'open\')">' . esc_html($current_name) . ' ▾</button>';
    echo '<ul class="lang-menu">';
    pll_the_languages([
        'show_flags' => 0,
        'show_names' => 1,
        'hide_current' => 0,
        'echo' => 1,
    ]);
    echo '</ul>';
    echo '</div>';

    echo "<script>
        document.addEventListener('click', function(e) {
            var dropdown = document.querySelector('.header-lang-dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    </script>";
}, 40);