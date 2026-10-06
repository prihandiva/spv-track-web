<x-layout :title="'Pusat Pengaturan & Master Data'">

    <!-- Header Section Card -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-spv-blue">
                        Sistem Konfigurasi
                    </span>
                    <span class="text-xs text-gray-400">&bull;</span>
                    <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Database PostgreSQL Aktif
                    </span>
                </div>
                <h1 class="text-xl lg:text-2xl font-black text-gray-800 mt-1.5 tracking-tight">Pusat Pengaturan & Master Data</h1>
                <p class="text-xs text-gray-500 mt-1">Konfigurasi hak akses pengguna, tata kelola 27 titik SOP evidence, dan parameter operasional warehouse.</p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
                <a href="{{ route('settings.sop.index') }}"
                   class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(5,158,61,0.25)] transition-all flex-1 sm:flex-none">
                    <i class="ph-bold ph-list-checks text-base"></i>
                    Kelola 27 Titik SOP
                </a>
                <a href="{{ route('settings.users.index') }}"
                   class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] transition-all flex-1 sm:flex-none">
                    <i class="ph-bold ph-user-plus text-base"></i>
                    Kelola Pengguna
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('settings.tabs', ['active' => 'index'])

    <!-- 4 Sub-Menu Navigation & Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

        <!-- Card 1: Pengguna -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style="background: linear-gradient(135deg, #285491, #3568b0);">
                        <i class="ph-fill ph-users text-2xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-50 text-spv-blue border border-blue-100">
                        {{ $totalUsers }} Akun
                    </span>
                </div>
                <h3 class="text-base font-bold text-gray-800">Manajemen Pengguna</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Kelola akun warehouse login, reset password, dan penugasan role hak akses pengguna.</p>

                <div class="mt-4 pt-3 border-t border-gray-100 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Superadmin:</span>
                        <span class="font-bold text-purple-600">{{ $totalSuperadmin }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Admin Warehouse:</span>
                        <span class="font-bold text-spv-blue">{{ $totalAdmin }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Operator Lapangan:</span>
                        <span class="font-bold text-emerald-600">{{ $totalOperator }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('settings.users.index') }}"
               class="mt-5 w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-gray-50 hover:bg-spv-blue hover:text-white text-gray-700 text-xs font-bold transition-all border border-gray-200 hover:border-spv-blue">
                <span>Buka Pengguna</span>
                <i class="ph-bold ph-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Card 2: Role & Hak Akses -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <i class="ph-fill ph-shield-check text-2xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $totalRoles }} Role
                    </span>
                </div>
                <h3 class="text-base font-bold text-gray-800">Role & Hak Akses</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Matriks perizinan aksi operasional (Field App, download laporan, koreksi data, edit SOP).</p>

                <div class="mt-4 pt-3 border-t border-gray-100 space-y-1.5 text-xs">
                    @foreach($roles as $r)
                        <div class="flex items-center justify-between text-gray-600">
                            <span class="truncate pr-2">{{ $r->display_name }}:</span>
                            <span class="font-bold text-gray-800">{{ $r->users_count }} user</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('settings.roles.index') }}"
               class="mt-5 w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-gray-50 hover:bg-indigo-600 hover:text-white text-gray-700 text-xs font-bold transition-all border border-gray-200 hover:border-indigo-600">
                <span>Atur Hak Akses</span>
                <i class="ph-bold ph-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Card 3: Master 27 Titik SOP -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style="background: linear-gradient(135deg, #059e3d, #63c384);">
                        <i class="ph-fill ph-list-checks text-2xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                        {{ $totalSop }} Titik SOP
                    </span>
                </div>
                <h3 class="text-base font-bold text-gray-800">Master 27 Titik SOP</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Standar baku 27 titik foto/dokumen/video staging di database, urutan kartu, dan validasi AI.</p>

                <div class="mt-4 pt-3 border-t border-gray-100 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Wajib Diisi:</span>
                        <span class="font-bold text-emerald-700">{{ $totalSopWajib }} Titik</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Verifikasi OCR Container:</span>
                        <span class="font-bold text-amber-600">{{ $totalSopOcr }} Titik</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Deteksi AI (Orang/Barcode):</span>
                        <span class="font-bold text-blue-600">{{ $totalSopAi }} Titik</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('settings.sop.index') }}"
               class="mt-5 w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-gray-50 hover:bg-emerald-600 hover:text-white text-gray-700 text-xs font-bold transition-all border border-gray-200 hover:border-emerald-600">
                <span>Kelola Master SOP</span>
                <i class="ph-bold ph-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Card 4: Parameter Sistem -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style="background: linear-gradient(135deg, #0d5950, #285491);">
                        <i class="ph-fill ph-sliders text-2xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 border border-teal-100">
                        {{ $settings->count() }} Parameter
                    </span>
                </div>
                <h3 class="text-base font-bold text-gray-800">Parameter Sistem</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Nama perusahaan, warehouse aktif, staging area, watermark dokumen, dan format unduhan.</p>

                <div class="mt-4 pt-3 border-t border-gray-100 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Auto-OCR Async:</span>
                        <span class="font-bold text-emerald-600">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Watermark PDF WIB:</span>
                        <span class="font-bold text-emerald-600">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Format File Batch:</span>
                        <span class="font-bold text-gray-800 truncate pl-2">ZIP Evidence</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('settings.system.index') }}"
               class="mt-5 w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-gray-50 hover:bg-teal-700 hover:text-white text-gray-700 text-xs font-bold transition-all border border-gray-200 hover:border-teal-700">
                <span>Buka Parameter</span>
                <i class="ph-bold ph-arrow-right text-xs"></i>
            </a>
        </div>

    </div>

    <!-- Additional Info Section: Architecture & Storage Policy -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Architecture Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)]">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center">
                    <i class="ph-fill ph-database text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Prinsip Master Data Tersimpan di Database</h3>
                    <p class="text-xs text-gray-500">Master SOP & Parameter tidak di-hardcode di kode program.</p>
                </div>
            </div>

            <div class="space-y-3 text-xs text-gray-600 leading-relaxed">
                <p>
                    Semua titik inspeksi (27 titik) disimpan secara dinamis pada tabel database <code class="px-1.5 py-0.5 rounded bg-gray-100 text-spv-blue font-mono font-bold">sop_photo_points</code>. Jika ada perubahan SOP operasional warehouse di masa depan, Administrator cukup memperbarui data dari menu ini tanpa memerlukan deploy ulang aplikasi.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center gap-2 font-bold text-gray-800 mb-1">
                            <i class="ph-bold ph-shield-check text-emerald-600"></i>
                            Evidence Append-Only
                        </div>
                        <p class="text-[11px] text-gray-500">Foto bukti staging tidak dapat dihapus sembarangan untuk menjamin integritas audit dan kepatuhan pengiriman.</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center gap-2 font-bold text-gray-800 mb-1">
                            <i class="ph-bold ph-cpu text-blue-600"></i>
                            AI & OCR Asinkron
                        </div>
                        <p class="text-[11px] text-gray-500">Verifikasi nomor kontainer, deteksi orang pada pembersihan bale, dan barcode label berjalan di background tanpa menghambat kerja operator.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Summary Box -->
        <div class="bg-gradient-to-br from-[#1a3c6e] via-[#285491] to-[#0d5950] rounded-2xl p-6 text-white shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Status Database</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">Sinkron</span>
                </div>
                <h3 class="text-lg font-extrabold text-white leading-tight">SPV-Track Master Control</h3>
                <p class="text-xs text-white/70 mt-2 leading-relaxed">
                    Sistem terhubung langsung ke database PostgreSQL lokal dengan 27 master titik SOP terkonfigurasi untuk Fiber & Sodium.
                </p>

                <div class="mt-5 space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-white/10 text-white/80">
                        <span>Total Akun Pengguna</span>
                        <span class="font-bold text-white">{{ $totalUsers }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-white/10 text-white/80">
                        <span>Role Hak Akses</span>
                        <span class="font-bold text-white">{{ $totalRoles }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 text-white/80">
                        <span>Titik Master SOP</span>
                        <span class="font-bold text-white">{{ $totalSop }} Titik</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-white/60">
                <span>Versi Schema: 2.1</span>
                <span>Waktu Server: WIB</span>
            </div>
        </div>

    </div>

</x-layout>
