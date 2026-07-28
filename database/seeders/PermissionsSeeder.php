<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard' => [
                'view-dashboard',
            ],
            'members' => [
                'view-members',
                'create-members',
                'edit-members',
                'delete-members',
            ],
            'next-of-kin' => [
                'manage-next-of-kin',
            ],
            'savings' => [
                'view-savings',
                'deposit-savings',
                'withdraw-savings',
            ],
            'loans' => [
                'view-loans',
                'create-loans',
                'approve-loans',
                'disburse-loans',
                'repay-loans',
                'delete-loans',
            ],
            'loan-products' => [
                'view-loan-products',
                'manage-loan-products',
            ],
            'shares' => [
                'view-shares',
                'purchase-shares',
            ],
            'products' => [
                'view-products',
                'manage-products',
                'view-purchase-orders',
                'create-purchase-orders',
                'approve-purchase-orders',
                'collect-purchase-orders',
            ],
            'dividends' => [
                'view-dividends',
                'declare-dividends',
                'calculate-dividends',
                'approve-dividends',
                'distribute-dividends',
            ],
            'payroll' => [
                'view-payroll',
                'compile-payroll',
                'upload-payroll',
                'export-payroll',
            ],
            'reports' => [
                'view-reports',
            ],
            'admin' => [
                'manage-users',
                'manage-roles',
                'send-broadcasts',
            ],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web']
                );
            }
        }

        $rolePermissions = [
            'super-admin' => array_merge(...array_values($permissions)),

            'admin' => [
                'view-dashboard',
                'view-members', 'create-members', 'edit-members',
                'manage-next-of-kin',
                'view-savings', 'deposit-savings', 'withdraw-savings',
                'view-loans', 'create-loans', 'approve-loans', 'disburse-loans', 'repay-loans',
                'view-loan-products', 'manage-loan-products',
                'view-shares', 'purchase-shares',
                'view-products', 'manage-products',
                'view-purchase-orders', 'create-purchase-orders', 'approve-purchase-orders', 'collect-purchase-orders',
                'view-dividends', 'declare-dividends', 'calculate-dividends', 'approve-dividends', 'distribute-dividends',
                'view-payroll', 'compile-payroll', 'upload-payroll', 'export-payroll',
                'view-reports',
                'manage-users', 'manage-roles',
            ],

            'secretary' => [
                'view-dashboard',
                'view-members', 'create-members', 'edit-members',
                'manage-next-of-kin',
                'view-savings',
                'view-loans',
                'view-shares',
                'view-products',
                'view-purchase-orders',
                'view-dividends',
                'view-payroll',
                'view-reports',
            ],

            'treasurer' => [
                'view-dashboard',
                'view-members',
                'view-savings', 'deposit-savings', 'withdraw-savings',
                'view-loans', 'disburse-loans', 'repay-loans',
                'view-shares', 'purchase-shares',
                'view-products', 'view-purchase-orders', 'create-purchase-orders',
                'view-dividends', 'declare-dividends', 'calculate-dividends',
                'view-payroll', 'compile-payroll', 'upload-payroll', 'export-payroll',
                'view-reports',
            ],

            'loan-officer' => [
                'view-dashboard',
                'view-members',
                'view-loans', 'create-loans', 'approve-loans', 'disburse-loans', 'repay-loans',
                'view-reports',
            ],

            'teller' => [
                'view-dashboard',
                'view-members',
                'view-savings', 'deposit-savings', 'withdraw-savings',
                'view-shares', 'purchase-shares',
                'view-reports',
            ],

            'member' => [
                'view-dashboard',
                'view-savings',
                'view-loans',
                'view-shares',
                'view-reports',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
            $role->syncPermissions($perms);
        }
    }
}
