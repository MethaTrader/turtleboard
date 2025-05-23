# TurtleBoard - MEXC Account Management Dashboard

A Laravel 12 application for managing MEXC cryptocurrency exchange accounts, email accounts, proxies, and Web3 wallets.

## Features

- User authentication with Google OAuth
- MEXC account management
- Email account management
- Proxy validation and management
- Web3 wallet integration
- Interactive relationship visualization
- Team collaboration features

## Installation

1. Clone the repository
2. Copy `.env.example` to `.env`
3. Run `docker-compose up -d`
4. Generate application key: `docker-compose exec app php artisan key:generate`
5. Run migrations: `docker-compose exec app php artisan migrate`

## Usage

Visit `http://localhost:8080` to access the application.

## Tech Stack

- Laravel 12
- PHP 8.4
- MySQL 8.4
- Nginx
- Docker
- Alpine.js
- Vue.js (for complex components)
- Rete.js (for relationship graphs)