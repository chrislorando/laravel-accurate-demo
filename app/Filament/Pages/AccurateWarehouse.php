<?php

namespace App\Filament\Pages;

use ChrisLorando\LaravelAccurate\Facades\Accurate;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use UnitEnum;

class AccurateWarehouse extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Accurate';

    protected static ?string $title = 'Gudang';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.accurate-warehouse';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Gudang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pic')
                    ->label('PIC')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),

                TextColumn::make('address.street')
                    ->label('Alamat')
                    ->limit(50),

            ])
            ->emptyStateHeading('Belum ada koneksi Accurate')
            ->emptyStateDescription('Hubungkan akun Accurate terlebih dahulu melalui OAuth.')
            ->emptyStateIcon(Heroicon::Link)
            ->headerActions([])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('edit')
                    ->icon(Heroicon::PencilSquare)
                    ->modalHeading(fn (array $record): string => 'Edit: '.($record['name'] ?? 'Gudang'))
                    ->fillForm(fn (array $record): array => $this->fetchWarehouseDetail($record['id']))
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Gudang')
                                            ->required()
                                            ->maxLength(200),
                                        TextInput::make('pic')
                                            ->label('PIC')
                                            ->maxLength(100),
                                        Checkbox::make('scrapWarehouse')
                                            ->label('Gudang Scrap'),
                                        Textarea::make('street')
                                            ->label('Alamat')
                                            ->columnSpanFull(),
                                        Textarea::make('description')
                                            ->label('Deskripsi')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, array $record): void {
                        $this->saveWarehouse($data, $record['id']);
                    }),

                Action::make('delete')
                    ->color('danger')
                    ->icon(Heroicon::Trash)
                    ->modalHeading(fn (array $record): string => 'Hapus: '.($record['name'] ?? 'Gudang'))
                    ->modalDescription('Gudang yang sudah dihapus tidak dapat dikembalikan.')
                    ->modalSubmitActionLabel('Hapus')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        $this->deleteWarehouse($record['id'], $record['name'] ?? '');
                    }),
            ])
            ->toolbarActions([
                Action::make('create')
                    ->label('New')
                    ->icon(Heroicon::Plus)
                    ->modalHeading('Tambah Data Baru')
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Gudang')
                                            ->required()
                                            ->maxLength(200),
                                        TextInput::make('pic')
                                            ->label('PIC')
                                            ->maxLength(100),
                                        Checkbox::make('scrapWarehouse')
                                            ->label('Gudang Scrap'),
                                        Textarea::make('street')
                                            ->label('Alamat')
                                            ->columnSpanFull(),
                                        Textarea::make('description')
                                            ->label('Deskripsi')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        $this->saveWarehouse($data);
                    }),
            ])
            ->records(function (?string $search, int $page, int $recordsPerPage): LengthAwarePaginator {
                return $this->fetchWarehouses(
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $this->getTableSortColumn(),
                    sortDirection: $this->getTableSortDirection(),
                );
            });
    }

    protected function fetchWarehouses(
        int $page,
        int $recordsPerPage,
        ?string $search = null,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
    ): LengthAwarePaginator {
        $db = Accurate::currentDatabase();

        if (! $db) {
            return $this->emptyPaginator($page, $recordsPerPage);
        }

        try {
            $query = Accurate::warehouses()
                ->query()
                ->select('id', 'name', 'pic', 'description', 'street');

            if (filled($search)) {
                $query->where('name', 'like', $search);
            }

            if (filled($sortColumn) && filled($sortDirection)) {
                $query->orderBy($sortColumn, $sortDirection);
            } else {
                $query->orderBy('name', 'asc');
            }

            $result = $query
                ->limit($recordsPerPage)
                ->page($page)
                ->paginate();

            $warehouses = collect($result['data'] ?? []);
            $total = (int) ($result['sp']['total'] ?? 0);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                items: $warehouses,
                total: $total,
                perPage: $recordsPerPage,
                currentPage: $page,
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->emptyPaginator($page, $recordsPerPage);
        }
    }

    protected function fetchWarehouseDetail(string $id): array
    {
        $db = Accurate::currentDatabase();

        if (! $db) {
            return [];
        }

        try {
            $result = Accurate::warehouses()->detail($id);

            return $result['d'] ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    protected function saveWarehouse(array $data, ?string $id = null): void
    {
        $db = Accurate::currentDatabase();

        if (! $db) {
            Notification::make()
                ->title('Tidak ada koneksi Accurate')
                ->danger()
                ->send();

            return;
        }

        if ($id !== null) {
            $data['id'] = $id;
        }

        try {
            Accurate::warehouses()->save($data);

            Notification::make()
                ->title($id ? 'Gudang berhasil diupdate' : 'Gudang berhasil dibuat')
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menyimpan gudang')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function deleteWarehouse(string $id, string $name): void
    {
        $db = Accurate::currentDatabase();

        if (! $db) {
            Notification::make()
                ->title('Tidak ada koneksi Accurate')
                ->danger()
                ->send();

            return;
        }

        try {
            Accurate::warehouses()->delete($id);

            Notification::make()
                ->title("Gudang \"{$name}\" berhasil dihapus")
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menghapus gudang')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function emptyPaginator(int $page, int $recordsPerPage): LengthAwarePaginator
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            items: collect(),
            total: 0,
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }
}
