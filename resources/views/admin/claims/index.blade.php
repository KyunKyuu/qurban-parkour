@extends('layouts.admin')

@section('title', 'Claims Data')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Claims Data</h2>
                <p class="text-gray-600">Daftar semua klaim voucher</p>
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="openCreateModal()"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                    ➕ Tambah Data Claim
                </button>
                <a href="{{ route('admin.exports.claims') }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Total Claims</div>
                <div class="text-3xl font-bold text-green-600">{{ number_format($stats['total_claims']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Today</div>
                <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['today_claims']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">This Week</div>
                <div class="text-3xl font-bold text-purple-600">{{ number_format($stats['this_week_claims']) }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('admin.claims.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">PIC</label>
                    <select name="pic_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All PICs</option>
                        @foreach($pics as $pic)
                            <option value="{{ $pic->id }}" {{ request('pic_id') == $pic->id ? 'selected' : '' }}>
                                {{ $pic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">Filter</button>
                    <a href="{{ route('admin.claims.index') }}"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- Claims Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto overflow-y-hidden">
                <table class="min-w-full divide-y divide-gray-200 whitespace-nowrap">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Voucher Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Donation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fund Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Claimed At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($claims as $claim)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $claim->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $claim->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <code
                                        class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $claim->initialVoucher->code ?? 'N/A' }}</code>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $claim->initialVoucher->pic->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $claim->initialVoucher->batch->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($claim->verification_status == 'VERIFIED')
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Verified
                                        </span>
                                    @elseif($claim->verification_status == 'ANOMALY')
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Anomaly
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $claim->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.claims.show', $claim->id) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                                    <span class="text-gray-300 mx-2">|</span>
                                    <button type="button" onclick="openEditModal({{ $claim->id }})"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                    <span class="text-gray-300 mx-2">|</span>
                                    <button type="button" onclick="confirmDelete({{ $claim->id }})"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>

                                    <!-- Delete Form -->
                                    <form id="deleteForm{{ $claim->id }}"
                                        action="{{ route('admin.claims.destroy', $claim->id) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal for Claim #{{ $claim->id }} -->
                            <div id="editModal{{ $claim->id }}" class="fixed inset-0 z-[100] hidden overflow-y-auto"
                                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div
                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                        onclick="closeEditModal({{ $claim->id }})" aria-hidden="true"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                        aria-hidden="true">&#8203;</span>
                                    <div
                                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                                        <form action="{{ route('admin.claims.update', $claim->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                        Edit Claim #{{ $claim->id }}
                                                    </h3>
                                                    <button type="button" onclick="closeEditModal({{ $claim->id }})"
                                                        class="text-gray-400 hover:text-gray-500">
                                                        <span class="sr-only">Close</span>
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-3">
                                                        <h4 class="text-sm font-medium text-gray-900 border-b pb-1">Donor Info
                                                        </h4>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Name</label>
                                                            <input type="text" name="name"
                                                                value="{{ old('name', $claim->name) }}"
                                                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                required>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Phone</label>
                                                            <input type="text" name="phone"
                                                                value="{{ old('phone', $claim->phone) }}"
                                                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                required>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Email</label>
                                                            <input type="email" name="email"
                                                                value="{{ old('email', $claim->email) }}"
                                                                class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-3">
                                                        <h4 class="text-sm font-medium text-gray-900 border-b pb-1">Donations
                                                            (Rp)</h4>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Zakat Fitrah</label>
                                                            <div class="relative">
                                                                <div
                                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                    <span class="text-gray-500 sm:text-sm">Rp.</span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ number_format(old('zakat_fitrah_amount', $claim->zakat_fitrah_amount), 0, ',', '.') }}"
                                                                    class="rupiah-input w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                    required>
                                                                <input type="hidden" name="zakat_fitrah_amount"
                                                                    value="{{ old('zakat_fitrah_amount', $claim->zakat_fitrah_amount) }}"
                                                                    class="raw-value">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Zakat Mal</label>
                                                            <div class="relative">
                                                                <div
                                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                    <span class="text-gray-500 sm:text-sm">Rp.</span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ number_format(old('zakat_mal_amount', $claim->zakat_mal_amount), 0, ',', '.') }}"
                                                                    class="rupiah-input w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                    required>
                                                                <input type="hidden" name="zakat_mal_amount"
                                                                    value="{{ old('zakat_mal_amount', $claim->zakat_mal_amount) }}"
                                                                    class="raw-value">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Infaq</label>
                                                            <div class="relative">
                                                                <div
                                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                    <span class="text-gray-500 sm:text-sm">Rp.</span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ number_format(old('infaq_amount', $claim->infaq_amount), 0, ',', '.') }}"
                                                                    class="rupiah-input w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                    required>
                                                                <input type="hidden" name="infaq_amount"
                                                                    value="{{ old('infaq_amount', $claim->infaq_amount) }}"
                                                                    class="raw-value">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-700 mb-1">Sodaqoh</label>
                                                            <div class="relative">
                                                                <div
                                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                    <span class="text-gray-500 sm:text-sm">Rp.</span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ number_format(old('sodaqoh_amount', $claim->sodaqoh_amount), 0, ',', '.') }}"
                                                                    class="rupiah-input w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500"
                                                                    required>
                                                                <input type="hidden" name="sodaqoh_amount"
                                                                    value="{{ old('sodaqoh_amount', $claim->sodaqoh_amount) }}"
                                                                    class="raw-value">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse space-x-2 space-x-reverse">
                                                <button type="submit"
                                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                    Save Settings
                                                </button>
                                                <button type="button" onclick="closeEditModal({{ $claim->id }})"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No claims found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($claims->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $claims->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Claim Modal -->
    <div id="createModal" class="fixed inset-0 z-[100] hidden overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                onclick="closeCreateModal()" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full max-h-[90vh] overflow-y-auto">
                <form action="{{ route('admin.claims.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Tambah Data Claim Baru
                            </h3>
                            <button type="button" onclick="closeCreateModal()"
                                class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Error Messages -->
                        @if($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                                @foreach($errors->all() as $error)
                                    <p class="whitespace-pre-line">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Left Column -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-gray-900 border-b pb-1">Data Voucher & PIC</h4>

                                <!-- Voucher Code -->
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Kode Voucher <span class="text-red-500">*</span></label>
                                    <input type="text" name="code"
                                        class="w-full px-3 py-2 border @error('code') border-red-500 @enderror border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Masukkan kode voucher" required>
                                    @error('code')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- PIC Selection -->
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Pilih PIC <span class="text-red-500">*</span></label>
                                    <select name="pic_id"
                                        class="w-full px-3 py-2 border @error('pic_id') border-red-500 @enderror border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                                        required>
                                        <option value="">-- Pilih PIC --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}">{{ $pic->name }}{{ $pic->code ? ' (' . $pic->code . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('pic_id')
                                        <p class="mt-1 text-xs text-red-600 whitespace-pre-line">{{ $message }}</p>
                                    @enderror
                                </div>

                                <h4 class="text-sm font-medium text-gray-900 border-b pb-1 pt-2">Data Donor</h4>

                                <!-- Name -->
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" name="name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror"
                                        placeholder="Masukkan nama lengkap" required>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">No. HP <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 @error('phone') border-red-500 @enderror"
                                        placeholder="Contoh: 081234567890" required>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 @error('email') border-red-500 @enderror"
                                        placeholder="nama@email.com" required>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                                    <select name="payment_method" id="payment_method"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 @error('payment_method') border-red-500 @enderror"
                                        required>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>

                                <div id="transferFields" class="space-y-3 hidden">
                                    <div>
                                        <label class="block text-xs text-gray-700 mb-1">Transfer ke Mana <span class="text-red-500">*</span></label>
                                        <input type="text" name="transfer_destination"
                                            value="Blu 090109627811 a.n Ahmad Bustan Djatmadipura"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 text-sm cursor-not-allowed"
                                            readonly>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-700 mb-1">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                                        <input type="file" name="transfer_proof"
                                            accept=".jpg,.jpeg,.png,.pdf,image/*,application/pdf"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 @error('transfer_proof') border-red-500 @enderror">
                                        <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, PDF. Maksimal 4MB.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Donations -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-gray-900 border-b pb-1">Nominal Penyaluran</h4>

                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Zakat Fitrah (Rp)</label>
                                    <input type="text" id="zakat_fitrah_amount" name="zakat_fitrah_amount_text"
                                        class="currency-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Rp 0">
                                    <input type="hidden" id="zakat_fitrah_amount_raw" name="zakat_fitrah_amount" value="0">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Zakat Mal (Rp)</label>
                                    <input type="text" id="zakat_mal_amount" name="zakat_mal_amount_text"
                                        class="currency-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Rp 0">
                                    <input type="hidden" id="zakat_mal_amount_raw" name="zakat_mal_amount" value="0">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Infaq (Rp)</label>
                                    <input type="text" id="infaq_amount" name="infaq_amount_text"
                                        class="currency-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Rp 0">
                                    <input type="hidden" id="infaq_amount_raw" name="infaq_amount" value="0">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">Sodaqoh (Rp)</label>
                                    <input type="text" id="sodaqoh_amount" name="sodaqoh_amount_text"
                                        class="currency-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Rp 0">
                                    <input type="hidden" id="sodaqoh_amount_raw" name="sodaqoh_amount" value="0">
                                </div>

                                <div class="pt-2">
                                    <p class="text-xs text-gray-500">
                                        Minimum total penyaluran Rp {{ number_format(config('app.min_claim_amount', 35000), 0, ',', '.') }}
                                        untuk mendapatkan voucher merchant.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse space-x-2 space-x-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Claim
                        </button>
                        <button type="button" onclick="closeCreateModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(id) {
                if (confirm('Apakah Anda yakin ingin menghapus claim ini? Data akan dihapus secara soft delete.')) {
                    const form = document.getElementById('deleteForm' + id);
                    form.submit();
                }
            }

            function openCreateModal() {
                document.getElementById('createModal').classList.remove('hidden');
            }

            function closeCreateModal() {
                document.getElementById('createModal').classList.add('hidden');
            }

            function openEditModal(id) {
                document.getElementById('editModal' + id).classList.remove('hidden');
            }

            function closeEditModal(id) {
                document.getElementById('editModal' + id).classList.add('hidden');
            }

            // Format Rupiah Input Functionality
            document.addEventListener('DOMContentLoaded', function () {
                // Payment method toggle for create modal
                const paymentMethod = document.getElementById('payment_method');
                const transferFields = document.getElementById('transferFields');

                function toggleTransferFields() {
                    const isTransfer = paymentMethod && paymentMethod.value === 'transfer';
                    if (transferFields) {
                        transferFields.classList.toggle('hidden', !isTransfer);
                    }
                }

                if (paymentMethod) {
                    paymentMethod.addEventListener('change', toggleTransferFields);
                }

                // Edit modal rupiah inputs
                const rupiahInputs = document.querySelectorAll('.rupiah-input');

                rupiahInputs.forEach(input => {
                    input.addEventListener('keyup', function (e) {
                        // Get raw numeric value
                        let val = this.value.replace(/[^0-9]/g, '');

                        // Update hidden input that corresponds to this visible input
                        const hiddenInput = this.parentElement.querySelector('.raw-value');
                        if (hiddenInput) {
                            hiddenInput.value = val ? val : 0;
                        }

                        // Format visible value with dot separators
                        this.value = formatRupiah(val);
                    });
                });

                // Create modal currency inputs
                const currencyInputs = document.querySelectorAll('.currency-input');

                function formatRupiahWithPrefix(num) {
                    if (!num || num === '0') return '';
                    let str = num.toString();
                    let formatted = '';
                    let count = 0;
                    for (let i = str.length - 1; i >= 0; i--) {
                        if (count > 0 && count % 3 === 0) {
                            formatted = '.' + formatted;
                        }
                        formatted = str[i] + formatted;
                        count++;
                    }
                    return 'Rp ' + formatted;
                }

                function updateHiddenField(inputId, rawValue) {
                    const hiddenField = document.getElementById(inputId + '_raw');
                    if (hiddenField) {
                        hiddenField.value = rawValue;
                    }
                }

                currencyInputs.forEach(function(input) {
                    input.addEventListener('input', function(e) {
                        let value = this.value.replace(/\D/g, '');
                        updateHiddenField(this.id, value || 0);
                        this.value = formatRupiahWithPrefix(value);
                    });

                    input.addEventListener('keydown', function(e) {
                        if ([8, 9, 27, 13, 35, 36, 37, 38, 39, 40, 46].indexOf(e.keyCode) !== -1 ||
                            (e.ctrlKey && [65, 67, 86, 88].indexOf(e.keyCode) !== -1)) {
                            return;
                        }
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }
                    });

                    input.addEventListener('blur', function() {
                        let value = this.value.replace(/\D/g, '');
                        updateHiddenField(this.id, value || 0);
                        this.value = formatRupiahWithPrefix(value);
                    });

                    let initialValue = input.value.replace(/\D/g, '');
                    updateHiddenField(input.id, initialValue || 0);
                });

                // Update hidden fields before form submission
                const createForm = document.querySelector('form[action="{{ route('admin.claims.store') }}"]');
                if (createForm) {
                    createForm.addEventListener('submit', function(e) {
                        currencyInputs.forEach(function(input) {
                            let value = input.value.replace(/\D/g, '');
                            updateHiddenField(input.id, value || 0);
                        });
                    });
                }
            });

            // Helper to format string numbers with period separators
            function formatRupiah(numberStr) {
                if (!numberStr) return '';
                const split = numberStr.toString().split(',');
                const sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    const separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            }

            // Show success message
            @if(session('success'))
                alert('{{ session('success') }}');
            @endif

            // Auto-open modal if there are errors
            @if($errors->any() || session('error'))
                openCreateModal();
            @endif
        </script>
    @endpush
@endsection