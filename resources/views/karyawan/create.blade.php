<x-layout :title="'Tambah Petugas Baru'">

    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-spv-blue transition-colors">Dashboard</a>
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <a href="{{ route('karyawan.index') }}" class="hover:text-spv-blue transition-colors">Petugas</a>
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <span class="text-gray-700 font-semibold">Tambah Petugas</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Tambah Petugas Lapangan Baru</h1>
                <p class="text-xs text-gray-500 mt-1">Daftarkan petugas pemeriksa baru agar dapat dipilih saat staging pengiriman di Field App.</p>
            </div>
            <a href="{{ route('karyawan.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                <i class="ph-bold ph-arrow-left text-sm"></i>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Main Form Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Card (Col 2) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between" style="background:#fafbfd;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white"
                             style="background: linear-gradient(135deg, #285491, #3568b0);">
                            <i class="ph-bold ph-user-plus text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-800">Formulir Data Petugas</h2>
                            <p class="text-[10px] text-gray-400">Harap isi data dengan lengkap dan valid</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('karyawan.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="nama" class="block text-xs font-bold text-gray-700 mb-1.5">
                            Nama Lengkap Petugas <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required
                                   placeholder="Contoh: Budi Santoso"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border {{ $errors->has('nama') ? 'border-rose-400 focus:ring-rose-400' : 'border-gray-200 focus:ring-spv-blue' }} rounded-xl text-xs focus:ring-2 outline-none transition-all">
                        </div>
                        @error('nama')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Induk / NIK -->
                    <div>
                        <label for="nomor_induk" class="block text-xs font-bold text-gray-700 mb-1.5">
                            Nomor Induk / NIK / Badge ID <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ph-bold ph-identification-card absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" id="nomor_induk" name="nomor_induk" value="{{ old('nomor_induk') }}" required
                                   placeholder="Contoh: KRY004 atau NIK-8921"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border {{ $errors->has('nomor_induk') ? 'border-rose-400 focus:ring-rose-400' : 'border-gray-200 focus:ring-spv-blue' }} rounded-xl text-xs focus:ring-2 outline-none transition-all">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Identitas unik karyawan untuk membedakan petugas di sistem.</p>
                        @error('nomor_induk')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            Status Petugas <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-emerald-50/40 hover:border-emerald-300 transition-all">
                                <input type="radio" name="status" value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <p class="text-xs font-bold text-emerald-800">Aktif</p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Dapat dipilih pada Field App</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                                <input type="radio" name="status" value="nonaktif" {{ old('status') === 'nonaktif' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                        <p class="text-xs font-bold text-gray-700">Nonaktif</p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Tidak dapat dipilih di Field App</p>
                                </div>
                            </label>
                        </div>
                        @error('status')
                            <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('karyawan.index') }}"
                           class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                                class="flex items-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-6 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all">
                            <i class="ph-bold ph-check text-sm"></i>
                            Simpan Petugas Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information / Side Panel (Col 1) -->
        <div class="space-y-5">
            <!-- Guidelines Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
                <div class="flex items-center gap-2.5 text-spv-blue mb-3">
                    <i class="ph-bold ph-info text-lg"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Petunjuk Petugas</h3>
                </div>
                <div class="space-y-3 text-xs text-gray-500 leading-relaxed">
                    <p>
                        Setiap data petugas yang didaftarkan dengan status <strong>Aktif</strong> akan otomatis sinkron ke:
                    </p>
                    <ul class="space-y-2 list-none">
                        <li class="flex items-start gap-2">
                            <i class="ph-bold ph-check-circle text-emerald-600 mt-0.5 shrink-0"></i>
                            <span>Pilihan <strong>Nama Petugas</strong> di aplikasi mobile / Field App.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="ph-bold ph-check-circle text-emerald-600 mt-0.5 shrink-0"></i>
                            <span>Label pelaksana pada watermark bukti foto SOP staging.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="ph-bold ph-check-circle text-emerald-600 mt-0.5 shrink-0"></i>
                            <span>Audit trail dan riwayat pengiriman warehouse.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Link to Field App -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="ph-fill ph-device-mobile text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-800">Field App Web</h4>
                        <p class="text-[10px] text-gray-500 mt-0.5">Uji coba langsung pilihan petugas di form staging.</p>
                    </div>
                </div>
                <a href="{{ route('field-app.create') }}" target="_blank"
                   class="mt-4 flex items-center justify-center gap-1.5 w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2 rounded-xl transition-all shadow-sm">
                    <span>Buka Field App</span>
                    <i class="ph-bold ph-arrow-square-out text-sm"></i>
                </a>
            </div>
        </div>

    </div>

</x-layout>
