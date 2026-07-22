# Laravel Accurate Demo

A demo / _sandbox_ app for trying out the **[chrislorando/laravel-accurate](https://github.com/chrislorando/laravel-accurate)** package — a Laravel wrapper for the [Accurate](https://accurate.id/) API.

Built with **Laravel 13**, **Filament v5**, and **Livewire v4**.

## Demo Features

- **Dashboard widget** — list of connected Accurate databases, switch active database
- **Items** (`/admin/accurate-items`) — CRUD, search, sort, pagination for Item data via Accurate API
- **Item Categories** (`/admin/accurate-item-categories`) — CRUD, search, sort, pagination for Item Category data via Accurate API
- **Units** (`/admin/accurate-units`) — CRUD, search, sort, pagination for Unit data via Accurate API
- **Warehouses** (`/admin/accurate-warehouses`) — CRUD, search, sort, pagination for Warehouse data via Accurate API

## Installation

```bash
git clone https://github.com/nama-user/laravel-accurate-demo.git
cd laravel-accurate-demo

composer install
cp .env.example .env
php artisan key:generate
```

Configure Accurate credentials in `.env`:

```
ACCURATE_CLIENT_ID=
ACCURATE_CLIENT_SECRET=
ACCURATE_REDIRECT_URI=
```

```bash
npm install && npm run build
php artisan serve
```

Open `/admin` to access the Filament panel.

## Demo

https://personal-projects-laravel-accurate-demo.isqfjy.easypanel.host/admin/login
username : demo@example.com
password : password

## Packages Used

| Package                                                                           | Version  |
| --------------------------------------------------------------------------------- | -------- |
| [chrislorando/laravel-accurate](https://github.com/chrislorando/laravel-accurate) | ^0.5     |
| [laravel/framework](https://laravel.com)                                          | ^13      |
| [filament/filament](https://filamentphp.com)                                      | ^5       |
| [livewire/livewire](https://livewire.laravel.com)                                 | ^4       |
| [laravel/boost](https://laravel.com/docs/ai)                                      | ^2 (dev) |

## Key Structure

```
app/Filament/
├── Pages/
│   ├── AccurateItem.php          # Item CRUD page
│   ├── AccurateItemCategory.php  # Item Category CRUD page
│   ├── AccurateUnit.php          # Unit CRUD page
│   └── AccurateWarehouse.php     # Warehouse CRUD page
└── Widgets/
    └── AccurateDatabasesWidget.php  # Database switcher widget
```

## License

MIT
