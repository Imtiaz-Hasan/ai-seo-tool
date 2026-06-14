<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function contentPieces(): HasMany
    {
        return $this->hasMany(ContentPiece::class)->latest('updated_at');
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class)->latest();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'llm_provider',
        'openai_key',
        'openai_model',
        'anthropic_key',
        'anthropic_model',
        'ollama_base_url',
        'ollama_model',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'openai_key',
        'anthropic_key',
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
            // API keys are encrypted at rest so a DB dump can't be replayed.
            'openai_key' => 'encrypted',
            'anthropic_key' => 'encrypted',
        ];
    }

    /** True if this user has configured their own provider + the key it needs. */
    public function hasOwnLlm(): bool
    {
        return match ($this->llm_provider) {
            'openai' => filled($this->openai_key),
            'anthropic' => filled($this->anthropic_key),
            'ollama' => filled($this->ollama_base_url),
            default => false,
        };
    }
}
