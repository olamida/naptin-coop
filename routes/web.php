<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\NextOfKinController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DataImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/shop', [\App\Http\Controllers\HomeController::class, 'shop'])->name('shop');
Route::get('/about', [\App\Http\Controllers\HomeController::class, 'about'])->name('about');

Route::get('/health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->timestamp]))->name('health');

Route::middleware('guest')->group(function () {
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store']);

    Route::get('register', [\App\Http\Controllers\RegistrationController::class, 'create'])->name('register');
    Route::post('register', [\App\Http\Controllers\RegistrationController::class, 'store'])->name('register.store');

    Route::get('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('logout', [SessionController::class, 'destroy'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'enforce-single-session'])->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('admin-only')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('members/import', [MemberController::class, 'import'])->name('members.import');
        Route::post('members/import', [MemberController::class, 'importStore'])->name('members.import.store');
        Route::get('members/download-template', [MemberController::class, 'downloadTemplate'])->name('members.download-template');
        Route::get('members/export', [MemberController::class, 'exportMembers'])->name('members.export');
        Route::post('members/bulk-status', [MemberController::class, 'bulkUpdateStatus'])->name('members.bulk-status');
        Route::resource('members', MemberController::class)->except(['show']);
        Route::get('members/{member}', [MemberController::class, 'show'])->name('members.show');
        Route::post('members/{member}/approve', [MemberController::class, 'approve'])->name('members.approve');
        Route::post('members/{member}/reject', [MemberController::class, 'reject'])->name('members.reject');
        Route::get('members/{member}/savings-detail', [MemberController::class, 'savingsDetail'])->name('members.savings-detail');
        Route::get('members/{member}/loans-detail', [MemberController::class, 'loansDetail'])->name('members.loans-detail');
        Route::get('members/{member}/purchases-detail', [MemberController::class, 'purchasesDetail'])->name('members.purchases-detail');

        Route::prefix('savings')->name('savings.')->group(function () {
            Route::get('/', [SavingsController::class, 'index'])->name('index');
            Route::get('/accounts', [SavingsController::class, 'accounts'])->name('accounts');
            Route::get('/deposit', [SavingsController::class, 'deposit'])->name('deposit');
            Route::post('/deposit', [SavingsController::class, 'storeDeposit'])->name('deposit.store');
            Route::get('/withdraw', [SavingsController::class, 'withdraw'])->name('withdraw');
            Route::post('/withdraw', [SavingsController::class, 'storeWithdrawal'])->name('withdraw.store');
            Route::get('/pending-withdrawals', [SavingsController::class, 'pendingApprovals'])->name('pending-withdrawals');
            Route::post('/withdrawals/{transaction}/approve', [SavingsController::class, 'approveWithdrawal'])->name('withdrawals.approve');
            Route::post('/withdrawals/{transaction}/reject', [SavingsController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
            Route::post('/deposits/{transaction}/confirm', [SavingsController::class, 'approveDeposit'])->name('deposits.confirm');
            Route::post('/deposits/{transaction}/reject', [SavingsController::class, 'rejectDeposit'])->name('deposits.reject');
            Route::get('/import', [SavingsController::class, 'import'])->name('import');
            Route::post('/import', [SavingsController::class, 'importStore'])->name('import.store');
            Route::get('/download-template', [SavingsController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/export', [SavingsController::class, 'exportSavings'])->name('export');
        });

        Route::resource('loans', LoanController::class)->except(['edit', 'update']);
        Route::post('loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
        Route::post('loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');
        Route::post('loans/{loan}/note', [LoanController::class, 'addNote'])->name('loans.note');
        Route::post('loans/{loan}/disburse', [LoanController::class, 'disburse'])->name('loans.disburse');
        Route::post('loans/{loan}/guarantors/{guarantor}', [LoanController::class, 'updateGuarantor'])->name('loans.guarantor.update');
        Route::get('loans/{loan}/repayment', [LoanController::class, 'repayment'])->name('loans.repayment');
        Route::post('loans/{loan}/repayment', [LoanController::class, 'storeRepayment'])->name('loans.repayment.store');
        Route::get('loans/import/repayments', [LoanController::class, 'import'])->name('loans.import');
        Route::post('loans/import/repayments', [LoanController::class, 'importStore'])->name('loans.import.store');
        Route::get('loans/download-template', [LoanController::class, 'downloadTemplate'])->name('loans.download-template');
        Route::get('loans/export', [LoanController::class, 'exportLoans'])->name('loans.export');
        Route::get('loans/{loan}/topup', [LoanController::class, 'topup'])->name('loans.topup');
        Route::post('loans/{loan}/topup', [LoanController::class, 'storeTopup'])->name('loans.topup.store');

        Route::prefix('shares')->name('shares.')->group(function () {
            Route::get('/', [ShareController::class, 'index'])->name('index');
            Route::get('/accounts', [ShareController::class, 'accounts'])->name('accounts');
            Route::get('/purchase', [ShareController::class, 'purchase'])->name('purchase');
            Route::post('/purchase', [ShareController::class, 'storePurchase'])->name('purchase.store');
            Route::get('/export', [ShareController::class, 'exportShares'])->name('export');
        });

        Route::prefix('purchases')->name('purchases.')->group(function () {
            Route::get('/', [PurchasesController::class, 'index'])->name('index');
            Route::get('/create', [PurchasesController::class, 'create'])->name('create');
            Route::get('/import', [PurchasesController::class, 'import'])->name('import');
            Route::post('/import', [PurchasesController::class, 'importStore'])->name('import.store');
            Route::get('/download-template', [PurchasesController::class, 'downloadTemplate'])->name('download-template');
        });

        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::get('/compile', [PayrollController::class, 'compile'])->name('compile');
            Route::post('/compile', [PayrollController::class, 'compilePost'])->name('compile.store');
            Route::get('/{monthlyPayroll}', [PayrollController::class, 'show'])->name('show');
            Route::get('/{monthlyPayroll}/upload', [PayrollController::class, 'upload'])->name('upload');
            Route::post('/{monthlyPayroll}/upload', [PayrollController::class, 'uploadDeductions'])->name('upload-deductions');
            Route::get('/{monthlyPayroll}/export', [PayrollController::class, 'exportDeductions'])->name('export-deductions');
            Route::get('/{monthlyPayroll}/download-template', [PayrollController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/{monthlyPayroll}/report/savings', [PayrollController::class, 'savingsReport'])->name('savings-report');
            Route::get('/{monthlyPayroll}/report/loans', [PayrollController::class, 'loansReport'])->name('loans-report');
            Route::get('/{monthlyPayroll}/report/purchases', [PayrollController::class, 'purchasesReport'])->name('purchases-report');
            Route::get('/{monthlyPayroll}/report/shares', [PayrollController::class, 'sharesReport'])->name('shares-report');
            Route::get('/{monthlyPayroll}/report/summary', [PayrollController::class, 'summaryReport'])->name('summary-report');
        });

        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::post('/update', [CartController::class, 'update'])->name('update');
            Route::post('/remove', [CartController::class, 'remove'])->name('remove');
            Route::post('/clear', [CartController::class, 'clear'])->name('clear');
            Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
            Route::post('/checkout', [CartController::class, 'processCheckout'])->name('process');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::get('/import', [ProductController::class, 'import'])->name('import');
            Route::post('/import', [ProductController::class, 'importStore'])->name('import.store');
            Route::get('/download-template', [ProductController::class, 'downloadTemplate'])->name('download-template');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::get('/orders', [ProductController::class, 'orders'])->name('orders');
            Route::get('/orders/create', [ProductController::class, 'createOrder'])->name('orders.create');
            Route::post('/orders', [ProductController::class, 'storeOrder'])->name('orders.store');
            Route::post('/orders/{order}/approve', [ProductController::class, 'approveOrder'])->name('orders.approve');
            Route::post('/orders/{order}/collect', [ProductController::class, 'collectOrder'])->name('orders.collect');
            Route::get('/orders/group/{orderGroup}', [ProductController::class, 'showOrderGroup'])->name('orders.show');
        });

        Route::prefix('dividends')->name('dividends.')->group(function () {
            Route::get('/', [DividendController::class, 'index'])->name('index');
            Route::get('/create', [DividendController::class, 'create'])->name('create');
            Route::post('/', [DividendController::class, 'store'])->name('store');
            Route::get('/{dividend}', [DividendController::class, 'show'])->name('show');
            Route::post('/{dividend}/calculate', [DividendController::class, 'calculate'])->name('calculate');
            Route::post('/{dividend}/approve', [DividendController::class, 'approve'])->name('approve');
            Route::post('/{dividend}/distribute', [DividendController::class, 'distribute'])->name('distribute');
        });

        Route::post('members/{member}/next-of-kin', [NextOfKinController::class, 'store'])->name('members.next-of-kin.store');
        Route::delete('members/{member}/next-of-kin/{nextOfKin}', [NextOfKinController::class, 'destroy'])->name('members.next-of-kin.destroy');

        Route::get('invoices/purchase/{order}', [InvoiceController::class, 'show'])->name('invoices.purchase.show');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/member/{member}', [ReportController::class, 'memberStatus'])->name('member-status');
        });

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/manage', function () {
                return view('admin.manage');
            })->name('manage');

            Route::get('/data-import', [DataImportController::class, 'index'])->name('data-import');

            Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

            Route::get('/users', [AdminController::class, 'users'])->name('users.index');
            Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
            Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
            Route::post('/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('users.reset-password');

            Route::get('/loan-products', [LoanProductController::class, 'index'])->name('loan-products.index');
            Route::get('/loan-products/create', [LoanProductController::class, 'create'])->name('loan-products.create');
            Route::post('/loan-products', [LoanProductController::class, 'store'])->name('loan-products.store');
            Route::get('/loan-products/{loanProduct}/edit', [LoanProductController::class, 'edit'])->name('loan-products.edit');
            Route::put('/loan-products/{loanProduct}', [LoanProductController::class, 'update'])->name('loan-products.update');
            Route::delete('/loan-products/{loanProduct}', [LoanProductController::class, 'destroy'])->name('loan-products.destroy');

            Route::resource('roles', RoleController::class)->except(['show']);

            Route::resource('regions', RegionController::class)->except(['show']);

            Route::get('/stock', [AdminController::class, 'stock'])->name('stock');
            Route::post('/stock/{product}', [AdminController::class, 'updateStock'])->name('stock.update');
            Route::get('/backup', [AdminController::class, 'backup'])->name('backup');
            Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');

            Route::get('/broadcasts', [\App\Http\Controllers\BroadcastController::class, 'index'])->name('broadcasts.index');
            Route::get('/broadcasts/create', [\App\Http\Controllers\BroadcastController::class, 'create'])->name('broadcasts.create');
            Route::post('/broadcasts', [\App\Http\Controllers\BroadcastController::class, 'store'])->name('broadcasts.store');

            Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications.index');
            Route::post('/notifications/{notification}/read', [AdminController::class, 'markNotificationRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [AdminController::class, 'markAllNotificationsRead'])->name('notifications.mark-all');
        });
    });

    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/savings/{transaction}', [ReceiptController::class, 'savingsDeposit'])->name('savings-deposit');
        Route::get('/loan-repayment/{repayment}', [ReceiptController::class, 'loanRepayment'])->name('loan-repayment');
        Route::get('/purchase/{order}', [ReceiptController::class, 'purchaseOrder'])->name('purchase-order');
        Route::get('/share/{transaction}', [ReceiptController::class, 'sharePurchase'])->name('share-purchase');
        Route::get('/loan-disbursement/{loan}', [ReceiptController::class, 'loanDisbursement'])->name('loan-disbursement');
        Route::get('/loan-statement/{loan}', [ReceiptController::class, 'loanStatement'])->name('loan-statement');
        Route::get('/savings-statement/{account}', [ReceiptController::class, 'savingsStatement'])->name('savings-statement');
        Route::get('/share-certificate/{account}', [ReceiptController::class, 'shareCertificate'])->name('share-certificate');
    });

    Route::prefix('my')->name('portal.')->middleware('portal-member')->group(function () {
        Route::get('/', [MemberPortalController::class, 'index'])->name('dashboard');
        Route::get('/savings', [MemberPortalController::class, 'savings'])->name('savings');
        Route::post('/savings/deposit', [MemberPortalController::class, 'requestDeposit'])->name('savings.deposit');
        Route::post('/savings/withdraw', [MemberPortalController::class, 'requestWithdrawal'])->name('savings.withdraw');
        Route::get('/loans', [MemberPortalController::class, 'loans'])->name('loans');
        Route::get('/loans/apply', [MemberPortalController::class, 'loanApplyForm'])->name('loan-apply');
        Route::post('/loans/apply', [MemberPortalController::class, 'submitLoanApplication'])->name('loan-apply.store');
        Route::get('/loans/{loan}', [MemberPortalController::class, 'loanDetail'])->name('loan-detail');
        Route::get('/shares', [MemberPortalController::class, 'shares'])->name('shares');
        Route::get('/purchases', [MemberPortalController::class, 'purchases'])->name('purchases');
        Route::get('/products', [MemberPortalController::class, 'orderProducts'])->name('products');
        Route::get('/cart', [MemberPortalController::class, 'cart'])->name('cart');
        Route::post('/cart/add', [MemberPortalController::class, 'add_to_cart'])->name('cart.add');
        Route::post('/cart/update', [MemberPortalController::class, 'update_cart'])->name('cart.update');
        Route::post('/cart/remove', [MemberPortalController::class, 'remove_from_cart'])->name('cart.remove');
        Route::post('/cart/clear', [MemberPortalController::class, 'clear_cart'])->name('cart.clear');
        Route::get('/cart/count', [MemberPortalController::class, 'cart_count'])->name('cart.count');
        Route::get('/checkout', [MemberPortalController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [MemberPortalController::class, 'processCheckout'])->name('checkout.process');
        Route::get('/guarantors', [MemberPortalController::class, 'guarantors'])->name('guarantors');
        Route::post('/guarantors/{guarantor}', [MemberPortalController::class, 'updateGuarantor'])->name('guarantor.update');
        Route::get('/notifications', [MemberPortalController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/{notification}/read', [MemberPortalController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [MemberPortalController::class, 'markAllRead'])->name('notifications.read-all');
    });
});
