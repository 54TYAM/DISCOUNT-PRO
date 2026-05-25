@php $isManager = auth()->user()->isManager(); @endphp

@if ($isManager)
    <x-admin-layout title="Notifications">
        @include('notifications._content', ['notifications' => $notifications])
    </x-admin-layout>
@else
    <x-app-layout title="Notifications">
        @include('notifications._content', ['notifications' => $notifications])
    </x-app-layout>
@endif
