    <div class="min-h-screen bg-gray-100">
        @if (!$hasEmployee)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Akses Ditolak</h2>
                    <p class="text-gray-600">{{ $errorMessage }}</p>
                    <a href="{{ route('dashboard') }}"
                        class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Dashboard Karyawan</h1>
                        <p class="text-gray-600 mt-1">{{ $employee['nama'] }} ({{ $employee['nip'] }})</p>
                        <p class="text-sm text-gray-500 mt-1">Periode Gaji: {{ $periodLabel }}</p>
                    </div>
                    <div>
                        <button wire:click="refreshDashboardData" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>

                <div class="space-y-8">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Absensi Hari Ini</h2>
                        @if ($todayAttendance['has_attendance'])
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                        <div>
                                            <p class="text-sm text-gray-500">Tanggal</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                {{ \Carbon\Carbon::parse($todayAttendance['work_date'])->locale('id')->format('l, d F Y') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Jam Masuk</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                {{ $todayAttendance['first_in_at'] ? \Carbon\Carbon::parse($todayAttendance['first_in_at'])->format('H:i:s') : '-' }}
                                            </p>
                                            @if ($todayAttendance['is_late'])
                                                <p class="text-xs text-red-600 mt-1">
                                                    Terlambat {{ $todayAttendance['late_minutes'] }} menit
                                                </p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Jam Pulang</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                {{ $todayAttendance['last_out_at'] ? \Carbon\Carbon::parse($todayAttendance['last_out_at'])->format('H:i:s') : '-' }}
                                            </p>
                                            @if ($todayAttendance['is_early_out'])
                                                <p class="text-xs text-orange-600 mt-1">
                                                    Pulang awal {{ $todayAttendance['early_out_minutes'] }} menit
                                                </p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Durasi Kerja</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                {{ $todayAttendance['work_minutes'] > 0 ? floor($todayAttendance['work_minutes'] / 60) . 'j ' . $todayAttendance['work_minutes'] % 60 . 'm' : '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex items-center justify-between">
                                        <div>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($todayAttendance['attendance_status']) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Budget Lunch: Rp
                                            {{ number_format($todayAttendance['lunch_budget'] ?? 0, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-white rounded-lg shadow p-8 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-gray-600 mb-4">Anda belum melakukan absensi hari ini</p>
                                <a href="{{ route('absensi.scanner') }}"
                                    class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                    Scan Absensi Sekarang
                                </a>
                            </div>
                        @endif
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Absensi Periode</h2>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            @if (count($periodAttendance) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tanggal</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Hari</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Jam Masuk</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Jam Pulang</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Terlambat</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($periodAttendance as $attendance)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($attendance['work_date'])->format('d/m/Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $attendance['day_name'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $attendance['first_in_at'] ? \Carbon\Carbon::parse($attendance['first_in_at'])->format('H:i') : '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $attendance['last_out_at'] ? \Carbon\Carbon::parse($attendance['last_out_at'])->format('H:i') : '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            {{ ucfirst($attendance['attendance_status']) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        @if ($attendance['is_late'])
                                                            <span
                                                                class="text-red-600">{{ $attendance['late_minutes'] }}
                                                                menit</span>
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $attendance['work_minutes'] > 0 ? floor($attendance['work_minutes'] / 60) . 'j ' . $attendance['work_minutes'] % 60 . 'm' : '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-8 text-center text-gray-500">
                                    Tidak ada data absensi pada periode ini
                                </div>
                            @endif
                        </div>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Transaksi Meal & Potongan Payroll</h2>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            @if (count($mealOrders) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tanggal</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tipe</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Sumber</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Base Amount</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Extra</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Budget Company</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Over Budget</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Metode</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($mealOrders as $order)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($order['work_date'])->format('d/m/Y') }}
                                                        <span
                                                            class="text-gray-400 ml-1">{{ $order['day_name'] }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order['meal_type'] === 'lunch' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                                            {{ ucfirst($order['meal_type']) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order['source'] ?? '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        Rp {{ number_format($order['base_amount'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order['extra_amount'] > 0 ? 'Rp ' . number_format($order['extra_amount'], 0, ',', '.') : '-' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        Rp
                                                        {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                                        Rp
                                                        {{ number_format($order['company_budget_amount'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                        {{ $order['employee_over_amount'] > 0 ? 'Rp ' . number_format($order['employee_over_amount'], 0, ',', '.') : '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order['settlement_method'] ?? '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order['settlement_status'] === 'settled' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                            {{ ucfirst($order['settlement_status']) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        Total Transaksi: <span class="font-medium">{{ count($mealOrders) }}</span>
                                        pesanan
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Total Potongan Payroll: Rp
                                        {{ number_format($budgetSummary['total_deductions'], 0, ',', '.') }}
                                    </div>
                                </div>
                            @else
                                <div class="p-8 text-center text-gray-500">
                                    Tidak ada transaksi meal pada periode ini
                                </div>
                            @endif
                        </div>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Summary Budget</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Lunch</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Total Hari Kerja</span>
                                        <span class="font-medium text-gray-900">{{ $budgetSummary['work_days'] }}
                                            hari</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Budget per Hari</span>
                                        <span class="font-medium text-gray-900">Rp
                                            {{ number_format($employee['daily_lunch_budget'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="border-t pt-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Budget</span>
                                            <span class="font-bold text-gray-900">Rp
                                                {{ number_format($budgetSummary['total_lunch_budget'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Sudah Digunakan</span>
                                        <span class="font-medium text-orange-600">Rp
                                            {{ number_format($budgetSummary['total_lunch_used'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Sisa Budget</span>
                                        <span
                                            class="font-bold {{ $budgetSummary['total_lunch_budget'] - $budgetSummary['total_lunch_used'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Rp
                                            {{ number_format($budgetSummary['total_lunch_budget'] - $budgetSummary['total_lunch_used'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Snack</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Total Hari Kerja</span>
                                        <span class="font-medium text-gray-900">{{ $budgetSummary['work_days'] }}
                                            hari</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Budget per Hari</span>
                                        <span class="font-medium text-gray-900">Rp
                                            {{ number_format($employee['daily_snack_budget'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="border-t pt-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Budget</span>
                                            <span class="font-bold text-gray-900">Rp
                                                {{ number_format($budgetSummary['total_snack_budget'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Sudah Digunakan</span>
                                        <span class="font-medium text-blue-600">Rp
                                            {{ number_format($budgetSummary['total_snack_used'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Sisa Budget</span>
                                        <span
                                            class="font-bold {{ $budgetSummary['total_snack_budget'] - $budgetSummary['total_snack_used'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Rp
                                            {{ number_format($budgetSummary['total_snack_budget'] - $budgetSummary['total_snack_used'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">Total Potongan Payroll Periode Ini
                            </h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-700">Total meal orders:
                                        {{ $budgetSummary['total_meal_orders'] }} pesanan</p>
                                    <p class="text-sm text-blue-600 mt-1">Akan dipotong dari gaji periode ini</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold text-blue-900">Rp
                                        {{ number_format($budgetSummary['total_deductions'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        @endif
    </div>
