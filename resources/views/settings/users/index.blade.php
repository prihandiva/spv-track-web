<x-layout :title="'Manajemen Pengguna'">

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        editUser: { id: '', nama_warehouse: '', email: '', role: 'operator', updateUrl: '' },
        deleteUser: { id: '', nama_warehouse: '', email: '', role: '', deleteUrl: '' },
        openEdit(id, nama_warehouse, email, role, updateUrl) {
            this.editUser = { id, nama_warehouse, email, role, updateUrl };
            this.editModalOpen = true;
        },
        openDelete(id, nama_warehouse, email, role, deleteUrl) {
            this.deleteUser = { id, nama_warehouse, email, role, deleteUrl };
            this.deleteModalOpen = true;
        }
    }">

        <!-- Header Section Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-gray-800 tracking-tight">Manajemen Pengguna</h1>
                    <p class="text-xs text-gray-500 mt-1">Kelola data akun login warehouse, kredensial pengguna, dan penetapan role hak akses.</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" @click="createModalOpen = true"
                            class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all w-full sm:w-auto">
                        <i class="ph-bold ph-user-plus text-base"></i>
                        Tambah Pengguna
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        @include('settings.tabs', ['active' => 'users'])

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Pengguna -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #285491, #3568b0); color: white;">
                    <i class="ph-fill ph-users text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Pengguna</p>
                    <p class="text-xl font-extrabold text-gray-800 mt-0.5">{{ $totalUsers }}</p>
                </div>
            </div>

            <!-- Superadmin -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #7c3aed, #a855f7); color: white;">
                    <i class="ph-fill ph-crown text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Superadmin</p>
                    <p class="text-xl font-extrabold text-purple-600 mt-0.5">{{ $totalSuperadmin }}</p>
                </div>
            </div>

            <!-- Admin Warehouse -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #0d5950, #285491); color: white;">
                    <i class="ph-fill ph-shield-check text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Admin</p>
                    <p class="text-xl font-extrabold text-spv-blue mt-0.5">{{ $totalAdmin }}</p>
                </div>
            </div>

            <!-- Operator Lapangan -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #059e3d, #63c384); color: white;">
                    <i class="ph-fill ph-hard-hat text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Operator</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $totalOperator }}</p>
                </div>
            </div>
        </div>

        <!-- Main User Table Card -->
        <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">

            <!-- Toolbar & Filter -->
            <form method="GET" action="{{ route('settings.users.index') }}" class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-bottom: 1px solid #f0f4f9;">
                <div class="relative w-full md:w-80">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama warehouse atau email..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <a href="{{ route('settings.users.index', array_filter(['search' => request('search')])) }}"
                       class="text-[11px] font-bold px-4 py-2 rounded-full whitespace-nowrap {{ !request('role') ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
                        Semua ({{ $totalUsers }})
                    </a>
                    <a href="{{ route('settings.users.index', array_filter(['role' => 'superadmin', 'search' => request('search')])) }}"
                       class="text-[11px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('role') === 'superadmin' ? 'bg-purple-600 text-white shadow-sm' : 'text-purple-700 border border-purple-200 hover:bg-purple-50' }}">
                        Superadmin ({{ $totalSuperadmin }})
                    </a>
                    <a href="{{ route('settings.users.index', array_filter(['role' => 'admin', 'search' => request('search')])) }}"
                       class="text-[11px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('role') === 'admin' ? 'bg-spv-blue text-white shadow-sm' : 'text-blue-700 border border-blue-200 hover:bg-blue-50' }}">
                        Admin ({{ $totalAdmin }})
                    </a>
                    <a href="{{ route('settings.users.index', array_filter(['role' => 'operator', 'search' => request('search')])) }}"
                       class="text-[11px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('role') === 'operator' ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-700 border border-emerald-200 hover:bg-emerald-50' }}">
                        Operator ({{ $totalOperator }})
                    </a>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-400" style="border-bottom: 1px solid #f0f4f9;">
                        <tr>
                            <th scope="col" class="py-3.5 px-5">Pengguna & Akun</th>
                            <th scope="col" class="py-3.5 px-5">Role Akses</th>
                            <th scope="col" class="py-3.5 px-5 text-center">Staging Terkait</th>
                            <th scope="col" class="py-3.5 px-5">Terdaftar Pada</th>
                            <th scope="col" class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            @php
                                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $user->nama_warehouse) ?: 'US', 0, 2));
                                $roleBadgeColor = match($user->role) {
                                    'superadmin' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'admin' => 'bg-blue-50 text-spv-blue border-blue-200',
                                    'operator' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <!-- Pengguna & Akun -->
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0"
                                             style="background: {{ $user->role === 'superadmin' ? 'linear-gradient(135deg, #7c3aed, #a855f7)' : ($user->role === 'admin' ? 'linear-gradient(135deg, #285491, #3568b0)' : 'linear-gradient(135deg, #059e3d, #63c384)') }}; color: white;">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">{{ $user->nama_warehouse }}</p>
                                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Akses -->
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $roleBadgeColor }}">
                                        @if($user->role === 'superadmin')
                                            <i class="ph-fill ph-crown text-xs"></i>
                                        @elseif($user->role === 'admin')
                                            <i class="ph-fill ph-shield-check text-xs"></i>
                                        @else
                                            <i class="ph-fill ph-hard-hat text-xs"></i>
                                        @endif
                                        {{ $user->role_display_name }}
                                    </span>
                                </td>

                                <!-- Staging Terkait -->
                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold">
                                        {{ $user->shipments_count ?? 0 }} Shipment
                                    </span>
                                </td>

                                <!-- Terdaftar Pada -->
                                <td class="py-3.5 px-5 whitespace-nowrap text-gray-500">
                                    {{ $user->created_at ? $user->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                @click="openEdit('{{ $user->id }}', '{{ addslashes($user->nama_warehouse) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', '{{ route('settings.users.update', $user) }}')"
                                                class="p-2 rounded-xl text-gray-500 hover:text-spv-blue hover:bg-blue-50 transition-all"
                                                title="Edit Pengguna">
                                            <i class="ph-bold ph-pencil-simple text-base"></i>
                                        </button>

                                        <button type="button"
                                                @click="openDelete('{{ $user->id }}', '{{ addslashes($user->nama_warehouse) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', '{{ route('settings.users.destroy', $user) }}')"
                                                class="p-2 rounded-xl text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                                title="Hapus Pengguna">
                                            <i class="ph-bold ph-trash text-base"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                            <i class="ph-bold ph-user-circle text-2xl"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-600">Tidak ada pengguna yang sesuai kriteria pencarian.</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Coba ganti filter role atau kata kunci pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: Tambah Pengguna Baru -->
        <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="createModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="createModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form action="{{ route('settings.users.store') }}" method="POST">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-user-plus text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Tambah Pengguna Baru</h3>
                                        <p class="text-[11px] text-gray-500">Daftarkan akun login warehouse ke sistem.</p>
                                    </div>
                                </div>
                                <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 mt-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Warehouse / Nama Akun <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_warehouse" required placeholder="Misal: Warehouse A, Admin Staging 1"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Alamat Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" required placeholder="contoh: user@spvtrack.com"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Kata Sandi (Password) <span class="text-red-500">*</span></label>
                                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Role & Tingkat Akses <span class="text-red-500">*</span></label>
                                    <select name="role" required
                                            class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                        <option value="operator">Operator Lapangan (Field App Staging & Upload Foto)</option>
                                        <option value="admin">Admin Warehouse (+ Download Laporan PDF/ZIP/Excel & Audit)</option>
                                        <option value="superadmin">Super Administrator (+ Kelola Akun, Master SOP & Sistem)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-check text-sm"></i>
                                Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Edit Pengguna -->
        <div x-cloak x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form :action="editUser.updateUrl" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Edit Akun Pengguna</h3>
                                        <p class="text-[11px] text-gray-500">Perbarui data login dan peran pengguna.</p>
                                    </div>
                                </div>
                                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            <div class="space-y-4 mt-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Warehouse / Nama Akun <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_warehouse" x-model="editUser.nama_warehouse" required
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Alamat Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" x-model="editUser.email" required
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                        Kata Sandi Baru <span class="text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span>
                                    </label>
                                    <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tetap"
                                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Role & Tingkat Akses <span class="text-red-500">*</span></label>
                                    <select name="role" x-model="editUser.role" required
                                            class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                        <option value="operator">Operator Lapangan (Field App Staging & Upload Foto)</option>
                                        <option value="admin">Admin Warehouse (+ Download Laporan PDF/ZIP/Excel & Audit)</option>
                                        <option value="superadmin">Super Administrator (+ Kelola Akun, Master SOP & Sistem)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-check text-sm"></i>
                                Perbarui Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Hapus Pengguna -->
        <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-3 sm:p-0 text-center sm:block">
                <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="deleteModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md my-auto sm:my-8 sm:align-middle border border-gray-100">

                    <form :action="deleteUser.deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                    <i class="ph-bold ph-warning text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Konfirmasi Hapus Pengguna</h3>
                                    <p class="text-[11px] text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600 leading-relaxed">
                                Apakah Anda yakin ingin menghapus akun pengguna <strong class="text-gray-900" x-text="deleteUser.nama_warehouse"></strong> (<span x-text="deleteUser.email"></span>)?
                            </p>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="deleteModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <i class="ph-bold ph-trash text-sm"></i>
                                Hapus Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-layout>
