# Log Lens

A beautiful, scalable Laravel log viewer. View, search, filter, download, and clear your Laravel log files from a clean dark UI — directly in the browser.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![Laravel](https://img.shields.io/badge/Laravel-10%20|%2011%20|%2012-red)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/license-MIT-green)

---

## Features

- View all log files from `storage/logs`
- Filter by level — Error, Warning, Info, Debug
- Search across log entries with highlight
- Download or clear individual log files
- Upload external log files for inspection
- Handles large log files (100MB+) via lazy streaming
- Paginated entries — no DOM overload
- Auto-scroll toggle
- Clean dark UI with no dependencies on your app's frontend

---

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

---

## Installation

```bash
composer require laxmidhar/log-lens
```

Publish assets:
```bash
php artisan vendor:publish --tag=loglens-assets
```

Publish config (optional):
```bash
php artisan vendor:publish --tag=loglens-config
```

---

## Usage

Visit `/logs` in your browser. That's it.

---

## Configuration

After publishing the config, edit `config/loglens.php`:

```php
return [
    // URL prefix — change to whatever you want e.g. 'admin/logs'
    'route_prefix' => 'logs',

    // Middleware applied to all routes
    'middleware'   => ['web'],

    // Max log entries per page
    'max_entries'  => 500,

    // File read chunk size in bytes
    'chunk_size'   => 8192,
];
```

---

## Customizing Views

To customize the UI, publish the views:

```bash
php artisan vendor:publish --tag=loglens-views
```

Views will be copied to `resources/views/vendor/loglens/`.

---

## Security

It is strongly recommended to protect the log routes with authentication middleware in production:

```php
// config/loglens.php
'middleware' => ['web', 'auth'],
```

---

## License

MIT — [Laxmidhar Maharana](https://github.com/dante-san)