# WP Woo — WordPress + WooCommerce + Polylang

A WordPress-based eCommerce project with multilingual support and REST API product import.

## Stack

- WordPress
- WooCommerce
- Polylang
- Contact Form 7

## Languages

- 🇺🇦 Ukrainian — default
- 🇬🇧 English — secondary

---

## Local Setup

### Requirements

- WampServer (Apache, PHP 8.x, MySQL 8.x)
- Git

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/DenysenkoStas/wp-woo.git
   ```

2. Move the project to WampServer directory:
   ```
   C:\wamp64\www\wp-woo\
   ```

3. Create a database in phpMyAdmin:
   - Open `http://localhost/phpmyadmin`
   - Create database: `wp_woo`

4. Copy and configure `wp-config.php`:
   ```bash
   cp wp-config-sample.php wp-config.php
   ```
   Edit `wp-config.php`:
   ```php
   define( 'DB_NAME', 'wp_woo' );
   define( 'DB_USER', 'root' );
   define( 'DB_PASSWORD', '' );
   define( 'DB_HOST', 'localhost' );
   ```

5. Open `http://localhost/wp-woo/` and complete WordPress installation.

6. Activate plugins:
   - WooCommerce
   - Polylang
   - Contact Form 7
   - Import Products API (custom plugin in `/wp-content/plugins/import-products/`)

---

## Import Products API

### Endpoint

```
POST /wp-json/test/v1/import-products
```

### Headers

```
Content-Type: application/json
```

### Logic

- If a product with the given SKU **does not exist** → create
- If a product with the given SKU **exists** → update
- No duplicates are created on repeated import
- An English translation is automatically created for each product

### Response

```json
{
  "created": 2,
  "updated": 0,
  "skipped": 0,
  "meta": {
    "total": 2,
    "processed": 2,
    "timestamp": "2026-04-13 07:16:40",
    "log": [
      "Created: SOFA-001 (Диван)",
      "Created: LAMP-001 (Лампа)"
    ]
  }
}
```

### Example JSON

```json
[
  {
    "sku": "CHAIR-001",
    "name": "Стілець",
    "price": 49.99,
    "stock": 10,
    "translations": {
      "en": { "name": "Chair" }
    }
  },
  {
    "sku": "TABLE-001",
    "name": "Стіл",
    "price": 149.99,
    "stock": 5,
    "translations": {
      "en": { "name": "Table" }
    }
  },
  {
    "sku": "SOFA-001",
    "name": "Диван",
    "price": 299.99,
    "stock": 5,
    "translations": {
      "en": { "name": "Sofa" }
    }
  },
  {
    "sku": "LAMP-001",
    "name": "Лампа",
    "price": 29.99,
    "stock": 15,
    "translations": {
      "en": { "name": "Lamp" }
    }
  }
]
```

### curl Example

```bash
curl -X POST http://localhost/wp-woo/wp-json/test/v1/import-products \
  -H "Content-Type: application/json" \
  -d '[{"sku":"CHAIR-001","name":"Стілець","price":49.99,"stock":10,"translations":{"en":{"name":"Chair"}}}]'
```

---

## Admin Access

| Field    | Value                            |
|----------|----------------------------------|
| URL      | http://localhost/wp-woo/wp-admin |
| Login    | your-login                       |
| Password | your-password                    |
