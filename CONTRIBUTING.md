# Contributing to OpenITS

Thank you for your interest in contributing to OpenITS. This document explains how to get involved, our coding standards, and what we expect from pull requests.

## How to contribute

1. **Check existing work** — Search [GitHub Issues](https://github.com/imRezaAlie/openits/issues) to avoid duplicate effort.
2. **Discuss significant changes** — Open an issue before large features or architectural changes so we can align on approach.
3. **Fork and branch** — Create a feature branch from `main`:
   ```bash
   git checkout -b feature/short-description
   ```
4. **Make your changes** — Follow the coding standards below.
5. **Add tests** — New major functionality should include automated tests (see [Test policy](#test-policy)).
6. **Run the test suite and linter** before submitting:
   ```bash
   composer test
   composer lint
   ```
7. **Open a pull request** — Describe what changed, why, and how to verify it. Link related issues.

## Bug reports and feature requests

- **Bugs:** Use the [bug report template](.github/ISSUE_TEMPLATE/bug_report.md) via [GitHub Issues](https://github.com/imRezaAlie/openits/issues/new/choose).
- **Features:** Use the [feature request template](.github/ISSUE_TEMPLATE/feature_request.md).
- **Security vulnerabilities:** Do **not** open a public issue. Follow [SECURITY.md](SECURITY.md).

### Response policy

Maintainers aim to acknowledge new issues within **5 business days**. Critical security reports are prioritized per [SECURITY.md](SECURITY.md). Pull requests are reviewed as capacity allows; smaller, focused PRs are merged faster.

## Development setup

```bash
git clone https://github.com/imRezaAlie/openits.git
cd openits
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Optional frontend asset build:

```bash
npm install
npm run build
```

See [README.md](README.md) for full installation and configuration details.

## Coding standards

OpenITS is a **Laravel 11** PHP application. Follow these conventions:

### PHP

- Target **PHP 8.2+** features where appropriate (typed properties, return types, constructor promotion).
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style. We enforce this with [Laravel Pint](https://laravel.com/docs/pint):
  ```bash
  composer lint:fix   # auto-fix style issues
  composer lint       # check only (CI-friendly)
  ```
- Use strict typing: `declare(strict_types=1);` is encouraged in new files.
- Place business logic in `app/Services/`; keep controllers thin.
- Use Form Request classes (`app/Http/Requests/`) for validation.
- Use Eloquent models and relationships; avoid raw SQL unless necessary.
- Document non-obvious public service methods with PHPDoc blocks (`@param`, `@return`, `@throws`).
- Never commit secrets, `.env` files, or credentials.

### Blade and frontend

- Use Blade templates in `resources/views/`.
- Keep JavaScript in `public/js/` or `resources/js/`; prefer existing patterns (Alpine.js, D3.js).
- Do not introduce new CSS frameworks; Bootstrap 5 is the standard.

### Database

- Add migrations for schema changes; never edit production databases manually.
- Use seeders/factories for test data.

### Security

- Hash passwords with Laravel's `Hash` facade (bcrypt by default; see `BCRYPT_ROUNDS` in `.env`).
- Use parameterized queries via Eloquent; escape LDAP filter inputs.
- Validate and sanitize all user input.
- Do not disable security middleware or rate limiting without documented justification.

### Git commits

- Write clear, imperative commit messages (e.g., `Add LDAP group filter validation`).
- Keep commits focused; one logical change per commit when possible.

## Test policy

- The project uses **PHPUnit** via Laravel's test runner.
- Run all tests: `composer test` (or `php artisan test`).
- **New major functionality must include tests** — feature tests for HTTP endpoints, unit tests for services.
- Place tests in `tests/Feature/` or `tests/Unit/` mirroring the code under test.
- Tests must pass locally before opening a PR.

## Documentation

- Update [README.md](README.md) for user-facing changes (installation, configuration, usage).
- Update [docs/](docs/) for API, CLI, or public service interface changes.
- All documentation must be written in **English**.

## Pull request checklist

- [ ] Code follows PSR-12 (`composer lint` passes)
- [ ] Tests pass (`composer test`)
- [ ] New features include tests
- [ ] Documentation updated where applicable
- [ ] No secrets or environment-specific values committed
- [ ] PR description explains the change and links issues

## License

By contributing, you agree that your contributions will be licensed under the [Apache License 2.0](LICENSE).
