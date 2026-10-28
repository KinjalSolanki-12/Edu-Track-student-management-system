<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();

$stmt = $pdo->prepare("SELECT t.*, c.course_name, c.course_code FROM timetable t
    JOIN courses c ON c.id = t.course_id
    JOIN enrollments e ON e.course_id = t.course_id AND e.student_id = :id
    ORDER BY t.start_time");
$stmt->execute(['id' => $studentId]);
$entries = $stmt->fetchAll();

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$grid = [];
foreach ($entries as $e) {
    $timeKey = substr($e['start_time'], 0, 5) . ' - ' . substr($e['end_time'], 0, 5);
    $grid[$timeKey][$e['day_of_week']] = $e;
}
ksort($grid);

$pageTitle = 'Timetable';
$extraCss = ['dashboard.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'timetable'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Weekly Timetable</h1></div>

        <div class="card">
            <?php if (empty($entries)): ?>
                <div class="empty-state"><div class="icon">🗓️</div>No timetable entries for your enrolled courses yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <div class="timetable-grid">
                        <div class="tt-head">Time</div>
                        <?php foreach ($days as $d): ?><div class="tt-head"><?= substr($d, 0, 3) ?></div><?php endforeach; ?>

                        <?php foreach ($grid as $timeKey => $dayEntries): ?>
                            <div class="tt-time"><?= $timeKey ?></div>
                            <?php foreach ($days as $d): ?>
                                <?php if (isset($dayEntries[$d])): $entry = $dayEntries[$d]; ?>
                                    <div class="tt-cell filled">
                                        <div class="tt-course"><?= sanitize($entry['course_code']) ?></div>
                                        <div class="tt-room">📍 <?= sanitize($entry['room']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="tt-cell"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
