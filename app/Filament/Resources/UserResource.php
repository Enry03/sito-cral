<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Soci';

    protected static ?string $modelLabel = 'Socio';

    protected static ?string $pluralModelLabel = 'Soci';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dati anagrafici')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome e cognome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('telefono')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('data_nascita')
                            ->label('Data di nascita')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('codice_fiscale')
                            ->label('Codice fiscale')
                            ->maxLength(16),
                        Forms\Components\TextInput::make('indirizzo')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('foto_profilo')
                            ->label('Foto tessera')
                            ->image()
                            ->maxSize(5120)
                            ->directory('soci')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Iscrizione al CRAL')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('numero_tessera')
                            ->label('Numero tessera')
                            ->helperText('Generato automaticamente quando lo stato passa ad "Attivo".')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('ruolo')
                            ->options([
                                'socio' => 'Socio',
                                'admin' => 'Amministratore',
                            ])
                            ->required(),
                        Forms\Components\Select::make('stato_iscrizione')
                            ->label('Stato iscrizione')
                            ->options([
                                'in_attesa' => 'In attesa di approvazione',
                                'attivo' => 'Attivo',
                                'sospeso' => 'Sospeso',
                                'scaduto' => 'Scaduto',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('data_iscrizione')
                            ->label('Data iscrizione')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('data_scadenza_quota')
                            ->label('Scadenza quota')
                            ->displayFormat('d/m/Y'),
                    ]),
                Forms\Components\Section::make('Accesso')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('numero_tessera')
                    ->label('Tessera')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('ruolo')
                    ->colors([
                        'primary' => 'socio',
                        'danger' => 'admin',
                    ]),
                Tables\Columns\BadgeColumn::make('stato_iscrizione')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'in_attesa',
                        'success' => 'attivo',
                        'gray' => 'sospeso',
                        'danger' => 'scaduto',
                    ]),
                Tables\Columns\TextColumn::make('data_iscrizione')
                    ->label('Iscritto il')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato_iscrizione')
                    ->label('Stato')
                    ->options([
                        'in_attesa' => 'In attesa di approvazione',
                        'attivo' => 'Attivo',
                        'sospeso' => 'Sospeso',
                        'scaduto' => 'Scaduto',
                    ]),
                Tables\Filters\SelectFilter::make('ruolo')
                    ->options([
                        'socio' => 'Socio',
                        'admin' => 'Amministratore',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
