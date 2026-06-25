<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Peralatan
        </h2>
    </x-slot>

    <livewire:master-data.peralatan-detail :peralatan="$peralatan" />
</x-app-layout>
