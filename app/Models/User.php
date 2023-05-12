<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Contracts\HasApiTokens as HasApiTokensContract;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasApiTokensContract, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Prunable, HasRoles, LogsActivity, CreatedUpdatedBy;
    protected $guard_name = 'sanctum';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname',
        'firstname',
        'lastname',
        'birthdate',
        'phone',
        'email',
        'password',
    ];

    protected $appends = ['approved'];

    public $pushNotificationType = 'users';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(["password"]);
        // Chain fluent methods for configuration options
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'datetime',
    ];

    /**
     * model life cycle event listeners
     */

    public static function boot(){
        parent::boot();
        static::created(function ($instance){
            $instance->notificationSettings()->attach([
                1 => ['email' => true, 'push' => true],
                2 => ['email' => true, 'push' => true],
                3 => ['email' => true, 'push' => true],
                4 => ['email' => true, 'push' => true],
            ]);
        });
    }

    public function getApprovedAttribute(): bool
    {
        return $this->can('login');
    }

    public function getImageUrlAttribute(): String
    {
        return $this->image ? asset('storage/' . $this->image) : '';
    }

    public function scopeApproved(Builder $query, bool $approved): Builder
    {
        $scopeQuery = function ($query) use ($approved) {
            $query->select('users.id')
                ->from('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('permissions.name', ($approved ? '=' : '!='), 'login')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->groupBy('users.id');

        };
        if ($approved) {
            return $query->whereIn('id', $scopeQuery);
        } else {
            return $query->whereNotIn('id', $scopeQuery);
        }
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'groups_users')->withPivot(['role']);;
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'trainings_users')->withPivot(['attendance']);
    }

    public function notificationSettings(): BelongsToMany
    {
        return $this->belongsToMany(NotificationSetting::class, 'notification_settings_users')->withPivot(['email', 'push']);
    }

    public function notificationSettingsEmail(): BelongsToMany
    {
        return $this->belongsToMany(NotificationSetting::class, 'notification_settings_users')->withPivotValue('email', true);
    }

    public function notificationSettingsPush(): BelongsToMany
    {
        return $this->belongsToMany(NotificationSetting::class, 'notification_settings_users')->withPivotValue('push', true);
    }

    public function upcomingTrainingsAsCoach(): BelongsToMany
    {
            return $this->belongsToMany(Training::class, 'trainings_users')
                ->where('trainings.date_start', '>=', now())
                ->where('trainings_users.role', 'coach')
                ->orderBy('trainings.date_start')
                ->withPivot(['attendance']);
    }

    public function upcomingTrainingsAsAthlete(): BelongsToMany
    {
            return $this->belongsToMany(Training::class, 'trainings_users')
                ->where('trainings.date_start', '>=', now())
                ->where('trainings.status', true)
                ->where('trainings_users.role', 'athlete')
                ->orderBy('trainings.date_start')
                ->withPivot(['attendance']);
    }

    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subMonths(1));
    }

}
