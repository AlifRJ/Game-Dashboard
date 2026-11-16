# Trivio Dashboard

A Laravel + Inertia dashboard for monitoring a trivia game platform. It gives game owners a quick view of player engagement, score performance, category trends, and recent activity without leaving the admin interface.

## Overview

Trivio Dashboard connects to the game analytics API when credentials are configured, and automatically falls back to a built-in demo dataset when they are not. This makes local development, demos, and repository previews work smoothly out of the box.

The app focuses on the operational data that matters most to a trivia product team:

- Real-time KPI summaries
- Category popularity trends
- Difficulty distribution
- Score distribution and player performance
- User account management
- Authentication and profile settings

## Features

### Analytics overview

- Executive KPI cards for total games played, active players, and average score
- Popular categories ranking by participation
- Difficulty breakdown for easy, medium, and hard rounds
- Timeline view of games played over time
- Score distribution chart for completed sessions
- Top active players leaderboard

### Product experience

- Built-in authentication and profile management
- Lazy server-side caching for analytics responses
- Rich Chart.js tooltips with contextual data
- Demo fallback mode for local setup and previews
- User and score management screens built into the dashboard

## Tech stack

- [Laravel 13](https://laravel.com/)
- [PHP 8.3+](https://www.php.net/)
- [Inertia.js](https://inertiajs.com/)
- [Vue 3](https://vuejs.org/)
- [TypeScript](https://www.typescriptlang.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Chart.js](https://www.chartjs.org/)
- [Vite](https://vite.dev/)
- SQLite by default, with Laravel-ready support for other databases

## Project structure

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── UserController.php
│   │   └── ScoreController.php
│   └── Middleware/
├── Models/
│   ├── User.php
│   └── Score.php
resources/
├── js/
│   ├── Components/
│   ├── Composables/
│   ├── Data/
│   ├── Layouts/
│   └── Pages/
│       └── Dashboard.vue
routes/
├── auth.php
├── console.php
└── web.php
database/
├── factories/
├── migrations/
└── seeders/
```

## Getting started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js and npm

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure the environment

Create your local environment file and generate the app key:

```bash
cp .env.example .env
php artisan key:generate
```

If you want real analytics from the upstream trivia game, add the external API settings in `.env`:

```dotenv
GAME_API_URL=https://your-game-api.example.com
GAME_API_KEY=your-api-key
```

If those values are not set, the dashboard will automatically use demo analytics so the app still works during local development.

### 3. Run the database

```bash
php artisan migrate
php artisan db:seed
```

### 4. Start the app

```bash
composer run dev
```

This starts Laravel and Vite together. The application is available at:

```text
http://localhost:8000
```

To build assets for production:

```bash
npm run build
```

## Useful commands

```bash
php artisan test
php artisan route:list
php artisan migrate:fresh --seed
```

## Notes

- Dashboard analytics are cached for five minutes to reduce repeated API calls.
- The fallback demo data lives under the frontend data layer and is used automatically when the external API is unavailable or not configured.
- The project is designed to work well for local demos, previews, and real game operations dashboards.
