<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create([
            'name' => 'get all users', 
            'guard_name' => 'web'
        ]);
        Permission::create([
            'name' => 'get user by id', 
            'guard_name' => 'web'
        ]);
        Permission::create([
            'name' => 'get user in trash', 
            'guard_name' => 'web'
        ]);
        Permission::create([
            'name' => 'delete user', 
            'guard_name' => 'web'
        ]);
        Permission::create([
            'name' => 'restore user', 
            'guard_name' => 'web'
        ]);

        // $role1 = Role::create([
        //     'name' => 'admin',
        //     'guard_name' => 'web'
        // ]);

        // $role1->givePermissionTo('get all users');
        // $role1->givePermissionTo('get user by id');
        // $role1->givePermissionTo('get users in trash');
        // $role1->givePermissionTo('delete user');
        // $role1->givePermissionTo('restore user');
        $superadminRole = Role::create(['name' => 'super-admin']);

        $user = User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('eraksaadmin123'),
            'status' => '1',
            'phone' => '+188894927721',
        ]);
        $user->assignRole($superadminRole);
    }
}
