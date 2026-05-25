# Environment variables for SMF / Breeze
export DB_HOST := 127.0.0.1
export DB_PORT := 3306
export DB_DATABASE := breeze_test
export DB_USERNAME := root
export DB_PASSWORD := root
export DB_PREFIX := smf_

VENDOR_DIR := breezeVendor
SYNC_SCRIPT := ./brp.sh
PACKAGE_SCRIPT := ./generate_zip.sh

.PHONY: all install test setup-test-database lint coverage ui-install ui-test e2e-up e2e-test e2e-down e2e clean sync package

all: install test ui-test e2e

sync: lint
	@echo "Sync with local SMF env"
	@sh $(SYNC_SCRIPT)

install:
	@echo "Checking PHP version..."
	@php -v | grep "PHP 8.3" || (echo "Error: PHP 8.3 is required" && exit 1)
	@echo "Installing composer dependencies into $(VENDOR_DIR)..."
	@composer install --no-progress --prefer-dist

setup-test-database:
	@echo "Setting up SMF test database..."
	@php tests/setup-test-database.php

test: setup-test-database
	@echo "Running PHPUnit tests..."
	@composer test

lint:
	@echo "Running PHP-CS-Fixer auto-fix..."
	@composer lint
	@echo "Running UI linter..."
	@npm run lint

coverage:
	@echo "Generating HTML coverage report..."
	@composer coverage

ui-install:
	@echo "Installing node dependencies..."
	@if [ -f package-lock.json ]; then npm ci; else npm install; fi

ui-test:
	@echo "Running UI tests..."
	@npm run test:run

e2e-up:
	@echo "Starting E2E services..."
	@docker compose -f docker-compose.e2e.yml up -d --build --wait db api app

e2e-test: e2e-up
	@echo "Running E2E Playwright tests..."
	@docker compose -f docker-compose.e2e.yml run --rm e2e

e2e-down:
	@echo "Tearing down E2E services..."
	@docker compose -f docker-compose.e2e.yml down -v

e2e: e2e-test e2e-down

package: lint
	@echo "Generating SMF package..."
	@chmod +x $(PACKAGE_SCRIPT)
	@$(PACKAGE_SCRIPT)

clean:
	@echo "Cleaning up $(VENDOR_DIR) and node_modules..."
	rm -rf $(VENDOR_DIR) node_modules
