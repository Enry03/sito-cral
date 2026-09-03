<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'numero_tessera',
        'ruolo',
        'stato_iscrizione',
        'telefono',
        'data_nascita',
        'codice_fiscale',
        'indirizzo',
        'foto_profilo',
        'data_iscrizione',
        'data_scadenza_quota',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_nascita' => 'date',
            'data_iscrizione' => 'date',
            'data_scadenza_quota' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('stato_iscrizione') && $user->stato_iscrizione === 'attivo' && empty($user->numero_tessera)) {
                $lastNumber = static::whereNotNull('numero_tessera')
                    ->pluck('numero_tessera')
                    ->map(fn ($value) => preg_match('/^CRAL-(\d+)$/', $value, $m) ? (int) $m[1] : 0)
                    ->max();

                $user->numero_tessera = 'CRAL-'.str_pad(((int) $lastNumber) + 1, 4, '0', STR_PAD_LEFT);

                if (empty($user->data_iscrizione)) {
                    $user->data_iscrizione = now();
                }
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->ruolo === 'admin';
    }

    public function isAttivo(): bool
    {
        return $this->stato_iscrizione === 'attivo';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function documentiCaricati()
    {
        return $this->hasMany(Documento::class, 'caricato_da');
    }
}
