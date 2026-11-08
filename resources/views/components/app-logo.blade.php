@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo" class="flex !h-10 !w-16 !min-w-0 items-center justify-center !overflow-visible">
            <x-app-logo-icon class="h-9 w-14" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <x-slot name="logo" class="flex !h-10 !w-16 !min-w-0 items-center justify-center !overflow-visible">
            <x-app-logo-icon class="h-9 w-14" />
        </x-slot>
    </flux:brand>
@endif
