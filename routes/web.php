<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PicController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\InitialVoucherController;
use App\Http\Controllers\Admin\InitialVoucherAssignController;
use App\Http\Controllers\Admin\InitialVoucherPrintController;
use App\Http\Controllers\Admin\ClaimDataController;
use App\Http\Controllers\Admin\RedeemDataController;

// Landing Page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Authentication Routes
require __DIR__ . '/auth.php';

// Admin Routes - Protected by auth and role:SUPERADMIN middleware
Route::middleware(['auth', 'role:SUPERADMIN'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // PICs CRUD
    Route::resource('pics', PicController::class);

    // Merchants CRUD
    Route::resource('merchants', MerchantController::class);

    // Offers CRUD
    Route::resource('offers', OfferController::class);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // Data Views
    Route::get('/claims', [ClaimDataController::class, 'index'])->name('claims.index');
    Route::put('/claims/{id}', [ClaimDataController::class, 'update'])->name('claims.update');
    Route::get('/claims/{id}', [ClaimDataController::class, 'show'])->name('claims.show');
    Route::delete('/claims/{id}', [ClaimDataController::class, 'destroy'])->name('claims.destroy');
    Route::get('/redeems', [RedeemDataController::class, 'index'])->name('redeems.index');
    Route::get('/redeems/{id}', [RedeemDataController::class, 'show'])->name('redeems.show');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/claims', [ExportController::class, 'claims'])->name('claims');
        Route::get('/redeems', [ExportController::class, 'redeems'])->name('redeems');
        Route::get('/vouchers', [ExportController::class, 'vouchers'])->name('vouchers');
    });

    // Voucher Management
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        // Generate
        Route::get('/generate', [InitialVoucherController::class, 'create'])->name('generate');
        Route::post('/generate', [InitialVoucherController::class, 'store']);

        // Assign
        Route::get('/assign', [InitialVoucherAssignController::class, 'create'])->name('assign');
        Route::post('/assign', [InitialVoucherAssignController::class, 'store']);

        // Print
        Route::get('/print', [InitialVoucherPrintController::class, 'index'])->name('print');
        Route::get('/print/pdf', [InitialVoucherPrintController::class, 'pdf'])->name('print.pdf');
        Route::get('/print/preview', [InitialVoucherPrintController::class, 'printPreview'])->name('print.preview');
    });
    // Fund Verification Routes
    Route::get('/fund-verification', [App\Http\Controllers\Admin\FundVerificationController::class, 'index'])->name('fund-verification.index');
    Route::get('/fund-verification/{date}', [App\Http\Controllers\Admin\FundVerificationController::class, 'show'])->name('fund-verification.show');
    Route::post('/fund-verification/{date}/verify', [App\Http\Controllers\Admin\FundVerificationController::class, 'verifyDay'])->name('fund-verification.verify-day');
    Route::post('/fund-verification/{claim}/anomaly', [App\Http\Controllers\Admin\FundVerificationController::class, 'markAnomaly'])->name('fund-verification.mark-anomaly');
});

// Merchant Routes - Protected by auth and role:MERCHANT middleware
use App\Http\Controllers\Merchant\DashboardController as MerchantDashboardController;
use App\Http\Controllers\Merchant\ScanController;
use App\Http\Controllers\Merchant\RedemptionController;
use App\Http\Controllers\Merchant\AnalyticsController as MerchantAnalyticsController;

Route::middleware(['auth', 'role:MERCHANT'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/', [MerchantDashboardController::class, 'index'])->name('dashboard');
    Route::get('/scan', [ScanController::class, 'index'])->name('scan');

    // Rate limited endpoints for validation and redemption
    Route::post('/scan/validate', [ScanController::class, 'validateVoucher'])
        ->middleware('throttle:30,1') // 30 requests per minute
        ->name('scan.validate');
    Route::post('/scan/redeem', [ScanController::class, 'redeem'])
        ->middleware('throttle:20,1') // 20 requests per minute
        ->name('scan.redeem');

    Route::get('/redemptions', [RedemptionController::class, 'index'])->name('redemptions');
    Route::get('/analytics', [MerchantAnalyticsController::class, 'index'])->name('analytics');
});

// PIC Routes - Protected by auth and role:PIC middleware
use App\Http\Controllers\Pic\DashboardController as PicDashboardController;

Route::middleware(['auth', 'role:PIC'])->prefix('pic')->name('pic.')->group(function () {
    Route::get('/', [PicDashboardController::class, 'index'])->name('dashboard');
    Route::get('/vouchers/export', [PicDashboardController::class, 'exportVouchers'])->name('vouchers.export');
    Route::get('/data/export', [PicDashboardController::class, 'exportData'])->name('data.export');
});

// Public Routes - No authentication required
use App\Http\Controllers\Public\ClaimController;
use App\Http\Controllers\Public\VoucherListController;

Route::get('/claim/{code}', [ClaimController::class, 'show'])->name('public.claim');
Route::post('/claim', [ClaimController::class, 'store'])
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('public.claim.store');
Route::get('/v/{token}', [VoucherListController::class, 'show'])->name('public.vouchers');

// Debug route - REMOVE AFTER FIX
// Route::get('/debug-php', function() {
//     return [
//         'post_max_size' => ini_get('post_max_size'),
//         'upload_max_filesize' => ini_get('upload_max_filesize'),
//         'memory_limit' => ini_get('memory_limit'),
//         'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'undefined',
//     ];
// });

// Route::get('/debug-claim', function(App\Services\ClaimService $service) {
//     try {
//         // Needs an existing voucher code that is ASSIGNED and NOT CLAIMED
//         // We will try to find one first
//         $voucher = \App\Models\InitialVoucher::where('status', 'ASSIGNED')->first();

//         if (!$voucher) {
//             return "No ASSIGNED voucher found to test.";
//         }

//         // Ensure PIC exists
//         if (!$voucher->pic) {
//              return "Voucher " . $voucher->code . " has no PIC assigned.";
//         }

//         echo "Testing Claim for Code: " . $voucher->code . "<br>";
//         echo "PIC ID: " . $voucher->assigned_pic_id . "<br>";

//         $claim = $service->processClaim(
//             $voucher->code,
//             $voucher->assigned_pic_id, // Correct PIC
//             'Debug User',
//             'debug@example.com',
//             '08123456789', // Phone
//             10000, 20000, 30000 // Amounts
//         );

//         return "SUCCESS! Claim created with token: " . $claim->public_token;

//     } catch (\Throwable $e) {
//         dd($e); // Dump the full error to screen
//     }
// });
