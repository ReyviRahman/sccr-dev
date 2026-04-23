<x-ui.sccr-card transparent wire:key="sso-devtools-index" class="h-full min-h-0 flex flex-col">

    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">SSO Dev Tools</h1>
                <p class="text-slate-200 text-sm">
                    Control center untuk device sync, queue, registry, dan tools internal SSO.
                </p>
            </div>

            <x-ui.sccr-button type="button" wire:click="refreshDashboard"
                class="bg-slate-900 text-white hover:bg-slate-700">
                🔄 Refresh
            </x-ui.sccr-button>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl shadow p-4">
                <div class="text-xs text-gray-500 uppercase font-bold">Active Devices</div>
                <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_devices'] ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="text-xs text-gray-500 uppercase font-bold">Provision Enabled</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $stats['provision_enabled_devices'] ?? 0 }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="text-xs text-gray-500 uppercase font-bold">Pending Jobs</div>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_jobs'] ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="text-xs text-gray-500 uppercase font-bold">Failed / Partial</div>
                <div class="text-2xl font-bold text-red-700 mt-1">{{ $stats['failed_jobs'] ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="text-xs text-gray-500 uppercase font-bold">Registry Synced</div>
                <div class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['registry_synced'] ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="text-xs text-gray-500 uppercase font-bold">Registry Failed</div>
                <div class="text-2xl font-bold text-rose-700 mt-1">{{ $stats['registry_failed'] ?? 0 }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('sso.devtools.device-sync') }}"
                class="px-4 py-2 rounded-lg bg-emerald-700 text-white hover:bg-emerald-800 font-medium">
                🛰️ Device Sync
            </a>

            <a href="{{ route('sso.devtools.provision-queue') }}"
                class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900 font-medium">
                📦 Provision Queue
            </a>

            <a href="{{ route('sso.devtools.person-registry') }}"
                class="px-4 py-2 rounded-lg bg-indigo-700 text-white hover:bg-indigo-800 font-medium">
                🧾 Person Registry
            </a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b font-semibold text-slate-800">Recent Failed / Partial Jobs</div>

                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-700 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">NIP</th>
                                <th class="px-4 py-3 text-left">Action</th>
                                <th class="px-4 py-3 text-left">Device</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentFailedJobs as $row)
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ $row['id'] }}</td>
                                    <td class="px-4 py-2 font-mono">{{ $row['employee_nip'] }}</td>
                                    <td class="px-4 py-2">{{ $row['action_type'] }}</td>
                                    <td class="px-4 py-2">{{ $row['target_device_code'] ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $row['queue_status'] }}</td>
                                    <td class="px-4 py-2 text-xs text-red-700">{{ $row['last_error_message'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">
                                        Tidak ada backlog gagal.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b font-semibold text-slate-800">Recent Pending Jobs</div>

                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-700 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">NIP</th>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Action</th>
                                <th class="px-4 py-3 text-left">Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPendingJobs as $row)
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ $row['id'] }}</td>
                                    <td class="px-4 py-2 font-mono">{{ $row['employee_nip'] }}</td>
                                    <td class="px-4 py-2">{{ $row['employee_name'] }}</td>
                                    <td class="px-4 py-2">{{ $row['action_type'] }}</td>
                                    <td class="px-4 py-2">{{ $row['target_device_code'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">
                                        Tidak ada pending job.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-ui.sccr-card>
