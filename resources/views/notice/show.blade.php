@extends('layouts.app')
@section('title', $notice->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/public-content.css') }}?v=1">
@endpush

@section('content')
<section class="content-page">
    <div class="container">
        <div class="content-detail">

            <a href="{{ route('notice') }}" class="content-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Notices
            </a>

            @if($notice->image)
                <img src="{{ $notice->image_url }}" alt="{{ $notice->title }}" class="content-detail__hero-img">
            @endif

            <div class="content-detail__meta">
                @if($notice->category)
                    <span class="notice-card__cat" style="position:static;">{{ $notice->category->name }}</span>
                @endif
                <span class="notice-card__date">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $notice->published_at->format('F j, Y') }}
                </span>
            </div>

            <h1 class="content-detail__title">{{ $notice->title }}</h1>

            <div class="rich-content">{!! $notice->description !!}</div>

        </div>
    </div>
</section>
@endsection