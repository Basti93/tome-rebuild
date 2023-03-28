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
        $trainer = Role::updateOrCreate(['id' => 2], ['name' => 'trainer', 'guard_name' => 'sanctum']);
        $player = Role::updateOrCreate(['id' => 3], ['name' => 'user', 'guard_name' => 'sanctum']);


        $user[] = Permission::updateOrCreate(['id' => 1], ['name' => 'edit-user']);
        $user[] = Permission::updateOrCreate(['id' => 2], ['name' => 'view-user']);

        /**
         * Permissões de Funções
         */
        $role[] = Permission::updateOrCreate(['id' => 11], ['name' => 'edit-role']);
        $role[] = Permission::updateOrCreate(['id' => 12], ['name' => 'view-role']);
        Permission::updateOrCreate(['id' => 13], ['name' => 'view-role-admin']);
        $role[] = Permission::updateOrCreate(['id' => 14], ['name' => 'view-role-trainer']);
        $role[] = Permission::updateOrCreate(['id' => 15], ['name' => 'view-role-user']);



        /**
         * Relacionando Permissões
         */
        $this->sync($trainer, $user);
        $this->sync($trainer, $role);

        $this->sync($admin, $user);
        $this->sync($admin, $role);

        $this->sync($player, $user);
        $this->sync($player, $role);

        User::whereEmail(env('MAIL_FROM_ADMIN'))->first()->assignRole('admin');

    }

    public function sync($role, $permissions)
    {
        foreach ($permissions as $permission) {
            $role->givePermissionTo($permission);
        }
    }
}