<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Training extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'date_start',
        'date_end',
        'location',
        'users',
        'groups',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    use HasFactory, LogsActivity, CreatedUpdatedBy;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'status', 'date_start', 'date_end', 'location'])
            ->logOnlyDirty();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users');
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users')->withPivotValue('role', 'athlete')->withPivot('attendance');
    }

    public function athletesAttending(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users')->withPivotValue('role', 'athlete')->withPivotValue('attendance', true);
    }

    public function athletesNotAttending(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users')->withPivotValue('role', 'athlete')->withPivotValue('attendance', false);
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users')->withPivotValue('role', 'coach')->withPivot('attendance');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'trainings_groups');
    }

    public function location():BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

}
