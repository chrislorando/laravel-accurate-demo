<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected int|string|array $columnSpan = 'full';
    
    public function getColumns(): int | array
    {
        return 1;
    }
}