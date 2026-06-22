<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {

        Permission::create([
            'name' => 'system_management_access',
        ]);
        
        Permission::create([
            'name' => 'system_user_access',
        ]);
        
        Permission::create([
            'name' => 'permission_update',
        ]);
        
        Permission::create([
            'name' => 'role_update',
        ]);
        
        // 5
        Permission::create([
            'name' => 'user_access',
        ]);
        
        // 6
        Permission::create([
            'name' => 'add_appointment',
        ]);
        
    }
}
