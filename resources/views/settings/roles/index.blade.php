<x-layout :title="'Role & Hak Akses'">

    <div x-data="{
        createRoleModalOpen: false,
        editDescModalOpen: false,
        deleteRoleModalOpen: false,
        deleteRole: { id: '', name: '', deleteUrl: '' },
        openDelete(id, name, deleteUrl) {
            this.deleteRole = { id, name, deleteUrl };
            this.deleteRoleModalOpen = true;
        }
    }">

        <!-- Header Section Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-gray-800 tracking-tight">Role & Hak Akses</h1>
                    <p class="text-xs text-gray-500 mt-1">Konfigurasi matriks perizinan fitur, modul operasional, dan kewenangan setiap peran pengguna.</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" @click="createRoleModalOpen = true"
                            class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all w-full sm:w-auto">
                        <i class="ph-bold ph-plus-circle text-base"></i>
                        Tambah Role Kustom
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        @include('settings.tabs', ['active' => 'roles'])

        <!-- Role Selector Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            @foreach($roles as $role)
                @php
                    $isSelected = ($selectedRole?->id === $role->id);
                    $roleTheme = match($role->name) {
                        'superadmin' => ['border' => 'border-purple-300', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'gradient' => 'linear-gradient(135deg, #7c3aed, #a855f7)', 'icon' => 'ph-crown'],
                        'admin' => ['border' => 'border-blue-300', 'bg' => 'bg-blue-50', 'text' => 'text-spv-blue', 'gradient' => 'linear-gradient(135deg, #285491, #3568b0)', 'icon' => 'ph-shield-check'],
                        'operator' => ['border' => 'border-emerald-300', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'gradient' => 'linear-gradient(135deg, #059e3d, #63c384)', 'icon' => 'ph-hard-hat'],
                        default => ['border' => 'border-gray-200', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'gradient' => 'linear-gradient(135deg, #4b5563, #6b7280)', 'icon' => 'ph-user-gear'],
                    };
                @endphp
                <a href="{{ route('settings.roles.index', ['role_id' => $role->id]) }}"
                   class="relative bg-white rounded-2xl p-5 border transition-all text-left block {{ $isSelected ? 'border-2 border-spv-blue shadow-lg -translate-y-1' : 'border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] hover:shadow-md' }}">

                    @if($isSelected)
                        <div class="absolute -top-2.5 right-4 bg-spv-blue text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shadow-sm flex items-center gap-1">
                            <i class="ph-bold ph-check text-xs"></i>
                            Sedang Diedit
                        </div>
                    @endif

                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white" style="background: {{ $roleTheme['gradient'] }};">
                            <i class="ph-fill {{ $roleTheme['icon'] }} text-xl"></i>
                        </div>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $roleTheme['bg'] }} {{ $roleTheme['text'] }}">
                            {{ $role->users_count }} Akun
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-gray-800">{{ $role->display_name }}</h3>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ $role->description }}</p>

                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                        <span>{{ is_array($role->permissions) ? count($role->permissions) : 0 }} Izin Aktif</span>
                        @if($role->is_system)
                            <span class="font-semibold text-gray-400">Role Sistem</span>
                        @else
                            <button type="button"
                                    @click.prevent="openDelete('{{ $role->id }}', '{{ addslashes($role->display_name) }}', '{{ route('settings.roles.destroy', $role) }}')"
                                    class="text-rose-500 hover:text-rose-700 font-bold">
                                Hapus
                            </button>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        @if($selectedRole)
            <!-- Matriks Perizinan Role Terpilih -->
            <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">
                <form action="{{ route('settings.roles.update', $selectedRole) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Card Header -->
                    <div class="p-5 sm:p-6 bg-gradient-to-r from-gray-50 to-white flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-bottom: 1px solid #f0f4f9;">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-spv-blue text-white">
                                    Matriks Hak Akses
                                </span>
                                <span class="text-xs text-gray-400">&bull;</span>
                                <span class="text-xs font-bold text-gray-700">{{ $selectedRole->display_name }}</span>
                            </div>
                            <h2 class="text-lg font-black text-gray-800 mt-1">Konfigurasi Hak Akses & Matriks Izin</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Centang izin yang diperbolehkan untuk role ini, lalu tekan tombol Simpan Perubahan.</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(5,158,61,0.25)] hover:-translate-y-0.5 transition-all">
                                <i class="ph-bold ph-floppy-disk text-base"></i>
                                Simpan Hak Akses ke Database
                            </button>
                        </div>
                    </div>

                    <!-- Role Details Form -->
                    <div class="p-5 sm:p-6 bg-white border-b border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Tampilan Role <span class="text-red-500">*</span></label>
                            <input type="text" name="display_name" value="{{ old('display_name', $selectedRole->display_name) }}" required
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi Kewenangan Role</label>
                            <input type="text" name="description" value="{{ old('description', $selectedRole->description) }}"
                                   placeholder="Ringkasan fungsi role ini..."
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        </div>
                    </div>

                    <!-- Modules & Permission Checkboxes -->
                    <div class="p-5 sm:p-6 space-y-6">
                        @php
                            $rolePerms = $selectedRole->permissions ?? [];
                        @endphp

                        @foreach($availableModules as $moduleName => $permissions)
                            <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-4 sm:p-5">
                                <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200/60">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full bg-spv-blue"></div>
                                        <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">{{ $moduleName }}</h4>
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-semibold">{{ count($permissions) }} Fitur</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($permissions as $permKey => $permLabel)
                                        @php
                                            $isChecked = in_array($permKey, $rolePerms, true);
                                        @endphp
                                        <label class="flex items-start gap-3 p-3 rounded-xl bg-white border border-gray-200/70 hover:border-spv-blue/50 hover:bg-blue-50/20 cursor-pointer transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   class="mt-0.5 rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                                            <div class="flex-1">
                                                <p class="text-xs font-bold text-gray-800 leading-snug">{{ $permLabel }}</p>
                                                <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $permKey }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Footer Action -->
                    <div class="p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                        <button type="submit"
                                class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-6 py-2.5 rounded-xl shadow-sm transition-all">
                            <i class="ph-bold ph-floppy-disk text-base"></i>
                            Simpan Hak Akses ke Database
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- MODAL: Tambah Role Kustom -->
        <div x-cloak x-show="createRoleModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="createRoleModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="createRoleModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createRoleModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-xl my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form action="{{ route('settings.roles.store') }}" method="POST">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-shield-plus text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Tambah Role Kustom Baru</h3>
                                        <p class="text-[11px] text-gray-500">Definisikan tingkat kewenangan baru di warehouse.</p>
                                    </div>
                                </div>
                                <button type="button" @click="createRoleModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 mt-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Tampilan Role <span class="text-red-500">*</span></label>
                                    <input type="text" name="display_name" required placeholder="Misal: Auditor Eksternal, Supervisor Staging"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Deskripsi Ringkas</label>
                                    <textarea name="description" rows="2" placeholder="Jelaskan tujuan dan batasan role ini..."
                                              class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="createRoleModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-check text-sm"></i>
                                Simpan Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Hapus Role Kustom -->
        <div x-cloak x-show="deleteRoleModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="deleteRoleModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="deleteRoleModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteRoleModalOpen"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form :action="deleteRole.deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                    <i class="ph-bold ph-warning text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Konfirmasi Hapus Role</h3>
                                    <p class="text-[11px] text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600 leading-relaxed">
                                Apakah Anda yakin ingin menghapus role <strong class="text-gray-900" x-text="deleteRole.name"></strong>?
                            </p>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="deleteRoleModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-trash text-sm"></i>
                                Hapus Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-layout>
