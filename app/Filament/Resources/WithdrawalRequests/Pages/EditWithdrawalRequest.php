<?php

namespace App\Filament\Resources\WithdrawalRequests\Pages;

use App\Filament\Resources\WithdrawalRequests\WithdrawalRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWithdrawalRequest extends EditRecord
{
    protected static string $resource = WithdrawalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
