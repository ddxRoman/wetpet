<?php

namespace App\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Общий менеджер фотогалереи для Organization/Clinic/Doctor/Specialist.
 * Подключается одинаково во всех 4 Resource'ах через getRelations().
 *
 * Из админки лимит пакета (1/15) не действует — сотрудник управляет
 * контентом напрямую, но абсолютный максимум в 15 фото — общий для всех
 * (см. HasGalleryPhotos::maxGalleryPhotos()).
 */
class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Фотогалерея';

    protected static ?string $modelLabel = 'фото';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('path')
                ->label('Фото')
                ->image()
                ->disk('public')
                ->directory('gallery')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->disk('public')
                    ->label('Превью')
                    ->square()
                    ->height(80),
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload')
                    ->label('Загрузить фото')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('photos')
                            ->label('Фотографии')
                            ->image()
                            ->multiple()
                            ->disk('public')
                            ->directory('gallery')
                            ->reorderable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $owner = $this->getOwnerRecord();
                        $max = $owner::maxGalleryPhotos();
                        $existing = $owner->photos()->count();
                        $slotsLeft = max(0, $max - $existing);

                        $paths = $data['photos'] ?? [];
                        $toCreate = array_slice($paths, 0, $slotsLeft);

                        $maxOrder = (int) $owner->photos()->max('sort_order');
                        foreach ($toCreate as $path) {
                            $maxOrder++;
                            $owner->photos()->create([
                                'path'       => $path,
                                'sort_order' => $maxOrder,
                            ]);
                        }

                        if (count($toCreate) < count($paths)) {
                            Notification::make()
                                ->warning()
                                ->title('Достигнут лимит в ' . $max . ' фотографий')
                                ->body('Часть файлов не была сохранена.')
                                ->send();
                        } else {
                            Notification::make()->success()->title('Фото загружены')->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => (function () use ($record) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($record->path);
                        $record->delete();
                    })()),
            ]);
    }
}
