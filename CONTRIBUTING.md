# Contributing

Thanks for taking a look. Bug reports, ideas, and pull requests are all welcome.

## Local setup

The quickest path is Docker:

```bash
cp .env.example .env
docker compose up
```

Or run it directly with PHP 8.3+ and a database:

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
# in another shell, for AI generation:
php artisan queue:work
```

## Before opening a PR

Run the style check and the tests:

```bash
./vendor/bin/pint        # format
./vendor/bin/pint --test # or just check
php artisan test
```

CI runs Pint and the test suite on every push and pull request.

## Notes

- Tests use an in-memory SQLite database and the mock LLM provider, so they never call a real API. Keep it that way - mock the provider or fake HTTP in tests.
- Adding an LLM provider is one class implementing `App\LLM\Contracts\LlmProvider`, wired in `App\LLM\LlmManager`.
- The SEO scoring lives in `app/Seo`. It has no framework dependencies; add new checks there with a matching test.
- Never commit secrets. API keys come from the environment or per-user settings only.
