<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissionNames = [
            'view_user',
            'add_user',
            'edit_user',
            'delete_user',

            'view_group',
            'create_group',
            'edit_group',
            'delete_group',

            'view_document_type',
            'create_document_type',
            'edit_document_type',
            'delete_document_type',

            'view_document_audit',
            'export_document',

            'approve_document',
            'view_document_versions',
            'restore_document_version',
            'share_document',

            'view_recycle_bin',
            'restore_document',
            'force_delete_document',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName]);
        }
    }
}
