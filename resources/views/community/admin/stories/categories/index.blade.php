@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Story Categories')

@section('content')

<div class="ec-page">

    <div class="ec-header">
        <div>
            <h1 class="ec-title">Story Categories</h1>
            <p class="ec-subtitle">Manage categories used when submitting stories</p>
        </div>
        <button class="ec-btn ec-btn-primary" onclick="openCreateModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Category
        </button>
    </div>

    @if(session('success'))
        <div class="ec-alert ec-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ec-alert ec-alert-error">{{ session('error') }}</div>
    @endif

    <div class="ec-stats">
        <div class="ec-stat-card">
            <span class="ec-stat-value">{{ $stats['total'] }}</span>
            <span class="ec-stat-label">Total</span>
        </div>
        <div class="ec-stat-card ec-stat-active">
            <span class="ec-stat-value">{{ $stats['active'] }}</span>
            <span class="ec-stat-label">Active</span>
        </div>
        <div class="ec-stat-card ec-stat-inactive">
            <span class="ec-stat-value">{{ $stats['inactive'] }}</span>
            <span class="ec-stat-label">Inactive</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.story-categories.index') }}" class="ec-filters">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search categories..." class="ec-input ec-search">
        <select name="status" class="ec-input ec-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="ec-btn ec-btn-secondary">Search</button>
        @if(request('q') || request('status'))
            <a href="{{ route('admin.story-categories.index') }}" class="ec-btn ec-btn-ghost">Clear</a>
        @endif
    </form>

    <div class="ec-table-wrap">
        <table class="ec-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr id="row-{{ $category->id }}">
                        <td class="ec-td-num">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                        <td class="ec-td-name">{{ $category->name }}</td>
                        <td><code class="ec-slug">{{ $category->slug }}</code></td>
                        <td class="ec-td-desc">{{ $category->description ?? '—' }}</td>
                        <td>
                            <span class="ec-badge {{ $category->is_active ? 'ec-badge-active' : 'ec-badge-inactive' }}" id="badge-{{ $category->id }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="ec-td-date">{{ $category->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="ec-actions">
                                <button class="ec-icon-btn ec-icon-edit" title="Edit" onclick="openEditModal({{ $category->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a4 4 0 01-1.414.828l-3 1 1-3a4 4 0 01.828-1.414z"/></svg>
                                </button>
                                <button class="ec-icon-btn ec-icon-toggle" title="Toggle Status" onclick="toggleCategory({{ $category->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
                                </button>
                                <button class="ec-icon-btn ec-icon-delete" title="Delete" onclick="confirmDelete({{ $category->id }}, '{{ addslashes($category->name) }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001-1h4a1 1 0 001 1m-6 0h6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ec-empty">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="ec-pagination">{{ $categories->links() }}</div>
    @endif

</div>

{{-- CREATE MODAL --}}
<div class="ec-modal-overlay" id="createModal" onclick="closeModal('createModal')">
    <div class="ec-modal" onclick="event.stopPropagation()">
        <div class="ec-modal-header">
            <h2 class="ec-modal-title">New Story Category</h2>
            <button class="ec-modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form id="createForm">
            @csrf
            <div class="ec-modal-body">
                <div class="ec-form-group">
                    <label class="ec-label">Name <span class="ec-required">*</span></label>
                    <input type="text" name="name" id="create_name" class="ec-input" placeholder="e.g. Cultural Exchange" required>
                    <span class="ec-field-error" id="create_name_error"></span>
                </div>
                <div class="ec-form-group">
                    <label class="ec-label">Description</label>
                    <textarea name="description" id="create_description" class="ec-input ec-textarea" placeholder="Optional description..."></textarea>
                    <span class="ec-field-error" id="create_description_error"></span>
                </div>
                <div class="ec-form-group ec-form-check">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked>
                    <label for="create_is_active">Active</label>
                </div>
            </div>
            <div class="ec-modal-footer">
                <button type="button" class="ec-btn ec-btn-ghost" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="ec-btn ec-btn-primary" id="createSubmitBtn">Create Category</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="ec-modal-overlay" id="editModal" onclick="closeModal('editModal')">
    <div class="ec-modal" onclick="event.stopPropagation()">
        <div class="ec-modal-header">
            <h2 class="ec-modal-title">Edit Story Category</h2>
            <button class="ec-modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm">
            @csrf
            @method('PUT')
            <div class="ec-modal-body">
                <div class="ec-form-group">
                    <label class="ec-label">Name <span class="ec-required">*</span></label>
                    <input type="text" name="name" id="edit_name" class="ec-input" required>
                    <span class="ec-field-error" id="edit_name_error"></span>
                </div>
                <div class="ec-form-group">
                    <label class="ec-label">Description</label>
                    <textarea name="description" id="edit_description" class="ec-input ec-textarea"></textarea>
                    <span class="ec-field-error" id="edit_description_error"></span>
                </div>
                <div class="ec-form-group ec-form-check">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <label for="edit_is_active">Active</label>
                </div>
            </div>
            <div class="ec-modal-footer">
                <button type="button" class="ec-btn ec-btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="ec-btn ec-btn-primary" id="editSubmitBtn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="ec-modal-overlay" id="deleteModal" onclick="closeModal('deleteModal')">
    <div class="ec-modal ec-modal-sm" onclick="event.stopPropagation()">
        <div class="ec-modal-header">
            <h2 class="ec-modal-title">Delete Category</h2>
            <button class="ec-modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="ec-modal-body">
            <p class="ec-confirm-text">Are you sure you want to delete <strong id="deleteModalName"></strong>? This cannot be undone.</p>
        </div>
        <div class="ec-modal-footer">
            <button type="button" class="ec-btn ec-btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="button" class="ec-btn ec-btn-danger" id="deleteConfirmBtn">Delete</button>
        </div>
    </div>
