<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasPermissions;

class Role extends SpatieRole
{
    use HasFactory, HasPermissions;

    /**
     * The "booted" method of the model.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('permission', function (Builder $builder) {
            /**
             * verify if auth()->user() has permission in role for view-role-admin
             * if not, remove role id 1 from query (admin)
             */
            return $builder->when(
                !auth()->user()->can('view-role-admin'),
                function (Builder $builder) {
                    $builder->whereNot('id', 1);
                }
            )

                /**
                 * verify if auth()->user() has permission in role for view-role-technician
                 * if not, remove role id 2 from query (Trainer)
                 */
                ->when(
                    !auth()->user()->can('view-role-coach'),
                    function (Builder $builder) {
                        $builder->whereNot('id', 2);
                    }
                )
                /**
                 * verify if auth()->user() has permission in role for view-role-player
                 * if not, remove role id 3 from query (User)
                 */
                ->when(
                    !auth()->user()->can('view-role-user'),
                    function (Builder $builder) {
                        $builder->whereNot('id', 3);
                    }
                );
        });
    }

}
