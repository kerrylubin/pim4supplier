<?php

use App\Laravue\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Laravue\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            // 'id'   => 1,
            'name' => 'Admin',
            'email' => 'admin@pim4sup.nl',
            'password' => Hash::make('pim4sup'),
        ]);

        $supplier = User::create([
            'name' => 'Supplier',
            'email' => 'supplier@pim4sup.nl',
            'password' => Hash::make('pim4sup'),
        ]);
        // $editor = User::create([
        //     'name' => 'Editor',
        //     'email' => 'editor@pim4sup.nl',
        //     'password' => Hash::make('pim4sup'),
        // ]);
        // $user = User::create([
        //     'name' => 'User',
        //     'email' => 'user@pim4sup.nl',
        //     'password' => Hash::make('pim4sup'),
        // ]);
        // $visitor = User::create([
        //     'name' => 'Visitor',
        //     'email' => 'visitor@pim4sup.nl',
        //     'password' => Hash::make('pim4sup'),
        // ]);

        $adminRole = Role::findByName(\App\Laravue\Acl::ROLE_ADMIN);
        $supplierRole = Role::findByName(\App\Laravue\Acl::ROLE_SUPPLIER);
        $editorRole = Role::findByName(\App\Laravue\Acl::ROLE_EDITOR);
        $userRole = Role::findByName(\App\Laravue\Acl::ROLE_USER);
        $visitorRole = Role::findByName(\App\Laravue\Acl::ROLE_VISITOR);

        $admin->syncRoles($adminRole);
        $supplier->syncRoles($supplierRole);
        // $editor->syncRoles($editorRole);
        // $user->syncRoles($userRole);
        // $visitor->syncRoles($visitorRole);
        $this->call(UsersTableSeeder::class);
    }
}
