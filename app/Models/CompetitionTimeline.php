<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionTimeline extends Model
{
    protected $table = 'competition_timeline';

    protected $fillable = ['id', 'title', 'start_date', 'end_date', 'description'];
    
    // Non-incrementing string ID since it uses UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}