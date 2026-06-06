<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'team';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'competition_id',
        'team_name',
        'team_code',
        'max_member',
        'is_verified',
        'verification_error',
        'payment_proof_id',
    ];

    protected $casts = [
        'is_verified' => 'integer',
        'max_member' => 'integer',
    ];

    /**
     * Get the event (competition) associated with the team.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'competition_id', 'id');
    }

    /**
     * Get the payment proof associated with the team.
     */
    public function paymentProof(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'payment_proof_id', 'id');
    }

    /**
     * Get the members details in the team.
     */
    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'team_id', 'id');
    }

    /**
     * The users belonging to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_member', 'team_id', 'user_id')
                    ->withPivot('role', 'verification_error', 'kartu_id');
    }

    /**
     * Get the submissions for the team.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(CompetitionSubmission::class, 'team_id', 'id');
    }
}
