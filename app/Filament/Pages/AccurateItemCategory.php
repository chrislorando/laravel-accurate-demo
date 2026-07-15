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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use UnitEnum;

class AccurateItemCategory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | UnitEnum | null $navigationGroup = 'Accurate';

    protected static ?string $title = 'Kategori Barang';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.accurate-item-category';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Parent Kategori')
                    ->placeholder('-'),

                IconColumn::make('defaultCategory')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::XCircle),

                IconColumn::make('suspended')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::XCircle)
                    ->state(fn (array $record): bool => ! ($record['suspended'] ?? false)),
            ])
            ->emptyStateHeading('Belum ada koneksi Accurate')
            ->emptyStateDescription('Hubungkan akun Accurate terlebih dahulu melalui OAuth.')
            ->emptyStateIcon(Heroicon::Link)
            ->headerActions([])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('view')
                    ->color('gray')
                    ->icon(Heroicon::Eye)
                    ->modalHeading(fn (array $record): string => $record['name'] ?? 'Detail Kategori')
                    ->fillForm(fn (array $record): array => $this->fetchCategoryDetail($record['id']))
                    ->schema([
                        Section::make('Informasi Kategori')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Kategori')
                                            ->disabled(),
                                        TextInput::make('parent.name')
                                            ->label('Parent Kategori')
                                            ->disabled()
                                            ->placeholder('-'),
                                        TextInput::make('suspended')
                                            ->label('Status')
                                            ->disabled(),
                                        TextInput::make('defaultCategory')
                                            ->label('Kategori Default')
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                Action::make('edit')
                    ->icon(Heroicon::PencilSquare)
                    ->modalHeading(fn (array $record): string => 'Edit: '.($record['name'] ?? 'Kategori'))
                    ->fillForm(fn (array $record): array => $this->fetchCategoryDetail($record['id']))
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Kategori')
                                            ->required()
                                            ->maxLength(200),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, array $record): void {
                        $this->saveCategory($data, $record['id']);
                    }),

                Action::make('delete')
                    ->color('danger')
                    ->icon(Heroicon::Trash)
                    ->modalHeading(fn (array $record): string => 'Hapus: '.($record['name'] ?? 'Kategori'))
                    ->modalDescription('Kategori yang sudah dihapus tidak dapat dikembalikan.')
                    ->modalSubmitActionLabel('Hapus')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        $this->deleteCategory($record['id'], $record['name'] ?? '');
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
                                            ->label('Nama Kategori')
                                            ->required()
                                            ->maxLength(200),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        $this->saveCategory($data);
                    }),
            ])
            ->records(function (?string $search, int $page, int $recordsPerPage): LengthAwarePaginator {
                return $this->fetchCategories(
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $this->getTableSortColumn(),
                    sortDirection: $this->getTableSortDirection(),
                );
            });
    }

    protected function fetchCategories(
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
                ->itemCategories()
                ->query()
                ->select('id', 'name', 'defaultCategory', 'suspended', 'parent');

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

            $categories = collect($result['data'] ?? []);
            $total = (int) ($result['sp']['total'] ?? 0);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                items: $categories,
                total: $total,
                perPage: $recordsPerPage,
                currentPage: $page,
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->emptyPaginator($page, $recordsPerPage);
        }
    }

    protected function fetchCategoryDetail(string $id): array
    {
        $db = Accurate::currentDatabase();

        if (! $db) {
            return [];
        }

        try {
            $result = Accurate::connection('default')
                ->openDatabase($db->database_id)
                ->itemCategories()
                ->detail($id);

            return $result['d'] ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    protected function saveCategory(array $data, ?string $id = null): void
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
                ->itemCategories()
                ->save($data);

            Notification::make()
                ->title($id ? 'Kategori berhasil diupdate' : 'Kategori berhasil dibuat')
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menyimpan kategori')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function deleteCategory(string $id, string $name): void
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
                ->itemCategories()
                ->delete($id);

            Notification::make()
                ->title("Kategori \"{$name}\" berhasil dihapus")
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal menghapus kategori')
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
