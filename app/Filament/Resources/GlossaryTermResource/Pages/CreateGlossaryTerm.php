<?php

namespace App\Filament\Resources\GlossaryTermResource\Pages;

use App\Filament\Resources\GlossaryTermResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGlossaryTerm extends CreateRecord
{
    protected static string $resource = GlossaryTermResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