</div>

<div class="ec-toast" id="ecToast"></div>

@push('styles')
<style>
.ec-page { padding: 2rem; max-width: 1200px; margin: 0 auto; }
.ec-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
.ec-title { font-size: 1.5rem; font-weight: 700; color: #111; margin: 0 0 .25rem; }
.ec-subtitle { font-size: .875rem; color: #6b7280; margin: 0; }
.ec-alert { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .875rem; }
.ec-alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.ec-alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.ec-stats { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.ec-stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: .875rem 1.25rem; display: flex; flex-direction: column; gap: .2rem; min-width: 100px; }
.ec-stat-value { font-size: 1.5rem; font-weight: 700; color: #111; }
.ec-stat-label { font-size: .75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.ec-stat-active .ec-stat-value  { color: #059669; }
.ec-stat-inactive .ec-stat-value { color: #dc2626; }
.ec-filters { display: flex; gap: .5rem; margin-bottom: 1rem; flex-wrap: wrap; }
.ec-search { flex: 1; min-width: 180px; }
.ec-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem 1rem; border-radius: 8px; font-size: .875rem; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: background .15s; }
.ec-btn-primary { background: #e8640c; color: #fff; }
.ec-btn-primary:hover { background: #d4570a; }
.ec-btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
.ec-btn-secondary:hover { background: #e5e7eb; }
.ec-btn-ghost { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.ec-btn-ghost:hover { background: #f9fafb; }
.ec-btn-danger { background: #dc2626; color: #fff; }
.ec-btn-danger:hover { background: #b91c1c; }
.ec-btn:disabled { opacity: .6; cursor: not-allowed; }
.ec-input { width: 100%; padding: .5rem .75rem; border: 1px solid #e5e7eb; border-radius: 8px; font-size: .875rem; color: #111; background: #fff; box-sizing: border-box; outline: none; transition: border-color .15s; }
.ec-input:focus { border-color: #e8640c; }
.ec-select { width: auto; min-width: 130px; cursor: pointer; }
.ec-textarea { resize: vertical; min-height: 80px; }
.ec-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.ec-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.ec-table thead { background: #f9fafb; }
.ec-table th { padding: .75rem 1rem; text-align: left; font-weight: 600; color: #374151; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
.ec-table td { padding: .75rem 1rem; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.ec-table tbody tr:last-child td { border-bottom: none; }
.ec-table tbody tr:hover { background: #f9fafb; }
.ec-td-num  { color: #9ca3af; width: 40px; }
.ec-td-name { font-weight: 600; color: #111; }
.ec-td-desc { color: #6b7280; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ec-td-date { white-space: nowrap; color: #9ca3af; font-size: .8rem; }
.ec-slug { background: #f3f4f6; padding: .15rem .4rem; border-radius: 4px; font-size: .8rem; color: #6b7280; font-family: monospace; }
.ec-empty { text-align: center; color: #9ca3af; padding: 2.5rem 1rem !important; }
.ec-badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
.ec-badge-active   { background: #ecfdf5; color: #065f46; }
.ec-badge-inactive { background: #fef2f2; color: #991b1b; }
.ec-actions { display: flex; gap: .25rem; }
.ec-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; transition: background .15s; color: #6b7280; }
.ec-icon-btn:hover { background: #f3f4f6; }
.ec-icon-delete:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
.ec-icon-edit:hover   { background: #fff7ed; border-color: #fed7aa; color: #e8640c; }
.ec-icon-toggle:hover { background: #f0fdf4; border-color: #bbf7d0; color: #059669; }
.ec-pagination { margin-top: 1rem; }
.ec-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center; }
.ec-modal-overlay.open { display: flex; }
.ec-modal { background: #fff; border-radius: 14px; width: 100%; max-width: 480px; margin: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.ec-modal-sm { max-width: 380px; }
.ec-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f3f4f6; }
.ec-modal-title { font-size: 1rem; font-weight: 700; color: #111; margin: 0; }
.ec-modal-close { background: none; border: none; font-size: 1.4rem; color: #9ca3af; cursor: pointer; line-height: 1; padding: 0; }
.ec-modal-close:hover { color: #374151; }
.ec-modal-body { padding: 1.25rem 1.5rem; }
.ec-modal-footer { display: flex; justify-content: flex-end; gap: .5rem; padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; }
.ec-form-group { margin-bottom: 1rem; }
.ec-label { display: block; font-size: .8rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }
.ec-required { color: #dc2626; }
.ec-field-error { display: block; font-size: .75rem; color: #dc2626; margin-top: .25rem; min-height: 1rem; }
.ec-form-check { display: flex; align-items: center; gap: .5rem; }
.ec-form-check input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
.ec-form-check label { font-size: .875rem; color: #374151; cursor: pointer; margin: 0; }
.ec-confirm-text { font-size: .9rem; color: #374151; margin: 0; line-height: 1.6; }
.ec-toast { position: fixed; bottom: 1.5rem; right: 1.5rem; background: #111; color: #fff; padding: .75rem 1.25rem; border-radius: 8px; font-size: .875rem; z-index: 2000; opacity: 0; transform: translateY(8px); transition: opacity .25s, transform .25s; pointer-events: none; }
.ec-toast.show { opacity: 1; transform: translateY(0); }
.ec-toast.ec-toast-error { background: #dc2626; }
</style>
@endpush

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/admin/story-categories") }}';

function showToast(msg, type = 'success') {
    const t = document.getElementById('ecToast');
    t.textContent = msg;
    t.className   = 'ec-toast' + (type === 'error' ? ' ec-toast-error' : '');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.ec-modal-overlay.open').forEach(m => m.classList.remove('open'));
});

function clearErrors(prefix) {
    document.querySelectorAll(`[id^="${prefix}_"][id$="_error"]`).forEach(el => el.textContent = '');
}
function showErrors(prefix, errors) {
    Object.entries(errors).forEach(([field, msgs]) => {
        const el = document.getElementById(`${prefix}_${field}_error`);
        if (el) el.textContent = msgs[0];
    });
}

function openCreateModal() {
    document.getElementById('createForm').reset();
    document.getElementById('create_is_active').checked = true;
    clearErrors('create');
    openModal('createModal');
}

document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrors('create');
    const btn = document.getElementById('createSubmitBtn');
    btn.disabled = true; btn.textContent = 'Creating...';
    const data = new FormData(this);
    if (!document.getElementById('create_is_active').checked) data.delete('is_active');
    try {
        const res  = await fetch(BASE, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: data });
        const json = await res.json();
        if (res.ok && json.success) { showToast(json.message); closeModal('createModal'); setTimeout(() => location.reload(), 800); }
        else { if (json.errors) showErrors('create', json.errors); else showToast(json.message || 'Error.', 'error'); }
    } catch { showToast('Request failed.', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Create Category'; }
});

let editingId = null;
async function openEditModal(id) {
    clearErrors('edit'); editingId = id;
    try {
        const res  = await fetch(`${BASE}/${id}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
        const data = await res.json();
        document.getElementById('edit_name').value        = data.name        ?? '';
        document.getElementById('edit_description').value = data.description ?? '';
        document.getElementById('edit_is_active').checked = !!data.is_active;
        openModal('editModal');
    } catch { showToast('Failed to load.', 'error'); }
}

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!editingId) return;
    clearErrors('edit');
    const btn = document.getElementById('editSubmitBtn');
    btn.disabled = true; btn.textContent = 'Saving...';
    const data = new FormData(this);
    data.append('_method', 'PUT');
    if (!document.getElementById('edit_is_active').checked) data.delete('is_active');
    try {
        const res  = await fetch(`${BASE}/${editingId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: data });
        const json = await res.json();
        if (res.ok && json.success) { showToast(json.message); closeModal('editModal'); setTimeout(() => location.reload(), 800); }
        else { if (json.errors) showErrors('edit', json.errors); else showToast(json.message || 'Error.', 'error'); }
    } catch { showToast('Request failed.', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Save Changes'; }
});

async function toggleCategory(id) {
    try {
        const res  = await fetch(`${BASE}/${id}/toggle`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const json = await res.json();
        if (res.ok && json.success) {
            showToast(json.message);
            const badge = document.getElementById(`badge-${id}`);
            if (badge) { badge.textContent = json.is_active ? 'Active' : 'Inactive'; badge.className = 'ec-badge ' + (json.is_active ? 'ec-badge-active' : 'ec-badge-inactive'); }
        } else showToast(json.message || 'Error.', 'error');
    } catch { showToast('Request failed.', 'error'); }
}

let deletingId = null;
function confirmDelete(id, name) {
    deletingId = id;
    document.getElementById('deleteModalName').textContent = name;
    openModal('deleteModal');
}

document.getElementById('deleteConfirmBtn').addEventListener('click', async function() {
    if (!deletingId) return;
    this.disabled = true; this.textContent = 'Deleting...';
    try {
        const res  = await fetch(`${BASE}/${deletingId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const json = await res.json();
        if (res.ok && json.success) {
            showToast(json.message); closeModal('deleteModal');
            const row = document.getElementById(`row-${deletingId}`);
            if (row) row.remove();
        } else showToast(json.message || 'Error.', 'error');
    } catch { showToast('Request failed.', 'error'); }
    finally { this.disabled = false; this.textContent = 'Delete'; deletingId = null; }
});
</script>
@endpush

@endsection
