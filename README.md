# WooCommerce & Gravity Forms Custom Integrations Starter

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg?logo=php)](https://www.php.net)
[![PHPUnit Tests](https://img.shields.io/badge/PHPUnit-Passing-brightgreen.svg)](tests/)

A robust, enterprise-grade WordPress plugin boilerplate demonstrating **custom WooCommerce checkout & fee pipelines**, **Gravity Forms validation and submission handlers**, **third-party REST API syncing with exponential backoff**, and **hardened `$wpdb->prepare` SQL migrations**.

---

## 🛠️ Key Architectural Patterns

### 1. WooCommerce Hooks & Checkout Extension
- Custom billing fields added cleanly via `woocommerce_checkout_fields` with data sanitation.
- Conditional cart fee calculations hooked into `woocommerce_cart_calculate_fees`.
- Webhook dispatch triggered upon order transition via `woocommerce_order_status_completed`.

### 2. Gravity Forms Custom Add-On Logic
- Custom field validation (`gform_field_validation`) with regex checks (e.g. Australian Business Number / ABN verification).
- Automated CRM/ERP sync triggered on `gform_after_submission` without blocking user browser redirect.

### 3. Resilient Third-Party API Client
- Built on top of WordPress HTTP API (`wp_remote_post`).
- Includes automatic retry strategies and exponential backoff for transient network failures.

### 4. Database Safety & Migrations
- Custom audit table managed via `dbDelta` during plugin activation.
- All dynamic parameters parameterized using `$wpdb->prepare` to guarantee zero SQL injection vulnerabilities.

---

## 🧪 Automated Testing

Run the test suite:
```bash
composer install
vendor/bin/phpunit
```

---

## 📄 License
GPLv2 or later © [Nadim Tuhin](https://github.com/nadimtuhin)
