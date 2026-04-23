<div x-data="scannerClock()" x-init="init()">
    @vite(['resources/js/absensi-scanner.js'])

    <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            @if ($toast['show'])
                <div wire:key="scanner-toast-{{ $toastSeq }}"
                    class="mb-6 p-6 rounded-2xl text-center text-xl font-bold border-2
                        @if ($toast['type'] === 'success') bg-green-600 border-green-400 text-white
                        @elseif ($toast['type'] === 'warning') bg-yellow-400 border-yellow-200 text-gray-900
                        @else bg-red-600 border-red-400 text-white @endif"
                    x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 15000)" x-show="visible" x-transition>
                    {{ $toast['message'] }}
                </div>
            @endif

            <div class="bg-gray-800 rounded-2xl p-8 border-2 border-gray-700">
                <h1 class="text-3xl font-bold text-center text-white mb-2">
                    ABSENSI KARYAWAN
                </h1>

                <p class="text-center text-sm text-gray-400 mb-6">
                    Device: HQ-SCAN-01
                </p>

                <div class="text-center mb-8">
                    <p class="text-gray-400 text-2xl" x-text="dateDisplay"></p>
                    <p class="text-7xl font-bold text-white font-mono mt-3" x-text="timeDisplay"></p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="nipInput" class="block text-lg text-gray-400 mb-2 text-center">
                            Tempelkan Kartu / Scan QR Code
                        </label>

                        <input type="text" id="nipInput" wire:model.defer="nipInput" x-ref="nipInput"
                            x-init="$refs.nipInput.focus()" inputmode="numeric" maxlength="32"
                            class="w-full text-center text-4xl font-mono font-bold py-6 px-4 rounded-xl bg-gray-900 border-2 border-gray-600 text-white focus:border-green-500 focus:outline-none"
                            placeholder="......" autocomplete="off" />
                    </div>
                </div>

                @if ($lastEmployeeName)
                    <div class="mt-8 p-4 rounded-xl bg-gray-700 border border-gray-600">
                        <p class="text-center text-gray-400 text-sm">Absensi Terakhir</p>
                        <p class="text-center text-white text-xl font-semibold mt-1">{{ $lastEmployeeName }}</p>
                        <p class="text-center text-green-400 text-lg mt-1">
                            {{ $lastAttendanceType }} - {{ $lastTime }}
                        </p>
                    </div>
                @endif
            </div>

            <p class="text-center text-gray-500 text-sm mt-6">
                Sistem Absensi SCCR
            </p>
        </div>
    </div>
</div>

<script>
    function scannerClock() {
        return {
            timeDisplay: '00:00:00',
            dateDisplay: 'Loading...',
            init() {
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
            },
            updateClock() {
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];

                const now = new Date();
                this.timeDisplay = now.toLocaleTimeString('id-ID', {
                    hour12: false
                });

                const dayName = days[now.getDay()];
                const day = now.getDate();
                const monthName = months[now.getMonth()];
                const year = now.getFullYear();

                this.dateDisplay = `${dayName}, ${day} ${monthName} ${year}`;
            }
        }
    }
</script>
