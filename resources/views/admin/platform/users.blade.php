<x-admin-layout title="All Users">

<x-page-header
    title="All Users"
    :subtitle="$counts['all'] . ' accounts on the platform'" />

{{-- Role tabs --}}
@php
    $tabs = ['all' => 'All', 'customer' => 'Customers', 'manager' => 'Managers', 'admin' => 'Admins'];
    $current = request('role', 'all');
@endphp
<div class="flex items-center gap-1 mb-5 border-b border-slate-200 overflow-x-auto">
    @foreach ($tabs as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['role' => $key === 'all' ? null : (['customer' => 'customer', 'manager' => 'store_manager', 'admin' => 'super_admin'][$key] ?? $key), 'page' => null]) }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap
                  {{ $current === ($key === 'all' ? 'all' : (['customer' => 'customer', 'manager' => 'store_manager', 'admin' => 'super_admin'][$key] ?? $key)) ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            {{ $label }}
            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">
                {{ $counts[$key] }}
            </span>
        </a>
    @endforeach
    @if ($counts['pending'] > 0)
        <a href="{{ route('admin.approvals.index') }}"
           class="ml-auto px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-amber-600 hover:text-amber-700 whitespace-nowrap">
            ⚠ {{ $counts['pending'] }} pending approval
        </a>
    @endif
</div>

<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    @if (request('role')) <input type="hidden" name="role" value="{{ request('role') }}"> @endif
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email…" class="form-input pl-9">
    </div>
    <button type="submit" class="btn-primary">Search</button>
</form>

@if ($users->isEmpty())
    <div class="text-center py-20 card">
        <p class="text-slate-400 text-sm">No users match those filters.</p>
    </div>
@else

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-right">Joined</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($users as $u)
                @php
                    $roleBadge = match($u->role) {
                        'super_admin'    => ['bg' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',     'label' => 'Super Admin',  'avatar' => 'bg-gradient-rose'],
                        'store_manager'  => ['bg' => 'bg-brand-50 text-brand-700 ring-1 ring-brand-200',  'label' => 'Manager',      'avatar' => 'bg-gradient-brand'],
                        'customer'       => ['bg' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',       'label' => 'Customer',      'avatar' => 'bg-gradient-sky'],
                        default          => ['bg' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200','label' => 'Unknown',       'avatar' => 'bg-slate-400'],
                    };
                    $approved = $u->isApproved();
                @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $roleBadge['avatar'] }} rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 shadow-card">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 truncate">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $roleBadge['bg'] }}">{{ $roleBadge['label'] }}</span>
                    </td>
                    <td>
                        @if (! $approved)
                            <span class="badge bg-amber-50 text-amber-700 ring-1 ring-amber-200 flex items-center gap-1 w-fit">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Pending
                            </span>
                        @elseif ($u->email_verified_at)
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge bg-slate-100 text-slate-500 ring-1 ring-slate-200">Unverified</span>
                        @endif
                    </td>
                    <td class="text-right text-xs text-slate-400 whitespace-nowrap">
                        {{ $u->created_at?->format('M j, Y') ?? '—' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $users->links() }}</div>

@endif

</x-admin-layout>
