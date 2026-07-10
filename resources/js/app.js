import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Chart from 'chart.js/auto';

const state = { charts: {}, refreshTimer: null };

function byId(id) { return document.getElementById(id); }
function qs(sel) { return document.querySelector(sel); }

function esc(v) {
    return String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

function fmtNum(v) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(v) || 0)); }
function fmtDec(v) { return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(v) || 0); }

// ── Sidebar Toggle ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = byId('sidebarToggle');
    const sidebar = qs('.sidebar');
    const overlay = qs('.sidebar-overlay');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    }
});

// ── Topbar Date ──────────────────────────────────────────
(function () {
    const el = byId('topbarCurrentDate');
    if (!el) return;
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const now = new Date();
    el.textContent = now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
})();

// ── Dashboard ────────────────────────────────────────────
function destroyChart(id) {
    if (state.charts[id]) { state.charts[id].destroy(); state.charts[id] = null; }
}

function setChartEmpty(id, visible) {
    const empty = qs(`[data-chart-empty="${id}"]`);
    const canvas = byId(id);
    if (empty) empty.hidden = !visible;
    if (canvas) canvas.hidden = visible;
}

function baseChartOpts() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 400 },
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: { label: ctx => `${ctx.dataset.label}: ${fmtNum(ctx.raw)}` },
            },
        },
    };
}

async function fetchJson(url) {
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const payload = await res.json().catch(() => null);
    if (!res.ok) {
        if (payload?.fallback) return payload.fallback;
        const err = payload?.errors ? Object.values(payload.errors).flat()[0] : null;
        throw new Error(err || payload?.error || payload?.message || 'Data gagal dimuat.');
    }
    return payload;
}

