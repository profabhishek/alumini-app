<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alumni Data Export</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

/* ── Print header ── */
.pdf-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px 12px;
    border-bottom: 2px solid #E8640C;
    margin-bottom: 16px;
}
.pdf-header__brand {
    font-size: 16px;
    font-weight: 800;
    color: #1C2331;
    letter-spacing: -0.02em;
}
.pdf-header__brand span { color: #E8640C; }
.pdf-header__meta {
    text-align: right;
    font-size: 10px;
    color: #6b7280;
    line-height: 1.6;
}
.pdf-header__meta strong { color: #374151; }

/* ── Summary stats ── */
.pdf-stats {
    display: flex;
    gap: 12px;
    padding: 0 20px 14px;
    flex-wrap: wrap;
}
.pdf-stat {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 8px 14px;
    text-align: center;
    min-width: 80px;
}
.pdf-stat__n { font-size: 16px; font-weight: 800; color: #E8640C; display: block; line-height: 1; }
.pdf-stat__l { font-size: 9px; color: #6b7280; margin-top: 2px; display: block; }

/* ── Table ── */
.pdf-wrap { padding: 0 20px 20px; overflow-x: auto; }
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}
thead th {
    background: #1C2331;
    color: #fff;
    padding: 7px 10px;
    text-align: left;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
    border: 1px solid #2d3a50;
}
tbody td {
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
    color: #374151;
    vertical-align: top;
    max-width: 180px;
    word-break: break-word;
    line-height: 1.4;
}
tbody tr:nth-child(even) td { background: #f9fafb; }
tbody tr:hover td { background: #fff4ee; }
tbody tr:last-child td { border-bottom: 2px solid #E8640C; }

.null { color: #d1d5db; font-style: italic; }
.badge-orange { background: #fff4ee; color: #E8640C; padding: 1px 6px; border-radius: 999px; font-size: 9px; font-weight: 700; }
.badge-blue   { background: #eff6ff; color: #1d4ed8; padding: 1px 6px; border-radius: 999px; font-size: 9px; font-weight: 700; }
.badge-gray   { background: #f3f4f6; color: #6b7280; padding: 1px 6px; border-radius: 999px; font-size: 9px; font-weight: 700; }

/* ── Footer ── */
.pdf-footer {
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    font-size: 9px;
    color: #9ca3af;
    display: flex;
    justify-content: space-between;
}

/* ── Print styles ── */
@media print {
    body { font-size: 9px; }
    .pdf-header { padding: 10px 14px 8px; margin-bottom: 10px; }
    thead th { font-size: 8px; padding: 5px 7px; }
    tbody td  { font-size: 8px; padding: 4px 7px; }
    table { page-break-inside: auto; }
    tr    { page-break-inside: avoid; page-break-after: auto; }
    .no-print { display: none; }
}

/* ── Screen-only print button ── */
.print-bar {
    background: #1C2331;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.print-bar p { color: rgba(255,255,255,.7); font-size: 11px; }
.print-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #E8640C;
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 7px;
    font: inherit;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
}
.print-btn:hover { background: #d05a0b; }
.print-btn svg { width: 14px; height: 14px; }
</style>
</head>
<body>

{{-- Print bar (screen only) --}}
<div class="print-bar no-print">
    <p>{{ count($records) }} record{{ count($records) !== 1 ? 's' : '' }} — {{ count($cols) }} column{{ count($cols) !== 1 ? 's' : '' }} selected</p>
    <button class="print-btn" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print / Save PDF
    </button>
</div>

{{-- Header --}}
<div class="pdf-header">
    <div>
        <div class="pdf-header__brand">ICCR <span>Alumni</span> Data Export</div>
    </div>
    <div class="pdf-header__meta">
        <strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}<br>
        <strong>Records:</strong> {{ number_format(count($records)) }}<br>
        <strong>Columns:</strong> {{ count($cols) }}
    </div>
</div>

{{-- Stats --}}
@php
    $statTotal    = count($records);
    $statEmail    = $records->filter(fn($r) => !empty($r->email))->count();
    $statEmployed = $records->filter(fn($r) => !empty($r->current_company))->count();
    $statCountries = $records->pluck('current_country')->filter()->unique()->count();
@endphp
<div class="pdf-stats">
    <div class="pdf-stat">
        <span class="pdf-stat__n">{{ number_format($statTotal) }}</span>
        <span class="pdf-stat__l">Records</span>
    </div>
    <div class="pdf-stat">
        <span class="pdf-stat__n">{{ number_format($statEmail) }}</span>
        <span class="pdf-stat__l">With Email</span>
    </div>
    <div class="pdf-stat">
        <span class="pdf-stat__n">{{ number_format($statEmployed) }}</span>
        <span class="pdf-stat__l">Employed</span>
    </div>
    <div class="pdf-stat">
        <span class="pdf-stat__n">{{ number_format($statCountries) }}</span>
        <span class="pdf-stat__l">Countries</span>
    </div>
</div>

{{-- Table --}}
<div class="pdf-wrap">
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            <tr>
                @foreach($cols as $col)
                <td>
                    @php $val = $record->{$col} ?? null; @endphp
                    @if(is_null($val) || $val === '')
                        <span class="null">—</span>
                    @elseif($col === 'alumni_code')
                        <span class="badge-orange">{{ $val }}</span>
                    @elseif($col === 'gender')
                        <span class="badge-gray">{{ ucfirst($val) }}</span>
                    @elseif(in_array($col, ['dob', 'record_created_at', 'record_updated_at']))
                        @php
                            try { echo \Carbon\Carbon::parse($val)->format('d M Y'); }
                            catch(\Exception $e) { echo $val; }
                        @endphp
                    @elseif($col === 'linkedin_url' || $col === 'facebook_url')
                        <span class="badge-blue">{{ $col === 'linkedin_url' ? 'LinkedIn' : 'Facebook' }}</span>
                    @elseif($col === 'profile_image')
                        <span class="badge-gray">Image</span>
                    @else
                        {{ \Illuminate\Support\Str::limit((string)$val, 40) }}
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($cols) }}" style="text-align:center;padding:24px;color:#9ca3af;font-style:italic;">
                    No records found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Footer --}}
<div class="pdf-footer">
    <span>ICCR Alumni Portal — Alumni Data Export</span>
    <span>{{ now()->format('d M Y H:i') }} · {{ number_format(count($records)) }} records</span>
</div>

</body>
</html>