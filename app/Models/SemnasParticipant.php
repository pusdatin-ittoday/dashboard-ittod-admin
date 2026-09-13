<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemnasParticipant extends Model
{
    use HasFactory;

    protected $table = 'semnas_participant';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'event_id',
        'kenal_sentral_komputer',
        'sumber_kenal_sentral',
        'kenal_acer',
        'kenal_nvidia',
        'kenal_microsoft',
        'ig_follow_proof_key',
        'created_at',
    ];

    protected $casts = [
        'kenal_sentral_komputer' => 'boolean',
        'kenal_acer' => 'boolean',
        'kenal_nvidia' => 'boolean',
        'kenal_microsoft' => 'boolean',
    ];

    /**
     * Get the user associated with this semnas registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the event associated with this semnas registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
