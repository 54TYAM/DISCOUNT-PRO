<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@discountpro.com',
                'password' => bcrypt('password'),
                'role'     => User::ROLE_ADMIN,
            ],
            [
                'name'     => 'Test Customer',
                'email'    => 'customer@discountpro.com',
                'password' => bcrypt('password'),
                'role'     => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(['email' => $data['email']], $data);
            $user->assignRole($role);
            $user->approve(); // seeded users are pre-approved
        }

        $this->command->info('Created super admin and demo customer.');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            [
                ['admin@discountpro.com',    'super_admin', 'password'],
                ['customer@discountpro.com', 'customer',    'password'],
            ]
        );
    }
}
