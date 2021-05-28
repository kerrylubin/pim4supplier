<?php

use Illuminate\Database\Seeder;
use App\Laravue\Acl;
use App\Laravue\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userList = [
            "Henk Valk",
            "Test User",
        ];

        foreach ($userList as $fullName) {
            $name = str_replace(' ', '.', $fullName);
            $roleName = \App\Laravue\Faker::randomInArray([
                Acl::ROLE_SUPPLIER,
                Acl::ROLE_EDITOR,
                Acl::ROLE_USER,
                Acl::ROLE_VISITOR,
            ]);
            $user = \App\Laravue\Models\User::create([
                'name' => $fullName,
                'email' => strtolower($name) . '@pim4sup.nl',
                'password' => \Illuminate\Support\Facades\Hash::make('laravue'),
            ]);

            $role = Role::findByName($roleName);
            $user->syncRoles($role);
        }
    }
}
