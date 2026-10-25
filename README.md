# Travel Photo Journal

A small Laravel API + Blade UI where a registered user can upload travel photos, keep their
location and description, and get an AI-generated summary for each photo.

- **Live URL:** [https://test-journal.duckdns.org](https://test-journal.duckdns.org)
- **Try it:** register with your own email on the live site, verify via the emailed link, then log in.
- **Repository:** conventional commits, one commit per implementation step.

## Stack

| Piece | Why |
|---|---|
| Laravel 13 (PHP 8.4) | batteries-included HTTP, validation, ORM, mail, testing |
| MySQL | the assessment database; simple relational ownership |
| `tymon/jwt-auth` | stateless API tokens with a refresh flow and blacklist |
| `pragmarx/google2fa` + `bacon/bacon-qr-code` | standard TOTP 2FA and a self-contained QR SVG |
| Pest | short, readable feature tests on top of PHPUnit |
| Blade + Tailwind CDN + Alpine.js | a small UI without a build pipeline |

There is no queue, no cron, no admin panel, and no build step.

## Architecture

```
Browser ─ GET /photos ──► routes/web.php ─► PageController ─► Blade (empty shell)
        └ fetch /api/* ─► routes/api.php ─► Api Controller ─► Service ─► Model ─► MySQL
                              FormRequest (validation) · Policy (ownership) · Resource (JSON shape)
                              Services: Auth, EmailVerification, TwoFactor, Photo, ImageStorage, Summary
```

```
app/Http/Controllers/Api/   Auth, EmailVerification, TwoFactor, Photo
app/Http/Controllers/Web/   PageController (views only)
app/Http/Requests/          Auth, TwoFactor, Photo validation
app/Http/Resources/         UserResource, PhotoResource
app/Http/Middleware/        EnsureEmailVerified, ForceJsonResponse, SecurityHeaders
app/Models/                 User, Photo, EmailVerificationToken
app/Policies/               PhotoPolicy
app/Services/               AuthService, EmailVerificationService, TwoFactorService,
                            PhotoService, ImageStorageService, SummaryService
app/Notifications/          VerifyEmailNotification
app/Support/                ApiResponse
app/Exceptions/             two-factor, unverified-email and bad-token exceptions
resources/views/            layouts, partials, auth, photos, settings
public/assets/js/           lib (api, auth store, guard) and page scripts
public/uploads/photos/      uploaded images (PHP execution blocked by .htaccess)
```

Dependency direction is route → controller → service → model. Controllers only validate, call one
service and shape the response. Services never touch `request()` / `response()`.

## Local setup

Requirements: PHP 8.4+, Composer, MySQL, and the usual Laravel PHP extensions
(`pdo_mysql`, `fileinfo`, `gd`, `mbstring`, `openssl`).

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# fill DB_* and (optionally) OPENROUTER_* in .env

php artisan migrate --seed
php artisan serve
```

Open `http://localhost:8000`. `MAIL_MAILER=log` writes the verification email (including the link)
to `storage/logs/laravel.log`, which is convenient locally. Use SMTP in production.

The `public/uploads/photos` directory must be writable by the web server. The included
`.htaccess` denies PHP execution in that directory.

## Environment variables

| Variable | Purpose |
|---|---|
| `APP_URL` | public URL, also used for image URLs and verification links |
| `DB_*` | MySQL connection |
| `JWT_SECRET`, `JWT_TTL` (minutes, default 15), `JWT_REFRESH_TTL` (minutes, default 20160 = 14 days) | token signing and lifetimes |
| `JWT_BLACKLIST_ENABLED` (true), `JWT_BLACKLIST_GRACE_PERIOD` (seconds, default 30) | token invalidation on refresh/logout |
| `MAIL_*` | verification mail (log locally, Brevo/SMTP in production) |
| `OPENROUTER_API_KEY`, `OPENROUTER_MODEL` | AI summary provider and model |
| `UPLOAD_MAX_KB` | image size limit (default 5120) |

## API

Every response uses one envelope:

```json
{ "success": true, "message": "OK", "data": {} }
{ "success": false, "message": "Validation failed.", "errors": { "field": ["..."] } }
```

| Method | Path | Middleware | Success | Errors |
|---|---|---|---|---|
| POST | `/api/auth/register` | `throttle:10,60` | 201 | 422 |
| POST | `/api/auth/verify-email` | `throttle:10,1` | 200 | 422 |
| POST | `/api/auth/resend-verification` | `throttle:3,1` | 200 | 422 |
| POST | `/api/auth/login` | `throttle:login` (5/min per email+IP) | 200 token | 401, 403, 422 |
| POST | `/api/auth/refresh` | `throttle:30,1` | 200 token | 401 |
| POST | `/api/auth/logout` | `auth:api` | 200 | 401 |
| POST | `/api/auth/2fa/setup` | `auth:api`, `verified.email` | 200 secret + `qr_svg` | 401, 403, 409 |
| POST | `/api/auth/2fa/verify` | `auth:api`, `verified.email`, `throttle:5,1` | 200 | 422 |
| GET | `/api/photos` | `auth:api`, `verified.email` | 200 paginated (10) | 401, 403 |
| POST | `/api/photos` | same | 201 | 422 |
| GET | `/api/photos/{id}` | same | 200 | 404 |
| PUT | `/api/photos/{id}` | same | 200 | 404, 422 |
| DELETE | `/api/photos/{id}` | same | 200 | 404 |

Login returns:

```json
{
  "success": true,
  "message": "Logged in.",
  "data": {
    "access_token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 900,
    "user": { "id": 1, "name": "Demo User", "email": "demo@example.com", "two_factor_enabled": false }
  }
}
```

The photo list returns `data.items` plus `data.meta` (`current_page`, `per_page`, `total`,
`last_page`). A photo looks like:

```json
{
  "id": 3,
  "name": "Sunset over the bay",
  "location": "Goa, India",
  "description": "A warm evening on the beach.",
  "summary": "The author describes a warm evening by the sea. The sky and water set a calm mood.",
  "image_url": "https://example.com/uploads/photos/8f12....jpg",
  "image_original_name": "sunset.jpg",
  "created_at": "2026-10-24T10:00:00+00:00",
  "updated_at": "2026-10-24T10:00:00+00:00"
}
```

If 2FA is active, login without `otp` returns 401 with `"two_factor_required": true`; a wrong
code returns 422 with `errors.otp`; a correct code returns the token.

## Design decisions

- **Another user's photo is a 404, not a 403.** Every lookup goes through
  `$user->photos()->findOrFail($id)`, so ids outside the authenticated user's scope simply do not
  exist. `PhotoPolicy` is a second, explicit ownership check.
- **Verification tokens are stored hashed.** A random 64-character token is emailed; only its
  SHA-256 hash is stored. Tokens expire after 60 minutes, are deleted when reissued, are compared
  with `hash_equals`, and are single-use.
- **AI summary is synchronous and fail-safe.** The summary must be present in the POST response,
  so no queue or `defer()` is used. The OpenRouter call has a 30-second timeout; every failure is
  caught and the photo is still saved with `summary = null`. Only the error message is logged —
  never the description. Descriptions are truncated to 2000 characters before sending and stored
  summaries to 500. The system prompt tells the model to ignore instructions inside the text, and
  the user text is sent as a separate user message. A changed description regenerates the summary.
- **Sliding refresh with one token.** The access token lives 15 minutes; the refresh window is 14
  days. `/api/auth/refresh` is intentionally not behind `auth:api`, so an expired access token can
  still be exchanged inside the window; the old token is blacklisted. This is *not* a separate
  refresh token; it is a single token with a sliding window. The 30-second blacklist grace period
  avoids breaking concurrent requests. Logout invalidates forever.
- **JWT is kept in `localStorage`.** This keeps the Blade UI simple, but any XSS would expose the
  token. The next step would be an httpOnly cookie (with CSRF protection) or a short-lived token
  plus a server-side session.
- **Web and API are separate.** Web routes only return views; every page then talks to `/api`.
  The API never renders HTML, and the Blade pages contain no database queries.
- **2FA returns 401 for "code required" and 422 for "wrong code".** Missing credentials are an
  authentication challenge; a submitted but incorrect code is a validation error on `otp`.
- **Login is rate-limited per email and IP** (5/min) to slow credential stuffing without locking
  out a whole network. Registration, verification, resend and 2FA verification have their own
  limits.
- **Uploads are renamed to UUIDs and PHP execution is blocked.** The original name is stored only
  in the database. The uploads directory has `Options -Indexes` and a `FilesMatch` rule denying
  `.php`, `.phtml`, `.phar`, and versioned PHP files (no `php_flag`, so PHP-FPM is unaffected).
- **Secrets stay in casts and config.** Passwords are hashed by the `hashed` cast, the TOTP secret
  uses the `encrypted` cast and is hidden from JSON, and no token, password or OTP is logged.

## Deployment

The app is designed for shared hosting (document root `public/`, PHP 8.4, separate MySQL database
and user, SSH, no cron):

```sh
git clone <repo> && cd travel-photo-journal
composer install --no-dev -o
cp .env.example .env
# APP_ENV=production, APP_DEBUG=false, APP_URL=https://<subdomain>, DB_*, MAIL_*, OPENROUTER_*
php artisan key:generate
php artisan jwt:secret
php artisan migrate --force --seed
php artisan config:cache
php artisan route:cache
```

Make `storage`, `bootstrap/cache` and `public/uploads/photos` writable (755/775). Configure mail
with Brevo SMTP (`smtp-relay.brevo.com`, port 587 with TLS, or 2525 when 587 is blocked). Confirm
that a `.php` file placed in the uploads directory does not execute, then delete it. Keep
`APP_DEBUG=false` and check the full flow online: register → email → verify → login → 2FA setup →
logout → login with OTP → upload → summary → edit → delete.

## Docker

```sh
cp .env.example .env
# fill APP_KEY (or run `php artisan key:generate`), JWT_SECRET, DB credentials, OPENROUTER_*
docker compose up -d --build
docker compose exec app php artisan migrate --seed --force
```

The app is then available at `http://localhost:8080`; MySQL is a separate `mysql` service with a
persistent volume.

The Docker setup is ultra-clean, minimalist, and self-contained:
- **`Dockerfile`**: Official PHP 8.4 CLI Bookworm container with PDO MySQL, GD (JPEG/WebP), Zip, Composer, and runtime storage permission assurance.
- **`docker-compose.yml`**: Orchestrates the Laravel application and MySQL 8.0 service with a built-in healthcheck (`mysqladmin ping`) to ensure zero race conditions before booting the web server.

## Running tests

```sh
php artisan test
```

The suite covers registration and verification (valid, wrong, expired, reused, resend), login and
token refresh/logout, photo CRUD and file cleanup, cross-user ownership (404), AI summary success,
failure, timeout, regeneration and truncation, 2FA setup/verify/login, response envelopes and
security headers. Tests use an in-memory SQLite database, `Http::fake()`, `Notification::fake()`
and fake uploads; no external service is called.

## With more time

- Queue or `defer()` the AI summary with a `summary_status` column and polling/streaming in the UI.
- Database-backed `token_version` in user claims for instant, cache-flush-resilient session invalidation.
- 2FA recovery backup codes and a "trust this device" option.
- Image resizing/thumbnails, EXIF metadata stripping, and S3/Cloudinary storage.
- CI pipeline with Pint code style, PHPStan static analysis, and the Pest test suite on every push.
- Better pagination UX and optimistic UI for deletes.
