@props([
    'show' => false,
    'title' => '',
    'description' => '',
    'type' => 'warning',
    'buttonColor' => null,
    'icon' => null,
    'closeMethod' => '',
    'showVar' => '',
    'actionMethod' => '',
    'actionText' => 'Konfirmasi',
    'textarea' => false,
    'textareaModel' => '',
    'textareaLabel' => '',
    'textareaRequired' => false,
    'textareaRows' => 4,
    'textareaPlaceholder' => '',
])

@php
    $themes = [
        'warning' => [
            'iconBg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'iconColor' => 'text-yellow-600 dark:text-yellow-400',
            'focusRing' => 'focus:ring-yellow-500',
            'buttonBg' => 'bg-yellow-600 hover:bg-yellow-700',
        ],
        'danger' => [
            'iconBg' => 'bg-red-100 dark:bg-red-900/30',
            'iconColor' => 'text-red-600 dark:text-red-400',
            'focusRing' => 'focus:ring-red-500',
            'buttonBg' => 'bg-red-600 hover:bg-red-700',
        ],
        'success' => [
            'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
            'focusRing' => 'focus:ring-emerald-500',
            'buttonBg' => 'bg-emerald-600 hover:bg-emerald-700',
        ],
    ];

    $buttonColors = [
        'green' => 'bg-green-600 hover:bg-green-700',
        'red' => 'bg-red-600 hover:bg-red-700',
        'yellow' => 'bg-yellow-600 hover:bg-yellow-700',
        'emerald' => 'bg-emerald-600 hover:bg-emerald-700',
    ];

    $theme = $themes[$type] ?? $themes['warning'];
    $buttonBg = $buttonColor ? ($buttonColors[$buttonColor] ?? $theme['buttonBg']) : $theme['buttonBg'];

    if (! $icon) {
        $icon = $type === 'success' ? 'check' : 'warning';
    }
@endphp

@if($show)
    <div class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80" @click="$wire.set('{{ $showVar }}', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md z-10 p-6 text-center">
                <div class="w-12 h-12 rounded-full {{ $theme['iconBg'] }} flex items-center justify-center mx-auto mb-4">
                    @if($icon === 'check')
                        <svg class="w-6 h-6 {{ $theme['iconColor'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($icon === 'x')
                        <svg class="w-6 h-6 {{ $theme['iconColor'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 {{ $theme['iconColor'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    @endif
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">{{ $title }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{!! $description !!}</p>
                @if($textarea)
                    <div class="mb-6 text-left">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ $textareaLabel }}
                            @if($textareaRequired)
                                <span class="text-red-500">*</span>
                            @else
                                <span class="text-gray-400 text-xs">(opsional)</span>
                            @endif
                        </label>
                        <textarea
                            wire:model="{{ $textareaModel }}"
                            rows="{{ $textareaRows }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 {{ $theme['focusRing'] }} dark:bg-gray-700 dark:text-white"
                            placeholder="{{ $textareaPlaceholder }}"></textarea>
                        @error($textareaModel)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                <div class="flex items-center justify-center gap-3">
                    <x-cancel-button wire:click="{{ $closeMethod }}" target="{{ $closeMethod }}" variant="secondary" />
                    <button wire:click="{{ $actionMethod }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 {{ $buttonBg }} text-white text-sm font-semibold rounded-xl shadow-sm transition-all"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                        wire:target="{{ $actionMethod }}">
                        <svg wire:loading wire:target="{{ $actionMethod }}" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ $actionText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
