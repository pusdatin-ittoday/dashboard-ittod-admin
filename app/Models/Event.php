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
     * Sync event registration status based on its registration timeline dates.
     * Returns true if status was changed.
     */
    public function syncRegistrationStatus(): bool
    {
        $regTimeline = $this->relationLoaded('timelines')
            ? $this->timelines->firstWhere('is_registration', true)
            : $this->registrationTimeline;

        if (!$regTimeline) {
            return false;
        }

        $now = now();
        $startDate = $regTimeline->end_date ? $regTimeline->date : null;
        $deadline = $regTimeline->end_date ?? $regTimeline->date;

        $shouldBeActive = true;

        if ($startDate && $now->lessThan($startDate)) {
            $shouldBeActive = false;
        } elseif ($deadline && $now->greaterThan($deadline)) {
            $shouldBeActive = false;
        }

        if ((bool)$this->is_active !== $shouldBeActive) {
            $this->update(['is_active' => $shouldBeActive]);
            $this->is_active = $shouldBeActive;
            return true;
        }

        return false;
    }

    /**
     * Check if registration deadline has passed or opening date has not arrived, and auto-sync if needed.
     */
    public function checkAndCloseIfRegistrationExpired(): bool
    {
        return $this->syncRegistrationStatus();
    }

    /**
     * Get the current registration status state.
     * Values: 'not_started', 'open', 'closed', 'manual'
     */
    public function getRegistrationStateAttribute(): string
    {
        $regTimeline = $this->relationLoaded('timelines')
            ? $this->timelines->firstWhere('is_registration', true)
            : $this->registrationTimeline;

        if (!$regTimeline) {
            return 'manual';
        }

        $now = now();
        $startDate = $regTimeline->end_date ? $regTimeline->date : null;
        $deadline = $regTimeline->end_date ?? $regTimeline->date;

        if ($startDate && $now->lessThan($startDate)) {
            return 'not_started';
        }

        if ($deadline && $now->greaterThan($deadline)) {
            return 'closed';
        }

        return 'open';
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
