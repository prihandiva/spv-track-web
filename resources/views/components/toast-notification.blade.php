{{-- Reusable Top-Right Corner Toast Notification Component --}}
<div x-data="spvToastManager()"
     @show-toast.window="add($event.detail)"
     class="fixed top-[70px] sm:top-[80px] right-3 sm:right-6 z-[999999] flex flex-col gap-2.5 max-w-[calc(100vw-1.5rem)] w-[360px] sm:w-[390px] pointer-events-none select-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-[-10px] opacity-0 sm:translate-y-0 sm:translate-x-8 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 sm:translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 sm:translate-x-8 scale-95"
             @mouseenter="pauseToast(toast)"
             @mouseleave="resumeToast(toast)"
             class="pointer-events-auto relative overflow-hidden rounded-2xl shadow-2xl backdrop-blur-xl border p-3.5 sm:p-4 text-sm transition-all"
             :class="{
                 'bg-white/95 border-emerald-500/40 text-gray-800 shadow-emerald-950/15': toast.type === 'success',
                 'bg-white/95 border-rose-500/40 text-gray-800 shadow-rose-950/15': toast.type === 'error',
                 'bg-white/95 border-amber-500/40 text-gray-800 shadow-amber-950/15': toast.type === 'warning',
                 'bg-white/95 border-blue-500/40 text-gray-800 shadow-blue-950/15': toast.type === 'info'
             }">
            
            <div class="flex items-start gap-3">
                <!-- Icon Badge -->
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-sm mt-0.5"
                     :class="{
                         'bg-emerald-500 text-white shadow-emerald-500/30': toast.type === 'success',
                         'bg-rose-500 text-white shadow-rose-500/30': toast.type === 'error',
                         'bg-amber-500 text-white shadow-amber-500/30': toast.type === 'warning',
                         'bg-blue-600 text-white shadow-blue-500/30': toast.type === 'info'
                     }">
                    <template x-if="toast.type === 'success'">
                        <i class="ph-bold ph-check text-base"></i>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <i class="ph-bold ph-x text-base"></i>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <i class="ph-bold ph-warning text-base"></i>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <i class="ph-bold ph-info text-base"></i>
                    </template>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pr-1">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs font-bold leading-tight uppercase tracking-wider"
                            :class="{
                                'text-emerald-700': toast.type === 'success',
                                'text-rose-700': toast.type === 'error',
                                'text-amber-700': toast.type === 'warning',
                                'text-blue-700': toast.type === 'info'
                            }"
                            x-text="toast.title"></h4>
                        <span class="text-[10px] text-gray-400 font-medium" x-text="toast.timeAgo"></span>
                    </div>
                    <p class="text-xs text-gray-700 mt-1 leading-relaxed font-medium break-words" x-text="toast.message"></p>
                </div>

                <!-- Manual Close Button -->
                <button type="button"
                        @click="remove(toast.id)"
                        class="p-1 -mr-1 -mt-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors shrink-0 cursor-pointer"
                        title="Tutup Notifikasi">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>

            <!-- Auto-dismiss Progress Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100/80 overflow-hidden">
                <div class="h-full transition-all duration-75 ease-linear"
                     :class="{
                         'bg-emerald-500': toast.type === 'success',
                         'bg-rose-500': toast.type === 'error',
                         'bg-amber-500': toast.type === 'warning',
                         'bg-blue-600': toast.type === 'info'
                     }"
                     :style="`width: ${toast.progress}%;`"></div>
            </div>
        </div>
    </template>
</div>

<script>
    function spvToastManager() {
        return {
            toasts: [],
            init() {
                // Flash message handlers from Laravel Session
                @if(session('success'))
                    this.add({
                        type: 'success',
                        title: 'Berhasil',
                        message: {!! json_encode(session('success')) !!},
                        duration: 4000
                    });
                @endif

                @if(session('error'))
                    this.add({
                        type: 'error',
                        title: 'Gagal / Terjadi Kesalahan',
                        message: {!! json_encode(session('error')) !!},
                        duration: 4500
                    });
                @endif

                @if(session('warning'))
                    this.add({
                        type: 'warning',
                        title: 'Peringatan',
                        message: {!! json_encode(session('warning')) !!},
                        duration: 4000
                    });
                @endif

                @if(session('info'))
                    this.add({
                        type: 'info',
                        title: 'Informasi',
                        message: {!! json_encode(session('info')) !!},
                        duration: 4000
                    });
                @endif

                @if($errors->any())
                    this.add({
                        type: 'error',
                        title: 'Validasi Gagal',
                        message: {!! json_encode($errors->first()) !!},
                        duration: 4500
                    });
                @endif

                // Expose global window helper for AJAX / dynamic CRUD actions
                window.showToast = (message, type = 'success', title = null, duration = 4000) => {
                    if (typeof message === 'object' && message !== null) {
                        this.add(message);
                    } else {
                        const defaultTitles = {
                            success: 'Berhasil',
                            error: 'Gagal / Terjadi Kesalahan',
                            warning: 'Peringatan',
                            info: 'Informasi'
                        };
                        this.add({
                            type: type,
                            title: title || defaultTitles[type] || 'Notifikasi',
                            message: message,
                            duration: duration
                        });
                    }
                };
            },
            add(data) {
                const id = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
                const duration = data.duration || 4000;
                const toast = {
                    id: id,
                    type: data.type || 'success',
                    title: data.title || (data.type === 'error' ? 'Gagal' : 'Berhasil'),
                    message: data.message || '',
                    duration: duration,
                    remaining: duration,
                    progress: 100,
                    visible: true,
                    paused: false,
                    timeAgo: 'Baru saja',
                    interval: null
                };

                this.toasts.push(toast);

                const step = 50;
                toast.interval = setInterval(() => {
                    if (!toast.paused) {
                        toast.remaining -= step;
                        toast.progress = Math.max(0, (toast.remaining / toast.duration) * 100);
                        if (toast.remaining <= 0) {
                            clearInterval(toast.interval);
                            this.remove(toast.id);
                        }
                    }
                }, step);
            },
            pauseToast(toast) {
                toast.paused = true;
            },
            resumeToast(toast) {
                toast.paused = false;
            },
            remove(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    const toast = this.toasts[index];
                    if (toast.interval) clearInterval(toast.interval);
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 250);
                }
            }
        };
    }
</script>
