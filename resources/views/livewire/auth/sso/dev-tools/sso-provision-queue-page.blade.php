<x-ui.sccr-card transparent class="h-full min-h-0 flex flex-col">
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <h1 class="text-3xl font-bold text-white">Provision Queue</h1>
        <p class="text-slate-200 text-sm">Monitoring queue provisioning employee ke device.</p>
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
                <option value="processing">processing</option>
                <option value="success">success</option>
                <option value="partial">partial</option>
                <option value="failed">failed</option>
                <option value="cancelled">cancelled</option>
            </select>
            <select wire:model.live="filterAction" class="border-gray-300 rounded-lg text-sm">
                <option value="">Semua Action</option>
                <option value="upsert_person">upsert_person</option>
                <option value="disable_person">disable_person</option>
                <option value="delete_person">delete_person</option>
            </select>
        </div>

        <div class="bg-white rounded-xl shadow overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">NIP</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Target</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Attempt</th>
                        <th class="px-4 py-3 text-left">Error</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $row->id }}</td>
                            <td class="px-4 py-2 font-mono">{{ $row->employee_nip }}</td>
                            <td class="px-4 py-2">{{ $row->employee_name }}</td>
                            <td class="px-4 py-2">{{ $row->action_type }}</td>
                            <td class="px-4 py-2">{{ $row->target_device_code ?? $row->target_scope }}</td>
                            <td class="px-4 py-2">{{ $row->queue_status }}</td>
                            <td class="px-4 py-2">{{ $row->attempt_count }}</td>
                            <td class="px-4 py-2 text-xs text-red-700">{{ $row->last_error_message }}</td>
                            <td class="px-4 py-2 text-center">
                                @if (in_array($row->queue_status, ['pending', 'failed', 'partial']))
                                    <button wire:click="retryOne({{ $row->id }})"
                                        class="px-3 py-1 rounded bg-amber-600 text-white hover:bg-amber-700">
                                        Retry
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-400 italic">Queue kosong.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $rows->links() }}</div>
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />
</x-ui.sccr-card>
