<x-layout :title="'Daftar Petugas Lapangan'">

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        editPetugas: { id: '', nama: '', nomor_induk: '', status: 'aktif', url: '' },
        deletePetugas: { id: '', nama: '', nomor_induk: '', url: '', shipments_count: 0 },
        openEdit(id, nama, nomor_induk, status, url) {
            this.editPetugas = { id, nama, nomor_induk, status, url };
            this.editModalOpen = true;
        },
        openDelete(id, nama, nomor_induk, url, shipments_count) {
            this.deletePetugas = { id, nama, nomor_induk, url, shipments_count };
            this.deleteModalOpen = true;
        }
    }">

        <!-- Header Section Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Daftar Petugas Lapangan</h1>
                    <p class="text-xs text-gray-500 mt-1">Kelola data petugas pemeriksa dan pelaksana staging di warehouse.</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" @click="createModalOpen = true"
                            class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all w-full sm:w-auto">
                        <i class="ph-bold ph-user-plus text-base"></i>
                        Tambah Petugas
                    </button>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Petugas -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #285491, #3568b0); color: white;">
                    <i class="ph-fill ph-users text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Petugas</p>
                    <p class="text-xl font-extrabold text-gray-800 mt-0.5">{{ $totalPetugas }}</p>
                </div>
            </div>

            <!-- Petugas Aktif -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #059e3d, #63c384); color: white;">
                    <i class="ph-fill ph-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Petugas Aktif</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $totalAktif }}</p>
                </div>
            </div>

            <!-- Petugas Nonaktif -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 bg-gray-100 text-gray-500">
                    <i class="ph-fill ph-user-minus text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nonaktif</p>
                    <p class="text-xl font-extrabold text-gray-600 mt-0.5">{{ $totalNonaktif }}</p>
                </div>
            </div>

            <!-- Total Staging Ditangani -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #0d5950, #285491); color: white;">
                    <i class="ph-fill ph-package text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Staging</p>
                    <p class="text-xl font-extrabold text-gray-800 mt-0.5">{{ $totalShipmentsHandled }}</p>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">

            <!-- Toolbar & Search -->
            <form method="GET" action="{{ route('karyawan.index') }}" class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-bottom: 1px solid #f0f4f9;">
                <div class="relative w-full md:w-80">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama petugas atau nomor induk..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <a href="{{ route('karyawan.index', array_filter(['search' => request('search')])) }}"
                       class="text-[11px] font-bold px-4 py-2 rounded-full whitespace-nowrap {{ !request('status') ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
                        Semua ({{ $totalPetugas }})
                    </a>
                    <a href="{{ route('karyawan.index', array_filter(['status' => 'aktif', 'search' => request('search')])) }}"
                       class="text-[11px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('status') === 'aktif' ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-700 border border-emerald-200 hover:bg-emerald-50' }}">
                        Aktif ({{ $totalAktif }})
                    </a>
                    <a href="{{ route('karyawan.index', array_filter(['status' => 'nonaktif', 'search' => request('search')])) }}"
                       class="text-[11px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('status') === 'nonaktif' ? 'bg-gray-600 text-white shadow-sm' : 'text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        Nonaktif ({{ $totalNonaktif }})
                    </a>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead style="background:#fafbfd;">
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Petugas Lapangan</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nomor Induk / NIK</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status Akun</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Staging Ditangani</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Terdaftar Sejak</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $karyawan)
                            @php
                                $initials = collect(explode(' ', $karyawan->nama))
                                    ->map(fn($segment) => mb_substr($segment, 0, 1))
                                    ->take(2)
                                    ->implode('');
                            @endphp
                            <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                                <!-- Petugas Info -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                                             style="background: linear-gradient(135deg, {{ $karyawan->status === 'aktif' ? '#e1f8eb, #63c384' : '#f3f4f6, #d1d5db' }}); color: {{ $karyawan->status === 'aktif' ? '#0d5950' : '#4b5563' }};">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('karyawan.show', $karyawan->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue transition-colors">
                                                {{ $karyawan->nama }}
                                            </a>
                                            <p class="text-[10px] text-gray-400">Petugas Staging Warehouse</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- NIK / Nomor Induk -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                        <i class="ph-bold ph-identification-badge text-sm text-gray-400"></i>
                                        {{ $karyawan->nomor_induk }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5">
                                    <form action="{{ route('karyawan.toggle-status', $karyawan->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                title="Klik untuk mengubah status"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition-all {{ $karyawan->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200' }}">
                                            @if($karyawan->status === 'aktif')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Aktif
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Nonaktif
                                            @endif
                                            <i class="ph-bold ph-arrows-clockwise text-[10px] opacity-60 ml-0.5"></i>
                                        </button>
                                    </form>
                                </td>

                                <!-- Shipments Count -->
                                <td class="px-5 py-3.5 text-center">
                                    @if($karyawan->shipments_count > 0)
                                        <a href="{{ route('karyawan.show', $karyawan->id) }}"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-spv-blue hover:bg-blue-100 transition-colors">
                                            <i class="ph-bold ph-package text-xs"></i>
                                            {{ $karyawan->shipments_count }} Staging
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">0 Staging</span>
                                    @endif
                                </td>

                                <!-- Registered At -->
                                <td class="px-5 py-3.5">
                                    <p class="text-[11px] text-gray-500 font-medium">
                                        {{ $karyawan->created_at ? $karyawan->created_at->format('d M Y') : '—' }}
                                    </p>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Detail -->
                                        <a href="{{ route('karyawan.show', $karyawan->id) }}"
                                           title="Lihat Detail Petugas"
                                           class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-blue hover:bg-blue-50">
                                            <i class="ph-bold ph-eye text-base"></i>
                                        </a>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button"
                                                @click="openEdit('{{ $karyawan->id }}', '{{ addslashes($karyawan->nama) }}', '{{ addslashes($karyawan->nomor_induk) }}', '{{ $karyawan->status }}', '{{ route('karyawan.update', $karyawan->id) }}')"
                                                title="Edit Petugas"
                                                class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-amber-600 hover:bg-amber-50">
                                            <i class="ph-bold ph-pencil-simple text-base"></i>
                                        </button>

                                        <!-- Delete Modal Trigger -->
                                        <button type="button"
                                                @click="openDelete('{{ $karyawan->id }}', '{{ addslashes($karyawan->nama) }}', '{{ addslashes($karyawan->nomor_induk) }}', '{{ route('karyawan.destroy', $karyawan->id) }}', {{ $karyawan->shipments_count }})"
                                                title="Hapus Petugas"
                                                class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-rose-600 hover:bg-rose-50">
                                            <i class="ph-bold ph-trash text-base"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                        <i class="ph-bold ph-users text-2xl"></i>
                                    </div>
                                    <p class="text-xs font-bold text-gray-700">Tidak ada data petugas ditemukan</p>
                                    <p class="text-[11px] text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status.</p>
                                    <button type="button" @click="createModalOpen = true"
                                            class="inline-flex items-center gap-1.5 mt-4 bg-spv-blue text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow hover:bg-blue-800 transition-colors">
                                        <i class="ph-bold ph-plus"></i> Tambah Petugas Sekarang
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($karyawans->hasPages())
                <div class="p-4 flex items-center justify-between border-t border-gray-100">
                    <p class="text-[11px] text-gray-500 font-medium">
                        Menampilkan {{ $karyawans->firstItem() ?? 0 }}-{{ $karyawans->lastItem() ?? 0 }} dari {{ $karyawans->total() }} petugas
                    </p>
                    <div class="flex items-center gap-1">
                        @if($karyawans->onFirstPage())
                            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 border border-gray-100"><i class="ph-bold ph-caret-left"></i></span>
                        @else
                            <a href="{{ $karyawans->previousPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 border border-gray-200"><i class="ph-bold ph-caret-left"></i></a>
                        @endif

                        <span class="text-xs font-bold px-3 py-1 bg-spv-blue text-white rounded-lg">{{ $karyawans->currentPage() }}</span>

                        @if($karyawans->hasMorePages())
                            <a href="{{ $karyawans->nextPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 border border-gray-200"><i class="ph-bold ph-caret-right"></i></a>
                        @else
                            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 border border-gray-100"><i class="ph-bold ph-caret-right"></i></span>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-4 border-t border-gray-100 text-[11px] text-gray-400">
                    Total {{ $karyawans->total() }} petugas terdaftar
                </div>
            @endif
        </div>

        <!-- =================== MODAL TAMBAH PETUGAS =================== -->
        <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="createModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
                         style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white"
                                 style="background: linear-gradient(135deg, #285491, #3568b0); box-shadow: 0 4px 12px rgba(40,84,145,0.3);">
                                <i class="ph-bold ph-user-plus text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800" id="modal-title">Tambah Petugas Baru</h3>
                                <p class="text-[10px] text-gray-500">Petugas akan muncul di daftar pilihan Field App</p>
                            </div>
                        </div>
                        <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form action="{{ route('karyawan.store') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <!-- Nama Petugas -->
                            <div>
                                <label for="create_nama" class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Nama Lengkap Petugas <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" id="create_nama" name="nama" required
                                           placeholder="Contoh: Budi Santoso"
                                           class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>
                            </div>

                            <!-- Nomor Induk / NIK -->
                            <div>
                                <label for="create_nomor_induk" class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Nomor Induk / NIK / Badge ID <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-identification-card absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" id="create_nomor_induk" name="nomor_induk" required
                                           placeholder="Contoh: KRY004 atau NIK-8921"
                                           class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Harus unik dan belum pernah digunakan petugas lain.</p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Status Awal <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="status" value="aktif" checked class="text-spv-blue focus:ring-spv-blue">
                                        <div>
                                            <p class="text-xs font-bold text-emerald-700">Aktif</p>
                                            <p class="text-[10px] text-gray-400">Dapat dipilih di Field App</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="status" value="nonaktif" class="text-spv-blue focus:ring-spv-blue">
                                        <div>
                                            <p class="text-xs font-bold text-gray-700">Nonaktif</p>
                                            <p class="text-[10px] text-gray-400">Tidak muncul di Field App</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="createModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all">
                                <i class="ph-bold ph-check text-sm"></i>
                                Simpan Petugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- =================== MODAL EDIT PETUGAS =================== -->
        <div x-cloak x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-edit-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
                         style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white bg-amber-500 shadow-sm">
                                <i class="ph-bold ph-pencil-simple text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800" id="modal-edit-title">Edit Data Petugas</h3>
                                <p class="text-[10px] text-gray-500">Perbarui profil dan status petugas lapangan</p>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form :action="editPetugas.url" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-6 space-y-4">
                            <!-- Nama Petugas -->
                            <div>
                                <label for="edit_nama" class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Nama Lengkap Petugas <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" id="edit_nama" name="nama" x-model="editPetugas.nama" required
                                           class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>
                            </div>

                            <!-- Nomor Induk / NIK -->
                            <div>
                                <label for="edit_nomor_induk" class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Nomor Induk / NIK / Badge ID <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-identification-card absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" id="edit_nomor_induk" name="nomor_induk" x-model="editPetugas.nomor_induk" required
                                           class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Status Petugas <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="status" value="aktif" x-model="editPetugas.status" class="text-spv-blue focus:ring-spv-blue">
                                        <div>
                                            <p class="text-xs font-bold text-emerald-700">Aktif</p>
                                            <p class="text-[10px] text-gray-400">Dapat dipilih di Field App</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="status" value="nonaktif" x-model="editPetugas.status" class="text-spv-blue focus:ring-spv-blue">
                                        <div>
                                            <p class="text-xs font-bold text-gray-700">Nonaktif</p>
                                            <p class="text-[10px] text-gray-400">Tidak muncul di Field App</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="editModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all">
                                <i class="ph-bold ph-check text-sm"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- =================== MODAL KONFIRMASI HAPUS =================== -->
        <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-delete-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="deleteModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <div class="p-6 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4 border border-rose-100 shadow-sm">
                            <i class="ph-bold ph-warning text-3xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-800" id="modal-delete-title">Hapus Data Petugas?</h3>
                        <p class="text-xs text-gray-500 mt-2">
                            Apakah Anda yakin ingin menghapus petugas <strong class="text-gray-800" x-text="deletePetugas.nama"></strong> (<span x-text="deletePetugas.nomor_induk"></span>)?
                        </p>

                        <!-- Peringatan jika punya shipments -->
                        <template x-if="deletePetugas.shipments_count > 0">
                            <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-left">
                                <div class="flex items-start gap-2.5">
                                    <i class="ph-bold ph-info text-amber-600 text-base shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-xs font-bold text-amber-900">Perhatian: Memiliki Riwayat Staging</p>
                                        <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                                            Petugas ini memiliki <span class="font-bold" x-text="deletePetugas.shipments_count"></span> riwayat staging yang tersimpan. Sistem tidak memperbolehkan penghapusan demi menjaga audit trail data pengiriman. Disarankan untuk menonaktifkan akun petugas.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="mt-6 flex items-center justify-center gap-3">
                            <button type="button" @click="deleteModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                                Batalkan
                            </button>
                            <form :action="deletePetugas.url" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        :disabled="deletePetugas.shipments_count > 0"
                                        :class="deletePetugas.shipments_count > 0 ? 'opacity-50 cursor-not-allowed bg-rose-400' : 'bg-rose-600 hover:bg-rose-700 shadow-[0_4px_12px_rgba(225,29,72,0.25)]'"
                                        class="flex items-center gap-2 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition-all">
                                    <i class="ph-bold ph-trash text-sm"></i>
                                    Ya, Hapus Data
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-layout>
