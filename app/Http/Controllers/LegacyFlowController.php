<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LegacyFlowController extends Controller
{
    public function adminAnalytics(): Response
    {
        return redirect()
            ->route('admin.dashboard')
            ->with('error', 'Analytics merchant lama sudah dipensiunkan. Gunakan Dashboard Kurban untuk monitoring utama.');
    }

    public function adminRedeems(): Response
    {
        return redirect()
            ->route('admin.claims.index')
            ->with('error', 'Data redeem merchant sudah dipensiunkan dari rebuild kurban.');
    }

    public function adminMerchantTools(): Response
    {
        return redirect()
            ->route('admin.dashboard')
            ->with('error', 'Modul merchant dan offer lama sudah dipensiunkan dari flow utama kurban.');
    }

    public function merchantPortal(): Response
    {
        return response()->view('merchant.retired', [], 410);
    }

    public function merchantApi(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Portal merchant lama sudah dipensiunkan.',
        ], 410);
    }
}
