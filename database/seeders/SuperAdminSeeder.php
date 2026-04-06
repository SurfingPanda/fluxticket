<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@fluxtickets.com'],
            [
                'name'            => 'Super Admin',
                'username'        => 'superadmin',
                'email'           => 'superadmin@fluxtickets.com',
                'password'        => Hash::make('Admin@1234'),
                'role'            => 'super_admin',
                'department'      => 'IT',
                'job_title'       => 'System Administrator',
                'employee_id'     => 'SA-0001',
                'primary_contact' => null,
            ]
        );
    }
}
