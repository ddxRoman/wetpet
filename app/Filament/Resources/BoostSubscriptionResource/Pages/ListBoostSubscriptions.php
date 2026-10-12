<?php

namespace App\Filament\Resources\BoostSubscriptionResource\Pages;

use App\Filament\Resources\BoostSubscriptionResource;
use App\Models\BoostSubscription;
use App\Models\Clinic;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBoostSubscriptions extends ListRecords
{
    protected static string $resource = BoostSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('grant')
                ->label('Выдать пакет')
                ->icon('heroicon-o-plus')
                ->form([
                    Select::make('entity_type')
                        ->label('Тип карточки')
                        ->options(['clinic' => 'Клиника', 'organization' => 'Организация'])
                        ->required(),
                    TextInput::make('entity_id')
                        ->label('ID карточки')
                        ->numeric()
                        ->required(),
                    Select::make('package')
                        ->label('Пакет')
                        ->options(fn () => collect(config('boost.plans'))->map(fn ($p) => $p['title'])->all())
                        ->required(),
                    DateTimePicker::make('expires_at')
                        ->label('Действует до')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $exists = $data['entity_type'] === 'clinic'
                        ? Clinic::whereKey($data['entity_id'])->exists()
                        : Organization::whereKey($data['entity_id'])->exists();

                    if (! $exists) {
                        Notification::make()->title('Карточка с таким ID не найдена')->danger()->send();

                        return;
                    }

                    BoostSubscription::updateOrCreate(
                        [
                            'entity_type' => $data['entity_type'],
                            'entity_id'   => (int) $data['entity_id'],
                            'package'     => $data['package'],
                        ],
                        ['expires_at' => $data['expires_at']]
                    );

                    Notification::make()->title('Пакет выдан')->success()->send();
                }),
        ];
    }
}
