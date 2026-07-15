<?php

namespace App\Filament\Widgets;

use ChrisLorando\LaravelAccurate\Facades\Accurate;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class AccurateDatabasesWidget extends Widget
{
    protected string $view = 'filament.widgets.accurate-databases';

    protected int|string|array $columnSpan = 'full';

    public function getColumns(): int | array
    {
        return 1;
    }

    public function getCurrentDatabaseId(): ?string
    {
        return Accurate::currentDatabase()?->database_id;
    }

    public function getDatabases(): array
    {
        try {
            $response = Accurate::connection('default')->databases();

            return $response['d'] ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    public function openDatabase(string $databaseId, string $alias): void
    {
        try {
            Accurate::connection('default')->openDatabase($databaseId, $alias);

            Notification::make()
                ->title('Database dibuka')
                ->body("\"{$alias}\" berhasil dibuka dan siap digunakan.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal membuka database')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
