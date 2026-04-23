<x-ui.sccr-card transparent wire:key="sso-device-sync-page" class="h-full min-h-0 flex flex-col">

    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div>
            <h1 class="text-3xl font-bold text-white">Device Sync</h1>
            <p class="text-slate-200 text-sm">
                Bootstrap employee ke device Hikvision dan jalankan sync manual by NIP.
            </p>
        </div>

        <div class="mt-4">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Bootstrap by Device --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h2 class="text-lg font-bold text-slate-800">Bootstrap by Device</h2>

                <div>
                    <label class="text-sm font-bold text-gray-700">Target Device</label>
                    <select wire:model.defer="deviceCode" class="w-full border-gray-300 rounded-lg text-sm mt-1">
                        @foreach ($deviceOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-gray-700">Limit</label>
                    <input type="number" min="1" max="500" wire:model.defer="limit"
                        class="w-full border-gray-300 rounded-lg text-sm mt-1">
                    <div class="text-[11px] text-gray-500 mt-1">Disarankan 20–100 per batch.</div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="onlyOutOfSync" class="rounded border-gray-300">
                    <span>Only Out of Sync</span>
                </label>

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="processNow" class="rounded border-gray-300">
                    <span>Process Now</span>
                </label>

                <div class="pt-2">
                    <x-ui.sccr-button type="button" wire:click="runBootstrap"
                        class="bg-emerald-700 text-white hover:bg-emerald-800">
                        🛰️ Jalankan Bootstrap
                    </x-ui.sccr-button>
                </div>
            </div>

            {{-- Sync by NIP --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h2 class="text-lg font-bold text-slate-800">Sync by NIP</h2>

                <div>
                    <label class="text-sm font-bold text-gray-700">Employee NIP</label>
                    <input type="text" wire:model.defer="singleNip"
                        class="w-full border-gray-300 rounded-lg text-sm mt-1" placeholder="contoh: 202507171001">
                </div>

                <div>
                    <label class="text-sm font-bold text-gray-700">Mode</label>
                    <select wire:model.live="singleMode" class="w-full border-gray-300 rounded-lg text-sm mt-1">
                        <option value="auto">Auto by Queue / Assignment</option>
                        <option value="single_device">Force Single Device</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-gray-700">Action</label>
                    <select wire:model.defer="singleAction" class="w-full border-gray-300 rounded-lg text-sm mt-1">
                        <option value="upsert_person">upsert_person</option>
                        <option value="disable_person">disable_person</option>
                        <option value="delete_person">delete_person</option>
                    </select>
                </div>

                @if ($singleMode === 'single_device')
                    <div>
                        <label class="text-sm font-bold text-gray-700">Target Device</label>
                        <select wire:model.defer="singleDeviceCode"
                            class="w-full border-gray-300 rounded-lg text-sm mt-1">
                            @foreach ($deviceOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="pt-2">
                    <x-ui.sccr-button type="button" wire:click="syncSingle"
                        class="bg-indigo-700 text-white hover:bg-indigo-800">
                        ⚙️ Proses NIP
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />
</x-ui.sccr-card>
