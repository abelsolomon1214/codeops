# Addis Eats API (`addis-eats-api`)

RESTful backend API for Addis Eats, a food-delivery platform in Addis Ababa. Built with Laravel 11 as part of the CodeOps Module 4 Backend Capstone.

## Tech Stack
- PHP 8.2+ / Laravel 11
- SQLite (local development & testing)
- Eloquent ORM & API Resources

## Installation & Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Create environment file and generate application key
cp .env.example .env
php artisan key:generate

# 3. Create SQLite database file (if not present) and run migrations with seed data
php artisan migrate --seed

# 4. Start the local development server
php artisan serve