function sparklineSvg(data, color) {
    const pts = (data ?? []).filter(v => v != null);
    if (pts.length < 2) {
        return `<svg width="80" height="36" viewBox="0 0 80 36"><line x1="0" y1="18" x2="80" y2="18" stroke="${color}" stroke-width="1.5" stroke-dasharray="4 3" opacity=".4"/></svg>`;
    }
    const max = Math.max(...pts), min = Math.min(...pts), range = max - min || 1;
    const points = pts.map((v, i) => {
        const x = (i / (pts.length - 1)) * 74 + 3;
        const y = 33 - ((v - min) / range) * 28 + 1;
        return `${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(' ');
    return `<svg width="80" height="36" viewBox="0 0 80 36"><polyline points="${points}" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
}

function kpiIcon(satisfaction) {
    if (satisfaction >= 4) return 'bi-emoji-smile';
    if (satisfaction >= 3) return 'bi-emoji-neutral';
    return 'bi-emoji-frown';
}

function renderSummary(payload) {
    const kpi = payload.kpi ?? {};
    const trend = payload.charts?.monthly_sync_trend ?? [];
    const totalData = trend.map(t => Number(t.total) || 0);
    const rateData = trend.map(t => {
        const tot = Number(t.total) || 0;
        return tot > 0 ? +((Number(t.high) / tot) * 100).toFixed(1) : 0;
    });
    const highData = trend.map(t => Number(t.high) || 0);
    const deptRisk = payload.charts?.department_risk ?? payload.charts?.department_overview ?? [];
    const trainingCount = deptRisk.filter(d => Number(d.high) > 0).length;

    const satisfaction = Number(kpi.avg_job_satisfaction) || 0;
    const wlb = Number(kpi.avg_work_life_balance) || 0;
    const income = Number(kpi.avg_monthly_income) || 0;
    const hours = Number(kpi.avg_monthly_work_hours) || 0;
    const medium = Number(kpi.medium_risk) || 0;
    const topDept = kpi.top_department || '-';
    const topDeptRisk = Number(kpi.top_department_high_risk) || 0;
    const attritionRate = Number(kpi.high_risk_percentage ?? kpi.attrition_rate) || 0;

    const cards = [
        { label: 'Total Employees', value: fmtNum(kpi.total_employees), sub: 'Data aktif di MySQL', bg: '#eff6ff', color: '#3b82f6', spark: totalData, sparkC: '#3b82f6', icon: 'bi-people-fill' },
        { label: 'Attrition Rate', value: `${fmtDec(attritionRate)}%`, sub: 'High risk employees', bg: attritionRate > 20 ? '#fef2f2' : '#f0fdf4', color: attritionRate > 20 ? '#dc2626' : '#16a34a', spark: rateData, sparkC: '#16a34a', icon: 'bi-graph-up-arrow' },
        { label: 'High Risk', value: fmtNum(kpi.high_risk), sub: `${fmtNum(medium)} Medium | ${fmtNum(kpi.low_risk)} Low`, bg: '#f5f3ff', color: '#7c3aed', spark: highData, sparkC: '#7c3aed', icon: 'bi-exclamation-triangle-fill' },
        { label: 'Avg Satisfaction', value: fmtDec(satisfaction), sub: `${fmtDec(wlb)} Work-Life Balance`, bg: '#ecfdf5', color: '#059669', spark: totalData, sparkC: '#059669', icon: kpiIcon(satisfaction) },
        { label: 'Avg Monthly Income', value: `$${fmtNum(income)}`, sub: `${fmtNum(hours)} jam/bulan`, bg: '#fffbeb', color: '#d97706', spark: totalData, sparkC: '#d97706', icon: 'bi-cash-stack' },
        { label: `${esc(topDept)} (Top Risk)`, value: fmtNum(topDeptRisk), sub: 'Departemen prioritas tertinggi', bg: '#fef2f2', color: '#dc2626', spark: highData, sparkC: '#dc2626', icon: 'bi-building-exclamation' },
    ];

    const grid = byId('analyticsSummaryGrid');
    if (grid) {
        grid.innerHTML = cards.map(c => `
            <div class="kpi-card">
                <div class="kpi-icon" style="background:${c.bg};color:${c.color};"><i class="bi ${c.icon}"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">${esc(c.label)}</div>
                    <div class="kpi-value">${esc(c.value)}</div>
                    <div class="kpi-sub">${esc(c.sub)}</div>
                </div>
            </div>`).join('');
    }

    const lastSync = payload.sync?.last_sync ?? payload.metadata?.last_synced_at ?? '-';
    const updatedEl = byId('analyticsUpdatedAt');
    if (updatedEl) updatedEl.textContent = lastSync || '-';

    const syncEl = byId('lastSyncTime');
    if (syncEl) syncEl.textContent = lastSync || '-';

    const badge = byId('dashDbStatusBadge');
    if (badge) badge.hidden = !(Number(kpi.total_employees) > 0);

    renderDataStatus(payload);
    renderAlertBanner(payload);
    renderTrainingTable(payload);
}

function renderDataStatus(payload) {
    const status = byId('dashboardDataStatus');
    if (!status) return;
    const tot = Number(payload.kpi?.total_employees ?? 0);
    if (tot > 0) { status.hidden = true; return; }
    status.hidden = false;
    status.className = 'alert alert-warning d-flex align-items-center gap-2';
    status.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i><strong>Data dashboard belum lengkap.</strong> Pastikan .env memakai MySQL dan sumber data aktif.`;
}

function renderAlertBanner(payload) {
    const banner = byId('dashboardAlertBanner');
    const desc = byId('alertBannerDesc');
    if (!banner) return;
    const deptRisk = payload.charts?.department_risk ?? [];
    const below = deptRisk.filter(d => (Number(d.high) / (Number(d.total) || 1)) > 0.15);
    if (below.length === 0) { banner.hidden = true; return; }
    banner.hidden = false;
    if (desc) desc.textContent = `${below.length} departemen memiliki skor di bawah standar dan memerlukan training tambahan.`;
}

function renderTrainingTable(payload) {
    const tbody = byId('trainingTableBody');
    if (!tbody) return;
    const deptRisk = payload.charts?.department_risk ?? [];
    if (deptRisk.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-muted text-center py-3">Belum ada data departemen.</td></tr>`;
        return;
    }
    const trainingMap = {
        Sales: 'Sales Performance Enhancement',
        HR: 'HR Management Best Practices',
        IT: 'Technical Skills Development',
        Marketing: 'Digital Marketing & Analytics',
        Finance: 'Financial Risk Management',
        Operations: 'Operational Excellence',
        'R&D': 'Innovation & Research Skills',
    };
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const today = new Date();
    const rows = [...deptRisk]
        .map(d => ({ ...d, score: Math.round((1 - (Number(d.high) / (Number(d.total) || 1))) * 100 * 10) / 10 }))
        .sort((a, b) => a.score - b.score)
        .slice(0, 5);

    tbody.innerHTML = rows.map((d, i) => {
        const date = new Date(today);
        date.setDate(today.getDate() + 3 + i * 2);
        const ds = `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        const below = d.score < 75;
        const training = trainingMap[d.label] ?? `${d.label} Skills Development`;
        return `<tr>
            <td>${esc(d.label)}</td>
            <td class="${below ? 'text-danger fw-bold' : 'text-success fw-bold'}">${fmtDec(d.score)}</td>
            <td><span class="badge ${below ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success'}">${below ? 'Di Bawah Standar' : 'Baik'}</span></td>
            <td>${esc(training)}</td>
            <td style="white-space:nowrap;">${ds}</td>
            <td><button class="btn btn-sm btn-outline-primary" type="button">Jadwalkan</button></td>
        </tr>`;
    }).join('');
}

function renderRiskDonut(distribution) {
    const id = 'riskDonutChart';
    const items = distribution ?? [];
    destroyChart(id);
    setChartEmpty(id, items.length === 0);
    if (items.length === 0) return;
    const colors = ['#16a34a', '#f59e0b', '#dc2626'];
    const counts = items.map(i => Number(i.count) || 0);
    const tot = counts.reduce((a, b) => a + b, 0) || 1;

    state.charts[id] = new Chart(byId(id), {
        type: 'doughnut',
        data: {
            labels: items.map(i => i.label),
            datasets: [{ data: counts, backgroundColor: colors, borderColor: '#fff', borderWidth: 2 }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '64%',
            animation: { duration: 500 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${fmtNum(ctx.parsed)} (${((ctx.parsed / tot) * 100).toFixed(1)}%)`,
                    },
                },
            },
        },
    });

    const legend = byId('donutLegend');
    if (legend) {
        legend.innerHTML = items.map((item, i) => {
            const pct = ((Number(item.count) / tot) * 100).toFixed(1);
            return `<div class="donut-legend-item">
                <span class="donut-dot" style="background:${colors[i]}"></span>
                <span class="donut-lbl">${esc(item.label)}</span>
                <span class="donut-val">${fmtNum(item.count)} &middot; ${pct}%</span>
            </div>`;
        }).join('');
    }
}

