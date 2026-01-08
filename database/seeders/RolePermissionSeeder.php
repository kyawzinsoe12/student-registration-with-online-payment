<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions= [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'role.manage',
        ];

        foreach($permissions as $permission){
            Permission::firstOrCreate(['name'=>$permission]);
        }

        $admin = Role::firstOrCreate(['name'=>'admin']);
        $admin->syncPermissions(Permission::all());

        $teacher=Role::firstOrCreate(['name'=>'teacher']);
        $teacher->syncPermissions(['user.view','user.create']);

        $student= Role::firstOrCreate(['name'=>'student']);
        $student->syncPermissions(['user.edit']);
    }
}
