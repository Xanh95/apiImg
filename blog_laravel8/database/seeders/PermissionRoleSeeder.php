<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $roles = [
            ['name' => 'admin'],
            ['name' => 'user'],
            ['name' => 'editor'],
            ['name' => 'customer'],
        ];
        DB::table('roles')->insert($roles);

        $permissions = [
            ['name' => 'view'],
            ['name' => 'create'],
            ['name' => 'update'],
            ['name' => 'delete'],
        ];
        DB::table('permissions')->insert($permissions);

        $rolePermissions = [
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
            ['role_id' => 1, 'permission_id' => 4],
            ['role_id' => 2, 'permission_id' => 1],
            ['role_id' => 3, 'permission_id' => 1],
            ['role_id' => 3, 'permission_id' => 2],
            ['role_id' => 3, 'permission_id' => 3],
            ['role_id' => 4, 'permission_id' => 1],
        ];
        DB::table('permission_role')->insert($rolePermissions);
    }
}