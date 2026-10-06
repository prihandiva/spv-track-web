<x-layout :title="'Master 27 Titik SOP'">

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        resetModalOpen: false,
        editPoint: {
            id: '',
            urutan: 1,
            nama_titik: '',
            deskripsi: '',
            jenis_pengiriman: 'both',
            tipe_item: 'foto',
            wajib: true,
            perlu_ocr_container: false,
            perlu_deteksi_orang: false,
            perlu_deteksi_barcode: false,
            updateUrl: ''
        },
        deletePoint: { id: '', urutan: 1, nama_titik: '', evidence_count: 0, deleteUrl: '' },
        openEdit(point, updateUrl) {
            this.editPoint = {
                id: point.id,
                urutan: point.urutan,
                nama_titik: point.nama_titik,
                deskripsi: point.deskripsi || '',
                jenis_pengiriman: point.jenis_pengiriman,
                tipe_item: point.tipe_item,
                wajib: Boolean(point.wajib),
                perlu_ocr_container: Boolean(point.perlu_ocr_container),
                perlu_deteksi_orang: Boolean(point.perlu_deteksi_orang),
                perlu_deteksi_barcode: Boolean(point.perlu_deteksi_barcode),
                updateUrl: updateUrl
            };
            this.editModalOpen = true;
        },
        openDelete(id, urutan, nama_titik, evidence_count, deleteUrl) {
            this.deletePoint = { id, urutan, nama_titik, evidence_count, deleteUrl };
            this.deleteModalOpen = true;
        }
    }">

        <!-- Header Section Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-gray-800 tracking-tight">Master Data 27 Titik SOP Evidence</h1>
                    <p class="text-xs text-gray-500 mt-1">Konfigurasi baku titik foto, dokumen, video timeline staging, aturan wajib/opsional, dan validasi AI asinkron.</p>
                </div>
                <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
                    <button type="button" @click="resetModalOpen = true"
                            class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 transition-all flex-1 sm:flex-none">
                        <i class="ph-bold ph-arrow-counter-clockwise text-base text-gray-500"></i>
                        Reset Standar 27 Titik
                    </button>
                    <button type="button" @click="createModalOpen = true"
                            class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all flex-1 sm:flex-none">
                        <i class="ph-bold ph-plus-circle text-base"></i>
                        Tambah Titik SOP
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        @include('settings.tabs', ['active' => 'sop'])

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Titik SOP -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #059e3d, #63c384); color: white;">
                    <i class="ph-fill ph-list-numbers text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Titik SOP</p>
                    <p class="text-xl font-extrabold text-gray-800 mt-0.5">{{ $totalPoints }} Titik</p>
                </div>
            </div>

            <!-- Titik Wajib -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #285491, #3568b0); color: white;">
                    <i class="ph-fill ph-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Wajib Diunggah</p>
                    <p class="text-xl font-extrabold text-spv-blue mt-0.5">{{ $totalWajib }} Titik</p>
                </div>
            </div>

            <!-- OCR Container -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: white;">
                    <i class="ph-fill ph-scan text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Validasi OCR Container</p>
                    <p class="text-xl font-extrabold text-amber-600 mt-0.5">{{ $totalOcr }} Titik</p>
                </div>
            </div>

            <!-- AI Object & Barcode -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #7c3aed, #a855f7); color: white;">
                    <i class="ph-fill ph-cpu text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Deteksi AI (Orang/Barcode)</p>
                    <p class="text-xl font-extrabold text-purple-600 mt-0.5">{{ $totalAiPerson + $totalAiBarcode }} Titik</p>
                </div>
            </div>
        </div>

        <!-- Main SOP Table Card -->
        <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">

            <!-- Toolbar & Filter -->
            <form method="GET" action="{{ route('settings.sop.index') }}" class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-bottom: 1px solid #f0f4f9;">
                <div class="relative w-full md:w-80">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama titik atau instruksi deskripsi..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <!-- Jenis Pengiriman Filter -->
                    <select name="jenis_pengiriman" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 outline-none">
                        <option value="">Semua Target (Export/Lokal)</option>
                        <option value="both" {{ request('jenis_pengiriman') === 'both' ? 'selected' : '' }}>Export & Lokal</option>
                        <option value="export" {{ request('jenis_pengiriman') === 'export' ? 'selected' : '' }}>Khusus Export</option>
                        <option value="lokal" {{ request('jenis_pengiriman') === 'lokal' ? 'selected' : '' }}>Khusus Lokal</option>
                    </select>

                    <!-- Tipe Item Filter -->
                    <select name="tipe_item" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 outline-none">
                        <option value="">Semua Tipe Media</option>
                        <option value="foto" {{ request('tipe_item') === 'foto' ? 'selected' : '' }}>Foto</option>
                        <option value="dokumen" {{ request('tipe_item') === 'dokumen' ? 'selected' : '' }}>Dokumen / Checklist</option>
                        <option value="video" {{ request('tipe_item') === 'video' ? 'selected' : '' }}>Video</option>
                    </select>

                    <!-- Wajib Filter -->
                    <select name="wajib" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 outline-none">
                        <option value="">Semua Status Wajib</option>
                        <option value="1" {{ request('wajib') === '1' ? 'selected' : '' }}>Wajib Saja</option>
                        <option value="0" {{ request('wajib') === '0' ? 'selected' : '' }}>Opsional Saja</option>
                    </select>

                    @if(request()->anyFilled(['search', 'jenis_pengiriman', 'tipe_item', 'wajib']))
                        <a href="{{ route('settings.sop.index') }}" class="p-2 text-rose-500 hover:text-rose-700 text-xs font-bold" title="Reset Filter">
                            <i class="ph-bold ph-x-circle text-lg"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Table of SOP Points -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-400" style="border-bottom: 1px solid #f0f4f9;">
                        <tr>
                            <th scope="col" class="py-3.5 px-4 text-center w-20">Urutan</th>
                            <th scope="col" class="py-3.5 px-5">Nama Titik & Petunjuk Petugas</th>
                            <th scope="col" class="py-3.5 px-4">Tipe Media</th>
                            <th scope="col" class="py-3.5 px-4">Target Kargo</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Status Wajib</th>
                            <th scope="col" class="py-3.5 px-5 text-center">Validasi & AI Trigger</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($points as $point)
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <!-- Urutan with Reorder Buttons -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <div class="flex flex-col gap-0.5">
                                            <form action="{{ route('settings.sop.move', $point) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="up">
                                                <button type="submit" class="text-gray-300 hover:text-spv-blue p-0.5 transition-colors" title="Naikkan Urutan">
                                                    <i class="ph-bold ph-caret-up text-xs"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('settings.sop.move', $point) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="down">
                                                <button type="submit" class="text-gray-300 hover:text-spv-blue p-0.5 transition-colors" title="Turunkan Urutan">
                                                    <i class="ph-bold ph-caret-down text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <span class="w-7 h-7 rounded-lg bg-gray-100 text-gray-800 font-extrabold text-xs flex items-center justify-center shadow-xs">
                                            #{{ $point->urutan }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Nama Titik & Deskripsi -->
                                <td class="py-3.5 px-5">
                                    <p class="font-bold text-gray-900 leading-snug">{{ $point->nama_titik }}</p>
                                    @if($point->deskripsi)
                                        <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">{{ $point->deskripsi }}</p>
                                    @else
                                        <p class="text-[11px] text-gray-400 italic mt-0.5">Tidak ada deskripsi tambahan</p>
                                    @endif
                                </td>

                                <!-- Tipe Media -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($point->tipe_item === 'foto')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-spv-blue border border-blue-200">
                                            <i class="ph-bold ph-camera text-xs"></i>
                                            Foto
                                        </span>
                                    @elseif($point->tipe_item === 'dokumen')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="ph-bold ph-file-text text-xs"></i>
                                            Dokumen
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i class="ph-bold ph-video-camera text-xs"></i>
                                            Video
                                        </span>
                                    @endif
                                </td>

                                <!-- Target Kargo -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="text-[11px] font-semibold text-gray-600 uppercase">
                                        {{ $point->jenis_pengiriman === 'both' ? 'Export & Lokal' : ucfirst($point->jenis_pengiriman) }}
                                    </span>
                                </td>

                                <!-- Status Wajib (Quick Toggle) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <form action="{{ route('settings.sop.toggle', $point) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="wajib">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border transition-all {{ $point->wajib ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}"
                                                title="Klik untuk ubah status wajib">
                                            <i class="ph-bold {{ $point->wajib ? 'ph-check' : 'ph-minus' }} text-xs"></i>
                                            {{ $point->wajib ? 'Wajib' : 'Opsional' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Validasi & AI Trigger -->
                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- OCR Container -->
                                        <form action="{{ route('settings.sop.toggle', $point) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="field" value="perlu_ocr_container">
                                            <button type="submit"
                                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold border transition-all {{ $point->perlu_ocr_container ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-50 text-gray-400 border-gray-200 opacity-40 hover:opacity-100' }}"
                                                    title="OCR No. Container: Klik untuk toggle">
                                                OCR
                                            </button>
                                        </form>

                                        <!-- Deteksi Orang -->
                                        <form action="{{ route('settings.sop.toggle', $point) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="field" value="perlu_deteksi_orang">
                                            <button type="submit"
                                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold border transition-all {{ $point->perlu_deteksi_orang ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-400 border-gray-200 opacity-40 hover:opacity-100' }}"
                                                    title="Deteksi Orang (YOLOv8): Klik untuk toggle">
                                                Orang
                                            </button>
                                        </form>

                                        <!-- Deteksi Barcode -->
                                        <form action="{{ route('settings.sop.toggle', $point) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="field" value="perlu_deteksi_barcode">
                                            <button type="submit"
                                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold border transition-all {{ $point->perlu_deteksi_barcode ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-gray-50 text-gray-400 border-gray-200 opacity-40 hover:opacity-100' }}"
                                                    title="Deteksi Barcode: Klik untuk toggle">
                                                Barcode
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                @click="openEdit({{ json_encode($point) }}, '{{ route('settings.sop.update', $point) }}')"
                                                class="p-2 rounded-xl text-gray-500 hover:text-spv-blue hover:bg-blue-50 transition-all"
                                                title="Edit Titik SOP">
                                            <i class="ph-bold ph-pencil-simple text-base"></i>
                                        </button>

                                        <button type="button"
                                                @click="openDelete('{{ $point->id }}', '{{ $point->urutan }}', '{{ addslashes($point->nama_titik) }}', {{ $point->evidence_items_count ?? 0 }}, '{{ route('settings.sop.destroy', $point) }}')"
                                                class="p-2 rounded-xl text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                                title="Hapus Titik SOP">
                                            <i class="ph-bold ph-trash text-base"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                            <i class="ph-bold ph-list-dashes text-2xl"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-600">Tidak ada titik SOP yang sesuai kriteria.</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Coba sesuaikan filter atau tambahkan titik SOP baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL: Tambah Titik SOP Baru -->
        <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="createModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="createModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-xl my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form action="{{ route('settings.sop.store') }}" method="POST">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-plus-circle text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Tambah Titik SOP Baru</h3>
                                        <p class="text-[11px] text-gray-500">Definisikan titik pemeriksaan baru ke database.</p>
                                    </div>
                                </div>
                                <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 mt-5">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Urutan <span class="text-red-500">*</span></label>
                                        <input type="number" name="urutan" value="{{ $maxUrutan + 1 }}" min="1" required
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Titik SOP <span class="text-red-500">*</span></label>
                                        <input type="text" name="nama_titik" required placeholder="Misal: Foto Segel Pelayaran"
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Deskripsi / Instruksi Petugas</label>
                                    <textarea name="deskripsi" rows="2" placeholder="Petunjuk posisi sudut foto atau ketentuan khusus bagi operator lapangan..."
                                              class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tipe Media <span class="text-red-500">*</span></label>
                                        <select name="tipe_item" required
                                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                            <option value="foto">Foto Bukti (JPG)</option>
                                            <option value="dokumen">Dokumen Checklist</option>
                                            <option value="video">Video Singkat (MP4)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Target Kargo <span class="text-red-500">*</span></label>
                                        <select name="jenis_pengiriman" required
                                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                            <option value="both">Export & Lokal (Keduanya)</option>
                                            <option value="export">Khusus Export Saja</option>
                                            <option value="lokal">Khusus Lokal Saja</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Checkbox Flags -->
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2.5">
                                    <p class="text-[11px] font-extrabold text-gray-600 uppercase tracking-wider mb-2">Aturan Validasi & AI Asinkron</p>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="wajib" value="1" checked
                                               class="rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                                        <span class="text-xs font-bold text-gray-800">Wajib Diisi oleh Petugas Lapangan</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="perlu_ocr_container" value="1"
                                               class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4 border-gray-300">
                                        <span class="text-xs font-medium text-gray-700">Jalankan OCR Validasi Nomor Container</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="perlu_deteksi_orang" value="1"
                                               class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 border-gray-300">
                                        <span class="text-xs font-medium text-gray-700">Jalankan AI Deteksi Keberadaan Orang (YOLOv8)</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="perlu_deteksi_barcode" value="1"
                                               class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4 border-gray-300">
                                        <span class="text-xs font-medium text-gray-700">Jalankan AI Deteksi Area & Barcode Bale</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-check text-sm"></i>
                                Simpan Titik SOP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Edit Titik SOP -->
        <div x-cloak x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-xl my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form :action="editPoint.updateUrl" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Edit Titik SOP #<span x-text="editPoint.urutan"></span></h3>
                                        <p class="text-[11px] text-gray-500">Perbarui informasi instruksi atau parameter validasi.</p>
                                    </div>
                                </div>
                                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 mt-5">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Urutan <span class="text-red-500">*</span></label>
                                        <input type="number" name="urutan" x-model="editPoint.urutan" min="1" required
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Titik SOP <span class="text-red-500">*</span></label>
                                        <input type="text" name="nama_titik" x-model="editPoint.nama_titik" required
                                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Deskripsi / Panduan Petugas</label>
                                    <textarea name="deskripsi" x-model="editPoint.deskripsi" rows="2"
                                              class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tipe Media <span class="text-red-500">*</span></label>
                                        <select name="tipe_item" x-model="editPoint.tipe_item" required
                                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                            <option value="foto">Foto Bukti (JPG)</option>
                                            <option value="dokumen">Dokumen Checklist</option>
                                            <option value="video">Video Singkat (MP4)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Target Kargo <span class="text-red-500">*</span></label>
                                        <select name="jenis_pengiriman" x-model="editPoint.jenis_pengiriman" required
                                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                            <option value="both">Export & Lokal (Keduanya)</option>
                                            <option value="export">Khusus Export Saja</option>
                                            <option value="lokal">Khusus Lokal Saja</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Checkbox Flags -->
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2.5">
                                    <p class="text-[11px] font-extrabold text-gray-600 uppercase tracking-wider mb-2">Aturan Validasi & AI Asinkron</p>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="wajib" value="1" x-model="editPoint.wajib"
                                               class="rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                                        <span class="text-xs font-bold text-gray-800">Wajib Diisi oleh Petugas Lapangan</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="perlu_ocr_container" value="1" x-model="editPoint.perlu_ocr_container"
                                               class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4 border-gray-300">
                                        <span class="text-xs font-medium text-gray-700">Jalankan OCR Validasi Nomor Container</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="perlu_deteksi_orang" value="1" x-model="editPoint.perlu_deteksi_orang"
                                               class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 border-gray-300">
                                        <span class="text-xs font-medium text-gray-700">Jalankan AI Deteksi Keberadaan Orang (YOLOv8)</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="perlu_deteksi_barcode" value="1" x-model="editPoint.perlu_deteksi_barcode"
                                               class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4 border-gray-300">
                                        <span class="text-xs font-medium text-gray-700">Jalankan AI Deteksi Area & Barcode Bale</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-check text-sm"></i>
                                Perbarui Titik SOP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Hapus Titik SOP -->
        <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="deleteModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form :action="deletePoint.deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                    <i class="ph-bold ph-warning text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Konfirmasi Hapus Titik SOP</h3>
                                    <p class="text-[11px] text-gray-500">Tindakan ini permanen.</p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600 leading-relaxed">
                                Apakah Anda yakin ingin menghapus Titik SOP #<strong class="text-gray-900" x-text="deletePoint.urutan"></strong>: <span class="font-bold text-gray-900" x-text="deletePoint.nama_titik"></span> dari database?
                            </p>

                            <template x-if="deletePoint.evidence_count > 0">
                                <div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                                    <i class="ph-bold ph-warning-circle text-base shrink-0"></i>
                                    <span>Peringatan: Terdapat <strong x-text="deletePoint.evidence_count"></strong> bukti foto staging yang merujuk ke titik ini. Sistem akan mencegah penghapusan jika ada relasi aktif.</span>
                                </div>
                            </template>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="deleteModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-trash text-sm"></i>
                                Hapus Titik SOP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Reset ke Standar 27 Titik -->
        <div x-cloak x-show="resetModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="resetModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="resetModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="resetModalOpen"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form action="{{ route('settings.sop.reset-defaults') }}" method="POST">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center font-bold">
                                    <i class="ph-bold ph-arrow-counter-clockwise text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Reset ke 27 Titik SOP Standar</h3>
                                    <p class="text-[11px] text-gray-500">Sinkronkan ulang titik inspeksi baku sistem.</p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600 leading-relaxed">
                                Tindakan ini akan menyelaraskan urutan 1 s/d 27 titik SOP sesuai master blueprint sistem SPV-Track (Plat Nomor, Area Staging, Bales Zoomable, Pembersihan, Swab Test, Pintu, Segel, Checklist Dokumen & Video Singkat).
                            </p>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="resetModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-check text-sm"></i>
                                Ya, Sinkronkan Standar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-layout>
