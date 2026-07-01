<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $table = 'team_member';

    // Disable auto-incrementing since it uses a composite key or string/UUID keys
    public $incrementing = false;
    protected $primaryKey = ['user_id', 'team_id'];

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'team_id',
        'role',
        'is_verified',
        'verification_error',
        'kartu_id',
    ];

    protected function setKeysForSaveQuery($query)
    {
        foreach ((array) $this->getKeyName() as $keyName) {
            $query->where($keyName, '=', $this->getAttribute($keyName));
        }

        return $query;
    }

    /**
     * Get the team associated with the team member.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * Get the user profile associated with the team member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the card/KTM media associated with the member.
     */
    public function kartu(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'kartu_id', 'id');
    }
}
