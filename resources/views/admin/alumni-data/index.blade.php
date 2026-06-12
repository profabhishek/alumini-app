@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Alumni Data')

@push('styles')
<style>
/* ─── Reset / layout overrides ─────────────────────────────────────── */
.comm-shell,.comm-content-grid,.comm-center{overflow:visible!important;height:auto!important;min-height:unset!important}
.comm-shell{min-height:100vh!important}
.comm-main{overflow-y:auto!important;height:auto!important;overflow:visible!important;min-height:calc(100vh - var(--header-h))!important}
.comm-content-grid--full .comm-center{max-width:100%}

/* ─── Tokens ─────────────────────────────────────────────────────── */
:root{
  --brand:#E8640C;
  --brand-dk:#c9540a;
  --brand-lt:#fff4ee;
  --brand-mid:#fde9d6;
  --surface:#fff;
  --surface-2:#f9fafb;
  --border:#e5e7eb;
  --border-focus:#E8640C;
  --text:#1C2331;
  --text-2:#374151;
  --text-muted:#6b7280;
  --text-xs:#9ca3af;
  --radius-sm:8px;
  --radius-md:12px;
  --radius-lg:16px;
  --shadow-sm:0 1px 4px rgba(17,24,39,.06);
  --shadow-md:0 4px 14px rgba(17,24,39,.08);
  --shadow-lg:0 24px 60px rgba(17,24,39,.18);
}

/* ─── Page shell ─────────────────────────────────────────────────── */
.ad{padding:28px 0 72px}
.ad__header1{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px}
.ad__title1{font-size:1.35rem;font-weight:800;color:var(--text);margin:0;letter-spacing:-.01em}
.ad__sub1{font-size:.82rem;color:var(--text-muted);margin:3px 0 0}

/* ─── Cards ──────────────────────────────────────────────────────── */
.ad-card1{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:20px;box-shadow:var(--shadow-sm)}
.ad-card1__hd{font-size:.88rem;font-weight:700;color:var(--text);margin:0 0 16px;display:flex;align-items:center;gap:8px}
.ad-card1__hd svg{width:15px;height:15px;color:var(--brand);flex-shrink:0}

/* ─── Stats ──────────────────────────────────────────────────────── */
.ad-stat1s1{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;margin-bottom:20px}
.ad-stat1{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:16px 18px;text-align:center}
.ad-stat1__n{font-size:1.6rem;font-weight:800;color:var(--brand);line-height:1;display:block}
.ad-stat1__l{font-size:.73rem;color:var(--text-muted);margin-top:4px;display:block}

/* ─── Alerts ─────────────────────────────────────────────────────── */
.ad-alert1{display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-radius:var(--radius-sm);font-size:.82rem;font-weight:500;margin-bottom:16px;line-height:1.5}
.ad-alert1 svg{width:15px;height:15px;flex-shrink:0;margin-top:1px}
.ad-alert1--ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
.ad-alert1--err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c}

