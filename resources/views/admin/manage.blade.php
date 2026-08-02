<x-app-layout title="Management">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Management']]" />
        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">Management</h2>
            <p class="text-xs text-slate-500 mt-1">System administration and configuration</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @can('manage-users')
                <a href="{{ route('admin.settings.edit') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center group-hover:bg-slate-200 transition">
                            <span class="material-symbols-outlined text-slate-600 text-2xl">settings</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Company Settings</h3>
                            <p class="text-xs text-slate-500">Logo, info, thrift & loan config</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.branding.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition">
                            <span class="material-symbols-outlined text-violet-600 text-2xl">palette</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Branding</h3>
                            <p class="text-xs text-slate-500">Logos, heroes &amp; favicon</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.users.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">manage_accounts</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">User Management</h3>
                            <p class="text-xs text-slate-500">Create, edit, and manage user accounts</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-roles')
                <a href="{{ route('admin.roles.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition">
                            <span class="material-symbols-outlined text-purple-600 text-2xl">admin_panel_settings</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Roles & Permissions</h3>
                            <p class="text-xs text-slate-500">Configure roles and access control</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.regions.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition">
                            <span class="material-symbols-outlined text-emerald-600 text-2xl">location_on</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Regional Centers</h3>
                            <p class="text-xs text-slate-500">Manage cooperative office locations</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-loan-products')
                <a href="{{ route('admin.loan-products.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition">
                            <span class="material-symbols-outlined text-amber-600 text-2xl">category</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Loan Products</h3>
                            <p class="text-xs text-slate-500">Configure loan types and interest rates</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-products')
                <a href="{{ route('products.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition">
                            <span class="material-symbols-outlined text-indigo-600 text-2xl">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Stock Management</h3>
                            <p class="text-xs text-slate-500">Adjust product stock levels from the Products page</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-products')
                <a href="{{ route('products.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center group-hover:bg-pink-200 transition">
                            <span class="material-symbols-outlined text-pink-600 text-2xl">storefront</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Products</h3>
                            <p class="text-xs text-slate-500">Add, edit, and manage product catalog</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.broadcasts.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition">
                            <span class="material-symbols-outlined text-red-600 text-2xl">campaign</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Broadcast Notifications</h3>
                            <p class="text-xs text-slate-500">Send announcements to all members</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.data-import') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-cyan-100 flex items-center justify-center group-hover:bg-cyan-200 transition">
                            <span class="material-symbols-outlined text-cyan-600 text-2xl">upload_file</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Data Import & Upload</h3>
                            <p class="text-xs text-slate-500">Bulk import members, savings, products, loans & more</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('view-reports')
                <a href="{{ route('reports.index') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center group-hover:bg-rose-200 transition">
                            <span class="material-symbols-outlined text-rose-600 text-2xl">description</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Reports</h3>
                            <p class="text-xs text-slate-500">View member status and activity reports</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.backup') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center group-hover:bg-teal-200 transition">
                            <span class="material-symbols-outlined text-teal-600 text-2xl">backup</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Database Backup</h3>
                            <p class="text-xs text-slate-500">Export and download database backup</p>
                        </div>
                    </div>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.statistics') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition">
                            <span class="material-symbols-outlined text-orange-600 text-2xl">analytics</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">Statistics</h3>
                            <p class="text-xs text-slate-500">Login stats, errors, activities and site data</p>
                        </div>
                    </div>
                </a>
            @endcan
        </div>
    </div>
</x-app-layout>
