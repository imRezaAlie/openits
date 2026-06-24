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
7. **Open a pull request** — Describe what changed, why, and how to verify it. Link related issues. Sign off your commits (see [Developer Certificate of Origin](#developer-certificate-of-origin-dco)).

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold it. Report violations to **rezaalie70@gmail.com**.

## Developer Certificate of Origin (DCO)

By contributing to OpenITS, you certify that your contribution is your own work and that you have the right to submit it under the project license, as described in the [Developer Certificate of Origin Version 1.1](https://developercertificate.org/).

To indicate acceptance of the DCO, add a `Signed-off-by` line to every commit:

```text
Signed-off-by: Your Name <your.email@example.com>
```

Use Git's sign-off flag when committing:

```bash
git commit -s -m "Your commit message"
```

Pull requests without proper sign-off on non-trivial commits may be asked to amend before merge.

## Bug reports and feature requests

- **Bugs:** Use the [bug report template](.github/ISSUE_TEMPLATE/bug_report.md) via [GitHub Issues](https://github.com/imRezaAlie/openits/issues/new/choose).
- **Features:** Use the [feature request template](.github/ISSUE_TEMPLATE/feature_request.md).
- **Security vulnerabilities:** Do **not** open a public issue. Follow [SECURITY.md](SECURITY.md).

### Response policy

Maintainers aim to acknowledge new issues within **5 business days**. Critical security reports are prioritized per [SECURITY.md](SECURITY.md). Pull requests are reviewed as capacity allows; smaller, focused PRs are merged faster.

## Development setup

Quick install for contributors using the standard **PHP/Laravel** workflow (`git clone` + `composer install`):

```bash
git clone https://github.com/imRezaAlie/openits.git
cd openits
composer install              # PHP dependencies + dev tools (PHPUnit, Pint)
cp .env.example .env
php artisan key:generate
php artisan migrate --seed    # database for local app and automated tests
composer test                 # run full PHPUnit suite (verify your environment)
composer lint                 # check PSR-12 style (optional before first change)
php artisan serve             # optional: web UI at http://localhost:8000
```

### Test environment

- **Run tests:** `composer test` (runs `php artisan test` / PHPUnit).
- **CI:** the [Tests workflow](.github/workflows/tests.yml) runs the full suite on every push and pull request to `main` and reports pass/fail in GitHub Actions.
- **Configuration:** [phpunit.xml](phpunit.xml) sets `APP_ENV=testing` and array/sync drivers for cache, mail, queue, and sessions during tests.
- **Database:** Feature and unit tests use Laravel `RefreshDatabase` against the database in your `.env`. After `migrate --seed`, the same database supports both local development and `composer test`.

**SQLite (fastest local setup):** create `database/database.sqlite`, then in `.env` set `DB_CONNECTION=sqlite` and comment out MySQL `DB_HOST` / `DB_DATABASE` lines. Run `php artisan migrate --seed` again.

Optional frontend asset build:

```bash
npm install
npm run build
```

See [README.md](README.md#quick-start) for full installation, configuration, and [Build & test](README.md#build--test) for additional test and lint commands.

## Coding standards

OpenITS is a **Laravel 12** PHP application. Follow these conventions:

### PHP

- Target **PHP 8.2+** features where appropriate (typed properties, return types, constructor promotion).
- **Avoid deprecated or obsolete APIs** when FLOSS alternatives exist in our stack: require PHP **8.2+** and Laravel **12** (see [README Requirements](README.md#requirements)); use Eloquent, Form Requests, and Laravel facades instead of removed PHP extensions (e.g. `mysql_*`, `mcrypt`) or legacy Laravel patterns; prefer `random_bytes()` / `Hash` over MD5/SHA-1 for security-sensitive code (see [SECURITY.md](SECURITY.md)).
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style. We enforce this with [Laravel Pint](https://laravel.com/docs/pint) (configuration: [pint.json](pint.json)):
  ```bash
  composer lint:fix   # auto-fix style issues
  composer lint       # check only (CI-friendly)
  ```
- **Automatic enforcement:** the [Lint workflow](.github/workflows/lint.yml) runs `composer lint` on every push and pull request to `main`.
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

- [ ] Commits are signed off (`git commit -s`) per the [DCO](#developer-certificate-of-origin-dco)
- [ ] Code follows PSR-12 (`composer lint` passes)
- [ ] Tests pass (`composer test`)
- [ ] New features include tests
- [ ] Documentation updated where applicable
- [ ] No secrets or environment-specific values committed
- [ ] PR description explains the change and links issues

## License

By contributing, you agree that your contributions will be licensed under the [Apache License 2.0](LICENSE).
