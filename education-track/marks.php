<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();

$stmt = $pdo->prepare("SELECT m.*, c.course_name, c.course_code FROM marks m
    JOIN courses c ON c.id = m.course_id WHERE m.student_id = :id ORDER BY c.course_name, m.exam_type");
$stmt->execute(['id' => $studentId]);
$records = $stmt->fetchAll();

$chartLabels = [];
$chartValues = [];
foreach ($records as $r) {
    $chartLabels[] = $r['course_code'] . ' (' . $r['exam_type'] . ')';
    $chartValues[] = round((float) $r['marks_obtained'], 1);
}

function gradeBadge(string $grade): string
{
    if (in_array($grade, ['A+', 'A'], true)) return 'badge-success';
    if (in_array($grade, ['B+', 'B'], true)) return 'badge-info';
    if ($grade === 'C') return 'badge-warning';
    return 'badge-danger';
}

$pageTitle = 'Marks & Results';
$extraCss = ['dashboard.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'marks'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Marks & Results</h1></div>

        <div class="card">
            <div class="card-title">Performance Overview</div>
            <?php if (empty($records)): ?>
                <div class="empty-state"><div class="icon">📊</div>No marks have been recorded yet.</div>
            <?php else: ?>
                <canvas id="marksResultChart" width="900" height="280"></canvas>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">Detailed Results</div>
            <?php if (empty($records)): ?>
                <div class="empty-state"><div class="icon">📄</div>No results to display.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Course</th><th>Exam Type</th><th>Marks Obtained</th><th>Total Marks</th><th>Percentage</th><th>Grade</th></tr></thead>
                        <tbody>
                        <?php foreach ($records as $r): ?>
                            <?php $pct = $r['total_marks'] > 0 ? round(($r['marks_obtained'] / $r['total_marks']) * 100, 1) : 0; ?>
                            <tr>
                                <td><?= sanitize($r['course_name']) ?> <span style="color:var(--text-muted);">(<?= sanitize($r['course_code']) ?>)</span></td>
                                <td><?= sanitize($r['exam_type']) ?></td>
                                <td><?= $r['marks_obtained'] ?></td>
                                <td><?= $r['total_marks'] ?></td>
                                <td><?= $pct ?>%</td>
                                <td><span class="badge <?= gradeBadge((string) $r['grade']) ?>"><?= sanitize((string) $r['grade']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = <?= json_encode($chartLabels) ?>;
    const values = <?= json_encode($chartValues) ?>;
    if (values.length && document.getElementById('marksResultChart')) {
        drawBarChart('marksResultChart', labels, values, { color: '#1e5eff' });
    }
});
</script>

<?php
$extraJs = ['chart.js'];
require_once __DIR__ . '/includes/footer.php';
?>
