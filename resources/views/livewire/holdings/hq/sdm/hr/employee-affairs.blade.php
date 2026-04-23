<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Employee Affairs
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if ($toast['show'])
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
                    class="mb-4 p-4 rounded-lg border {{ $toast['type'] === 'success' ? 'bg-green-100 border-green-300 text-green-800' : 'bg-red-100 border-red-300 text-red-800' }}">
                    <p class="font-semibold">{{ $toast['message'] }}</p>
                </div>
            @endif

            @if ($employee)
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-2xl p-6 space-y-6">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Employee Info</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">NIP</p>
                            <p class="text-lg font-mono text-gray-900 dark:text-white">{{ $employee->nip }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $employee->nama }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Job Title</p>
                            <p class="text-gray-900 dark:text-white">{{ $employee->job_title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-gray-900 dark:text-white">{{ $employee->employee_status }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Join</p>
                            <p class="text-gray-900 dark:text-white">{{ $employee->tanggal_join?->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Attendance Note</p>
                            <p class="text-gray-900 dark:text-white">{{ $employee->attendance_note ?? '-' }}</p>
                        </div>
                    </div>

                    @if ($canUpdate)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="actionType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Pilih Action
                                    </label>
                                    <select id="actionType" wire:model.live="actionType"
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="unlock">Unlock Attendance</option>
                                        <option value="resign">Resign</option>
                                        <option value="cuti">Cuti/Izin</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Catatan
                                    </label>
                                    <textarea id="notes" wire:model="notes" rows="3"
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Masukkan catatan (opsional)"></textarea>
                                </div>

                                <button type="button" wire:click="processAction" wire:loading.attr="disabled"
                                    class="w-full py-3 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold disabled:opacity-50">
                                    <span wire:loading.remove wire:target="processAction">Proses</span>
                                    <span wire:loading wire:target="processAction">Memproses...</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 p-4 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-800">
                            <p class="font-semibold">Anda tidak punya izin untuk update employee affairs.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-50 dark:bg-gray-800 shadow sm:rounded-2xl p-6">
                    <p class="text-gray-500 dark:text-gray-400">Loading employee...</p>
                </div>
            @endif
        </div>
    </div>
</div>