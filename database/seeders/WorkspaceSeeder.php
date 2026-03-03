<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceUser;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@erp.com')->first();

        if (!$admin) return;

        $workspace = Workspace::updateOrCreate(
            ['slug' => 'keluarga-utama'],
            [
                'name' => 'Keluarga Utama',
                'owner_id' => $admin->id,
            ]
        );

        // Assign admin as owner
        WorkspaceUser::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'user_id' => $admin->id,
            ],
            ['role' => 'owner']
        );

        // Assign regular user as editor
        $user = User::where('email', 'user@erp.com')->first();
        if ($user) {
            WorkspaceUser::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'user_id' => $user->id,
                ],
                ['role' => 'editor']
            );
        }
    }
}
