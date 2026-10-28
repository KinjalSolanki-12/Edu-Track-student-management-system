/**
 * script.js - Global UI interactivity
 * Sidebar toggle, mobile navbar toggle, modal helpers, auto-hide alerts
 */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Mobile hamburger menu ----
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const topnavLinks = document.getElementById('topnavLinks');
    if (hamburgerBtn && topnavLinks) {
        hamburgerBtn.addEventListener('click', function () {
            topnavLinks.classList.toggle('open');
        });
    }

    // ---- Sidebar toggle (tablet/mobile) ----
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    // ---- Auto-hide flash alerts after 5s ----
    document.querySelectorAll('.alert[data-autohide]').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // ---- Password show/hide toggle ----
    document.querySelectorAll('.toggle-eye').forEach(function (eye) {
        eye.addEventListener('click', function () {
            const input = eye.previousElementSibling;
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                eye.textContent = input.type === 'password' ? '👁️' : '🙈';
            }
        });
    });

    // ---- Modal open/close (data-modal-target / data-modal-close) ----
    document.querySelectorAll('[data-modal-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.querySelector(btn.getAttribute('data-modal-target'));
            if (target) target.classList.add('open');
        });
    });
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.modal-overlay').classList.remove('open');
        });
    });
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    // ---- Confirm before destructive actions ----
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ---- File input preview (for image uploads) ----
    document.querySelectorAll('.js-image-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = input.getAttribute('data-preview');
            const preview = previewId ? document.getElementById(previewId) : null;
            if (preview && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => (preview.src = e.target.result);
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
});
