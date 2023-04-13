<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;

class Training extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location_id',
        'status',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users');
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users')->wherePivot('role', 'athlete');
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trainings_users')->wherePivot('role', 'coach');
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
