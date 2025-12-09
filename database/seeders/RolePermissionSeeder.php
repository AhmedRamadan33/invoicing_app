<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users_view',
            'users_create',
            'users_edit',
            'users_delete',

            'roles_view',
            'roles_create',
            'roles_edit',
            'roles_delete',

            'client_view',
            'client_create',
            'client_edit',
            'client_delete',

            'invoice_view',
            'invoice_create',
            'invoice_edit',
            'invoice_delete',
            'invoice_pdf_generate',
            'invoice_search',
            'statistics_view',

            'client_view_all',
            'invoice_view_all',
            'user_manage',
            'report_view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userPermissions = [
            'client_view',
            'client_create',
            'client_edit',
            'client_delete',
            'invoice_view',
            'invoice_create',
            'invoice_edit',
            'invoice_delete',
            'invoice_pdf_generate',
            'invoice_search',
            'statistics_view',
        ];
        $userRole->syncPermissions($userPermissions);

        $this->command->info(' Roles and permissions created successfully!');
    }
}
