<x-layout>
    @section('title', 'Dashboard')

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between group hover:shadow-[0_8px_20px_-6px_rgba(6,81,237,0.15)] transition-all duration-300">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Shipments</p>
                <h3 class="text-3xl font-bold text-gray-800">1,284</h3>
                <p class="text-xs font-medium text-spv-green mt-2 flex items-center">
                    <i class="ph-bold ph-trend-up mr-1"></i> +12% dari bulan lalu
                </p>
            </div>
            <div class="w-14 h-14 rounded-full bg-spv-light-green text-spv-green flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                <i class="ph-fill ph-package"></i>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between group hover:shadow-[0_8px_20px_-6px_rgba(6,81,237,0.15)] transition-all duration-300">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Staging Hari Ini</p>
                <h3 class="text-3xl font-bold text-gray-800">42</h3>
                <p class="text-xs font-medium text-gray-400 mt-2 flex items-center">
                    <i class="ph-bold ph-clock mr-1"></i> Terakhir update 5mnt lalu
                </p>
            </div>
            <div class="w-14 h-14 rounded-full bg-blue-50 text-spv-blue flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                <i class="ph-fill ph-truck"></i>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between group hover:shadow-[0_8px_20px_-6px_rgba(6,81,237,0.15)] transition-all duration-300">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Menunggu Validasi</p>
                <h3 class="text-3xl font-bold text-gray-800">8</h3>
                <p class="text-xs font-medium text-yellow-500 mt-2 flex items-center">
                    <i class="ph-bold ph-warning mr-1"></i> Butuh review Admin
                </p>
            </div>
            <div class="w-14 h-14 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                <i class="ph-fill ph-clipboard-text"></i>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between group hover:shadow-[0_8px_20px_-6px_rgba(6,81,237,0.15)] transition-all duration-300">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Petugas Aktif</p>
                <h3 class="text-3xl font-bold text-gray-800">15</h3>
                <p class="text-xs font-medium text-spv-dark-teal mt-2 flex items-center">
                    <i class="ph-bold ph-users mr-1"></i> 3 shift berjalan
                </p>
            </div>
            <div class="w-14 h-14 rounded-full bg-[#0d5950]/10 text-spv-dark-teal flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300">
                <i class="ph-fill ph-user-circle-gear"></i>
            </div>
        </div>

    </div>

    <!-- Recent Shipments Table -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-lg text-gray-800">Shipments Terbaru</h3>
            <button class="text-sm font-medium text-spv-blue hover:text-spv-dark-teal transition-colors flex items-center">
                Lihat Semua <i class="ph-bold ph-arrow-right ml-1"></i>
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500 uppercase text-xs font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">No. Container / Plat</th>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Petugas</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Row 1 -->
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="ph-fill ph-shipping-container text-spv-blue text-lg mr-2"></i>
                                <span class="font-medium text-gray-800">MSKU1234567</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Fiber</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-spv-light-green text-spv-green">
                                Export
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Budi Santoso</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">Hari ini, 14:30</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></div> Submitted
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="p-1.5 text-gray-400 hover:text-spv-blue hover:bg-blue-50 rounded-lg transition-colors">
                                <i class="ph-bold ph-eye text-lg"></i>
                            </button>
                            <button class="p-1.5 text-gray-400 hover:text-spv-dark-teal hover:bg-[#0d5950]/10 rounded-lg transition-colors">
                                <i class="ph-bold ph-download-simple text-lg"></i>
                            </button>
                        </td>
                    </tr>
                    <!-- Row 2 -->
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="ph-fill ph-truck text-gray-400 text-lg mr-2"></i>
                                <span class="font-medium text-gray-800">B 1234 CD</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Sodium</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Lokal
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Agus Supriyadi</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">Hari ini, 13:15</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-600 border border-yellow-200">
                                <div class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5 animate-pulse"></div> Draft
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="p-1.5 text-gray-400 hover:text-spv-blue hover:bg-blue-50 rounded-lg transition-colors">
                                <i class="ph-bold ph-eye text-lg"></i>
                            </button>
                            <button disabled class="p-1.5 text-gray-200 cursor-not-allowed rounded-lg">
                                <i class="ph-bold ph-download-simple text-lg"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
