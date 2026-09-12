<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'email',
        'full_name',
        'birth_date',
        'pendidikan',
        'nama_sekolah',
        'entry_source',
        'phone_number',
        'id_line',
        'id_discord',
        'id_instagram',
        'is_registration_complete',
        'jenis_kelamin',
        'ktm_key',
        'twibbon_key',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
        'is_registration_complete' => 'boolean',
    ];

    /**
     * Get the identity associated with the user.
     */
    public function identity(): HasOne
    {
        return $this->hasOne(UserIdentity::class, 'id', 'id');
    }

    /**
     * Get the media uploaded by the user.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'uploader_id', 'id');
    }

    /**
     * The teams that this user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_member', 'user_id', 'team_id')
                    ->withPivot('role', 'verification_error', 'kartu_id');
    }

    /**
     * The events that this user is registered for as an individual.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participant', 'user_id', 'event_id')
                    ->withPivot('date_added', 'payment_proof', 'payment_verification');
    }

    /**
     * Get the Semnas participant records for this user.
     */
    public function semnasParticipants(): HasMany
    {
        return $this->hasMany(SemnasParticipant::class, 'user_id', 'id');
    }
}
