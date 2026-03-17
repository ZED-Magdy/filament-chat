# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

`zedmagdy/filament-chat` is a Laravel filamentphp package that provides chat interfaces as a Filament v4 plugin. it supports polling and reverb/pusher.

- **Namespace:** `ZEDMagdy\FilamentChat`
- **PHP:** ^8.3
- **Filament:** ^4.3.1 || ^5.0
- **Laravel:** ^11.0 || ^12.0

## Commands

```bash
# Run tests
composer test                  # or: vendor/bin/pest

# Run a single test
vendor/bin/pest --filter=testName

# Format code
composer format                # or: vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty

# Static analysis
composer analyse               # or: vendor/bin/phpstan analyse

# Test with coverage
composer test-coverage
```

## Architecture

This is a Filament Plugin package built with [spatie/laravel-package-tools](https://github.com/spatie/laravel-package-tools).

### Key entry points

- `src/FilamentChatServiceProvider.php` — Implements `Filament\Contracts\Plugin`. Registers resources and pages with a Filament panel. This is the plugin class users register via `->plugin(FilamentChatServiceProvider::make())`.
- `src/FilamentChat.php` — Main package facade/class (currently empty scaffold).
- `config/filament-chat.php` — Package configuration (publishable).
- `database/migrations/` — Migration stubs (`.php.stub`) published to the host app.

### Testing

- Uses **Pest v4** with **Orchestra Testbench** for package testing.
- `tests/TestCase.php` — Base test case that boots the service provider and configures factory resolution for `ZEDMagdy\FilamentChat\Database\Factories\*`.
- `tests/Pest.php` — Binds `TestCase` to all tests in the `tests/` directory.
- Workbench app scaffolding available at `workbench/` for integration testing.

### Package conventions

- Migrations are stubs (`.php.stub`) — they get published to the consuming app, not run directly.
- Config tag: `filament-chat-config`
- Migration tag: `filament-chat-migrations`
- Views tag: `filament-chat-views`
