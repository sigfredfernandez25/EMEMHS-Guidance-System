# EMEMHS Guidance System

Web-based guidance management system for Emilio M. Espinosa Memorial High School with complaint tracking, lost & found, and automated SMS notifications.

## Features

- Student complaint and concern management with voice-to-text
- Lost and found item tracking with smart matching
- Automated SMS notifications for parents via Semaphore API
- Anonymous suggestion box
- Counseling session scheduling
- Analytics dashboard

## Tech Stack

- PHP 7.4+, MySQL 5.7+, Apache 2.4+
- Tailwind CSS, Chart.js, Font Awesome
- Semaphore SMS API, EmailJS

## Installation

1. Import database: `mysql -u root -p < guidancesystem.sql`
2. Copy `config.example.php` to `config.php`
3. Edit `config.php` with your database and Semaphore API credentials
4. Access at `http://localhost/EMEMHS-Guidance-System`

## Configuration

```php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'guidancesystem');
define('SEMAPHORE_API_KEY', 'your-api-key');
```

Get Semaphore API key: https://semaphore.co/

## User Roles

- Students: Submit complaints, report lost items, view dashboard
- Admin/Staff: Manage complaints, schedule sessions, send SMS, view analytics
- Public: Submit anonymous suggestions

## Documentation

See [SYSTEM_SUMMARY.md](SYSTEM_SUMMARY.md) for complete documentation.

---

Capstone project for EMEMHS | Version 1.0
