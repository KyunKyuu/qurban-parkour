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
            <a href="{{ route('admin.exports.claims') }}"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                📥 Export CSV
            </a>
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

    @push('scripts')
        <script>
            function confirmDelete(id) {
                if (confirm('Apakah Anda yakin ingin menghapus claim ini? Data akan dihapus secara soft delete.')) {
                    const form = document.getElementById('deleteForm' + id);
                    form.submit();
                }
            }

            function openEditModal(id) {
                document.getElementById('editModal' + id).classList.remove('hidden');
            }

            function closeEditModal(id) {
                document.getElementById('editModal' + id).classList.add('hidden');
            }

            // Format Rupiah Input Functionality
            document.addEventListener('DOMContentLoaded', function () {
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

            // Output error messages automatically if there's a validation error
            @if($errors->any())
                // Ideally we'd know WHICH claim failed, but since we're using a generic error bag
                alert('Ada error saat menyimpan data. Silakan cek kembali inputan Anda.');
            @endif
        </script>
    @endpush
@endsection