<?php

namespace App\Filament\Resources\SiteNoticeResource\Pages;

use App\Filament\Resources\SiteNoticeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSiteNotice extends EditRecord
{
    protected static string $resource = SiteNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
