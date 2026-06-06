<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'title',
        'description',
        'guide_book_url',
        'type',
        'contact_person1',
        'contact_person2',
        'max_noncompetition_participant',
        'price',
    ];

    /**
     * Get the teams registered for this event.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'competition_id', 'id');
    }

    /**
     * Get the timeline items for this event.
     */
    public function timelines(): HasMany
    {
        return $this->hasMany(EventTimeline::class, 'event_id', 'id');
    }

    /**
     * Get the announcements for this event.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(EventAnnouncement::class, 'event_id', 'id');
    }

    /**
     * Get the participants registered for this event.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participant', 'event_id', 'user_id')
                    ->withPivot('date_added', 'payment_proof', 'payment_verification');
    }

    /**
     * Get the staff assigned to manage this event.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(UserIdentity::class, 'event_staff', 'event_id', 'user_id')
                    ->withTimestamps();
    }
}
