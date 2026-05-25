<x-admin-layout title="Manager Approvals">

<x-page-header
    title="Manager Approvals"
    subtitle="Review store-manager applications. Approved managers get an auto-created store.">
    <x-slot:actions>
        <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30">{{ $approvedCount }} approved</span>
        <span class="badge bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30">{{ $pending->total() }} pending</span>
    </x-slot:actions>
</x-page-header>

@if (session('error'))
    <div class="mb-4 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">{{ session('error') }}</div>
@endif

@if ($pending->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-emerald-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-slate-600 text-sm font-medium">No pending applications</p>
        <p class="text-slate-400 text-xs mt-1">New store-manager registrations will appear here for review.</p>
    </div>
@else

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Applicant</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Requested store</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Category</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Applied</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            @foreach ($pending as $applicant)
                <tr class="hover:bg-stone-50/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-semibold text-sm">
                                {{ strtoupper(substr($applicant->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ $applicant->name }}</p>
                                <p class="text-xs text-slate-400">{{ $applicant->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $applicant->requested_store_name ?: '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($applicant->requested_store_category)
                            <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ $applicant->requested_store_category }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-400">
                        {{ $applicant->created_at?->diffForHumans() }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center gap-2 justify-end">
                            <form method="POST" action="{{ route('admin.approvals.approve', (string) $applicant->_id) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.approvals.reject', (string) $applicant->_id) }}"
                                  onsubmit="return confirm('Reject this application? The user record will be deleted.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-white text-rose-600 border border-rose-200 rounded-lg hover:bg-rose-50 transition-colors
                                               dark:bg-slate-800 dark:text-rose-400 dark:border-rose-500/30 dark:hover:bg-rose-500/10">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $pending->links() }}</div>

@endif

</x-admin-layout>
