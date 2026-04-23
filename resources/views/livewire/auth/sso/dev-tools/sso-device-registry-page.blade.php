<x-ui.sccr-card transparent class="h-full min-h-0 flex flex-col">
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <h1 class="text-3xl font-bold text-white">Person Registry</h1>
        <p class="text-slate-200 text-sm">Mapping employee per device dan status sinkron terakhir.</p>
        <div class="mt-4">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="p-6 space-y-4">
        <div class="bg-white rounded-xl shadow p-4 flex flex-wrap gap-3">
            <input wire:model.live="searchNip" type="text" placeholder="Cari NIP..."
                class="border-gray-300 rounded-lg text-sm">
            <select wire:model.live="filterDevice" class="border-gray-300 rounded-lg text-sm">
                <option value="">Semua Device</option>
                @foreach ($deviceOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="border-gray-300 rounded-lg text-sm">
                <option value="">Semua Status</option>
                <option value="pending">pending</option>
                <option value="synced">synced</option>
                <option value="failed">failed</option>
                <option value="disabled">disabled</option>
                <option value="deleted">deleted</option>
            </select>
        </div>

        <div class="bg-white rounded-xl shadow overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">NIP</th>
                        <th class="px-4 py-3 text-left">Device</th>
                        <th class="px-4 py-3 text-left">Company DB</th>
                        <th class="px-4 py-3 text-left">Sync Status</th>
                        <th class="px-4 py-3 text-left">Last Action</th>
                        <th class="px-4 py-3 text-left">Last Synced Name</th>
                        <th class="px-4 py-3 text-left">Remote Exists</th>
                        <th class="px-4 py-3 text-left">Last Synced At</th>
                        <th class="px-4 py-3 text-left">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-b">
                            <td class="px-4 py-2 font-mono">{{ $row->employee_nip }}</td>
                            <td class="px-4 py-2">{{ $row->device_code }}</td>
                            <td class="px-4 py-2">{{ $row->company_db }}</td>
                            <td class="px-4 py-2">{{ $row->sync_status }}</td>
                            <td class="px-4 py-2">{{ $row->last_action }}</td>
                            <td class="px-4 py-2">{{ $row->last_synced_name }}</td>
                            <td class="px-4 py-2">{{ (int) $row->remote_exists === 1 ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-2">{{ $row->last_synced_at }}</td>
                            <td class="px-4 py-2 text-xs text-red-700">{{ $row->last_error_message }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-400 italic">Registry kosong.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $rows->links() }}</div>
    </div>
</x-ui.sccr-card>
