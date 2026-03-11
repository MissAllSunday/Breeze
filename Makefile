# Environment variables for SMF / Breeze
export DB_HOST := 127.0.0.1
export DB_PORT := 3306
export DB_DATABASE := breeze_test
export DB_USERNAME := root
export DB_PASSWORD := root
export DB_PREFIX := smf_

VENDOR_DIR := breezeVendor

.PHONY: all install test setup-test-database lint ui-install ui-test clean

all: install test ui-test

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
	@echo "Running static analysis..."
	@composer lint:check

ui-install:
	@echo "Installing node dependencies..."
	@if [ -f package-lock.json ]; then npm ci; else npm install; fi

ui-test:
	@echo "Running UI tests..."
	@npm run test:run

clean:
	@echo "Cleaning up $(VENDOR_DIR) and node_modules..."
	rm -rf $(VENDOR_DIR) node_modules
