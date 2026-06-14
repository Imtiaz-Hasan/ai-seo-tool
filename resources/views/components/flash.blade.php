@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)" x-transition
         class="fixed bottom-5 right-5 z-50 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-3 text-sm font-medium shadow-lg">
        {{ session('status') }}
    </div>
@endif
