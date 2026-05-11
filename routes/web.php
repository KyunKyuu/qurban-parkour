<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\PicController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\InitialVoucherController;
use App\Http\Controllers\Admin\InitialVoucherAssignController;
use App\Http\Controllers\Admin\InitialVoucherPrintController;
use App\Http\Controllers\Admin\ClaimDataController;
use App\Http\Controllers\Admin\QurbanSettingController;
use App\Http\Controllers\LegacyFlowController;

// Landing Page
Route::get('/', function () {
    $pricePerSheep  = 2_500_000;
    $totalSheep     = 21;
    $targetAmount   = $pricePerSheep * $totalSheep; // 52_500_000

    $totalCollected = (float) \App\Models\Claim::where('verification_status', 'VERIFIED')
        ->whereIn('category_type', ['DOMBA', 'PATUNGAN'])
        ->sum('contribution_amount');

    $progressPct  = (int) min(100, round($totalCollected / $targetAmount * 100));
    $sheepCurrent = (int) min($totalSheep, floor($totalCollected / $pricePerSheep));

    return view('landing', compact('progressPct', 'sheepCurrent', 'totalSheep', 'totalCollected'));
})->name('landing');

// Authentication Routes
require __DIR__ . '/auth.php';

// Admin Routes - Protected by auth and role:SUPERADMIN middleware
Route::middleware(['auth', 'role:SUPERADMIN'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Financial Report
    Route::get('/financial-report', [FinancialReportController::class, 'index'])->name('financial-report');

    // PICs CRUD
    Route::resource('pics', PicController::class);

    // Communities
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/{id}', [CommunityController::class, 'show'])->name('communities.show');

    // Legacy merchant tools kept as retired routes for compatibility
    Route::prefix('merchants')->name('merchants.')->group(function () {
        Route::get('/', [LegacyFlowController::class, 'adminMerchantTools'])->name('index');
        Route::get('/create', [LegacyFlowController::class, 'adminMerchantTools'])->name('create');
        Route::post('/', [LegacyFlowController::class, 'adminMerchantTools'])->name('store');
        Route::get('/{merchant}/edit', [LegacyFlowController::class, 'adminMerchantTools'])->name('edit');
        Route::match(['put', 'patch'], '/{merchant}', [LegacyFlowController::class, 'adminMerchantTools'])->name('update');
        Route::delete('/{merchant}', [LegacyFlowController::class, 'adminMerchantTools'])->name('destroy');
    });

    Route::prefix('offers')->name('offers.')->group(function () {
        Route::get('/', [LegacyFlowController::class, 'adminMerchantTools'])->name('index');
        Route::get('/create', [LegacyFlowController::class, 'adminMerchantTools'])->name('create');
        Route::post('/', [LegacyFlowController::class, 'adminMerchantTools'])->name('store');
        Route::get('/{offer}/edit', [LegacyFlowController::class, 'adminMerchantTools'])->name('edit');
        Route::match(['put', 'patch'], '/{offer}', [LegacyFlowController::class, 'adminMerchantTools'])->name('update');
        Route::delete('/{offer}', [LegacyFlowController::class, 'adminMerchantTools'])->name('destroy');
    });

    // Analytics
    Route::get('/analytics', [LegacyFlowController::class, 'adminAnalytics'])->name('analytics');

    // Data Views
    Route::get('/transactions', [ClaimDataController::class, 'transactions'])->name('transactions.index');
    Route::get('/claims', [ClaimDataController::class, 'index'])->name('claims.index');
    Route::post('/claims', [ClaimDataController::class, 'store'])->name('claims.store');
    Route::put('/claims/{id}', [ClaimDataController::class, 'update'])->name('claims.update');
    Route::get('/claims/{id}', [ClaimDataController::class, 'show'])->name('claims.show');
    Route::get('/claims/{id}/certificate', [ClaimDataController::class, 'downloadCertificate'])->name('claims.certificate');
    Route::delete('/claims/{id}', [ClaimDataController::class, 'destroy'])->name('claims.destroy');
    Route::get('/redeems', [LegacyFlowController::class, 'adminRedeems'])->name('redeems.index');
    Route::get('/redeems/{id}', [LegacyFlowController::class, 'adminRedeems'])->name('redeems.show');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/claims', [ExportController::class, 'claims'])->name('claims');
        Route::get('/redeems', [ExportController::class, 'redeems'])->name('redeems');
        Route::get('/vouchers', [ExportController::class, 'vouchers'])->name('vouchers');
    });

    Route::prefix('settings/qurban')->name('settings.qurban.')->group(function () {
        Route::get('/', [QurbanSettingController::class, 'edit'])->name('edit');
        Route::put('/', [QurbanSettingController::class, 'update'])->name('update');
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
Route::middleware(['auth', 'role:MERCHANT'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/', [LegacyFlowController::class, 'merchantPortal'])->name('dashboard');
    Route::get('/scan', [LegacyFlowController::class, 'merchantPortal'])->name('scan');

    Route::post('/scan/validate', [LegacyFlowController::class, 'merchantApi'])
        ->name('scan.validate');
    Route::post('/scan/redeem', [LegacyFlowController::class, 'merchantApi'])
        ->name('scan.redeem');

    Route::get('/redemptions', [LegacyFlowController::class, 'merchantPortal'])->name('redemptions');
    Route::get('/analytics', [LegacyFlowController::class, 'merchantPortal'])->name('analytics');
});

// PIC Routes - Protected by auth and role:PIC middleware
use App\Http\Controllers\Pic\DashboardController as PicDashboardController;

Route::middleware(['auth', 'role:PIC'])->prefix('pic')->name('pic.')->group(function () {
    Route::get('/', [PicDashboardController::class, 'index'])->name('dashboard');
    Route::get('/data/export', [PicDashboardController::class, 'exportData'])->name('data.export');
    Route::get('/data/export-excel', [PicDashboardController::class, 'exportExcel'])->name('data.export-excel');
    Route::get('/vouchers/export-pdf', [PicDashboardController::class, 'exportVouchersPdf'])->name('vouchers.export-pdf');
    Route::get('/vouchers/export-pdf-komunitas', [PicDashboardController::class, 'exportVouchersPdfKomunitas'])->name('vouchers.export-pdf-komunitas');
    Route::get('/claims/{id}/certificate', [PicDashboardController::class, 'downloadCertificate'])->name('claims.certificate');
});

// Public Routes - No authentication required
use App\Http\Controllers\Public\ClaimController;
use App\Http\Controllers\Public\VoucherListController;

// Direct contribution disabled — must use voucher QR code
Route::get('/kurban', fn () => redirect()->route('public.claim-closed'))->name('public.contribute');
Route::get('/claim/{code}', [ClaimController::class, 'show'])->name('public.claim');
Route::get('/claim-closed', [ClaimController::class, 'closed'])->name('public.claim-closed');
Route::post('/claim', [ClaimController::class, 'store'])
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('public.claim.store');
Route::get('/sertifikat/{token}', [VoucherListController::class, 'show'])->name('public.certificate');
Route::get('/sertifikat/{token}/download', [ClaimController::class, 'downloadCertificate'])->name('public.certificate.download');
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
