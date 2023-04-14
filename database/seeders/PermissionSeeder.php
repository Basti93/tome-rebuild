<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::updateOrCreate(['id' => 1], ['name' => 'admin', 'guard_name' => 'sanctum']);
        $trainer = Role::updateOrCreate(['id' => 2], ['name' => 'coach', 'guard_name' => 'sanctum']);
        $athlete = Role::updateOrCreate(['id' => 3], ['name' => 'athlete', 'guard_name' => 'sanctum']);


        $editUserPermission[] = Permission::updateOrCreate(['id' => 1], ['name' => 'edit-user']);
        $userPermission[] = Permission::updateOrCreate(['id' => 2], ['name' => 'view-user']);

        $userPermission[] = Permission::updateOrCreate(['id' => 3], ['name' => 'login']);

        $rolePermission[] = Permission::updateOrCreate(['id' => 4], ['name' => 'edit-role']);
        $rolePermission[] = Permission::updateOrCreate(['id' => 5], ['name' => 'view-role']);
        $adminRolePermission[] = Permission::updateOrCreate(['id' => 6], ['name' => 'view-role-admin']);
        $adminRolePermission[] = Permission::updateOrCreate(['id' => 7], ['name' => 'edit-role-admin']);
        $trainerRolePermission[] = Permission::updateOrCreate(['id' => 8], ['name' => 'edit-role-coach']);
        $trainerRolePermission[] = Permission::updateOrCreate(['id' => 9], ['name' => 'view-role-coach']);
        $rolePermission[] = Permission::updateOrCreate(['id' => 10], ['name' => 'view-role-user']);

        $editGroupPermission[] = Permission::updateOrCreate(['id' => 11], ['name' => 'edit-group']);
        $groupPermission[] = Permission::updateOrCreate(['id' => 12], ['name' => 'view-group']);

        $editLocationPermission[] = Permission::updateOrCreate(['id' => 13], ['name' => 'edit-location']);
        $locationPermission[] = Permission::updateOrCreate(['id' => 14], ['name' => 'view-location']);

        $editConfigPermission[] = Permission::updateOrCreate(['id' => 15], ['name' => 'edit-config']);
        $configPermission[] = Permission::updateOrCreate(['id' => 16], ['name' => 'view-config']);

        $editTrainingPermission[] = Permission::updateOrCreate(['id' => 17], ['name' => 'edit-training']);
        $trainingPermission[] = Permission::updateOrCreate(['id' => 18], ['name' => 'view-training']);


        $this->sync($trainer, $trainerRolePermission);
        $this->sync($trainer, $userPermission);
        $this->sync($trainer, $editUserPermission);
        $this->sync($trainer, $rolePermission);
        $this->sync($trainer, $groupPermission);
        $this->sync($trainer, $editGroupPermission);
        $this->sync($trainer, $locationPermission);
        $this->sync($trainer, $editLocationPermission);
        $this->sync($trainer, $configPermission);
        $this->sync($trainer, $trainingPermission);
        $this->sync($trainer, $editTrainingPermission);

        $this->sync($admin, $adminRolePermission);
        $this->sync($admin, $trainerRolePermission);
        $this->sync($admin, $userPermission);
        $this->sync($admin, $editUserPermission);
        $this->sync($admin, $rolePermission);
        $this->sync($admin, $groupPermission);
        $this->sync($admin, $editGroupPermission);
        $this->sync($admin, $locationPermission);
        $this->sync($admin, $editLocationPermission);
        $this->sync($admin, $editConfigPermission);
        $this->sync($admin, $configPermission);
        $this->sync($admin, $trainingPermission);
        $this->sync($admin, $editTrainingPermission);

        $this->sync($athlete, $userPermission);
        $this->sync($athlete, $groupPermission);
        $this->sync($athlete, $locationPermission);
        $this->sync($athlete, $configPermission);
        $this->sync($athlete, $trainingPermission);

        User::whereEmail(env('MAIL_FROM_ADMIN'))->first()->assignRole(['admin', 'coach', 'athlete']);
        if (env('APP_DEBUG')) {
            User::whereEmail(env('MAIL_FROM_TEST_TRAINER'))->first()->assignRole('coach');
            User::whereEmail(env('MAIL_FROM_TEST_USER'))->first()->assignRole('athlete');
        }

    }

    public function sync($role, $permissions)
    {
        foreach ($permissions as $permission) {
            $role->givePermissionTo($permission);
        }
    }
}