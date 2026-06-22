<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure lenpro-specific roles exist (super admin/admin/user created by existing RolesTableSeeder)
        foreach (['staff', 'collector'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Ensure admin role exists (may already exist from legacy seeder)
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@lenpro.local'],
            [
                'name'     => 'Admin User',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'status'   => 1,
            ]
        );
        $admin->syncRoles(['admin']);

        // Office staff
        $staffNames = [
            ['name' => 'Maria Santos',  'username' => 'maria.santos',  'email' => 'maria@lenpro.local'],
            ['name' => 'Jose Reyes',    'username' => 'jose.reyes',    'email' => 'jose@lenpro.local'],
            ['name' => 'Ana Cruz',      'username' => 'ana.cruz',      'email' => 'ana@lenpro.local'],
        ];

        foreach ($staffNames as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => bcrypt('password'), 'status' => 1])
            );
            $user->syncRoles(['staff']);
        }

        // Collectors
        $collectorNames = [
            ['name' => 'Pedro Dela Cruz',  'username' => 'pedro.delacruz',  'email' => 'pedro@lenpro.local'],
            ['name' => 'Liza Villanueva',  'username' => 'liza.villanueva',  'email' => 'liza@lenpro.local'],
            ['name' => 'Roberto Mendoza',  'username' => 'roberto.mendoza',  'email' => 'roberto@lenpro.local'],
            ['name' => 'Carmen Ramos',     'username' => 'carmen.ramos',     'email' => 'carmen@lenpro.local'],
        ];

        foreach ($collectorNames as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => bcrypt('password'), 'status' => 1])
            );
            $user->syncRoles(['collector']);
        }

        $this->command->info('RolesAndUsersSeeder: 1 admin, 3 staff, 4 collectors created.');
    }
}
