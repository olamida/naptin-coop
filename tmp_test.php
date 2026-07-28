<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'Permissions: ' . Spatie\Permission\Models\Permission::count() . PHP_EOL;
echo 'Roles: ' . Spatie\Permission\Models\Role::count() . PHP_EOL;
$admin = App\Models\User::where('email','admin@naptin.coop')->first();
echo 'Admin role: ' . ($admin->getRoleNames()->implode(', ') ?? 'none') . PHP_EOL;
echo 'Admin permissions: ' . $admin->getAllPermissions()->pluck('name')->implode(', ') . PHP_EOL;
