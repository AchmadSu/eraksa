<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $role1 = Role::create(['name' => 'Super-Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Member']);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $user = \App\Models\User::factory()->create([
            'name' => 'Super-Admin',
            'email' => 'eraksasuperadmin@gmail.com',
            'password' => bcrypt('eraksasuperadmin'),
            'status' => '1',
            'phone' => '+12223993',
            'study_program_id' => 0,
        ]);
        $user->assignRole($role1);
    }
}
