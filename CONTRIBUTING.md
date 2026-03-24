# Contributing Guide

## Development Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Before Opening a PR

Run the quality checks locally:

```bash
vendor/bin/pint --test
composer validate --strict
```

## Coding Expectations

- Keep changes scoped and incremental.
- Preserve existing architecture and conventions.
- Avoid introducing new dependencies or frameworks without approval.
- Update documentation when setup or behavior changes.

## Pull Request Notes

- Describe what changed and why.
- Include testing/verification steps.
- Highlight any assumptions or known limitations.
