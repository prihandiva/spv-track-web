<x-layout :title="'Edit Petugas: ' . $karyawan->nama">

    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-spv-blue transition-colors">Dashboard</a>
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <a href="{{ route('karyawan.index') }}" class="hover:text-spv-blue transition-colors">Petugas</a>
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <span class="text-gray-700 font-semibold">Edit: {{ $karyawan->nama }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Edit Data Petugas Lapangan</h1>
                <p class="text-xs text-gray-500 mt-1">Perbarui nama, nomor induk/NIK, dan status keaktifan petugas.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('karyawan.show', $karyawan->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ph-bold ph-eye text-sm"></i>
                    Lihat Profil
                </a>
                <a href="{{ route('karyawan.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ph-bold ph-arrow-left text-sm"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Card (Col 2) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between" style="background:#fafbfd;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white bg-amber-500 shadow-sm">
                            <i class="ph-bold ph-pencil-simple text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-800">Perbarui Profil Petugas</h2>
                            <p class="text-[10px] text-gray-400">ID Petugas #{{ $karyawan->id }} &bull; Terdaftar {{ $karyawan->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Nama Petugas -->
                    <div>
                        <label for="nama" class="block text-xs font-bold text-gray-700 mb-1.5">
                            Nama Lengkap Petugas <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" id="nama" name="nama" value="{{ old('nama', $karyawan->nama) }}" required
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
                            <input type="text" id="nomor_induk" name="nomor_induk" value="{{ old('nomor_induk', $karyawan->nomor_induk) }}" required
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 border {{ $errors->has('nomor_induk') ? 'border-rose-400 focus:ring-rose-400' : 'border-gray-200 focus:ring-spv-blue' }} rounded-xl text-xs focus:ring-2 outline-none transition-all">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Harus unik di database SPV-Track.</p>
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
                                <input type="radio" name="status" value="aktif" {{ old('status', $karyawan->status) === 'aktif' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <p class="text-xs font-bold text-emerald-800">Aktif</p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Dapat dipilih pada Field App</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                                <input type="radio" name="status" value="nonaktif" {{ old('status', $karyawan->status) === 'nonaktif' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
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
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card (Col 1) -->
        <div class="space-y-5">
            <!-- Profile Summary Box -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-[0_1px_6px_rgba(40,84,145,0.05)] text-center">
                @php
                    $initials = collect(explode(' ', $karyawan->nama))
                        ->map(fn($segment) => mb_substr($segment, 0, 1))
                        ->take(2)
                        ->implode('');
                @endphp
                <div class="w-16 h-16 rounded-2xl mx-auto flex items-center justify-center font-bold text-lg shadow-sm mb-3"
                     style="background: linear-gradient(135deg, {{ $karyawan->status === 'aktif' ? '#e1f8eb, #63c384' : '#f3f4f6, #d1d5db' }}); color: {{ $karyawan->status === 'aktif' ? '#0d5950' : '#4b5563' }};">
                    {{ strtoupper($initials) }}
                </div>
                <h3 class="text-sm font-bold text-gray-800">{{ $karyawan->nama }}</h3>
                <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $karyawan->nomor_induk }}</p>

                <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-left">
                    <div>
                        <p class="text-[10px] text-gray-400">Total Staging</p>
                        <p class="text-sm font-bold text-gray-800">{{ $karyawan->shipments()->count() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400">Status Saat Ini</p>
                        <p class="text-xs font-bold capitalize {{ $karyawan->status === 'aktif' ? 'text-emerald-600' : 'text-gray-500' }}">
                            {{ $karyawan->status }}
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    <form action="{{ route('karyawan.toggle-status', $karyawan->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-bold border transition-colors {{ $karyawan->status === 'aktif' ? 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100' : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                            <i class="ph-bold ph-arrows-clockwise"></i>
                            {{ $karyawan->status === 'aktif' ? 'Ubah Menjadi Nonaktif' : 'Aktifkan Petugas Ini' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-layout>
