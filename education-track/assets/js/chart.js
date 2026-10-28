/**
 * chart.js - Minimal dependency-free canvas chart renderer
 * (Not the Chart.js library - a tiny custom implementation so the project
 * has zero external JS dependencies, as required.)
 *
 * Usage:
 *   drawBarChart('canvasId', ['Math','CS','Physics'], [78,92,65], { color: '#1e5eff' });
 *   drawLineChart('canvasId', ['Mon','Tue','Wed'], [80,90,70]);
 *   drawDoughnutChart('canvasId', { Present: 18, Absent: 3, Late: 2 });
 */

function drawBarChart(canvasId, labels, values, opts) {
    opts = opts || {};
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const padding = 40;
    const color = opts.color || '#1e5eff';
    const max = Math.max(...values, 10) * 1.15;

    ctx.clearRect(0, 0, W, H);
    ctx.strokeStyle = '#e2e8f0';
    ctx.beginPath();
    ctx.moveTo(padding, H - padding);
    ctx.lineTo(W - 10, H - padding);
    ctx.stroke();

    const barWidth = (W - padding - 20) / values.length * 0.6;
    const gap = (W - padding - 20) / values.length;

    values.forEach(function (val, i) {
        const barHeight = ((H - padding - 20) * val) / max;
        const x = padding + i * gap + (gap - barWidth) / 2;
        const y = H - padding - barHeight;

        const grad = ctx.createLinearGradient(0, y, 0, H - padding);
        grad.addColorStop(0, color);
        grad.addColorStop(1, '#0ea5e9');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.roundRect ? ctx.roundRect(x, y, barWidth, barHeight, 4) : ctx.rect(x, y, barWidth, barHeight);
        ctx.fill();

        ctx.fillStyle = '#1a2333';
        ctx.font = '11px Segoe UI';
        ctx.textAlign = 'center';
        ctx.fillText(val, x + barWidth / 2, y - 6);
        ctx.fillStyle = '#64748b';
        ctx.fillText(String(labels[i]).slice(0, 8), x + barWidth / 2, H - padding + 15);
    });
}

function drawLineChart(canvasId, labels, values, opts) {
    opts = opts || {};
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const padding = 40;
    const color = opts.color || '#1e5eff';
    const max = Math.max(...values, 10) * 1.15;

    ctx.clearRect(0, 0, W, H);
    ctx.strokeStyle = '#e2e8f0';
    ctx.beginPath();
    ctx.moveTo(padding, H - padding);
    ctx.lineTo(W - 10, H - padding);
    ctx.stroke();

    const stepX = (W - padding - 20) / (values.length - 1 || 1);

    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth = 2.5;
    values.forEach(function (val, i) {
        const x = padding + i * stepX;
        const y = H - padding - ((H - padding - 20) * val) / max;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();

    values.forEach(function (val, i) {
        const x = padding + i * stepX;
        const y = H - padding - ((H - padding - 20) * val) / max;
        ctx.beginPath();
        ctx.fillStyle = color;
        ctx.arc(x, y, 4, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#64748b';
        ctx.font = '11px Segoe UI';
        ctx.textAlign = 'center';
        ctx.fillText(String(labels[i]).slice(0, 8), x, H - padding + 15);
    });
}

function drawDoughnutChart(canvasId, dataObj) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const cx = W / 2, cy = H / 2;
    const radius = Math.min(W, H) / 2 - 10;
    const colors = ['#1e5eff', '#dc2626', '#ffb020', '#0ea5e9', '#16a34a'];

    const entries = Object.entries(dataObj);
    const total = entries.reduce((sum, [, v]) => sum + v, 0) || 1;

    ctx.clearRect(0, 0, W, H);
    let startAngle = -Math.PI / 2;

    entries.forEach(function ([label, value], i) {
        const sliceAngle = (value / total) * Math.PI * 2;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
        ctx.closePath();
        ctx.fillStyle = colors[i % colors.length];
        ctx.fill();
        startAngle += sliceAngle;
    });

    // inner hole for doughnut look
    ctx.beginPath();
    ctx.arc(cx, cy, radius * 0.55, 0, Math.PI * 2);
    ctx.fillStyle = '#ffffff';
    ctx.fill();

    ctx.fillStyle = '#1a2333';
    ctx.font = 'bold 14px Segoe UI';
    ctx.textAlign = 'center';
    ctx.fillText(total, cx, cy + 5);
}
