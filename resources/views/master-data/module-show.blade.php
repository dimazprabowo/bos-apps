<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Modul
        </h2>
    </x-slot>

    <livewire:master-data.module-detail :module="$module" />
</x-app-layout>
