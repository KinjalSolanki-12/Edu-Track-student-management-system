/**
 * dashboard.js - Renders dashboard charts using data injected from PHP
 * Expects a global `window.dashboardData` object set inline on the page, e.g.:
 * window.dashboardData = { attendanceLabels:[...], attendancePresent:[...], marksLabels:[...], marksValues:[...] };
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.dashboardData === 'undefined') return;
    const d = window.dashboardData;

    if (d.marksLabels && document.getElementById('marksChart')) {
        drawBarChart('marksChart', d.marksLabels, d.marksValues, { color: '#1e5eff' });
    }
    if (d.attendanceSummary && document.getElementById('attendanceDoughnut')) {
        drawDoughnutChart('attendanceDoughnut', d.attendanceSummary);
    }
    if (d.trendLabels && document.getElementById('trendChart')) {
        drawLineChart('trendChart', d.trendLabels, d.trendValues, { color: '#0ea5e9' });
    }
});
