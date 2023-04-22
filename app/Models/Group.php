<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\PermissionRegistrar;

class Group extends Model
{
    use HasFactory, LogsActivity;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groups_users');
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groups_users')->wherePivot('role', 'athlete');
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groups_users')->wherePivot('role', 'coach');
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'trainings_groups');
    }
}
