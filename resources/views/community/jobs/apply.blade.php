@extends('layouts.community')
@section('title', 'Apply for Job')
@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/jobs/apply-job.css') }}">
@endpush

@section('content')

<div class="container py-4">

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="mb-4">
                <h2 class="mb-2">{{ $job->title }}</h2>

                <p class="text-muted mb-1">
                    {{ $job->company_name }}
                </p>

                @if($job->location)
                    <p class="text-muted">
                        {{ $job->location }}
                    </p>
                @endif
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('jobs.apply.store', $job) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="mb-3">
                    <label class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="{{ session('alumni_name') }}"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        value="{{ session('alumni_email') }}"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="{{ old('phone') }}"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Resume
                    </label>

                    <input
                        type="file"
                        name="resume"
                        class="form-control"
                        accept=".pdf,.doc,.docx"
                        required
                    >

                    <small class="text-muted">
                        PDF, DOC or DOCX. Max 5MB.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Cover Letter
                    </label>

                    <textarea
                        name="cover_letter"
                        rows="8"
                        class="form-control"
                    >{{ old('cover_letter') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Submit Application
                    </button>

                    <a
                        href="{{ route('jobs.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection