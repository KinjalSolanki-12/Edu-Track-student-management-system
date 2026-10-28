/**
 * attendance.js - helper interactivity for the admin "mark attendance" table
 * Lets admin quickly toggle Present/Absent/Late per student row before submit.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.attendance-status-select').forEach(function (select) {
        select.addEventListener('change', function () {
            const row = select.closest('tr');
            if (!row) return;
            row.classList.remove('att-present', 'att-absent', 'att-late');
            if (select.value === 'Present') row.classList.add('att-present');
            if (select.value === 'Absent') row.classList.add('att-absent');
            if (select.value === 'Late') row.classList.add('att-late');
        });
    });

    const markAllPresentBtn = document.getElementById('markAllPresent');
    if (markAllPresentBtn) {
        markAllPresentBtn.addEventListener('click', function () {
            document.querySelectorAll('.attendance-status-select').forEach(function (select) {
                select.value = 'Present';
                select.dispatchEvent(new Event('change'));
            });
        });
    }
});
