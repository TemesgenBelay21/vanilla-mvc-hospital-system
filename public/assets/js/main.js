/* Hospital Management System — shared interactions */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Collapsible sidebar
        var toggle = document.getElementById('sidebarToggle');
        var app = document.getElementById('app');
        if (toggle && app) {
            toggle.addEventListener('click', function () {
                app.classList.toggle('collapsed');
            });
        }

        // Confirm destructive/status-changing actions triggered via forms
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var message = form.getAttribute('data-confirm');
                if (!window.confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    });
})();