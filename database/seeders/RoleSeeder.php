<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role-based access, as specified: Super Admin, Registration Manager,
 * Content Editor, Check-in Staff and Sponsor Manager.
 */
class RoleSeeder extends Seeder
{
    public const PERMISSIONS = [
        // Registrations
        'view-registrations', 'edit-registrations', 'approve-registrations',
        'cancel-registrations', 'export-registrations', 'create-walkins',
        // Messaging
        'view-messages', 'send-messages', 'manage-templates',
        // Content
        'manage-content', 'publish-content', 'manage-media',
        // Partners
        'manage-sponsors', 'manage-leads',
        // Gate
        'scan-tickets',
        // Platform
        'manage-users', 'manage-settings', 'view-analytics', 'manage-qr-campaigns',
    ];

    public const ROLES = [
        'Super Admin' => 'all',
        'Registration Manager' => [
            'view-registrations', 'edit-registrations', 'approve-registrations',
            'cancel-registrations', 'export-registrations', 'create-walkins',
            'view-messages', 'send-messages', 'manage-templates',
            'scan-tickets', 'view-analytics', 'manage-qr-campaigns',
        ],
        'Content Editor' => [
            'manage-content', 'publish-content', 'manage-media', 'view-analytics',
        ],
        'Check-in Staff' => [
            'scan-tickets',
        ],
        'Sponsor Manager' => [
            'manage-sponsors', 'manage-leads', 'view-analytics', 'manage-qr-campaigns',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLES as $name => $permissions) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($permissions === 'all' ? self::PERMISSIONS : $permissions);
        }

        // Seed accounts. Passwords are rotated on handover — see docs/admin-guide.md.
        $accounts = [
            ['Avin Qadir', 'admin@nextstepfair.com', 'Super Admin', 'Organizer and Co-Founder'],
            ['Registration Desk', 'registration@nextstepfair.com', 'Registration Manager', 'Registration Manager'],
            ['Newsroom', 'editor@nextstepfair.com', 'Content Editor', 'Content Editor'],
            ['Gate Staff', 'gate@nextstepfair.com', 'Check-in Staff', 'Check-in Staff'],
            ['Partnerships', 'partnerships@nextstepfair.com', 'Sponsor Manager', 'Sponsor Manager'],
        ];

        foreach ($accounts as [$name, $email, $role, $title]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'job_title' => $title,
                    'locale' => 'en',
                    'is_active' => true,
                    'default_gate' => $role === 'Check-in Staff' ? 'A' : null,
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