function renderDeptOverview(rows) {
    const id = 'departmentOverviewChart';
    const items = rows ?? [];
    destroyChart(id);
    setChartEmpty(id, items.length === 0);
    if (items.length === 0) return;

    state.charts[id] = new Chart(byId(id), {
        type: 'bar',
        data: {
            labels: items.map(i => i.label),
            datasets: [
                { label: 'Low Risk', data: items.map(i => i.low), backgroundColor: 'rgba(22,163,74,.7)', borderColor: '#16a34a', borderWidth: 1 },
                { label: 'Medium Risk', data: items.map(i => i.medium), backgroundColor: 'rgba(217,119,6,.7)', borderColor: '#d97706', borderWidth: 1 },
                { label: 'High Risk', data: items.map(i => i.high), backgroundColor: 'rgba(220,38,38,.7)', borderColor: '#dc2626', borderWidth: 1 },
            ],
        },
        options: {
            ...baseChartOpts(),
            scales: {
                x: { stacked: true, ticks: { maxRotation: 20 } },
                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Employees' } },
            },
        },
    });
}

function renderTrendAttrition(rows) {
    const id = 'trendAttritionChart';
    const items = rows ?? [];
    destroyChart(id);
    setChartEmpty(id, items.length === 0);
    if (items.length === 0) return;

    const rates = items.map(item => {
        const tot = Number(item.total) || 0;
        return tot > 0 ? +((Number(item.high) / tot * 100).toFixed(2)) : 0;
    });

    state.charts[id] = new Chart(byId(id), {
        type: 'line',
        data: {
            labels: items.map(i => i.period),
            datasets: [{
                label: 'Attrition Rate (%)',
                data: rates,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.1)',
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 5,
                tension: 0.3,
                fill: true,
            }],
        },
        options: {
            ...baseChartOpts(),
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { beginAtZero: true, suggestedMax: 100, title: { display: true, text: 'Attrition Rate (%)' } },
            },
            plugins: {
                ...baseChartOpts().plugins,
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${fmtDec(ctx.raw)}%`,
                        afterBody: ctx => {
                            const item = items[ctx[0]?.dataIndex ?? 0] ?? {};
                            return [`High risk: ${fmtNum(item.high)}`, `Total: ${fmtNum(item.total)}`];
                        },
                    },
                },
            },
        },
    });
}

async function loadDashboard(showSuccess = false) {
    const message = byId('analyticsMessage');
    if (message) message.textContent = 'Memuat data...';
    const skeleton = byId('dashboardSkeleton');
    if (skeleton) skeleton.hidden = false;

    try {
        const [summaryData, chartsData] = await Promise.all([
            fetchJson('/api/hr-analytics'),
            fetchJson('/api/hr-analytics/charts').catch(() => ({ charts: {} })),
        ]);

        const payload = { ...summaryData, charts: chartsData.charts || summaryData.charts || {} };
        renderSummary(payload);

        requestAnimationFrame(() => {
            renderDeptOverview(payload.charts?.department_risk ?? []);
            renderTrendAttrition(payload.charts?.monthly_sync_trend ?? []);
            renderRiskDonut(payload.charts?.risk_distribution ?? []);
        });

        if (message) message.textContent = showSuccess ? 'Data berhasil dimuat.' : '';
    } catch (error) {
        if (message) message.textContent = error.message || 'Data gagal dimuat.';
        const status = byId('dashboardDataStatus');
        if (status) {
            status.hidden = false;
            status.className = 'alert alert-danger d-flex align-items-center gap-2';
            status.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i><strong>${esc(error.message || 'Gagal memuat dashboard.')}</strong>`;
        }
    } finally {
        if (skeleton) skeleton.hidden = true;
    }
}

function bootDashboard() {
    if (!qs('[data-dashboard]')) return;
    const refreshBtn = byId('refreshDashboardButton');
    if (refreshBtn) refreshBtn.addEventListener('click', () => loadDashboard(true));
    loadDashboard();
    state.refreshTimer = setInterval(() => loadDashboard(), 120000);
}

document.addEventListener('DOMContentLoaded', bootDashboard);
