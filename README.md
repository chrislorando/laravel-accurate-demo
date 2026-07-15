# Laravel Accurate Demo

Aplikasi demo / _sandbox_ untuk mencoba package **[chrislorando/laravel-accurate](https://github.com/chrislorando/laravel-accurate)** — wrapper Laravel untuk [Accurate](https://accurate.id/) API.

Dibangun dengan **Laravel 13**, **Filament v5**, dan **Livewire v4**.

## Fitur Demo

- **Dashboard widget** — daftar database Accurate yang terkoneksi, bisa _switch_ database aktif
- **Item List** (`/admin/accurate-item`) — CRUD, search, sort, pagination data Item dari Accurate API
- **Kategori Item** (`/admin/accurate-item-category`) — CRUD, search, sort, pagination data Kategori Item dari Accurate API

## Instalasi

```bash
git clone https://github.com/nama-user/laravel-accurate-demo.git
cd laravel-accurate-demo

composer install
cp .env.example .env
php artisan key:generate
```

Konfigurasi kredensial Accurate di `.env`:

```
ACCURATE_CLIENT_ID=
ACCURATE_CLIENT_SECRET=
ACCURATE_REDIRECT_URI=
```

```bash
npm install && npm run build
php artisan serve
```

Buka `/admin` untuk mengakses panel Filament.

## Package yang Digunakan

| Package                                                                           | Versi    |
| --------------------------------------------------------------------------------- | -------- |
| [chrislorando/laravel-accurate](https://github.com/chrislorando/laravel-accurate) | ^0.2     |
| [laravel/framework](https://laravel.com)                                          | ^13      |
| [filament/filament](https://filamentphp.com)                                      | ^5       |
| [livewire/livewire](https://livewire.laravel.com)                                 | ^4       |
| [laravel/boost](https://laravel.com/docs/ai)                                      | ^2 (dev) |

## Struktur Penting

```
app/Filament/
├── Pages/
│   ├── AccurateItem.php          # Halaman CRUD Item
│   └── AccurateItemCategory.php  # Halaman CRUD Kategori Item
└── Widgets/
    └── AccurateDatabasesWidget.php  # Widget pilih database
```

## License

MIT
