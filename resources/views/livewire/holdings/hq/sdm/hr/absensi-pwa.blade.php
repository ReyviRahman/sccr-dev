<div x-data="pwaClock()">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Absensi Karyawan
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-6">
                <p class="text-gray-600 dark:text-gray-400 text-xl" x-text="dateDisplay"></p>
                <p class="text-5xl font-bold text-blue-600 dark:text-blue-400 font-mono mt-1" x-text="timeDisplay"></p>
            </div>

            @if ($toast['show'])
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
                    class="mb-4 p-4 rounded-lg border {{ $toast['type'] === 'success' ? 'bg-green-100 border-green-300 text-green-800' : 'bg-red-100 border-red-300 text-red-800' }}">
                    <p class="font-semibold">{{ $toast['message'] }}</p>
                </div>
            @endif

            @if ($employee)
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-2xl p-6 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nama Karyawan</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $employee->nama }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">NIP</p>
                            <p class="text-lg font-mono text-gray-900 dark:text-white">{{ $employee->nip }}</p>
                        </div>
                    </div>

                    @if (!$canAbsen)
                        <div class="p-4 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-800">
                            <p class="font-semibold">Anda tidak wajib absensi.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div>
                                <label for="holding" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Kantor/Lokasi
                                </label>
                                <select id="holding" wire:model.live="selectedHoldingId"
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                    @foreach ($holdings as $h)
                                        <option value="{{ $h['id'] }}">{{ $h['name'] }} ({{ $h['alias'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" wire:click="absen('IN')" wire:loading.attr="disabled"
                                    class="py-4 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-lg disabled:opacity-50">
                                    <span wire:loading.remove wire:target="absen('IN')">ABSENSI MASUK</span>
                                    <span wire:loading wire:target="absen('IN')">Memproses...</span>
                                </button>
                                <button type="button" wire:click="absen('OUT')" wire:loading.attr="disabled"
                                    class="py-4 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-lg disabled:opacity-50">
                                    <span wire:loading.remove wire:target="absen('OUT')">ABSENSI PULANG</span>
                                    <span wire:loading wire:target="absen('OUT')">Memproses...</span>
                                </button>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                * Pastikan GPS aktif dan berada di sekitar kantor
                            </p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-2xl p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Riwayat Absensi Hari Ini</h3>
                    @if (empty($todayLogs))
                        <p class="text-gray-500 dark:text-gray-400">Belum ada absensi hari ini.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($todayLogs as $log)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $log['attendance_type'] === 'IN' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $log['attendance_type'] === 'IN' ? 'MASUK' : 'PULANG' }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($log['attendance_at'])->setTimezone('Asia/Jakarta')->format('H:i:s') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-red-100 border border-red-300 rounded-lg p-4">
                    <p class="text-red-800 font-semibold">Data employee tidak ditemukan. Silakan hubungi HR.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function pwaClock() {
    return {
        timeDisplay: '00:00:00',
        dateDisplay: 'Loading...',
        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },
        updateClock() {
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const now = new Date();
            this.timeDisplay = now.toLocaleTimeString('id-ID', { hour12: false });
            const dayName = days[now.getDay()];
            const day = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            this.dateDisplay = `${dayName}, ${day} ${monthName} ${year}`;
        }
    }
}
</script>