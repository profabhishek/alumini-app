@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Admin Dashboard')

@section('content')
<div class="adash">

    {{-- ── Header ── --}}
    <div class="adash__header">
        <div>
            <h1 class="adash__title">Dashboard</h1>
            <p class="adash__subtitle">Platform overview &amp; activity analytics</p>
        </div>
        <button class="adash__export-btn" id="exportPdf" onclick="exportDashboardPdf()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export PDF
        </button>

        {{-- Range filter --}}
        <div class="adash__filters" id="adashFilters">
            <div class="adash__range-pills">
                <button class="adash__pill adash__pill--active" data-range="7d">7 Days</button>
                <button class="adash__pill" data-range="4w">4 Weeks</button>
                <button class="adash__pill" data-range="1y">1 Year</button>
                <button class="adash__pill" data-range="custom" id="customPill">Custom</button>
            </div>
            <div class="adash__custom-dates" id="customDates" style="display:none;">
                <input type="date" id="dateFrom" class="adash__date-input" />
                <span class="adash__date-sep">→</span>
                <input type="date" id="dateTo" class="adash__date-input" />
                <button class="adash__apply-btn" id="applyCustom">Apply</button>
            </div>
        </div>
    </div>

    {{-- ── Period label ── --}}
    <div class="adash__period-label" id="periodLabel">Loading…</div>

    {{-- ── Stat cards (totals) ── --}}
    <div class="adash__cards" id="statCards">
        @foreach([
            ['id'=>'stat-alumni',     'label'=>'Total Alumni',      'icon'=>'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75', 'color'=>'blue'],
            ['id'=>'stat-pending',    'label'=>'Pending Approvals', 'icon'=>'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM19 8v6M22 11h-6', 'color'=>'orange'],
            ['id'=>'stat-posts',      'label'=>'Total Posts',       'icon'=>'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z', 'color'=>'teal'],
            ['id'=>'stat-events',     'label'=>'Events',            'icon'=>'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01', 'color'=>'purple'],
            ['id'=>'stat-jobs',       'label'=>'Job Posts',         'icon'=>'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z', 'color'=>'green'],
            ['id'=>'stat-stories',    'label'=>'Stories',           'icon'=>'M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 004 17V4h16v13H6.5', 'color'=>'pink'],
            ['id'=>'stat-groups',     'label'=>'Community Groups',  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'indigo'],
            ['id'=>'stat-newsletter', 'label'=>'Newsletter Subs',   'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color'=>'gold'],
        ] as $card)
        <div class="adash__card adash__card--{{ $card['color'] }}">
            <div class="adash__card-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $card['icon'] }}"/></svg>
            </div>
            <div class="adash__card-body">
                <div class="adash__card-val" id="{{ $card['id'] }}">—</div>
                <div class="adash__card-label">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Period activity strip ── --}}
    <div class="adash__period-strip" id="periodStrip">
        @foreach([
            ['id'=>'p-members',    'label'=>'New Members'],
            ['id'=>'p-posts',      'label'=>'New Posts'],
            ['id'=>'p-comments',   'label'=>'Comments'],
            ['id'=>'p-likes',      'label'=>'Likes'],
            ['id'=>'p-events',     'label'=>'New Events'],
            ['id'=>'p-jobs',       'label'=>'New Jobs'],
            ['id'=>'p-event-regs', 'label'=>'Event Registrations'],
            ['id'=>'p-job-apps',   'label'=>'Job Applications'],
            ['id'=>'p-newsletter', 'label'=>'New Subscribers'],
        ] as $p)
        <div class="adash__pstat">
            <div class="adash__pstat-val" id="{{ $p['id'] }}">—</div>
            <div class="adash__pstat-label">{{ $p['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Chart + Top Alumni ── --}}
    <div class="adash__bottom">

        {{-- Activity Chart --}}
        <div class="adash__chart-wrap">
            <div class="adash__section-head">
                <span class="adash__section-title">Activity Over Time</span>
                <div class="adash__legend" id="chartLegend">
                    <span class="adash__leg adash__leg--posts">Posts</span>
                    <span class="adash__leg adash__leg--members">Members</span>
                    <span class="adash__leg adash__leg--events">Events</span>
                    <span class="adash__leg adash__leg--jobs">Jobs</span>
                </div>
            </div>
            <div class="adash__chart-container">
                <canvas id="activityChart"></canvas>
                <div class="adash__chart-empty" id="chartEmpty" style="display:none;">No activity data for this period.</div>
            </div>
        </div>

        {{-- Top Alumni --}}
        <div class="adash__top-wrap">
            <div class="adash__section-head">
                <span class="adash__section-title">Most Active Alumni</span>
                <span class="adash__section-sub">in selected period</span>
            </div>
            <div class="adash__top-list" id="topAlumniList">
                <div class="adash__loading-rows">
                    @for($i=0;$i<5;$i++)
                    <div class="adash__skeleton-row"></div>
                    @endfor
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin-dashboard.css') }}?v=1">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(() => {
    'use strict';

    const STATS_URL = '{{ route('admin.dashboard.stats') }}';
    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── State ────────────────────────────────────────────────────────────
    let currentRange = '7d';
    let customFrom   = null;
    let customTo     = null;
    let chartInstance = null;

    // ── DOM refs ─────────────────────────────────────────────────────────
    const pills       = document.querySelectorAll('.adash__pill');
    const customDates = document.getElementById('customDates');
    const dateFrom    = document.getElementById('dateFrom');
    const dateTo      = document.getElementById('dateTo');
    const applyBtn    = document.getElementById('applyCustom');
    const periodLabel = document.getElementById('periodLabel');

    // ── Pill click ───────────────────────────────────────────────────────
    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('adash__pill--active'));
            pill.classList.add('adash__pill--active');
            currentRange = pill.dataset.range;
            if (currentRange === 'custom') {
                customDates.style.display = 'flex';
            } else {
                customDates.style.display = 'none';
                load();
            }
        });
    });

    applyBtn.addEventListener('click', () => {
        if (!dateFrom.value || !dateTo.value) return;
        customFrom = dateFrom.value;
        customTo   = dateTo.value;
        load();
    });

    // ── Fetch ────────────────────────────────────────────────────────────
    function load() {
        setLoading(true);
        const params = new URLSearchParams({ range: currentRange });
        if (currentRange === 'custom' && customFrom && customTo) {
            params.set('from', customFrom);
            params.set('to', customTo);
        }
        fetch(`${STATS_URL}?${params}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            credentials: 'same-origin',
        })
        .then(r => r.json())
        .then(render)
        .catch(() => setLoading(false));
    }

    // ── Render ───────────────────────────────────────────────────────────
    function render(d) {
        window._adashLastData = d; // for PDF export
        // totals
        setText('stat-alumni',     fmt(d.totals.alumni));
        setText('stat-pending',    fmt(d.totals.pending));
        setText('stat-posts',      fmt(d.totals.posts));
        setText('stat-events',     fmt(d.totals.events));
        setText('stat-jobs',       fmt(d.totals.jobs));
        setText('stat-stories',    fmt(d.totals.stories));
        setText('stat-groups',     fmt(d.totals.groups));
        setText('stat-newsletter', fmt(d.totals.newsletter));

        // period strip
        setText('p-members',    fmt(d.period.new_members));
        setText('p-posts',      fmt(d.period.new_posts));
        setText('p-comments',   fmt(d.period.new_comments));
        setText('p-likes',      fmt(d.period.new_likes));
        setText('p-events',     fmt(d.period.new_events));
        setText('p-jobs',       fmt(d.period.new_jobs));
        setText('p-event-regs', fmt(d.period.event_regs));
        setText('p-job-apps',   fmt(d.period.job_apps));
        setText('p-newsletter', fmt(d.period.new_newsletter));

        // period label
        periodLabel.textContent = `${d.range.start}  →  ${d.range.end}`;

        // chart
        renderChart(d.chart);

        // top alumni
        renderTop(d.top_alumni);

        setLoading(false);
    }

    // ── Chart ────────────────────────────────────────────────────────────
    function renderChart(chart) {
        const canvas = document.getElementById('activityChart');
        const empty  = document.getElementById('chartEmpty');

        const hasData = chart.series.posts.some(v => v > 0)
                     || chart.series.members.some(v => v > 0)
                     || chart.series.events.some(v => v > 0)
                     || chart.series.jobs.some(v => v > 0);

        if (!hasData) {
            canvas.style.display = 'none';
            empty.style.display  = 'flex';
            if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
            return;
        }
        canvas.style.display = 'block';
        empty.style.display  = 'none';

        const datasets = [
            { label: 'Posts',   data: chart.series.posts,   borderColor: '#e8640c', backgroundColor: 'rgba(232,100,12,0.10)', tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2.5 },
            { label: 'Members', data: chart.series.members, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)',  tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2.5 },
            { label: 'Events',  data: chart.series.events,  borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)', tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2.5 },
            { label: 'Jobs',    data: chart.series.jobs,    borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2.5 },
        ];

        if (chartInstance) {
            chartInstance.data.labels   = chart.labels;
            chartInstance.data.datasets = datasets;
            chartInstance.update('active');
            return;
        }

        chartInstance = new Chart(canvas, {
            type: 'line',
            data: { labels: chart.labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1c2331',
                        titleColor: '#fff',
                        bodyColor: 'rgba(255,255,255,0.75)',
                        padding: 12,
                        cornerRadius: 10,
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                    },
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { color: '#8a92a0', font: { size: 11 }, maxTicksLimit: 12 },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { color: '#8a92a0', font: { size: 11 }, precision: 0 },
                    },
                },
            },
        });
    }

    // ── Top Alumni ───────────────────────────────────────────────────────
    function renderTop(list) {
        const el = document.getElementById('topAlumniList');
        if (!list || !list.length) {
            el.innerHTML = '<p class="adash__top-empty">No activity in this period.</p>';
            return;
        }
        const max = list[0]?.activity || 1;
        el.innerHTML = list.map((u, i) => `
            <div class="adash__top-row">
                <div class="adash__top-rank">${i + 1}</div>
                <div class="adash__top-avatar">${u.photo
                    ? `<img src="${u.photo}" alt="">`
                    : `<span>${u.name.charAt(0).toUpperCase()}</span>`}
                </div>
                <div class="adash__top-info">
                    <div class="adash__top-name">${esc(u.name)}</div>
                    <div class="adash__top-bar-wrap">
                        <div class="adash__top-bar" style="width:${Math.round((u.activity/max)*100)}%"></div>
                    </div>
                </div>
                <div class="adash__top-meta">
                    <span class="adash__top-chip adash__top-chip--post">${u.posts}P</span>
                    <span class="adash__top-chip adash__top-chip--cmt">${u.comments}C</span>
                </div>
            </div>
        `).join('');
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }
    function fmt(n) {
        if (n == null) return '—';
        if (n >= 1000000) return (n/1000000).toFixed(1) + 'M';
        if (n >= 1000)    return (n/1000).toFixed(1) + 'K';
        return n.toString();
    }
    function esc(s) {
        return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function setLoading(on) {
        document.getElementById('statCards').classList.toggle('adash__cards--loading', on);
        document.getElementById('periodStrip').classList.toggle('adash__strip--loading', on);
    }

    // ── Boot ─────────────────────────────────────────────────────────────
    load();
})();

// ── PDF Export ───────────────────────────────────────────────────────────
function exportDashboardPdf() {
    const d = window._adashLastData;
    const periodLabel = document.getElementById('periodLabel')?.textContent ?? '';
    const rangeLabel  = document.querySelector('.adash__pill--active')?.textContent ?? '';

    // Capture chart as image
    const chartCanvas = document.getElementById('activityChart');
    const chartImg    = chartCanvas ? chartCanvas.toDataURL('image/png') : null;

    const fmt = n => {
        if (n == null) return '—';
        if (n >= 1000000) return (n/1000000).toFixed(1)+'M';
        if (n >= 1000)    return (n/1000).toFixed(1)+'K';
        return String(n);
    };

    const statRows = d ? [
        ['Total Alumni',       fmt(d.totals.alumni)],
        ['Pending Approvals',  fmt(d.totals.pending)],
        ['Total Posts',        fmt(d.totals.posts)],
        ['Events',             fmt(d.totals.events)],
        ['Job Posts',          fmt(d.totals.jobs)],
        ['Stories',            fmt(d.totals.stories)],
        ['Community Groups',   fmt(d.totals.groups)],
        ['Newsletter Subs',    fmt(d.totals.newsletter)],
    ] : [];

    const periodRows = d ? [
        ['New Members',           fmt(d.period.new_members)],
        ['New Posts',             fmt(d.period.new_posts)],
        ['Comments',              fmt(d.period.new_comments)],
        ['Likes',                 fmt(d.period.new_likes)],
        ['New Events',            fmt(d.period.new_events)],
        ['New Jobs',              fmt(d.period.new_jobs)],
        ['Event Registrations',   fmt(d.period.event_regs)],
        ['Job Applications',      fmt(d.period.job_apps)],
        ['New Newsletter Subs',   fmt(d.period.new_newsletter)],
    ] : [];

    const topRows = (d?.top_alumni ?? []).map((u, i) =>
        `<tr><td>${i+1}</td><td>${u.name}</td><td>${u.posts}</td><td>${u.comments}</td><td>${u.activity}</td></tr>`
    ).join('');

    const html = `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard Report — ICCR Alumni</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; color: #1c2331; background: #fff; font-size: 13px; }
  .cover { background: #1c2331; color: #fff; padding: 40px 48px 32px; margin-bottom: 28px; }
  .cover h1 { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 6px; }
  .cover p { color: rgba(255,255,255,.6); font-size: 13px; }
  .cover .period { margin-top: 12px; background: rgba(255,255,255,.1); display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; }
  .section { padding: 0 48px; margin-bottom: 32px; }
  h2 { font-size: 15px; font-weight: 700; color: #1c2331; border-left: 3px solid #e8640c; padding-left: 10px; margin-bottom: 14px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #f6f4f0; color: #555c6b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: 8px 12px; text-align: left; border-bottom: 2px solid #eee; }
  td { padding: 8px 12px; border-bottom: 1px solid #f0ede6; color: #1c2331; }
  td.val { font-weight: 800; font-size: 15px; color: #e8640c; }
  .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
  .grid2 td, .grid2 th { border-right: 1px solid #f0ede6; }
  .chart-img { width: 100%; border-radius: 12px; border: 1px solid #eee; }
  .footer { padding: 20px 48px; font-size: 11px; color: #8a92a0; border-top: 1px solid #eee; margin-top: 8px; display: flex; justify-content: space-between; }
  @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="cover">
  <h1>ICCR Alumni — Dashboard Report</h1>
  <p>Admin Analytics Export &nbsp;·&nbsp; Generated ${new Date().toLocaleString('en-IN', {day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'})}</p>
  <div class="period">📅 ${rangeLabel} &nbsp;·&nbsp; ${periodLabel}</div>
</div>

<div class="section">
  <h2>Platform Totals (All-Time)</h2>
  <table class="grid2">
    <thead><tr><th>Metric</th><th>Count</th><th>Metric</th><th>Count</th></tr></thead>
    <tbody>
      ${statRows.reduce((acc, row, i) => {
        if (i % 2 === 0) acc.push(`<tr><td>${statRows[i][0]}</td><td class="val">${statRows[i][1]}</td>`);
        else acc[acc.length-1] += `<td>${statRows[i][0]}</td><td class="val">${statRows[i][1]}</td></tr>`;
        if (i === statRows.length - 1 && i % 2 === 0) acc[acc.length-1] += `<td></td><td></td></tr>`;
        return acc;
      }, []).join('')}
    </tbody>
  </table>
</div>

<div class="section">
  <h2>Period Activity (${rangeLabel})</h2>
  <table class="grid2">
    <thead><tr><th>Metric</th><th>Count</th><th>Metric</th><th>Count</th></tr></thead>
    <tbody>
      ${periodRows.reduce((acc, row, i) => {
        if (i % 2 === 0) acc.push(`<tr><td>${periodRows[i][0]}</td><td class="val">${periodRows[i][1]}</td>`);
        else acc[acc.length-1] += `<td>${periodRows[i][0]}</td><td class="val">${periodRows[i][1]}</td></tr>`;
        if (i === periodRows.length - 1 && i % 2 === 0) acc[acc.length-1] += `<td></td><td></td></tr>`;
        return acc;
      }, []).join('')}
    </tbody>
  </table>
</div>

${chartImg ? `<div class="section"><h2>Activity Chart</h2><img class="chart-img" src="${chartImg}"></div>` : ''}

${topRows ? `<div class="section">
  <h2>Most Active Alumni (${rangeLabel})</h2>
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Posts</th><th>Comments</th><th>Total Activity</th></tr></thead>
    <tbody>${topRows}</tbody>
  </table>
</div>` : ''}

<div class="footer">
  <span>ICCR Alumni Portal &mdash; Admin Dashboard</span>
  <span>Generated by Admin Panel &nbsp;·&nbsp; ${new Date().toLocaleDateString('en-IN')}</span>
</div>
</body>
</html>`;

    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 600);
}
</script>
@endpush
