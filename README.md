# Trivio Dashboard

Trivio Dashboard is the administration dashboard for [Trivio](https://nuxt-trivia-game.vercel.app/), a trivia game. It is intended to give the game owner a place to manage players and scores and understand how people are playing.

## Planned capabilities

- Browse and manage player accounts.
- Review scores and player rankings.
- Explore game activity and statistics, including the most-played categories and most-active players.
- Track activity and score trends over time.

These are the project's goals, not all currently implemented features. The app is currently an early Laravel starter with authentication, profile pages, and a basic dashboard screen. Game data integration and the Trivio-specific management and analytics features have not been implemented yet.

## Tech stack

- [Laravel 13](https://laravel.com/) and PHP 8.3+
- [Vue 3](https://vuejs.org/) with TypeScript
- [Inertia.js](https://inertiajs.com/) for the Laravel/Vue application bridge
- [Vite](https://vite.dev/) for frontend development and builds
- SQLite by default, configurable through Laravel's database settings

## Getting started

### Requirements

- PHP 8.3+
- Composer
- Node.js and npm

### Setup

From the project directory, install PHP dependencies, create the local environment file, and generate an application key:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

The project defaults to SQLite. Ensure the database file exists, then run migrations:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

Install frontend dependencies and start the application and Vite development server:

```bash
npm install
composer run dev
```

The app is available at [http://localhost:8000](http://localhost:8000). To create a production frontend build, run:

```bash
npm run build
```

## Testing

Run the Laravel test suite with:

```bash
composer test
```

## Project status

This repository contains the dashboard foundation; it does not currently connect to the Trivio game or store game scores and categories. A game-data integration will be needed before the planned management and statistics can display live data.
