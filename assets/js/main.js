/**
 * Page:      assets/js/main.js
 * Component: Global JavaScript Behaviours
 * Developer: Shreeman Bhandari
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── 1. Delete expense confirm dialog ────────────────────────────── */
    document.querySelectorAll('.form-delete').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this expense?\nThis cannot be undone.')) {
                form.submit();
            }
        });
    });

    /* ── 2. Close account confirm dialog ─────────────────────────────── */
    var closeForm = document.getElementById('form-close-account');
    if (closeForm) {
        closeForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to close your account?\nThis action cannot be reversed.')) {
                closeForm.submit();
            }
        });
    }

    /* ── 3. Mobile hamburger nav toggle ──────────────────────────────── */
    var hamburger = document.getElementById('nav-hamburger');
    var navLinks  = document.getElementById('nav-links');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            navLinks.classList.toggle('nav-open');
            hamburger.setAttribute(
                'aria-expanded',
                navLinks.classList.contains('nav-open') ? 'true' : 'false'
            );
        });
    }

    /* ── 4. Auto-hide flash messages after 4 seconds ─────────────────── */
    var flashes = document.querySelectorAll('.flash');
    flashes.forEach(function (flash) {
        setTimeout(function () {
            flash.style.opacity = '0';
            setTimeout(function () {
                if (flash.parentNode) {
                    flash.parentNode.removeChild(flash);
                }
            }, 400);
        }, 4000);
    });

    /* ── 5. Set max attribute on expense date inputs to today ─────────── */
    var today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('.expense-date-input').forEach(function (input) {
        input.setAttribute('max', today);
    });

});
