@extends('layouts.community')

@section('title', 'Pending Jobs')
@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/jobs/pending-job.css') }}">
<style>
.modal-backdrop[hidden] { display:none !important; }

.pj-toast-container {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px; pointer-events: none;
}
.pj-toast {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 18px; border-radius: 12px; font-size: 14px; font-weight: 500;
    color: #fff; box-shadow: 0 8px 30px rgba(0,0,0,.15);
    pointer-events: auto; opacity: 0; transform: translateY(8px);
    transition: opacity .25s, transform .25s; min-width: 260px; max-width: 380px;
}
.pj-toast--show    { opacity: 1; transform: translateY(0); }
.pj-toast--success { background: #059669; }
.pj-toast--error   { background: #dc2626; }

.pj-confirm-icon {
    width: 56px; height: 56px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
}
.pj-confirm-icon--approve { background:#ecfdf5; border:1.5px solid #a7f3d0; color:#059669; }
.dark .pj-confirm-icon--approve { background:#052e16; border-color:#166534; color:#4ade80; }
.pj-confirm-text { text-align:center; font-size:14px; color:#374151; line-height:1.6; margin:0; }
.dark .pj-confirm-text { color:#94a3b8; }

@keyframes pj-spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Job Moderation</p>
            <h1 class="admin-page__title">Moderation Queue</h1>
            <p class="admin-page__subtitle">Review pending job posts, approve valid listings, and reject unwanted submissions.</p>
        </div>
        <div class="admin-page__actions">
            <a href="{{ route('admin.jobs.index') }}" class="admin-btn admin-btn--ghost">All Jobs</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-card__label">Pending</div><div class="stat-card__value">{{ $stats['pending'] ?? 0 }}</div></div>
        <div class="stat-card"><div class="stat-card__label">Published</div><div class="stat-card__value">{{ $stats['published'] ?? 0 }}</div></div>
        <div class="stat-card"><div class="stat-card__label">Rejected</div><div class="stat-card__value">{{ $stats['rejected'] ?? 0 }}</div></div>
        <div class="stat-card"><div class="stat-card__label">Total</div><div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div></div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Pending Job Posts</h2>
                <p class="panel__subtitle">Search by title, company, location, or creator.</p>
            </div>
            <form method="GET" action="{{ route('admin.jobs.pending') }}" class="search-form">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search pending jobs…" class="search-form__input">
                <button type="submit" class="admin-btn admin-btn--primary">Search</button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.jobs.pending') }}" class="admin-btn admin-btn--ghost">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Job</th><th>Company</th><th>Location</th><th>Type</th>
                        <th>Creator</th><th>Deadline</th><th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                    <tr id="job-row-{{ $job->id }}">
                        <td>
                            <div class="job-cell">
                                <div class="job-cell__title">{{ $job->title }}</div>
                                <div class="job-cell__meta">Posted {{ optional($job->created_at)->format('d M Y') }}</div>
                            </div>
                        </td>
                        <td>{{ $job->company_name ?? '-' }}</td>
                        <td>{{ $job->location ?? '-' }}</td>
                        <td>{{ $job->job_type ?? '-' }}</td>
                        <td>
                            <div class="creator-cell">
                                <div class="creator-cell__name">{{ optional($job->creator)->full_name ?? 'Unknown' }}</div>
                                <div class="creator-cell__meta">{{ optional($job->creator)->email ?? '' }}</div>
                            </div>
                        </td>
                        <td>{{ $job->application_deadline ? \Carbon\Carbon::parse($job->application_deadline)->format('d M Y') : '-' }}</td>
                        <td><span class="badge badge--pending">Pending</span></td>
                        <td class="text-right">
                            <div class="action-group">
                                <button type="button" class="action-btn action-btn--approve"
                                    data-open-approve
                                    data-job-id="{{ $job->id }}"
                                    data-job-title="{{ $job->title }}"
                                    data-approve-url="{{ route('admin.jobs.approve', $job->id) }}">
                                    Approve
                                </button>
                                <button type="button" class="action-btn action-btn--reject"
                                    data-open-reject
                                    data-job-id="{{ $job->id }}"
                                    data-job-title="{{ $job->title }}"
                                    data-reject-url="{{ route('admin.jobs.reject', $job->id) }}">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state__icon">⏳</div>
                            <div class="empty-state__title">No pending jobs right now</div>
                            <div class="empty-state__text">New submissions will appear here for moderation.</div>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $jobs->links() }}</div>
    </div>
</div>

{{-- APPROVE CONFIRM MODAL --}}
<div class="modal-backdrop" id="approveModal" hidden>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="approveModalTitle" style="max-width:440px;">
        <div class="modal-card__head">
            <div>
                <h3 class="modal-card__title" id="approveModalTitle">Approve Job</h3>
                <p class="modal-card__subtitle">This will publish the listing immediately.</p>
            </div>
            <button type="button" class="modal-close" id="closeApproveModal" aria-label="Close">×</button>
        </div>
        <div class="modal-card__body" style="text-align:center;padding:28px 24px;">
            <div class="pj-confirm-icon pj-confirm-icon--approve">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p class="pj-confirm-text">
                Approve <strong id="approveJobTitle"></strong>?<br>
                It will be <strong>published</strong> and visible to all alumni.
            </p>
        </div>
        <div class="modal-card__footer">
            <button type="button" class="admin-btn admin-btn--ghost" id="cancelApproveBtn">Cancel</button>
            <button type="button" class="admin-btn admin-btn--primary" id="confirmApproveBtn">✓&nbsp; Yes, Approve</button>
        </div>
    </div>
</div>

{{-- REJECT MODAL --}}
<div class="modal-backdrop" id="rejectModal" hidden>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle">
        <div class="modal-card__head">
            <div>
                <h3 class="modal-card__title" id="rejectModalTitle">Reject Job</h3>
                <p class="modal-card__subtitle">Provide an optional reason for the creator.</p>
            </div>
            <button type="button" class="modal-close" id="closeRejectModal" aria-label="Close">×</button>
        </div>
        <div class="modal-card__body">
            <p class="modal-job-title" id="rejectJobTitle"></p>
            <label for="rejectReason" class="field-label">Reason <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
            <textarea id="rejectReason" class="field-textarea" rows="4" placeholder="Tell the creator why this was rejected…"></textarea>
        </div>
        <div class="modal-card__footer">
            <button type="button" class="admin-btn admin-btn--ghost" id="cancelRejectBtn">Cancel</button>
            <button type="button" class="admin-btn admin-btn--danger" id="confirmRejectBtn">Reject Job</button>
        </div>
    </div>
</div>

<div class="pj-toast-container" id="pjToastContainer" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const $    = id => document.getElementById(id);
    const spin = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:pj-spin .8s linear infinite;vertical-align:middle;margin-right:4px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>`;

    function toast(msg, type = 'success') {
        const t = document.createElement('div');
        t.className = `pj-toast pj-toast--${type}`;
        t.setAttribute('role', 'alert');
        t.textContent = msg;
        $('pjToastContainer').appendChild(t);
        requestAnimationFrame(() => t.classList.add('pj-toast--show'));
        setTimeout(() => { t.classList.remove('pj-toast--show'); t.addEventListener('transitionend', () => t.remove(), {once:true}); }, 4500);
    }

    @if(session('success')) toast(@json(session('success')), 'success'); @endif
    @if(session('error'))   toast(@json(session('error')),   'error');   @endif

    let lastFocus;
    function openModal(m) { lastFocus=document.activeElement; m.removeAttribute('hidden'); document.body.style.overflow='hidden'; setTimeout(()=>m.querySelector('button,textarea')?.focus(),50); }
    function closeModal(m){ m.setAttribute('hidden',''); document.body.style.overflow=''; lastFocus?.focus(); }

    document.querySelectorAll('.modal-backdrop').forEach(b => b.addEventListener('click', e => { if(e.target===b) closeModal(b); }));
    document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal-backdrop:not([hidden])').forEach(closeModal); });

    function removeRow(id) {
        const row = document.getElementById(`job-row-${id}`);
        if (!row) return;
        row.style.transition = 'opacity .3s, transform .3s';
        row.style.opacity    = '0';
        row.style.transform  = 'translateX(20px)';
        setTimeout(() => row.remove(), 320);
    }

    // ── APPROVE ──────────────────────────────────────────────────────
    const approveModal = $('approveModal');
    let pa = {};

    document.querySelectorAll('[data-open-approve]').forEach(btn => {
        btn.addEventListener('click', () => {
            pa = { id: btn.dataset.jobId, url: btn.dataset.approveUrl };
            $('approveJobTitle').textContent = btn.dataset.jobTitle;
            openModal(approveModal);
        });
    });

    $('closeApproveModal').addEventListener('click', () => closeModal(approveModal));
    $('cancelApproveBtn').addEventListener('click',  () => closeModal(approveModal));

    $('confirmApproveBtn').addEventListener('click', async () => {
        const btn = $('confirmApproveBtn'), orig = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = spin + 'Approving…';
        try {
            const res  = await fetch(pa.url, { method:'PATCH', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'} });
            const data = await res.json();
            if (!res.ok) { toast(data.message ?? 'Could not approve.', 'error'); }
            else { closeModal(approveModal); removeRow(pa.id); toast(data.message ?? 'Job approved and published! ✓', 'success'); }
        } catch { toast('Network error — please try again.', 'error'); }
        finally { btn.disabled=false; btn.innerHTML=orig; }
    });

    // ── REJECT ───────────────────────────────────────────────────────
    const rejectModal = $('rejectModal');
    let pr = {};

    document.querySelectorAll('[data-open-reject]').forEach(btn => {
        btn.addEventListener('click', () => {
            pr = { id: btn.dataset.jobId, url: btn.dataset.rejectUrl };
            $('rejectJobTitle').textContent = 'Reject: ' + btn.dataset.jobTitle;
            $('rejectReason').value = '';
            openModal(rejectModal);
        });
    });

    $('closeRejectModal').addEventListener('click', () => closeModal(rejectModal));
    $('cancelRejectBtn').addEventListener('click',  () => closeModal(rejectModal));

    $('confirmRejectBtn').addEventListener('click', async () => {
        const btn = $('confirmRejectBtn'), orig = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = spin + 'Rejecting…';
        try {
            const res  = await fetch(pr.url, { method:'PATCH', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}, body:JSON.stringify({reason:$('rejectReason').value.trim()||null}) });
            const data = await res.json();
            if (!res.ok) { toast(data.message ?? 'Could not reject.', 'error'); }
            else { closeModal(rejectModal); removeRow(pr.id); toast(data.message ?? 'Job rejected.', 'success'); }
        } catch { toast('Network error — please try again.', 'error'); }
        finally { btn.disabled=false; btn.innerHTML=orig; }
    });
})();
</script>
@endpush
