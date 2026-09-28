<?php

namespace App\Filament\Resources\OwnerFeedbackResource\Pages;

use App\Filament\Resources\OwnerFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOwnerFeedback extends ViewRecord
{
    protected static string $resource = OwnerFeedbackResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Открыли сообщение — считаем прочитанным
        if (!$this->record->is_read) {
            $this->record->update(['is_read' => true]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
