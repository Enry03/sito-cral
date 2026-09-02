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
