<div x-data="{
    refreshInterval: 30,
    nextRefresh: 30,
    intervalId: null,
    
    init() {
        this.startAutoRefresh();
        
        Livewire.on('data-refreshed', () => {
            this.nextRefresh = this.refreshInterval;
        });
    },
    
    startAutoRefresh() {
        this.intervalId = setInterval(() => {
            this.nextRefresh--;
            if (this.nextRefresh <= 0) {
                this.refresh();
                this.nextRefresh = this.refreshInterval;
            }
        }, 1000);
    },
    
    stopAutoRefresh() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    },
    
    refresh() {
        @this.call('refreshData');
    },
    
    destroy() {
        this.stopAutoRefresh();
    }
}" x-init="init()">

    <div class="p-6">
        <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        
        <div class="mt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Monitoring Absensi Karyawan</h1>
                    <p class="text-sm text-gray-500 mt-1">Real-time monitoring kehadiran karyawan hari ini</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span>Auto-refresh dalam <span x-text="nextRefresh" class="font-mono font-bold text-gray-700"></span> detik</span>
                    </div>
                    <button @click="refresh()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Wajib</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['total_required'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Sudah Absensi</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ $summary['did_attendance'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Belum Absensi</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">{{ $summary['no_attendance'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Terblokir</p>
                            <p class="text-2xl font-bold text-orange-600 mt-1">{{ $summary['blocked'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Budget Lunch</p>
                            <p class="text-2xl font-bold text-purple-600 mt-1">{{ $summary['lunch_budget'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Persentase</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $summary['percentage'] }}%</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex rounded-lg overflow-hidden border border-gray-300">
                            <button 
                                wire:click="setViewMode('no_attendance')"
                                class="px-4 py-2 text-sm font-medium transition-colors {{ $viewMode === 'no_attendance' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                Belum Absensi
                            </button>
                            <button 
                                wire:click="setViewMode('did_attendance')"
                                class="px-4 py-2 text-sm font-medium transition-colors {{ $viewMode === 'did_attendance' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                Sudah Absensi
                            </button>
                            <button 
                                wire:click="setViewMode('blocked')"
                                class="px-4 py-2 text-sm font-medium transition-colors {{ $viewMode === 'blocked' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                Terblokir
                            </button>
                            <button 
                                wire:click="setViewMode('lunch_budget')"
                                class="px-4 py-2 text-sm font-medium transition-colors {{ $viewMode === 'lunch_budget' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                Budget Lunch
                            </button>
                        </div>

                        <div class="flex-1"></div>

                        <div class="flex flex-col md:flex-row gap-3">
                            <div class="relative">
                                <input type="text" 
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Cari NIP atau Nama..."
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64">
                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <select wire:model.live="filterHolding" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Holding</option>
                                @foreach($holdings as $holding)
                                    <option value="{{ $holding['id'] }}">{{ $holding['name'] }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="filterDepartment" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="filterDivision" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division['id'] }}">{{ $division['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Holding</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Division</th>
                                @if($viewMode === 'did_attendance')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                @endif
                                @if($viewMode === 'blocked')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                @endif
                                @if($viewMode === 'lunch_budget')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget Lunch</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->employees as $index => $employee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $index + 1 + ($this->employees->currentPage() - 1) * $this->perPage }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $employee->nip }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $employee->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $employee->holding_name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $employee->department_name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $employee->division_name ?? '-' }}
                                    </td>
                                    @if($viewMode === 'did_attendance')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($employee->first_in_at)
                                                <span class="text-gray-900">{{ \Carbon\Carbon::parse($employee->first_in_at)->format('H:i:s') }}</span>
                                                @if($employee->is_late)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Terlambat {{ $employee->late_minutes ?? 0 }} menit
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $employee->last_out_at ? \Carbon\Carbon::parse($employee->last_out_at)->format('H:i:s') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($employee->attendance_status ?? 'present') }}
                                            </span>
                                        </td>
                                    @endif
                                    @if($viewMode === 'blocked')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $employee->attendance_note ?? 'Terblokir' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button 
                                                wire:click="unblockEmployee('{{ $employee->nip }}')"
                                                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-medium">
                                                Unblock
                                            </button>
                                        </td>
                                    @endif
                                    @if($viewMode === 'lunch_budget')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-medium">
                                            Rp {{ number_format($employee->lunch_budget ?? 0, 0, ',', '.') }}
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $viewMode === 'did_attendance' ? 9 : ($viewMode === 'blocked' ? 8 : ($viewMode === 'lunch_budget' ? 7 : 6)) }}" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-500 text-sm">
                                                @if($viewMode === 'no_attendance')
                                                    Tidak ada karyawan yang belum absensi hari ini
                                                @elseif($viewMode === 'blocked')
                                                    Tidak ada karyawan yang terblokir
                                                @elseif($viewMode === 'lunch_budget')
                                                    Tidak ada karyawan dengan budget lunch
                                                @else
                                                    Belum ada data absensi hari ini
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Menampilkan {{ $this->employees->firstItem() ?? 0 }} - {{ $this->employees->lastItem() ?? 0 }} dari {{ $this->employees->total() }} data
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <select wire:model.live="perPage" class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        
                        {{ $this->employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
