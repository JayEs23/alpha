# Leapsoft Limited Asset Management

**Leapsoft Limited Asset Management** is the internal platform used by Leapsoft Limited to manage company equipment and inventory needed for day-to-day operations.

ADMIN USER

![User-Admin](https://github.com/AlpetGexha/Laravel-Asset-Management/assets/50520333/795574a8-4e6c-4043-8bb0-3d77534e0a43)

<details close>
<summary>Dark Mode</summary>

![User-Admin-Dark-Mode](https://github.com/AlpetGexha/Laravel-Asset-Management/assets/50520333/4812a044-0d1f-484c-ab68-ef3fbed2c5e9)

</details>

NORMAL USER

![User-User](https://github.com/AlpetGexha/Laravel-Asset-Management/assets/50520333/10884d6a-500c-4579-9836-09432e8b77d2)

## Tech Stack

- PHP 8.1+
- Laravel 10
- Filament v2 (admin UI)
- MySQL
- Vite

## Installation

```bash
git clone <your-repository-url>
cd alpha
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Start Project

```bash
php artisan serve
```

## Development Commands

```bash
# Code style check
vendor/bin/pint --test

# Run formatter
vendor/bin/pint

# Run database seeders
php artisan db:seed

# Rebuild local database from scratch
php artisan migrate:fresh --seed
```

## Frontend Assets

```bash
npm install
npm run dev
```

## Environment Notes

- Keep `.env` local only. Never commit secrets.
- Set `CORS_ALLOWED_ORIGINS` in `.env` as a comma-separated allowlist.
- Do not use wildcard CORS origins outside local development.

## Security Notes

- Do not use default or seeded credentials in shared/staging/production environments.
- Rotate local credentials and keys when environments are cloned.
- See `SECURITY.md` for reporting and hardening guidance.

## Users

Seeders may create local development users depending on your setup. Update any seeded/default passwords immediately in non-local environments.
