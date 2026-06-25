@extends('layouts.community')
@section('title', 'Mentor Categories')
@section('hideRightSidebar', true)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/community/mentors.css') }}">
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Mentor Categories</h1>
            <p class="admin-page-subtitle">Manage the fields of expertise available for mentors.</p>
        </div>
        <a href="{{ route('admin.mentors.requests') }}" class="mtr-btn mtr-btn--outline">← Mentor Requests</a>
    </div>

    @foreach(['success','error','info'] as $k)
        @if(session($k))
            <div class="admin-alert admin-alert--{{ $k }}">{{ session($k) }}</div>
        @endif
    @endforeach

    {{-- Create form --}}
    <div style="background:#fff;border:1.5px solid #edf2f7;border-radius:14px;padding:22px 24px;margin-bottom:24px;">
        <h3 style="font-size:14px;font-weight:800;color:#1c2331;margin:0 0 16px;">Add New Category</h3>
        <form method="POST" action="{{ route('admin.mentor-categories.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#4a5568;margin-bottom:5px;">Category Name *</label>
                    <input type="text" name="name" required maxlength="80" placeholder="e.g. Kathak"
                           value="{{ old('name') }}"
                           style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:13.5px;outline:none;box-sizing:border-box;">
                    @error('name') <small style="color:#e53e3e;font-size:11.5px;">{{ $message }}</small> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#4a5568;margin-bottom:5px;">Description</label>
                    <input type="text" name="description" maxlength="250" placeholder="Brief description of what mentors in this category offer…"
                           value="{{ old('description') }}"
                           style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:13.5px;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;white-space:nowrap;">Card Color</label>
                    <input type="color" name="color" value="{{ old('color', '#e8640c') }}"
                           style="width:46px;height:36px;border:1.5px solid #e2e8f0;border-radius:8px;cursor:pointer;padding:2px;">
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;white-space:nowrap;">Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}"
                           style="width:72px;padding:9px 10px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:13.5px;outline:none;">
                </div>
                <button type="submit" class="mtr-btn mtr-btn--primary" style="margin-left:auto;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Category
                </button>
            </div>
        </form>
    </div>

    {{-- Category list --}}
    @if($categories->isEmpty())
        <div class="admin-empty-state">
            <p>No categories yet</p>
            <span>Add categories above to get started.</span>
        </div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px;">Color</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th style="width:80px;text-align:center;">Order</th>
                        <th style="width:80px;text-align:center;">Mentors</th>
                        <th style="width:90px;text-align:center;">Status</th>
                        <th class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr style="{{ $cat->is_active ? '' : 'opacity:.55;' }}">
                        <td>
                            <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:{{ $cat->color }};vertical-align:middle;"></span>
                        </td>
                        <td>
                            <span style="font-size:13.5px;font-weight:700;color:#1c2331;">{{ $cat->name }}</span>
                        </td>
                        <td style="font-size:12.5px;color:#718096;max-width:280px;">
                            {{ $cat->description ?: '—' }}
                        </td>
                        <td style="text-align:center;font-size:13px;color:#4a5568;">{{ $cat->sort_order }}</td>
                        <td style="text-align:center;font-size:13px;color:#4a5568;">{{ $cat->mentorProfiles()->count() }}</td>
                        <td style="text-align:center;">
                            <span class="status-badge {{ $cat->is_active ? 'status-badge--accepted' : 'status-badge--declined' }}">
                                {{ $cat->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <button class="mtr-btn mtr-btn--outline mtr-btn--sm"
                                        onclick="openEditModal({{ $cat->id }}, '{{ e($cat->name) }}', '{{ e($cat->description) }}', '{{ $cat->color }}', {{ $cat->sort_order }})">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.mentor-categories.toggle', $cat) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="mtr-btn mtr-btn--outline mtr-btn--sm">
                                        {{ $cat->is_active ? 'Hide' : 'Show' }}
                                    </button>
                                </form>
                                @if($cat->mentorProfiles()->count() === 0)
                                <form method="POST" action="{{ route('admin.mentor-categories.destroy', $cat) }}" style="display:inline;"
                                      onsubmit="return confirm('Delete {{ e($cat->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="mtr-btn mtr-btn--outline mtr-btn--sm" style="color:#c53030;border-color:#fed7d7;">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Edit Modal --}}
<div class="mtr-modal-overlay" id="editModal">
    <div class="mtr-modal">
        <h3 class="mtr-modal__title">Edit Category</h3>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:12px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#4a5568;margin-bottom:5px;">Name *</label>
                <input type="text" id="editName" name="name" required maxlength="80"
                       style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:13.5px;outline:none;">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#4a5568;margin-bottom:5px;">Description</label>
                <input type="text" id="editDesc" name="description" maxlength="250"
                       style="width:100%;padding:9px 12px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:13.5px;outline:none;">
            </div>
            <div style="display:flex;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#4a5568;margin-bottom:5px;">Color</label>
                    <input type="color" id="editColor" name="color"
                           style="width:46px;height:38px;border:none;border-radius:8px;cursor:pointer;padding:2px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#4a5568;margin-bottom:5px;">Sort Order</label>
                    <input type="number" id="editOrder" name="sort_order" min="0"
                           style="width:80px;padding:9px 10px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:13.5px;outline:none;">
                </div>
            </div>
            <div class="mtr-modal__actions">
                <button type="button" class="mtr-btn mtr-btn--outline" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="mtr-btn mtr-btn--primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const BASE = (window.APP_BASE_URL || '').replace(/\/$/, '');

function openEditModal(id, name, desc, color, order) {
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editColor').value = color;
    document.getElementById('editOrder').value = order;
    document.getElementById('editForm').action = `${BASE}/admin/mentor-categories/${id}`;
    document.getElementById('editModal').classList.add('open');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endsection
