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

            'major.view',
            'major.create',
            'major.edit',
            'major.delete',
            
            'course.view',
            'course.create',
            'course.edit',
            'course.delete',

            'lesson.view',
            'lesson.create',
            'lesson.edit',
            'lesson.delete',

            'enrollment.view',
            'enrollment.create',
            'enrollment.edit',
            'enrollment.cancel',
            'enrollment.delete',
        ];

        foreach($permissions as $permission){
            Permission::firstOrCreate(['name'=>$permission,'guard_name'=>'api']);
        }

        $admin = Role::firstOrCreate(['name'=>'admin','guard_name'=>'api']);
        $admin->syncPermissions(Permission::all());

        $teacher=Role::firstOrCreate(['name'=>'teacher','guard_name'=>'api']);
        $teacher->syncPermissions(['user.view','user.create']);

        $student= Role::firstOrCreate(['name'=>'student','guard_name'=>'api']);
        $student->syncPermissions(['user.edit']);
    }
}