/* ─── Dropzone ───────────────────────────────────────────────────── */
.ad-dz{border:2px dashed var(--border);border-radius:var(--radius-md);padding:32px 24px;text-align:center;cursor:pointer;transition:all .18s;position:relative;background:var(--surface-2)}
.ad-dz:hover,.ad-dz.is-over{border-color:var(--brand);background:var(--brand-lt)}
.ad-dz__icon{width:44px;height:44px;margin:0 auto 12px;background:var(--brand-mid);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--brand)}
.ad-dz__icon svg{width:22px;height:22px}
.ad-dz__title{font-size:.92rem;font-weight:700;color:var(--text);margin:0 0 4px}
.ad-dz__sub{font-size:.78rem;color:var(--text-xs);margin:0}
.ad-dz input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%}
.ad-file-ok{display:none;align-items:center;gap:10px;padding:10px 14px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:var(--radius-sm);margin-top:12px;font-size:.82rem;color:#15803d;font-weight:600}
.ad-file-ok svg{width:15px;height:15px;flex-shrink:0}

/* ─── Mode radio ─────────────────────────────────────────────────── */
.ad-mode{display:flex;gap:10px;margin:16px 0;flex-wrap:wrap}
.ad-mode__opt{flex:1;min-width:160px}
.ad-mode__opt input{display:none}
.ad-mode__opt label{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:2px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:all .15s;background:var(--surface-2)}
.ad-mode__opt input:checked+label{border-color:var(--brand);background:var(--brand-lt)}
.ad-mode__opt label strong{display:block;font-size:.84rem;color:var(--text);margin-bottom:2px}
.ad-mode__opt label span{font-size:.74rem;color:var(--text-muted);line-height:1.4}
.ad-mode__dot{width:16px;height:16px;border:2px solid var(--border);border-radius:50%;flex-shrink:0;margin-top:2px;transition:all .15s;position:relative}
.ad-mode__opt input:checked+label .ad-mode__dot{border-color:var(--brand);background:var(--brand)}
.ad-mode__opt input:checked+label .ad-mode__dot::after{content:'';position:absolute;inset:3px;background:#fff;border-radius:50%}

/* ─── Progress ───────────────────────────────────────────────────── */
.ad-prog{display:none;margin-top:14px}
.ad-prog__bar{height:6px;background:var(--surface-2);border-radius:999px;overflow:hidden;margin-bottom:6px}
.ad-prog__fill{height:100%;background:var(--brand);border-radius:999px;width:0%;transition:width .3s ease;animation:pulse 1.2s ease-in-out infinite alternate}
@keyframes pulse{from{opacity:1}to{opacity:.6}}
.ad-prog__lbl{font-size:.76rem;color:var(--text-muted);text-align:center}

/* ─── Buttons ────────────────────────────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);font:inherit;font-size:.83rem;font-weight:700;cursor:pointer;border:1.5px solid transparent;text-decoration:none;transition:all .15s;white-space:nowrap;line-height:1}
.btn svg{width:14px;height:14px}
.btn--primary{background:var(--brand);color:#fff;border-color:var(--brand)}
.btn--primary:hover:not(:disabled){background:var(--brand-dk);border-color:var(--brand-dk)}
.btn--primary:disabled{opacity:.55;cursor:not-allowed}
.btn--outline{background:var(--surface);color:var(--text-2);border-color:var(--border)}
.btn--outline:hover{background:var(--surface-2)}
.btn--danger{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
.btn--danger:hover{background:#fee2e2}
.btn--ghost{background:none;border:none;padding:6px 10px;color:var(--text-muted)}
.btn--ghost:hover{color:var(--text);background:var(--surface-2)}
.btn--sm{padding:6px 12px;font-size:.78rem}
.btn--sm svg{width:12px;height:12px}

/* ─── Filter panel ───────────────────────────────────────────────── */
.ad-filters{background:var(--surface-2);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:18px 20px;margin-bottom:16px}
.ad-filters__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;margin-bottom:14px}
.ad-filters__footer{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.ad-fg{display:flex;flex-direction:column;gap:5px}
.ad-fg label{font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em}
.ad-fg--wide{grid-column:1 / -1}
.ad-input,.ad-select{height:36px;padding:0 10px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font:inherit;font-size:.82rem;background:var(--surface);color:var(--text);outline:none;transition:border-color .15s;width:100%;box-sizing:border-box}
.ad-input:focus,.ad-select:focus{border-color:var(--border-focus)}
.ad-range{display:flex;align-items:center;gap:6px;width:100%}
.ad-range .ad-input{flex:1;min-width:0;width:0;padding:0 8px}
.ad-range span{font-size:.78rem;color:var(--text-xs);white-space:nowrap;flex-shrink:0}
.ad-search-wrap{position:relative}
.ad-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-xs);pointer-events:none}
.ad-search-wrap .ad-input{padding-left:32px}
input[type="date"].ad-input{padding:0 6px;font-size:.78rem}

/* ─── Toolbar ────────────────────────────────────────────────────── */
.ad-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.ad-toolbar__spacer{flex:1}

