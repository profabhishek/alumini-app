@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Newsletter Subscribers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/admin-newsletter.css') }}">
@endpush

@section('content')
<div class="nlsub-page">

    <div class="nlsub-header">
        <div>
            <h1 class="nlsub-title">Newsletter Subscribers</h1>
            <p class="nlsub-sub">Manage everyone subscribed to the ICCR Alumni newsletter.</p>
        </div>
        <div class="nlsub-header-actions">
            <a href="{{ route('admin.newsletter.export') }}" class="nlsub-btn nlsub-btn--ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
            <button type="button" class="nlsub-btn nlsub-btn--primary" id="addSubscriberBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Subscriber
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="nlsub-stats">
        <div class="nlsub-stat-card">
            <span class="nlsub-stat-value">{{ $stats['total'] }}</span>
            <span class="nlsub-stat-label">Total</span>
        </div>
        <div class="nlsub-stat-card nlsub-stat-card--active">
            <span class="nlsub-stat-value">{{ $stats['active'] }}</span>
            <span class="nlsub-stat-label">Active</span>
        </div>
        <div class="nlsub-stat-card nlsub-stat-card--unsub">
            <span class="nlsub-stat-value">{{ $stats['unsubscribed'] }}</span>
            <span class="nlsub-stat-label">Unsubscribed</span>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.newsletter.index') }}" class="nlsub-filters">
        <div class="nlsub-search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by email..." class="nlsub-search">
        </div>

        <select name="status" class="nlsub-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
        </select>

        <button type="submit" class="nlsub-btn nlsub-btn--ghost">Search</button>

        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('admin.newsletter.index') }}" class="nlsub-btn nlsub-btn--ghost">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="nlsub-table-wrap">
        <table class="nlsub-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Subscribed</th>
                    <th>Unsubscribed</th>
                    <th class="nlsub-th-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="nlsubTableBody">
                @forelse($subscribers as $sub)
                    <tr data-id="{{ $sub->id }}">
                        <td class="nlsub-email">{{ $sub->email }}</td>
                        <td>
                            <span class="nlsub-badge nlsub-badge--{{ $sub->status }}" data-status-badge>
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td>{{ $sub->subscribed_at?->format('d M Y, g:i A') ?? '—' }}</td>
                        <td>{{ $sub->unsubscribed_at?->format('d M Y, g:i A') ?? '—' }}</td>
                        <td class="nlsub-actions">
                            <button type="button"
                                    class="nlsub-icon-btn nlsub-toggle-btn"
                                    data-id="{{ $sub->id }}"
                                    data-status="{{ $sub->status }}"
                                    title="{{ $sub->status === 'active' ? 'Unsubscribe' : 'Resubscribe' }}">
                                @if($sub->status === 'active')
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                @else
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                @endif
                            </button>
                            <button type="button"
                                    class="nlsub-icon-btn nlsub-icon-btn--danger nlsub-delete-btn"
                                    data-id="{{ $sub->id }}"
                                    data-email="{{ $sub->email }}"
                                    title="Delete">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr id="nlsubEmptyRow">
                        <td colspan="5" class="nlsub-empty">
                            @if(request()->hasAny(['q','status']))
                                No subscribers match your search.
                            @else
                                No subscribers yet.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($subscribers->hasPages())
        <div class="nlsub-pagination">
            {{ $subscribers->links() }}
        </div>
    @endif

</div>

{{-- Add Subscriber Modal --}}
<div class="nlsub-modal-overlay" id="addSubscriberModal" hidden>
    <div class="nlsub-modal">
        <div class="nlsub-modal-header">
            <h3>Add Subscriber</h3>
            <button type="button" class="nlsub-modal-close" id="closeAddModal" aria-label="Close">&times;</button>
        </div>
        <form id="addSubscriberForm">
            @csrf
            <div class="nlsub-field">
                <label for="newSubEmail">Email Address</label>
                <input type="email" id="newSubEmail" name="email" required placeholder="alumni@example.com">
                <span class="nlsub-error" id="newSubEmailError"></span>
            </div>
            <div class="nlsub-modal-footer">
                <button type="button" class="nlsub-btn nlsub-btn--ghost" id="cancelAddModal">Cancel</button>
                <button type="submit" class="nlsub-btn nlsub-btn--primary" id="addSubscriberSubmit">Add Subscriber</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.NLSUB_STORE_URL = '{{ route('admin.newsletter.store') }}';
    window.NLSUB_TOGGLE_URL_TEMPLATE = '{{ route('admin.newsletter.toggle-status', '__ID__') }}';
    window.NLSUB_DELETE_URL_TEMPLATE = '{{ route('admin.newsletter.destroy', '__ID__') }}';
</script>
<script src="{{ asset('js/community/admin-newsletter.js') }}"></script>
@endpush