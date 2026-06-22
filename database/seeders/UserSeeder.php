<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sAdminTwo = User::create([
            'name' => 'Jorem Belen',
            'username' => 'jorem.belen',
            'email' => 'rcl.support@rezayat.net',
            'password' => bcrypt('password'),
        ]);
        $sAdminTwo->assignRole('super admin');

        $user = User::create([
            'name' => 'Test User',
            'username' => 'user',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');

        
    }

}