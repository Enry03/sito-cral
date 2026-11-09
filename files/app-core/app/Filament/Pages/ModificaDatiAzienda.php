<?php

namespace App\Filament\Pages;

use App\Services\DatiAzienda;
use App\Support\Iban;
use Closure;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Dati dell'azienda: conto per i bonifici, quota di tessera e PayPal. Quello che
 * si scrive qui compare sul sito a chi sceglie "Paga tramite bonifico".
 */
class ModificaDatiAzienda extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Dati dell\'azienda';

    protected static ?string $title = 'Dati dell\'azienda';

    protected static ?string $slug = 'dati-azienda';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.dati-azienda';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $dati = app(DatiAzienda::class);
        $valori = $dati->tutti();

        $this->form->fill(array_merge(
            array_fill_keys(DatiAzienda::CHIAVI, null),
            array_intersect_key($valori, array_flip(DatiAzienda::CHIAVI)),
        ));
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Dati per il bonifico')
                    ->description('Compaiono sul sito a chi sceglie "Paga tramite bonifico" per la quota di tessera. Finché non li compili, il bonifico non è selezionabile.')
                    ->schema([
                        Forms\Components\TextInput::make('intestatario')
                            ->label('Intestatario del conto')
                            ->maxLength(140)
                            ->requiredWith('iban')
                            ->validationMessages(['required_with' => 'Indica a chi è intestato il conto.']),
                        Forms\Components\TextInput::make('iban')
                            ->label('IBAN')
                            ->placeholder('IT00 X000 0000 0000 0000 0000 000')
                            ->maxLength(40)
                            ->extraInputAttributes(['class' => 'uppercase font-mono'])
                            ->requiredWith('intestatario')
                            ->validationMessages(['required_with' => 'Inserisci l\'IBAN del conto.'])
                            ->rule(fn () => function (string $attribute, $value, Closure $fail) {
                                if (filled($value) && ! Iban::valido($value)) {
                                    $fail('L\'IBAN non è valido: controlla di averlo scritto giusto.');
                                }
                            }),
                        Forms\Components\TextInput::make('banca')
                            ->label('Banca (facoltativo)')
                            ->maxLength(140),
                        Forms\Components\TextInput::make('bic')
                            ->label('BIC / SWIFT (facoltativo)')
                            ->helperText('Serve solo per i bonifici dall\'estero.')
                            ->maxLength(11),
                        Forms\Components\TextInput::make('causale')
                            ->label('Causale del bonifico (facoltativo)')
                            ->placeholder(DatiAzienda::CAUSALE_PREDEFINITA)
                            ->helperText('Al testo viene aggiunto il nome di chi paga, es. "Quota tessera CRAL - Mario Rossi".')
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Quota di tessera e PayPal')
                    ->description('La quota compare in tutti i modi di pagamento. Il pulsante "Paga con PayPal" si attiva quando sono compilati sia la quota sia l\'indirizzo PayPal. Se lasci vuoto un campo vale quello impostato sul server (file .env), se c\'è.')
                    ->schema([
                        Forms\Components\TextInput::make('quota_tessera')
                            ->label('Quota di tessera (€)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999)
                            ->step(0.01)
                            ->prefix('€'),
                        Forms\Components\TextInput::make('paypal_email')
                            ->label('Indirizzo PayPal che riceve i pagamenti')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @return array<int, Forms\Components\Actions\Action>
     */
    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('salva')
                ->label('Salva')
                ->submit('salva'),
        ];
    }

    public function salva(): void
    {
        $dati = $this->form->getState();

        app(DatiAzienda::class)->salva($dati);

        Notification::make()->success()->title('Dati salvati')->send();
    }
}
