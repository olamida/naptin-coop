<x-app-layout title="Chart of Accounts">
    <div class="max-w-6xl mx-auto space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-[#0F172A]">Chart of Accounts</h2>
            <button x-on:click="$dispatch('open-modal', 'create-account')" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">add</span> New Account
            </button>
        </div>

        <div class="grid grid-cols-5 gap-3">
            @foreach (['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expenses'] as $type => $label)
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">{{ $label }}</h3>
                    <div class="space-y-1">
                        @foreach ($accounts->where('type', $type) as $account)
                            <div class="flex items-center justify-between text-sm py-1 px-2 rounded-lg hover:bg-slate-50">
                                <span class="text-slate-700 font-mono text-xs">{{ $account->code }}</span>
                                <span class="text-slate-600 text-xs truncate ml-2 flex-1">{{ $account->name }}</span>
                                <button x-on:click="$dispatch('open-modal', 'edit-account-{{ $account->id }}')" class="text-slate-400 hover:text-slate-600">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Create Account Modal --}}
    <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'create-account') open = true" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
        <div class="fixed inset-0 bg-black/40" x-on:click="open = false"></div>
        <div class="bg-white rounded-[16px] shadow-xl p-6 w-full max-w-md relative z-10">
            <h3 class="text-sm font-bold text-[#0F172A] mb-4">New Account</h3>
            <form method="POST" action="{{ route('ledger.accounts.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Code</label>
                        <input type="text" name="code" required maxlength="20" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach (['asset' => 'Asset', 'liability' => 'Liability', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expense'] as $val => $lab)
                                <option value="{{ $val }}">{{ $lab }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Normal Side</label>
                        <select name="normal_side" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Parent (optional)</label>
                        <select name="parent_id" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— None —</option>
                            @foreach (\App\Models\ChartOfAccount::whereNull('parent_id')->orderBy('code')->get() as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Account Modals --}}
    @foreach ($accounts as $account)
        <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'edit-account-{{ $account->id }}') open = true" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
            <div class="fixed inset-0 bg-black/40" x-on:click="open = false"></div>
            <div class="bg-white rounded-[16px] shadow-xl p-6 w-full max-w-md relative z-10">
                <h3 class="text-sm font-bold text-[#0F172A] mb-4">Edit Account</h3>
                <form method="POST" action="{{ route('ledger.accounts.update', $account) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Code</label>
                            <input type="text" name="code" value="{{ $account->code }}" required maxlength="20" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Type</label>
                            <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach (['asset' => 'Asset', 'liability' => 'Liability', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expense'] as $val => $lab)
                                    <option value="{{ $val }}" @selected($account->type === $val)>{{ $lab }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ $account->name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Normal Side</label>
                            <select name="normal_side" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="debit" @selected($account->normal_side === 'debit')>Debit</option>
                                <option value="credit" @selected($account->normal_side === 'credit')>Credit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Active</label>
                            <select name="is_active" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1" @selected($account->is_active)>Yes</option>
                                <option value="0" @selected(!$account->is_active)>No</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">{{ $account->description }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
