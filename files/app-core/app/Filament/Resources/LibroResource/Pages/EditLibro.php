<?php

namespace App\Filament\Resources\LibroResource\Pages;

use App\Filament\Resources\LibroResource;
use App\Models\Libro;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLibro extends EditRecord
{
    protected static string $resource = LibroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, Libro $record) {
                    if ($record->prestiti()->where('stato', 'in_corso')->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Il libro ha prestiti in corso')
                            ->body('Registra prima la restituzione, poi potrai eliminarlo.')
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
