# Log Lens

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laxmidhar/log-lens)](https://packagist.org/packages/laxmidhar/log-lens)
[![Total Downloads](https://img.shields.io/packagist/dt/laxmidhar/log-lens)](https://packagist.org/packages/laxmidhar/log-lens)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10|11|12-red)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/dante-san)

Log Lens is a browser-based Laravel log viewer. It reads directly from `storage/logs`, streams files of any size without memory issues, and ships with a fully standalone dark UI that won't interfere with your application's frontend.

---

![Log Lens](https://raw.githubusercontent.com/dante-san/log-lens/master/screenshot.png)

---

## Installation

```bash
composer require laxmidhar/log-lens
php artisan vendor:publish --tag=loglens-assets
```

Visit `/logs`.

---

## Configuration

Publish the config:

```bash
php artisan vendor:publish --tag=loglens-config
```

```php
// config/loglens.php
return [
    'route_prefix' => 'logs',     // e.g. 'admin/logs'
    'middleware'   => ['web'],
    'max_entries'  => 500,
    'chunk_size'   => 8192,
];
```

---

## Customizing Views

```bash
php artisan vendor:publish --tag=loglens-views
```

Published to `resources/views/vendor/loglens/`.

---

## Security

Log files can expose sensitive application data. In production, always restrict access:

```php
'middleware' => ['web', 'auth'],
```

---

## License

MIT — [Laxmidhar Maharana](https://github.com/dante-san)
