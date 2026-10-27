<x-layout :title="'Master Data Warehouse Eksternal'">

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        editWh: { id: '', nama: '', kode: '', alamat: '', pic_nama: '', pic_kontak: '', status: 'aktif', catatan: '', updateUrl: '' },
        deleteWh: { id: '', nama: '', deleteUrl: '' },
        openEdit(id, nama, kode, alamat, pic_nama, pic_kontak, status, catatan, updateUrl) {
            this.editWh = { id, nama, kode, alamat, pic_nama, pic_kontak, status, catatan, updateUrl };
            this.editModalOpen = true;
        },
        openDelete(id, nama, deleteUrl) {
            this.deleteWh = { id, nama, deleteUrl };
            this.deleteModalOpen = true;
        }
    }">

        <!-- Header Section Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-spv-blue">
                            Master Logistik
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                            <i class="ph-bold ph-database"></i> Database Driven
                        </span>
                    </div>
                    <h1 class="text-xl lg:text-2xl font-black text-gray-800 tracking-tight">Master Data Warehouse Eksternal</h1>
                    <p class="text-xs text-gray-500 mt-1">Kelola daftar rekanan PT pergudangan luar, kontak PIC, dan opsi dropdown otomatis untuk aplikasi lapangan (Field App).</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" @click="createModalOpen = true"
                            class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all w-full sm:w-auto">
                        <i class="ph-bold ph-plus-circle text-base"></i>
                        Tambah Warehouse Eksternal
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        @include('settings.tabs', ['active' => 'external-warehouses'])

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <!-- Total Warehouse -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #285491, #3568b0); color: white;">
                    <i class="ph-fill ph-warehouse text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Rekanan Gudang</p>
                    <p class="text-xl font-extrabold text-gray-800 mt-0.5">{{ $totalWarehouses }}</p>
                </div>
            </div>

            <!-- Gudang Aktif -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #059e3d, #63c384); color: white;">
                    <i class="ph-fill ph-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status Aktif (Tampil di Form)</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $totalAktif }}</p>
                </div>
            </div>

            <!-- Gudang Nonaktif -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #ef4444, #f87171); color: white;">
                    <i class="ph-fill ph-prohibit text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nonaktif</p>
                    <p class="text-xl font-extrabold text-red-500 mt-0.5">{{ $totalNonaktif }}</p>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">

            <!-- Toolbar & Filter -->
            <form method="GET" action="{{ route('settings.external-warehouses.index') }}" class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-bottom: 1px solid #f0f4f9;">
                <div class="relative w-full md:w-80">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama PT, kode, PIC, atau alamat..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <a href="{{ route('settings.external-warehouses.index', array_filter(['search' => request('search')])) }}"
                       class="text-[11px] font-bold px-4 py-2 rounded-full whitespace-nowrap {{ !request('status') ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
                        Semua ({{ $totalWarehouses }})
                    </a>
                    <a href="{{ route('settings.external-warehouses.index', array_merge(request()->query(), ['status' => 'aktif'])) }}"
                       class="text-[11px] font-bold px-4 py-2 rounded-full whitespace-nowrap {{ request('status') === 'aktif' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
                        Aktif ({{ $totalAktif }})
                    </a>
                    <a href="{{ route('settings.external-warehouses.index', array_merge(request()->query(), ['status' => 'nonaktif'])) }}"
                       class="text-[11px] font-bold px-4 py-2 rounded-full whitespace-nowrap {{ request('status') === 'nonaktif' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
                        Nonaktif ({{ $totalNonaktif }})
                    </a>
                </div>
            </form>

            <!-- Table Responsive Container -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama PT / Rekanan Eksternal</th>
                            <th class="py-3 px-4">PIC / Kontak</th>
                            <th class="py-3 px-4">Alamat & Catatan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($warehouses as $index => $wh)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono">
                                    {{ $warehouses->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-spv-blue flex items-center justify-center shrink-0 font-bold border border-blue-100">
                                            <i class="ph-bold ph-warehouse text-base"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-900">{{ $wh->nama }}</p>
                                                @if($wh->kode)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-gray-100 text-gray-700 border border-gray-200">
                                                        {{ $wh->kode }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-gray-400 mt-0.5">Dibuat {{ $wh->created_at ? $wh->created_at->isoFormat('D MMM Y') : '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    @if($wh->pic_nama || $wh->pic_kontak)
                                        <p class="font-semibold text-gray-800">{{ $wh->pic_nama ?: '-' }}</p>
                                        <p class="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                                            <i class="ph-bold ph-phone text-xs text-spv-blue"></i> {{ $wh->pic_kontak ?: '-' }}
                                        </p>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">- Tidak ada kontak -</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 max-w-xs">
                                    @if($wh->alamat)
                                        <p class="text-gray-700 truncate" title="{{ $wh->alamat }}">
                                            <i class="ph-bold ph-map-pin text-gray-400 mr-1"></i>{{ $wh->alamat }}
                                        </p>
                                    @endif
                                    @if($wh->catatan)
                                        <p class="text-[11px] text-gray-400 truncate mt-0.5" title="{{ $wh->catatan }}">
                                            <i class="ph-bold ph-note text-gray-400 mr-1"></i>{{ $wh->catatan }}
                                        </p>
                                    @endif
                                    @if(!$wh->alamat && !$wh->catatan)
                                        <span class="text-gray-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('settings.external-warehouses.toggle-status', $wh->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                title="Klik untuk mengubah status aktif/nonaktif"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition-all {{ $wh->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $wh->status === 'aktif' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            <span>{{ ucfirst($wh->status) }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Tombol Edit -->
                                        <button type="button"
                                                @click="openEdit(
                                                    '{{ $wh->id }}',
                                                    '{{ addslashes($wh->nama) }}',
                                                    '{{ addslashes($wh->kode ?? '') }}',
                                                    '{{ addslashes($wh->alamat ?? '') }}',
                                                    '{{ addslashes($wh->pic_nama ?? '') }}',
                                                    '{{ addslashes($wh->pic_kontak ?? '') }}',
                                                    '{{ $wh->status }}',
                                                    '{{ addslashes($wh->catatan ?? '') }}',
                                                    '{{ route('settings.external-warehouses.update', $wh->id) }}'
                                                )"
                                                class="p-1.5 rounded-lg text-spv-blue hover:bg-blue-50 transition-colors"
                                                title="Edit data warehouse">
                                            <i class="ph-bold ph-pencil-simple text-base"></i>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button type="button"
                                                @click="openDelete(
                                                    '{{ $wh->id }}',
                                                    '{{ addslashes($wh->nama) }}',
                                                    '{{ route('settings.external-warehouses.destroy', $wh->id) }}'
                                                )"
                                                class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors"
                                                title="Hapus warehouse">
                                            <i class="ph-bold ph-trash text-base"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mx-auto mb-3">
                                        <i class="ph-bold ph-warehouse text-2xl"></i>
                                    </div>
                                    <p class="font-bold text-gray-700">Belum ada data Warehouse Eksternal</p>
                                    <p class="text-xs text-gray-400 mt-1">Tambahkan rekanan PT logistik baru melalui tombol di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($warehouses->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $warehouses->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: TAMBAH WAREHOUSE EKSTERNAL BARU  -->
        <!-- ========================================== -->
        <div x-cloak x-show="createModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="createModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl sm:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                    <form method="POST" action="{{ route('settings.external-warehouses.store') }}">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-warehouse text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900">Tambah Warehouse Eksternal</h3>
                                        <p class="text-xs text-gray-400">Daftarkan rekanan logistik baru ke master data.</p>
                                    </div>
                                </div>
                                <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 text-xs">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Nama PT / Rekanan Eksternal <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama" required placeholder="Contoh: PT Kamadjaja Logistics"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">Kode Singkat (Opsional)</label>
                                        <input type="text" name="kode" placeholder="Contoh: KMJ"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                                        <select name="status" required
                                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all font-semibold">
                                            <option value="aktif">Aktif (Tampil di Form)</option>
                                            <option value="nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">Nama PIC (Opsional)</label>
                                        <input type="text" name="pic_nama" placeholder="Contoh: Bpk. Budi Santoso"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">No. Telp / HP PIC (Opsional)</label>
                                        <input type="text" name="pic_kontak" placeholder="Contoh: 0812-3456-7890"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Alamat Gudang / Depo (Opsional)</label>
                                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap fasilitas gudang..."
                                              class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Catatan Tambahan (Opsional)</label>
                                    <input type="text" name="catatan" placeholder="Keterangan tambahan atau nomor kontrak..."
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50/75 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="createModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-100 transition-all text-xs">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-spv-blue text-white font-bold hover:bg-blue-800 transition-all text-xs shadow-md shadow-blue-900/20">
                                Simpan ke Master Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: EDIT WAREHOUSE EKSTERNAL         -->
        <!-- ========================================== -->
        <div x-cloak x-show="editModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl sm:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                    <form method="POST" :action="editWh.updateUrl">
                        @csrf
                        @method('PUT')
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900">Edit Warehouse Eksternal</h3>
                                        <p class="text-xs text-gray-400">Perbarui rincian data rekanan logistik.</p>
                                    </div>
                                </div>
                                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 text-xs">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Nama PT / Rekanan Eksternal <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama" x-model="editWh.nama" required
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">Kode Singkat (Opsional)</label>
                                        <input type="text" name="kode" x-model="editWh.kode"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-mono focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                                        <select name="status" x-model="editWh.status" required
                                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all font-semibold">
                                            <option value="aktif">Aktif (Tampil di Form)</option>
                                            <option value="nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">Nama PIC (Opsional)</label>
                                        <input type="text" name="pic_nama" x-model="editWh.pic_nama"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1.5">No. Telp / HP PIC (Opsional)</label>
                                        <input type="text" name="pic_kontak" x-model="editWh.pic_kontak"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Alamat Gudang / Depo (Opsional)</label>
                                    <textarea name="alamat" x-model="editWh.alamat" rows="2"
                                              class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Catatan Tambahan (Opsional)</label>
                                    <input type="text" name="catatan" x-model="editWh.catatan"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50/75 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="editModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-100 transition-all text-xs">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-amber-600 text-white font-bold hover:bg-amber-700 transition-all text-xs shadow-md shadow-amber-900/20">
                                Perbarui Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 3: KONFIRMASI HAPUS WAREHOUSE       -->
        <!-- ========================================== -->
        <div x-cloak x-show="deleteModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="deleteModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl sm:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">

                    <form method="POST" :action="deleteWh.deleteUrl">
                        @csrf
                        @method('DELETE')
                        <div class="p-6">
                            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mb-4">
                                <i class="ph-bold ph-warning-octagon text-2xl"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Hapus Warehouse Eksternal?</h3>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                Anda akan menghapus master data <strong class="text-gray-800" x-text="deleteWh.nama"></strong>.
                                Jika warehouse ini pernah tercatat dalam riwayat shipment, sistem akan secara aman mengubah statusnya menjadi <strong>NONAKTIF</strong> untuk melindungi data audit.
                            </p>
                        </div>

                        <div class="px-6 py-4 bg-gray-50/75 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="deleteModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-100 transition-all text-xs">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 transition-all text-xs shadow-md shadow-red-900/20">
                                Ya, Lanjutkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-layout>
