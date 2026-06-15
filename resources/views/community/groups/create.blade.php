@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Create Group')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
@endpush

@section('content')
<div class="groups-page groups-page--narrow">

    <a href="{{ route('groups.index') }}" class="groups-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Groups
    </a>

    <div class="group-form-card">
        <h1 class="group-form-card__title">Create a Community Group</h1>
        <p class="group-form-card__sub">
            Your group will appear in the directory immediately and you'll be its
            admin — able to approve join requests and assign moderators.
        </p>

        @if($errors->any())
            <div class="groups-flash groups-flash--error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data" class="group-form">
            @csrf

            <div class="group-form__field">
                <label for="group_name">Group Name <span>*</span></label>
                <input
                    type="text"
                    id="group_name"
                    name="name"
                    maxlength="120"
                    required
                    value="{{ old('name') }}"
                    placeholder="e.g. Delhi Alumni Chapter"
                >
            </div>

            <div class="group-form__field">
                <label for="group_description">Description</label>
                <textarea
                    id="group_description"
                    name="description"
                    rows="4"
                    maxlength="2000"
                    placeholder="What's this group about? Who should join?"
                >{{ old('description') }}</textarea>
            </div>

            <div class="group-form__field">
                <label for="group_cover">Cover Image</label>
                <input type="file" id="group_cover" name="cover_image" accept="image/jpeg,image/png,image/webp">
                <span class="group-form__hint">Optional. JPG, PNG or WEBP, up to 5MB.</span>
            </div>

            <div class="group-form__actions">
                <a href="{{ route('groups.index') }}" class="groups-btn groups-btn--ghost">Cancel</a>
                <button type="submit" class="groups-btn groups-btn--primary">Create Group</button>
            </div>
        </form>
    </div>

</div>
@endsection