<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'slug',
        'title',
        'description',
        'guide_book_url',
        'type',
        'is_active',
        'contact_person1',
        'contact_person2',
        'max_noncompetition_participant',
        'max_member',
        'price',
        'requires_submission',
        'submission_fields',
        'external_platform_link',
        'whatsapp_group_link',
        'logo_url',
        'participation_type',
        'method',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_submission' => 'boolean',
        'price' => 'integer',
        'max_noncompetition_participant' => 'integer',
        'max_member' => 'integer',
        'submission_fields' => 'array',
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
     * Get the registration timeline for this event.
     */
    public function registrationTimeline(): HasOne
    {
        return $this->hasOne(EventTimeline::class, 'event_id', 'id')->where('is_registration', true);
    }

    /**
     * Check if registration deadline has passed and auto-close if needed.
     */
    public function checkAndCloseIfRegistrationExpired(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $regTimeline = $this->relationLoaded('timelines')
            ? $this->timelines->firstWhere('is_registration', true)
            : $this->registrationTimeline;

        if (!$regTimeline) {
            return false;
        }

        $deadline = $regTimeline->end_date ?? $regTimeline->date;
        if ($deadline && now()->greaterThan($deadline)) {
            $this->update(['is_active' => false]);
            $this->is_active = false;
            return true;
        }

        return false;
    }

    /**
     * Get the submissions for this event.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(CompetitionSubmission::class, 'competition_id', 'id');
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
