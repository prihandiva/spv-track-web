@props(['active' => 'index'])

<div class="bg-white rounded-2xl p-1.5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] mb-6 overflow-x-auto scrollbar-none">
    <div class="flex items-center gap-1.5 min-w-max">
        <!-- Ringkasan -->
        <a href="{{ route('settings.index') }}"
           class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ $active === 'index' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-600 hover:text-spv-blue hover:bg-gray-50' }}">
            <i class="ph-bold ph-squares-four text-base"></i>
            <span>Ringkasan Pengaturan</span>
        </a>

        <!-- Pengguna -->
        <a href="{{ route('settings.users.index') }}"
           class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ $active === 'users' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-600 hover:text-spv-blue hover:bg-gray-50' }}">
            <i class="ph-bold ph-users text-base"></i>
            <span>Manajemen Pengguna</span>
        </a>

        <!-- Role & Hak Akses -->
        <a href="{{ route('settings.roles.index') }}"
           class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ $active === 'roles' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-600 hover:text-spv-blue hover:bg-gray-50' }}">
            <i class="ph-bold ph-shield-check text-base"></i>
            <span>Role & Hak Akses</span>
        </a>

        <!-- Master 27 Titik SOP -->
        <a href="{{ route('settings.sop.index') }}"
           class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ $active === 'sop' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-600 hover:text-spv-blue hover:bg-gray-50' }}">
            <i class="ph-bold ph-list-checks text-base"></i>
            <span>Master 27 Titik SOP</span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $active === 'sop' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">27</span>
        </a>

        <!-- Parameter Sistem -->
        <a href="{{ route('settings.system.index') }}"
           class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ $active === 'system' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-600 hover:text-spv-blue hover:bg-gray-50' }}">
            <i class="ph-bold ph-sliders text-base"></i>
            <span>Parameter Sistem</span>
        </a>
    </div>
</div>
