<?php

namespace App\Filament\Pages;

use ChrisLorando\LaravelAccurate\Facades\Accurate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use UnitEnum;

class AccurateItem extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string | UnitEnum | null $navigationGroup = 'Accurate';

    protected static ?string $title = 'Barang & Jasa';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.accurate-item';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Item No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit1.name')
                    ->label('Satuan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('itemTypeName')
                    ->label('Tipe')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('unitPrice')
                    ->label('Harga')
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
            ])
            ->emptyStateHeading('Belum ada koneksi Accurate')
            ->emptyStateDescription('Hubungkan akun Accurate terlebih dahulu melalui OAuth.')
            ->emptyStateIcon(Heroicon::Link)
            ->emptyStateActions([
                // Bisa tambah action untuk redirect ke OAuth
            ])
            ->headerActions([])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                 Action::make('view')
                    ->color('gray')
                    ->icon(Heroicon::Eye)
                    ->modalHeading(fn (array $record): string => $record['name'] ?? 'Detail Barang')
                    ->fillForm(fn (array $record): array => $this->fetchItemDetail($record['id']))
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('no')
                                            ->label('Item No')
                                            ->disabled(),
                                        TextInput::make('name')
                                            ->label('Nama Barang')
                                            ->disabled(),
                                        TextInput::make('itemTypeName')
                                            ->label('Tipe')
                                            ->disabled(),
                                        TextInput::make('shortName')
                                            ->label('Nama Pendek')
                                            ->disabled(),
                                        TextInput::make('suspended')
                                            ->label('Status')
                                            ->disabled(),
                                        TextInput::make('notes')
                                            ->label('Catatan')
                                            ->disabled(),
                                    ]),
                            ]),
                        Section::make('Unit & Harga')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('unit1Name')
                                            ->label('Unit 1')
                                            ->disabled(),
                                        TextInput::make('unit2Name')
                                            ->label('Unit 2')
                                            ->disabled(),
                                        TextInput::make('unitPrice')
                                            ->label('Harga Jual')
                                            ->disabled(),
                                        TextInput::make('cost')
                                            ->label('Harga Pokok')
                                            ->disabled(),
                                        TextInput::make('balanceUnitCost')
                                            ->label('HPP Rata-rata')
                                            ->disabled(),
                                        TextInput::make('ratio2')
                                            ->label('Rasio Unit 2')
                                            ->disabled(),
                                        TextInput::make('defaultDiscount')
                                            ->label('Diskon Default (%)')
                                            ->disabled(),
                                        TextInput::make('percentTaxable')
                                            ->label('Taxable (%)')
                                            ->disabled(),
                                    ]),
                            ]),
                        Section::make('Stok')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('balance')
                                            ->label('Stok (Unit 1)')
                                            ->disabled(),
                                        TextInput::make('balanceInUnit')
                                            ->label('Stok (Semua Unit)')
                                            ->disabled(),
                                        TextInput::make('availableToSell')
                                            ->label('Tersedia Dijual')
                                            ->disabled(),
                                        TextInput::make('availableToSellInAllUnit')
                                            ->label('Tersedia (Semua Unit)')
                                            ->disabled(),
                                        TextInput::make('minimumQuantity')
                                            ->label('Min. Stok')
                                            ->disabled(),
                                        TextInput::make('minimumQuantityReorder')
                                            ->label('Min. Reorder')
                                            ->disabled(),
                                        TextInput::make('minimumSellingQuantity')
                                            ->label('Min. Jual')
                                            ->disabled(),
                                        TextInput::make('onSales')
                                            ->label('Dijual?')
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                Action::make('edit')
                    ->icon(Heroicon::PencilSquare)
                    ->modalHeading(fn (array $record): string => 'Edit: '.($record['name'] ?? 'Item'))
                    ->fillForm(fn (array $record): array => $this->fetchItemDetail($record['id']))
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('no')
                                            ->label('Item No')
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('name')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->maxLength(200),
                                        Select::make('itemType')
                                            ->label('Tipe Item')
                                            ->required()
                                            ->options([
                                                'INVENTORY' => 'Persediaan',
                                                'NON_INVENTORY' => 'Non Persediaan',
                                                'PRODUCTION_COST' => 'Biaya Produksi',
                                                'SERVICE' => 'Jasa',
                                            ]),
                                        TextInput::make('unit1Name')
                                            ->label('Satuan')
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('unitPrice')
                                            ->label('Harga Jual')
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('cost')
                                            ->label('Harga Pokok')
                                            ->numeric(),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, array $record): void {
                        $this->saveItem($data, $record['id']);
                    }),

                Action::make('delete')
                    ->color('danger')
                    ->icon(Heroicon::Trash)
                    ->modalHeading(fn (array $record): string => 'Hapus: '.($record['name'] ?? 'Item'))
                    ->modalDescription('Item yang sudah dihapus tidak dapat dikembalikan.')
                    ->modalSubmitActionLabel('Hapus')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        $this->deleteItem($record['id'], $record['name'] ?? '');
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
                                        TextInput::make('no')
                                            ->label('Item No')
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('name')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->maxLength(200),
                                        Select::make('itemType')
                                            ->label('Tipe Item')
                                            ->required()
                                            ->options([
                                                'INVENTORY' => 'Persediaan',
                                                'NON_INVENTORY' => 'Non Persediaan',
                                                'PRODUCTION_COST' => 'Biaya Produksi',
                                                'SERVICE' => 'Jasa',
                                            ])
                                            ->default('INVENTORY'),
                                        TextInput::make('unit1Name')
                                            ->label('Satuan')
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('unitPrice')
                                            ->label('Harga Jual')
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('cost')
                                            ->label('Harga Pokok')
                                            ->numeric(),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        $this->saveItem($data);
                    }),
            ])
            ->records(function (?string $search, int $page, int $recordsPerPage): LengthAwarePaginator {
                return $this->fetchAccurateItems(
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $this->getTableSortColumn(),
                    sortDirection: $this->getTableSortDirection(),
                );
            });
    }

    protected function fetchAccurateItems(
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
            $query = Accurate::connection('default')
                ->openDatabase($db->database_id)
                ->items()
                ->query()
                ->select('id', 'no', 'name', 'itemTypeName', 'unitPrice', 'unit1');

            if (filled($search)) {
                $query->where('keywords', 'like', $search);
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

            $items = collect($result['data'] ?? []);
            $total = (int) ($result['sp']['total'] ?? 0);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                items: $items,
                total: $total,
                perPage: $recordsPerPage,
                currentPage: $page,
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->emptyPaginator($page, $recordsPerPage);
        }
    }

    protected function fetchItemDetail(string $id): array
    {
        $db = Accurate::currentDatabase();

        if (! $db) {
            return [];
        }

        try {
            $result = Accurate::connection('default')
                ->openDatabase($db->database_id)
                ->items()
                ->detail($id);

            return $result['d'] ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    protected function saveItem(array $data, ?string $id = null): void
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
            Accurate::connection('default')
                ->openDatabase($db->database_id)
                ->items()
                ->save($data);

            Notification::make()
                ->title($id ? 'Item berhasil diupdate' : 'Item berhasil dibuat')
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menyimpan item')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function deleteItem(string $id, string $name): void
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
            Accurate::connection('default')
                ->openDatabase($db->database_id)
                ->items()
                ->delete($id);

            Notification::make()
                ->title("Item \"{$name}\" berhasil dihapus")
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menghapus item')
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
