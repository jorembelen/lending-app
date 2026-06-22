<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        
        $admin_permissions = Permission::all();
        Role::findOrFail(2)->permissions()->sync($admin_permissions->pluck('id'));

        $user = [5,6];
        Role::findOrFail(3)->permissions()->sync($user);
    }
}
