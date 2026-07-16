<?php

namespace App\Filament\Pages;

use ChrisLorando\LaravelAccurate\Facades\Accurate;
use Filament\Actions\Action;
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

class AccurateUnit extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|UnitEnum|null $navigationGroup = 'Accurate';

    protected static ?string $title = 'Satuan Barang';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.accurate-unit';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Satuan')
                    ->searchable()
                    ->sortable(),

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
                    ->modalHeading(fn (array $record): string => 'Edit: '.($record['name'] ?? 'Satuan'))
                    ->fillForm(fn (array $record): array => $this->fetchUnitDetail($record['id']))
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Satuan')
                                            ->required()
                                            ->maxLength(50),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, array $record): void {
                        $this->saveUnit($data, $record['id']);
                    }),

                Action::make('delete')
                    ->color('danger')
                    ->icon(Heroicon::Trash)
                    ->modalHeading(fn (array $record): string => 'Hapus: '.($record['name'] ?? 'Satuan'))
                    ->modalDescription('Satuan yang sudah dihapus tidak dapat dikembalikan.')
                    ->modalSubmitActionLabel('Hapus')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        $this->deleteUnit($record['id'], $record['name'] ?? '');
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
                                            ->label('Nama Satuan')
                                            ->required()
                                            ->maxLength(50),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        $this->saveUnit($data);
                    }),
            ])
            ->records(function (?string $search, int $page, int $recordsPerPage): LengthAwarePaginator {
                return $this->fetchUnits(
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $this->getTableSortColumn(),
                    sortDirection: $this->getTableSortDirection(),
                );
            });
    }

    protected function fetchUnits(
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
            $query = Accurate::units()
                ->query()
                ->select('id', 'name', 'suspended');

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

            $units = collect($result['data'] ?? []);
            $total = (int) ($result['sp']['total'] ?? 0);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                items: $units,
                total: $total,
                perPage: $recordsPerPage,
                currentPage: $page,
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->emptyPaginator($page, $recordsPerPage);
        }
    }

    protected function fetchUnitDetail(string $id): array
    {
        $db = Accurate::currentDatabase();

        if (! $db) {
            return [];
        }

        try {
            $result = Accurate::units()->detail($id);

            return $result['d'] ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    protected function saveUnit(array $data, ?string $id = null): void
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
            Accurate::units()->save($data);

            Notification::make()
                ->title($id ? 'Satuan berhasil diupdate' : 'Satuan berhasil dibuat')
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menyimpan satuan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function deleteUnit(string $id, string $name): void
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
            Accurate::units()->delete($id);

            Notification::make()
                ->title("Satuan \"{$name}\" berhasil dihapus")
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menghapus satuan')
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
