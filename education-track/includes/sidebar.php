<?php
/**
 * Sidebar navigation.
 * Expects: $sidebarType = 'student' | 'admin'
 *          $activePage  = string key matching the current page (for highlighting)
 */
$sidebarType = $sidebarType ?? 'student';
$activePage  = $activePage ?? '';

$studentLinks = [
    'dashboard'     => ['icon' => '🏠', 'label' => 'Dashboard',      'href' => BASE_URL . '/dashboard.php'],
    'profile'       => ['icon' => '👤', 'label' => 'Profile',        'href' => BASE_URL . '/profile.php'],
    'courses'       => ['icon' => '📚', 'label' => 'Courses',        'href' => BASE_URL . '/courses.php'],
    'attendance'    => ['icon' => '📅', 'label' => 'Attendance',     'href' => BASE_URL . '/attendance.php'],
    'marks'         => ['icon' => '📊', 'label' => 'Marks & Results','href' => BASE_URL . '/marks.php'],
    'assignments'   => ['icon' => '📝', 'label' => 'Assignments',    'href' => BASE_URL . '/assignments.php'],
    'timetable'     => ['icon' => '🗓️', 'label' => 'Timetable',      'href' => BASE_URL . '/timetable.php'],
    'notifications' => ['icon' => '🔔', 'label' => 'Notifications',  'href' => BASE_URL . '/notifications.php'],
];

$adminLinks = [
    'dashboard'     => ['icon' => '🏠', 'label' => 'Dashboard',        'href' => BASE_URL . '/admin/dashboard.php'],
    'students'      => ['icon' => '🎓', 'label' => 'Manage Students',  'href' => BASE_URL . '/admin/students.php'],
    'teachers'      => ['icon' => '🧑‍🏫', 'label' => 'Manage Teachers', 'href' => BASE_URL . '/admin/teachers.php'],
    'courses'       => ['icon' => '📚', 'label' => 'Courses & Subjects','href' => BASE_URL . '/admin/courses.php'],
    'attendance'    => ['icon' => '📅', 'label' => 'Attendance',       'href' => BASE_URL . '/admin/attendance.php'],
    'marks'         => ['icon' => '📊', 'label' => 'Marks & Results',  'href' => BASE_URL . '/admin/marks.php'],
    'assignments'   => ['icon' => '📝', 'label' => 'Assignments',      'href' => BASE_URL . '/admin/assignments.php'],
    'timetable'     => ['icon' => '🗓️', 'label' => 'Timetable',        'href' => BASE_URL . '/admin/timetable.php'],
    'notifications' => ['icon' => '🔔', 'label' => 'Notifications',    'href' => BASE_URL . '/admin/notifications.php'],
    'settings'      => ['icon' => '⚙️', 'label' => 'Settings',         'href' => BASE_URL . '/admin/settings.php'],
];

$links = $sidebarType === 'admin' ? $adminLinks : $studentLinks;
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="sidebar-title"><?= $sidebarType === 'admin' ? 'Admin Panel' : 'Student Panel' ?></span>
    </div>
    <nav class="sidebar-links">
        <?php foreach ($links as $key => $link): ?>
            <a href="<?= $link['href'] ?>" class="sidebar-link <?= $activePage === $key ? 'active' : '' ?>">
                <span class="sidebar-icon"><?= $link['icon'] ?></span>
                <span><?= sanitize($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
