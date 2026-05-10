<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InitialVoucherGeneratorService;
use Illuminate\Http\Request;

class InitialVoucherController extends Controller
{
    protected $generatorService;

    public function __construct(InitialVoucherGeneratorService $generatorService)
    {
        $this->generatorService = $generatorService;
    }

    /**
     * Show the form for generating vouchers.
     */
    public function create()
    {
        return view('admin.vouchers.generate');
    }

    /**
     * Generate vouchers.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:1000',
            'batch_name' => 'nullable|string|max:100',
        ]);

        try {
            $batch = $this->generatorService->generate(
                $validated['count'],
                $validated['batch_name'] ?? null,
                auth()->id(),
            );

            return redirect()
                ->route('admin.vouchers.generate')
                ->with('success', "Successfully generated {$batch->generated_count} vouchers in batch: {$batch->name}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to generate vouchers: ' . $e->getMessage());
        }
    }
}