/* ─── Table ──────────────────────────────────────────────────────── */
.ad-tbl-wrap{overflow-x:auto;border-radius:var(--radius-md);border:1px solid var(--border);background:var(--surface);box-shadow:var(--shadow-md);-webkit-overflow-scrolling:touch}
.ad-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:.78rem;min-width:3200px}
.ad-tbl thead th{position:sticky;top:0;z-index:1;background:var(--surface-2);padding:10px 13px;text-align:left;font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border);white-space:nowrap}
.ad-tbl tbody td{padding:10px 13px;border-bottom:1px solid #f3f4f6;color:var(--text-2);vertical-align:middle;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;background:var(--surface)}
.ad-tbl tbody tr:hover td{background:#fafafa}
.ad-tbl tbody tr:last-child td{border-bottom:0}
.ad-tbl td.num{text-align:right;font-variant-numeric:tabular-nums}
.ad-null{color:var(--text-xs);font-style:italic;font-size:.72rem}

/* Pinned columns */
.ad-tbl th.pin-l,.ad-tbl td.pin-l{position:sticky;left:0;z-index:2;background:var(--surface);box-shadow:2px 0 6px rgba(17,24,39,.04)}
.ad-tbl thead th.pin-l{background:var(--surface-2)}
.ad-tbl th.pin-r,.ad-tbl td.pin-r{position:sticky;right:0;z-index:2;background:var(--surface);box-shadow:-2px 0 6px rgba(17,24,39,.04);text-align:center;width:72px}
.ad-tbl thead th.pin-r{background:var(--surface-2)}

/* ─── Badges ─────────────────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:.68rem;font-weight:700}
.badge--green{background:#f0fdf4;color:#15803d}
.badge--blue{background:#eff6ff;color:#1d4ed8}
.badge--gray{background:var(--surface-2);color:var(--text-muted)}
.badge--orange{background:var(--brand-lt);color:var(--brand)}

/* ─── Delete button ──────────────────────────────────────────────── */
.ad-del{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font:inherit;font-size:.7rem;font-weight:600;cursor:pointer;border:1px solid #fecaca;background:#fef2f2;color:#b91c1c;transition:all .15s}
.ad-del:hover{background:#fee2e2}
.ad-del svg{width:11px;height:11px}

/* ─── Pagination ─────────────────────────────────────────────────── */
.ad-pager{display:flex;flex-direction:column;align-items:center;gap:12px;margin-top:24px}
.ad-pager p{margin:0;font-size:.82rem;color:var(--text-muted)}
.ad-pager .pagination{display:flex;flex-wrap:wrap;gap:6px;padding:0;list-style:none;margin:0}
.ad-pager .page-link{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-2);font-size:.82rem;font-weight:600;text-decoration:none;transition:all .15s}
.ad-pager .page-link:hover{background:var(--surface-2)}
.ad-pager .page-item.active .page-link{background:var(--brand);border-color:var(--brand);color:#fff;box-shadow:0 4px 12px rgba(232,100,12,.22)}
.ad-pager .page-item.disabled .page-link{opacity:.4;pointer-events:none}
.ad-pager .page-item:first-child,.ad-pager .page-item:last-child{display:none}

/* ─── Empty state ────────────────────────────────────────────────── */
.ad-empty{text-align:center;padding:56px 20px;color:var(--text-xs)}
.ad-empty svg{width:48px;height:48px;margin:0 auto 16px;opacity:.25;display:block}
.ad-empty strong{display:block;font-size:.9rem;color:var(--text-muted);margin-bottom:4px}
.ad-empty span{font-size:.8rem}

/* ─── Export modal ───────────────────────────────────────────────── */
.ad-modal-bd{display:none;position:fixed;inset:0;background:rgba(17,24,39,.5);z-index:9999;align-items:center;justify-content:center;padding:16px}
.ad-modal-bd.open{display:flex}
.ad-modal{background:var(--surface);border-radius:var(--radius-lg);padding:28px;width:700px;max-width:100%;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-lg)}
.ad-modal__hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.ad-modal__title{font-size:1rem;font-weight:800;color:var(--text);margin:0}
.ad-modal__close{width:32px;height:32px;border:none;background:var(--surface-2);border-radius:var(--radius-sm);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);transition:all .15s}
.ad-modal__close:hover{background:var(--border);color:var(--text)}
.ad-modal__close svg{width:15px;height:15px}
.ad-modal__sub{font-size:.8rem;color:var(--text-muted);margin:0 0 20px;line-height:1.5}
.ad-modal__sub strong{color:var(--text)}
.ad-modal__section-lbl{font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}

/* Column picker */
.ad-col-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:7px;margin-bottom:20px;max-height:300px;overflow-y:auto;padding-right:4px}
.ad-col-item{display:flex;align-items:center;gap:8px;padding:8px 12px;border:1.5px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:all .15s;background:var(--surface-2);user-select:none}
.ad-col-item:hover{border-color:var(--brand);background:var(--brand-lt)}
.ad-col-item.on{border-color:var(--brand);background:var(--brand-lt)}
.ad-col-item input{display:none}
.ad-col-chk{width:15px;height:15px;border:2px solid var(--border);border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s}
.ad-col-item.on .ad-col-chk{background:var(--brand);border-color:var(--brand)}
.ad-col-chk svg{width:9px;height:9px;color:#fff;display:none}
.ad-col-item.on .ad-col-chk svg{display:block}
.ad-col-label{font-size:.77rem;font-weight:600;color:var(--text-2)}

.ad-export-footer{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding-top:16px;border-top:1px solid var(--border);margin-top:4px}
.ad-export-footer__count{font-size:.78rem;color:var(--text-muted)}
.ad-export-footer__btns{display:flex;gap:8px;margin-left:auto}

/* ─── Responsive ─────────────────────────────────────────────────── */
@media(max-width:992px){.ad-card1{padding:16px}.ad-filters__grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.ad-filters__grid{grid-template-columns:1fr}.ad-stat1s1{grid-template-columns:repeat(3,1fr)}}
</style>
@endpush

@section('content')
<div class="ad">

    {{-- Header --}}
    <div class="ad__header1">
        <div>
            <h1 class="ad__title1">Alumni Data</h1>
            <p class="ad__sub1">Bulk import and manage alumni records. Separate from active portal accounts.</p>
        </div>
        <a href="{{ route('admin.alumni-data.template') }}" class="btn btn--outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('import_success'))
        <div class="ad-alert1 ad-alert1--ok">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            {{ session('import_success') }}
        </div>
    @endif
    @if(session('import_error'))
        <div class="ad-alert1 ad-alert1--err">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('import_error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="ad-stat1s1">
        <div class="ad-stat1">
            <span class="ad-stat1__n">{{ number_format($total) }}</span>
            <span class="ad-stat1__l">Total Records</span>
        </div>
        <div class="ad-stat1">
            <span class="ad-stat1__n">{{ number_format($withEmail) }}</span>
            <span class="ad-stat1__l">With Email</span>
        </div>
        <div class="ad-stat1">
            <span class="ad-stat1__n">{{ number_format($withPhone) }}</span>
            <span class="ad-stat1__l">With Phone</span>
        </div>
        <div class="ad-stat1">
            <span class="ad-stat1__n">{{ number_format($employed) }}</span>
            <span class="ad-stat1__l">Employed</span>
        </div>
        <div class="ad-stat1">
            <span class="ad-stat1__n">{{ number_format($batchYears) }}</span>
            <span class="ad-stat1__l">Batch Years</span>
        </div>
        <div class="ad-stat1">
            <span class="ad-stat1__n">{{ number_format($countries) }}</span>
            <span class="ad-stat1__l">Countries</span>
        </div>
    </div>

    {{-- Upload card --}}
    <div class="ad-card1">
        <p class="ad-card1__hd">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload File
        </p>
        <form method="POST" action="{{ route('admin.alumni-data.import') }}" enctype="multipart/form-data" id="importForm">
            @csrf
            <div class="ad-dz" id="dropzone">
                <input type="file" name="csv_file" id="csvFile" accept=".csv,.xlsx,.xls,.txt">
                <div class="ad-dz__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h4"/></svg>
                </div>
                <p class="ad-dz__title">Drop CSV or Excel file here</p>
                <p class="ad-dz__sub">or click to browse — CSV, XLSX, XLS, up to 20 MB</p>
            </div>
            <div class="ad-file-ok" id="fileOk">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                <span id="fileName"></span>
            </div>
            <div class="ad-mode">
                <div class="ad-mode__opt">
                    <input type="radio" name="mode" id="modeAppend" value="append" checked>
                    <label for="modeAppend">
                        <span class="ad-mode__dot"></span>
                        <div><strong>Append</strong><span>Add records without touching existing data</span></div>
                    </label>
                </div>
                <div class="ad-mode__opt">
                    <input type="radio" name="mode" id="modeReplace" value="replace">
                    <label for="modeReplace">
                        <span class="ad-mode__dot"></span>
                        <div><strong>Replace All</strong><span>Delete all existing records, then import fresh</span></div>
                    </label>
                </div>
            </div>
            <div class="ad-prog" id="importProg">
                <div class="ad-prog__bar"><div class="ad-prog__fill" id="progFill"></div></div>
                <p class="ad-prog__lbl">Uploading and processing — please wait…</p>
            </div>
            <button type="submit" class="btn btn--primary" id="importBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Records
            </button>
        </form>
    </div>

    {{-- Records card --}}
    <div class="ad-card1">
        <p class="ad-card1__hd">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Records
        </p>

        {{-- Filter form --}}
        <form method="GET" action="{{ route('admin.alumni-data.index') }}" id="filterForm">
            <div class="ad-filters">
                <div class="ad-filters__grid">

                    {{-- Search (full-width) --}}
                    <div class="ad-fg ad-fg--wide">
                        <label>Search</label>
                        <div class="ad-search-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Name, email, phone, code, company, city…">
                        </div>
                    </div>

                    {{-- Gender --}}
                    <div class="ad-fg">
                        <label>Gender</label>
                        <select name="gender" class="ad-select js-auto-filter">
                            <option value="">All Genders</option>
                            <option value="male"   @selected(($filters['gender'] ?? '') === 'male')>Male</option>
                            <option value="female" @selected(($filters['gender'] ?? '') === 'female')>Female</option>
                            <option value="other"  @selected(($filters['gender'] ?? '') === 'other')>Other</option>
                        </select>
                    </div>

                    {{-- Level of Study --}}
                    <div class="ad-fg">
                        <label>Level of Study</label>
                        <select name="level_of_study" class="ad-select js-auto-filter">
                            <option value="">All Levels</option>
                            @foreach($levelOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['level_of_study'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Course --}}
                    <div class="ad-fg">
                        <label>Course</label>
                        <select name="course" class="ad-select js-auto-filter">
                            <option value="">All Courses</option>
                            @foreach($courseOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['course'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Branch --}}
                    <div class="ad-fg">
                        <label>Branch</label>
                        <select name="branch" class="ad-select js-auto-filter">
                            <option value="">All Branches</option>
                            @foreach($branchOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['branch'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Institute --}}
                    <div class="ad-fg">
                        <label>Institute</label>
                        <select name="institute" class="ad-select js-auto-filter">
                            <option value="">All Institutes</option>
                            @foreach($instituteOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['institute'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Campus --}}
                    <div class="ad-fg">
                        <label>Campus</label>
                        <select name="campus" class="ad-select js-auto-filter">
                            <option value="">All Campuses</option>
                            @foreach($campusOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['campus'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Joining Year --}}
                    <div class="ad-fg">
                        <label>Joining Year</label>
                        <div class="ad-range">
                            <input type="number" name="joining_from" value="{{ $filters['joining_from'] ?? '' }}" class="ad-input" placeholder="From" min="1950" max="{{ date('Y') }}">
                            <span>–</span>
                            <input type="number" name="joining_to" value="{{ $filters['joining_to'] ?? '' }}" class="ad-input" placeholder="To" min="1950" max="{{ date('Y') }}">
                        </div>
                    </div>

                    {{-- Graduation Year --}}
                    <div class="ad-fg">
                        <label>Graduation Year</label>
                        <div class="ad-range">
                            <input type="number" name="grad_from" value="{{ $filters['grad_from'] ?? '' }}" class="ad-input" placeholder="From" min="1950" max="{{ date('Y') + 10 }}">
                            <span>–</span>
                            <input type="number" name="grad_to" value="{{ $filters['grad_to'] ?? '' }}" class="ad-input" placeholder="To" min="1950" max="{{ date('Y') + 10 }}">
                        </div>
                    </div>

                    {{-- Date of Birth --}}
                    <div class="ad-fg">
                        <label>Date of Birth</label>
                        <div class="ad-range">
                            <input type="date" name="dob_from" value="{{ $filters['dob_from'] ?? '' }}" class="ad-input">
                            <span>–</span>
                            <input type="date" name="dob_to" value="{{ $filters['dob_to'] ?? '' }}" class="ad-input">
                        </div>
                    </div>

                    {{-- Current Country --}}
                    <div class="ad-fg">
                        <label>Current Country</label>
                        <select name="country" class="ad-select js-auto-filter">
                            <option value="">All Countries</option>
                            @foreach($countryOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['country'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Current City --}}
                    <div class="ad-fg">
                        <label>Current City</label>
                        <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" class="ad-input" placeholder="e.g. Mumbai">
                    </div>

                    {{-- Company --}}
                    <div class="ad-fg">
                        <label>Company</label>
                        <input type="text" name="company" value="{{ $filters['company'] ?? '' }}" class="ad-input" placeholder="e.g. Infosys">
                    </div>

                    {{-- Designation --}}
                    <div class="ad-fg">
                        <label>Designation</label>
                        <input type="text" name="designation" value="{{ $filters['designation'] ?? '' }}" class="ad-input" placeholder="e.g. Software Engineer">
                    </div>

                    {{-- Address State --}}
                    <div class="ad-fg">
                        <label>Address State</label>
                        <select name="address_state" class="ad-select js-auto-filter">
                            <option value="">All States</option>
                            @foreach($addressStateOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['address_state'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Address Country --}}
                    <div class="ad-fg">
                        <label>Address Country</label>
                        <select name="address_country" class="ad-select js-auto-filter">
                            <option value="">All Countries</option>
                            @foreach($addressCountryOptions as $opt)
                                <option value="{{ $opt }}" @selected(($filters['address_country'] ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Has Email --}}
                    <div class="ad-fg">
                        <label>Email</label>
                        <select name="has_email" class="ad-select js-auto-filter">
                            <option value="">Any</option>
                            <option value="1" @selected(($filters['has_email'] ?? '') === '1')>Has Email</option>
                            <option value="0" @selected(($filters['has_email'] ?? '') === '0')>No Email</option>
                        </select>
                    </div>

                    {{-- Employed --}}
                    <div class="ad-fg">
                        <label>Employment</label>
                        <select name="employed" class="ad-select js-auto-filter">
                            <option value="">Any</option>
                            <option value="1" @selected(($filters['employed'] ?? '') === '1')>Employed</option>
                            <option value="0" @selected(($filters['employed'] ?? '') === '0')>Not Employed</option>
                        </select>
                    </div>

                    {{-- Record Created --}}
                    <div class="ad-fg">
                        <label>Record Created</label>
                        <div class="ad-range">
                            <input type="date" name="created_from" value="{{ $filters['created_from'] ?? '' }}" class="ad-input">
                            <span>–</span>
                            <input type="date" name="created_to" value="{{ $filters['created_to'] ?? '' }}" class="ad-input">
                        </div>
                    </div>

                </div>
                <div class="ad-filters__footer">
                    <button type="submit" class="btn btn--primary btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.alumni-data.index') }}" class="btn btn--outline btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Reset
                    </a>
                    <span style="font-size:.78rem;color:var(--text-muted);margin-left:4px;">
                        {{ number_format($rows->total()) }} result{{ $rows->total() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        </form>

        {{-- Action bar --}}
        <div class="ad-toolbar">
            <button type="button" class="btn btn--outline btn--sm" id="exportBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            @if($total > 0)
                <form method="POST" action="{{ route('admin.alumni-data.clear') }}"
                      onsubmit="return confirm('Delete ALL {{ number_format($total) }} records? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6"/></svg>
                        Clear All
                    </button>
                </form>
            @endif
        </div>

        {{-- Table --}}
        @if($rows->isEmpty())
            <div class="ad-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <strong>{{ !empty(array_filter($filters ?? [])) ? 'No matching records' : 'No records yet' }}</strong>
                <span>{{ !empty(array_filter($filters ?? [])) ? 'Try adjusting your filters.' : 'Upload a CSV or Excel file above to get started.' }}</span>
            </div>
        @else
            <div class="ad-tbl-wrap">
                <table class="ad-tbl">
                    <thead>
                        <tr>
                            <th class="pin-l">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Alumni Code</th>
                            <th>Legacy ID</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Institute</th>
                            <th>Campus</th>
                            <th>Course</th>
                            <th>Branch</th>
                            <th>Level of Study</th>
                            <th>Joining Year</th>
                            <th>Graduation Year</th>
                            <th>Company</th>
                            <th>Designation</th>
                            <th>Current City</th>
                            <th>Current Country</th>
                            <th>LinkedIn</th>
                            <th>Facebook</th>
                            <th>Profile Image</th>
                            <th>Addr Line 1</th>
                            <th>Addr Line 2</th>
                            <th>Addr City</th>
                            <th>Addr State</th>
                            <th>Addr Country</th>
                            <th>Pincode</th>
                            <th>Record Created</th>
                            <th>Record Updated</th>
                            <th class="pin-r">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td class="pin-l num" style="color:var(--text-muted);font-size:.72rem">{{ $row->id }}</td>
                            <td title="{{ $row->name }}"><strong style="color:var(--text)">{{ $row->name ?: '—' }}</strong></td>
                            <td title="{{ $row->email }}">
                                @if($row->email)
                                    <a href="mailto:{{ $row->email }}" style="color:var(--brand);text-decoration:none">{{ $row->email }}</a>
                                @else
                                    <span class="ad-null">—</span>
                                @endif
                            </td>
                            <td>{{ $row->phone ?: '—' }}</td>
                            <td>
                                @if($row->alumni_code)
                                    <span class="badge badge--orange">{{ $row->alumni_code }}</span>
                                @else
                                    <span class="ad-null">—</span>
                                @endif
                            </td>
                            <td>{{ $row->legacy_user_id ?: '—' }}</td>
                            <td>
                                @if($row->gender)
                                    <span class="badge badge--gray">{{ ucfirst($row->gender) }}</span>
                                @else
                                    <span class="ad-null">—</span>
                                @endif
                            </td>
                            <td>{{ $row->dob ? \Carbon\Carbon::parse($row->dob)->format('d M Y') : '—' }}</td>
                            <td title="{{ $row->institute }}">{{ \Illuminate\Support\Str::limit($row->institute, 30) ?: '—' }}</td>
                            <td title="{{ $row->campus }}">{{ \Illuminate\Support\Str::limit($row->campus, 24) ?: '—' }}</td>
                            <td title="{{ $row->course }}">{{ \Illuminate\Support\Str::limit($row->course, 24) ?: '—' }}</td>
                            <td title="{{ $row->branch }}">{{ \Illuminate\Support\Str::limit($row->branch, 24) ?: '—' }}</td>
                            <td>{{ $row->level_of_study ?: '—' }}</td>
                            <td class="num">{{ $row->joining_year ?: '—' }}</td>
                            <td class="num">{{ $row->graduation_year ?: '—' }}</td>
                            <td title="{{ $row->current_company }}">{{ \Illuminate\Support\Str::limit($row->current_company, 24) ?: '—' }}</td>
                            <td title="{{ $row->current_designation }}">{{ \Illuminate\Support\Str::limit($row->current_designation, 24) ?: '—' }}</td>
                            <td>{{ $row->current_city ?: '—' }}</td>
                            <td>{{ $row->current_country ?: '—' }}</td>
                            <td>
                                @if($row->linkedin_url)
                                    <a href="{{ $row->linkedin_url }}" target="_blank" rel="noopener" class="badge badge--blue" title="{{ $row->linkedin_url }}">LinkedIn</a>
                                @else
                                    <span class="ad-null">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->facebook_url)
                                    <a href="{{ $row->facebook_url }}" target="_blank" rel="noopener" class="badge badge--blue" title="{{ $row->facebook_url }}">Facebook</a>
                                @else
                                    <span class="ad-null">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->profile_image)
                                    <a href="{{ $row->profile_image }}" target="_blank" rel="noopener" class="badge badge--gray" title="{{ $row->profile_image }}">Image</a>
                                @else
                                    <span class="ad-null">—</span>
                                @endif
                            </td>
                            <td title="{{ $row->address_line1 }}">{{ \Illuminate\Support\Str::limit($row->address_line1, 24) ?: '—' }}</td>
                            <td title="{{ $row->address_line2 }}">{{ \Illuminate\Support\Str::limit($row->address_line2, 24) ?: '—' }}</td>
                            <td>{{ $row->address_city ?: '—' }}</td>
                            <td>{{ $row->address_state ?: '—' }}</td>
                            <td>{{ $row->address_country ?: '—' }}</td>
                            <td>{{ $row->address_pincode ?: '—' }}</td>
                            <td>
                                @php
                                    $created = $row->record_created_at ?? $row->created_at;
                                @endphp
                                {{ $created ? \Carbon\Carbon::parse($created)->format('d M Y') : '—' }}
                            </td>
                            <td>
                                @php
                                    $updated = $row->record_updated_at ?? $row->updated_at;
                                @endphp
                                {{ $updated ? \Carbon\Carbon::parse($updated)->format('d M Y') : '—' }}
                            </td>
                            <td class="pin-r">
                                <form method="POST" action="{{ route('admin.alumni-data.destroy', $row->id) }}"
                                      onsubmit="return confirm('Delete this record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ad-del">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6"/></svg>
                                        Del
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($rows->hasPages())
                <div class="ad-pager">
                    {{ $rows->appends(request()->query())->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- ── Export Modal ─────────────────────────────────────────────────────── --}}
<div class="ad-modal-bd" id="exportModal">
    <div class="ad-modal">
        <div class="ad-modal__hd">
            <h2 class="ad-modal__title">Export Alumni Data</h2>
            <button class="ad-modal__close" id="exportClose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <p class="ad-modal__sub">
            Pick columns to include. <strong>Active filters apply</strong> — only matching records are exported.
            PDF is capped at 5,000 rows; CSV has no limit.
        </p>

        <div class="ad-modal__section-lbl">
            <span>Columns</span>
            <div style="display:flex;gap:12px">
                <button type="button" class="btn btn--ghost btn--sm" data-sel="all">Select all</button>
                <button type="button" class="btn btn--ghost btn--sm" data-sel="none">Deselect all</button>
            </div>
        </div>

        <div class="ad-col-grid" id="colGrid">
            @php
                $exportCols    = \App\Models\AlumniData::exportableColumns();
                $defaultCols   = \App\Models\AlumniData::defaultExportColumns();
            @endphp
            @foreach($exportCols as $col => $label)
                <label class="ad-col-item {{ in_array($col, $defaultCols) ? 'on' : '' }}" data-col="{{ $col }}">
                    <input type="checkbox" value="{{ $col }}" {{ in_array($col, $defaultCols) ? 'checked' : '' }}>
                    <span class="ad-col-chk">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    </span>
                    <span class="ad-col-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div class="ad-export-footer">
            <span class="ad-export-footer__count" id="colCount">{{ count($defaultCols) }} columns selected</span>
            <div class="ad-export-footer__btns">
                <button type="button" class="btn btn--outline" id="exportCsvBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h4"/></svg>
                    Export CSV
                </button>
                <button type="button" class="btn btn--primary" id="exportPdfBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8Z"/><path d="M14 2v6h6"/><path d="M9 13h1a2 2 0 010 4H9v-4Z"/><path d="M15 13h2M15 17h2M15 15h1"/></svg>
                    Export PDF
                </button>
            </div>
        </div>

        {{-- Hidden export form — carries all current filters --}}
        <form method="GET" action="{{ route('admin.alumni-data.export') }}" id="exportForm" style="display:none">
            <input type="hidden" name="format"   id="expFormat"  value="csv">
            <input type="hidden" name="columns"  id="expColumns" value="">
            <input type="hidden" name="q"              value="{{ $q }}">
            <input type="hidden" name="gender"         value="{{ $filters['gender'] ?? '' }}">
            <input type="hidden" name="level_of_study" value="{{ $filters['level_of_study'] ?? '' }}">
            <input type="hidden" name="course"         value="{{ $filters['course'] ?? '' }}">
            <input type="hidden" name="branch"         value="{{ $filters['branch'] ?? '' }}">
            <input type="hidden" name="institute"      value="{{ $filters['institute'] ?? '' }}">
            <input type="hidden" name="campus"         value="{{ $filters['campus'] ?? '' }}">
            <input type="hidden" name="joining_from"   value="{{ $filters['joining_from'] ?? '' }}">
            <input type="hidden" name="joining_to"     value="{{ $filters['joining_to'] ?? '' }}">
            <input type="hidden" name="grad_from"      value="{{ $filters['grad_from'] ?? '' }}">
            <input type="hidden" name="grad_to"        value="{{ $filters['grad_to'] ?? '' }}">
            <input type="hidden" name="dob_from"       value="{{ $filters['dob_from'] ?? '' }}">
            <input type="hidden" name="dob_to"         value="{{ $filters['dob_to'] ?? '' }}">
            <input type="hidden" name="country"        value="{{ $filters['country'] ?? '' }}">
            <input type="hidden" name="city"           value="{{ $filters['city'] ?? '' }}">
            <input type="hidden" name="company"        value="{{ $filters['company'] ?? '' }}">
            <input type="hidden" name="designation"    value="{{ $filters['designation'] ?? '' }}">
            <input type="hidden" name="address_state"  value="{{ $filters['address_state'] ?? '' }}">
            <input type="hidden" name="address_country" value="{{ $filters['address_country'] ?? '' }}">
            <input type="hidden" name="has_email"      value="{{ $filters['has_email'] ?? '' }}">
            <input type="hidden" name="employed"       value="{{ $filters['employed'] ?? '' }}">
            <input type="hidden" name="created_from"   value="{{ $filters['created_from'] ?? '' }}">
            <input type="hidden" name="created_to"     value="{{ $filters['created_to'] ?? '' }}">
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ── Upload ───────────────────────────────────────────────────── */
    const dz         = document.getElementById('dropzone');
    const fileInput  = document.getElementById('csvFile');
    const fileOk     = document.getElementById('fileOk');
    const fileName   = document.getElementById('fileName');
    const importForm = document.getElementById('importForm');
    const importBtn  = document.getElementById('importBtn');
    const prog       = document.getElementById('importProg');
    const fill       = document.getElementById('progFill');

    fileInput.addEventListener('change', function () {
        if (!this.files.length) return;
        fileName.textContent = this.files[0].name + ' (' + fmtBytes(this.files[0].size) + ')';
        fileOk.style.display = 'flex';
    });

    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('is-over'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('is-over'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('is-over');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    importForm.addEventListener('submit', function () {
        if (!fileInput.files.length) return;
        importBtn.disabled = true;
        importBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Processing…';
        prog.style.display = 'block';
        let pct = 0;
        const iv = setInterval(() => { pct = Math.min(pct + Math.random() * 7, 92); fill.style.width = pct + '%'; }, 350);
        window.addEventListener('beforeunload', () => { clearInterval(iv); fill.style.width = '100%'; });
    });

    /* ── Auto-submit selects ─────────────────────────────────────── */
    document.querySelectorAll('.js-auto-filter').forEach(sel => {
        sel.addEventListener('change', () => document.getElementById('filterForm').submit());
    });

    /* ── Export modal ────────────────────────────────────────────── */
    const modal    = document.getElementById('exportModal');
    const colGrid  = document.getElementById('colGrid');
    const colCount = document.getElementById('colCount');

    document.getElementById('exportBtn').addEventListener('click',  () => modal.classList.add('open'));
    document.getElementById('exportClose').addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });

    function updateCount() {
        const n = colGrid.querySelectorAll('input:checked').length;
        colCount.textContent = n + ' column' + (n !== 1 ? 's' : '') + ' selected';
    }

    colGrid.addEventListener('click', e => {
        const item = e.target.closest('.ad-col-item');
        if (!item) return;
        const cb = item.querySelector('input[type=checkbox]');
        cb.checked = !cb.checked;
        item.classList.toggle('on', cb.checked);
        updateCount();
    });

    document.querySelectorAll('[data-sel]').forEach(btn => {
        btn.addEventListener('click', () => {
            const all = btn.dataset.sel === 'all';
            colGrid.querySelectorAll('.ad-col-item').forEach(item => {
                const cb = item.querySelector('input');
                cb.checked = all;
                item.classList.toggle('on', all);
            });
            updateCount();
        });
    });

    function doExport(fmt) {
        const cols = [...colGrid.querySelectorAll('input:checked')].map(cb => cb.value);
        if (!cols.length) { alert('Please select at least one column.'); return; }
        document.getElementById('expFormat').value  = fmt;
        document.getElementById('expColumns').value = cols.join(',');
        document.getElementById('exportForm').submit();
        modal.classList.remove('open');
    }

    document.getElementById('exportCsvBtn').addEventListener('click', () => doExport('csv'));
    document.getElementById('exportPdfBtn').addEventListener('click', () => doExport('pdf'));

    /* ── Helpers ─────────────────────────────────────────────────── */
    function fmtBytes(b) {
        if (!b) return '';
        const u = ['B','KB','MB','GB'], i = Math.min(Math.floor(Math.log(b) / Math.log(1024)), 3);
        return (b / 1024 ** i).toFixed(i ? 1 : 0) + ' ' + u[i];
    }
})();
</script>
@endpush