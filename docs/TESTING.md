# Breeze Testing Guide

Breeze uses three layers of testing to ensure quality across both backend and frontend code.

| Layer | Framework | Location | Requires DB | CI Workflow |
|-------|-----------|----------|-------------|-------------|
| PHP Unit / Integration | PHPUnit 10 | `tests/` | No (mocks) | `.github/workflows/php.yml` |
| React Component | Vitest + Testing Library | `src/**/*.test.tsx` | No | `.github/workflows/ui.yml` |
| End-to-End (E2E) | Playwright | `e2e/tests/` | Docker | `.github/workflows/e2e.yml` |

---

## 1. PHP Tests (PHPUnit)

### Prerequisites

- PHP 8.3+
- Composer dependencies installed (`composer install`)

### Running Tests

```bash
# Run all PHP tests (lint check + PHPUnit + PHPStan)
composer test

# Run PHPUnit only
composer phpunit

# Run a specific test suite
./breezeVendor/bin/phpunit tests/Repository
./breezeVendor/bin/phpunit tests/Service
./breezeVendor/bin/phpunit tests/Controller

# Run integration tests
./breezeVendor/bin/phpunit tests/Integration

# Generate HTML coverage report
composer coverage
# Output: tests/log/coverage/
```

### Using Make

```bash
make install        # Install PHP dependencies
make test           # Setup test DB + run all tests
```

### Test Structure

Tests are organized by architectural layer:

```text
tests/
├── Controller/     # Controller tests
├── Entity/         # Entity tests
├── Event/          # Event handler tests
├── Integration/    # Integration tests (mocked services)
├── Repository/     # Repository layer tests
├── Service/        # Service layer tests
├── Traits/         # Trait tests
├── Util/           # Utility tests
├── Validate/       # Validation tests
└── bootstrap.php   # PHPUnit bootstrap
```

All unit tests use mocks and stubs — **no database required**.

### Static Analysis

```bash
# PHPStan (level configured in phpstan.neon)
composer phpstan

# PHP-CS-Fixer lint check
composer lint:check

# PHP-CS-Fixer auto-fix
composer lint
```

---

## 2. React Component Tests (Vitest)

### Prerequisites

- Node.js 22+
- npm dependencies installed (`npm ci`)

### Running Tests

```bash
# Run tests in watch mode
npm test

# Run once (CI mode)
npm run test:run

# Run with interactive UI
npm run test:ui
```

### Using Make

```bash
make ui-install     # Install npm dependencies
make ui-test        # Run Vitest
```

### Test Structure

Component tests are co-located with their source files:

```text
src/
├── Wall.test.tsx
├── components/
│   ├── Editor.test.tsx
│   ├── Like.test.tsx
│   ├── Tabs.test.tsx
│   └── ...
└── __fixtures__/   # Shared test fixtures
    ├── permissions.ts
    ├── statusData.ts
    └── userData.ts
```

### Configuration

- **Config:** `vitest.config.ts`
- **Setup:** `vitest.setup.ts` (loads `@testing-library/jest-dom/vitest`)
- **Environment:** jsdom
- **Linting:** Biome (`biome.json`) — do **not** use ESLint or Prettier

---

## 3. End-to-End Tests (Playwright + Docker)

E2E tests run the full app stack inside Docker containers using a mock PHP API.

### Prerequisites

- Docker and Docker Compose

### Architecture

The `docker-compose.e2e.yml` file orchestrates four services:

- **db** — MySQL 8.0 (host port 3307)
- **api** — PHP mock server (host port 8080)
- **app** — Vite dev server (host port 3001)
- **e2e** — Playwright runner (runs tests then exits)

The mock API (`e2e/api/`) returns fixture data matching the real Breeze API
contract so tests run without a full SMF installation.

### Running E2E Tests Locally

#### 1. Start the stack

Build and start the database, mock API, and Vite dev server.
The `--wait` flag blocks until every container reports healthy.

```bash
docker compose -f docker-compose.e2e.yml up -d --build --wait db api app
```

> **First run** pulls the MySQL and Node images, so it may take a few minutes.
> Subsequent runs reuse cached layers and start in ~15 seconds.

#### 2. Run the tests

```bash
# Run all E2E tests
docker compose -f docker-compose.e2e.yml run --rm e2e

# Run a single test file
docker compose -f docker-compose.e2e.yml run --rm e2e \
  npx playwright test tests/wall.spec.ts --config=playwright.config.ts

# Run tests matching a grep pattern
docker compose -f docker-compose.e2e.yml run --rm e2e \
  npx playwright test -g "displays mock statuses" --config=playwright.config.ts
```

Because `e2e/tests/` is volume-mounted, you can edit or add test files
without rebuilding the container — just re-run step 2.

#### 3. View the HTML report

After a run, Playwright writes an HTML report to `e2e/playwright-report/`
(volume-mounted to the host). Open it in your browser:

```bash
open e2e/playwright-report/index.html      # macOS
xdg-open e2e/playwright-report/index.html  # Linux
```

Failure screenshots are saved to `e2e/test-results/`.

#### 4. Tear down

```bash
# Stop containers and remove volumes
docker compose -f docker-compose.e2e.yml down -v
```

#### Quick reference (copy-paste)

```bash
# Full cycle: start → test → stop
docker compose -f docker-compose.e2e.yml up -d --build --wait db api app
docker compose -f docker-compose.e2e.yml run --rm e2e
docker compose -f docker-compose.e2e.yml down -v
```

#### Troubleshooting

| Problem | Fix |
|---------|-----|
| `app` container not healthy | Check `docker compose -f docker-compose.e2e.yml logs app` — usually a port conflict on 3001 |
| Tests time out waiting for statuses | Verify the mock API is running: `curl http://localhost:8080/index.php?action=breezeStatus&sa=wall` |
| `net::ERR_CONNECTION_REFUSED` in tests | Make sure `VITE_APP_DEV_URL` in `docker-compose.e2e.yml` points to `http://api:8000/index.php` (Docker internal hostname) |
| Stale containers from a previous run | Run `docker compose -f docker-compose.e2e.yml down -v` before starting again |

#### Running with a visible browser (headed / debug)

For local visual debugging, run Playwright **on the host** instead of inside
Docker. The host browser hits the Docker services through port-mapped `localhost`.

> **Note:** The headless Docker runner uses Docker-internal hostnames
> (`http://api:8000`). When running locally, the browser needs `localhost`
> URLs instead. Override `VITE_APP_DEV_URL` on the app container for this.

**One-time setup:**

```bash
cd e2e
npm init -y
npm install @playwright/test
npx playwright install chromium
cd ..
```

**Start the stack with localhost API URL:**

```bash
VITE_APP_DEV_URL=http://localhost:8080/index.php \
  docker compose -f docker-compose.e2e.yml up -d --build --wait db api app
```

**Run tests with a visible browser:**

```bash
cd e2e

# Headed mode — opens a Chromium window
npx playwright test tests/wall.spec.ts --headed --config=playwright.config.ts

# UI mode — interactive panel with timeline, DOM snapshot, step-by-step
npx playwright test --ui --config=playwright.config.ts

# Debug mode — pauses at each step with Playwright Inspector
npx playwright test tests/wall.spec.ts --debug --config=playwright.config.ts
```

> `baseURL` defaults to `http://localhost:3001` and the API override maps
> to `http://localhost:8080` — both are host-mapped Docker ports.

### Writing Tests

Place test files in `e2e/tests/`. The directory is volume-mounted so you
don't need to rebuild the container when adding or editing tests.

```typescript
// e2e/tests/wall.spec.ts
import { test, expect } from '@playwright/test';

test('wall loads and displays statuses', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('.status')).toHaveCount(3);
});
```

### Configuration

- **Playwright config:** `e2e/playwright.config.ts`
- **Dockerfile:** `e2e/Dockerfile` (based on `mcr.microsoft.com/playwright`)
- **Base URL (Docker):** `http://app:3000` · **Base URL (local):** `http://localhost:3001`
- **Reports:** `e2e/playwright-report/` (volume-mounted to host)
- **Screenshots on failure:** `e2e/test-results/`

### Mock API

The mock API (`e2e/api/`) handles the same endpoints as the real Breeze backend:

| Action | Sub-action | What it returns |
|--------|-----------|-----------------|
| `breezeStatus` | `profile` / `wall` | 3 mock statuses with comments |
| `breezeStatus` | `postStatus` | New status (201) |
| `breezeStatus` | `deleteStatus` | Empty (204) |
| `breezeComment` | `postComment` | New comment (201) |
| `breezeComment` | `deleteComment` | Empty (204) |
| `breezeLike` | `like` | Like info (201) |

---

## 4. CI / GitHub Actions

| Workflow | File | Trigger | What it runs |
|----------|------|---------|-------------|
| PHP | `.github/workflows/php.yml` | push, PR | PHPUnit + PHPStan + lint |
| UI | `.github/workflows/ui.yml` | push, PR | Vitest component tests |
| E2E | `.github/workflows/e2e.yml` | push, PR | Docker Compose + Playwright |

### Running Everything Locally

```bash
# All PHP checks
composer test

# All React tests
npm run test:run

# All E2E tests
docker compose -f docker-compose.e2e.yml up -d --build --wait db api app
docker compose -f docker-compose.e2e.yml run --rm e2e
docker compose -f docker-compose.e2e.yml down -v
```
