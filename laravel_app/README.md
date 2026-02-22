# Laravel Conversion (Capstone LMS)

This directory contains a Laravel-oriented rewrite of the legacy `capstone/*.php` flow.

## Converted features

- Registration (`/register`)
- Login (`/login`)
- Logout (`POST /logout`)
- Dashboard (`/dashboard`)
- Participants list with pagination and search (`/participants`)

## Mapping from legacy files

- `capstone/login.php` -> `App\Http\Controllers\AuthController@login`
- `capstone/register.php` -> `App\Http\Controllers\AuthController@register`
- `capstone/logout.php` -> `App\Http\Controllers\AuthController@logout`
- `capstone/dashboard.php` -> `App\Http\Controllers\DashboardController@index`
- `capstone/index.php` -> `App\Http\Controllers\ParticipantsController@index`

## Run instructions

1. Create a fresh Laravel project (or copy these files into one).
2. Configure `.env` database settings.
3. Run migrations:

```bash
php artisan migrate
```

4. Start app:

```bash
php artisan serve
```

