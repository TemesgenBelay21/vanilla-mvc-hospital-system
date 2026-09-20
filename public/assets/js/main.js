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

        initSlotPicker();
    });

    /**
     * Appointment slot picker.
     * Shared by the booking form (department -> doctor -> date -> slot) and
     * the reschedule form (doctor/date -> slot). Requires this markup:
     *   select#department, select#doctor, input#date,
     *   div#slotList, input#start_time (hidden), button#submitBtn
     */
    function initSlotPicker() {
        var deptSel    = document.getElementById('department');
        var docSel     = document.getElementById('doctor');
        var dateInput  = document.getElementById('date');
        var slotList   = document.getElementById('slotList');
        var timeHidden = document.getElementById('start_time');
        var submitBtn  = document.getElementById('submitBtn');

        if (!docSel || !dateInput || !slotList || !timeHidden) {
            return;
        }

        function clearSlots() {
            slotList.innerHTML = '';
            timeHidden.value = '';
            if (submitBtn) submitBtn.disabled = true;
        }

        function resetDoctors() {
            docSel.innerHTML = '<option value="">— Choose a doctor —</option>';
            docSel.disabled = true;
            dateInput.value = '';
            dateInput.disabled = true;
            clearSlots();
        }

        function loadDoctors() {
            var departmentId = deptSel.value;
            if (!departmentId) return;
            resetDoctors();
            if (!submitBtn) submitBtn.disabled = true;

            fetch(window.APP_BASE + '/appointments/doctors?department_id=' + encodeURIComponent(departmentId), { headers: { 'X-Requested-With': 'fetch' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var opts = '<option value="">— Choose a doctor —</option>';
                    (data.doctors || []).forEach(function (d) {
                        opts += '<option value="' + d.id + '">Dr. ' + escapeHtml(d.name) + ' — ' + escapeHtml(d.specialization) + '</option>';
                    });
                    docSel.innerHTML = opts;
                    docSel.disabled = false;
                })
                .catch(function () {
                    slotList.innerHTML = '<p class="text-muted">Could not load doctors. Try again.</p>';
                });
        }

        function loadSlots() {
            var doctorId = docSel.value;
            var date     = dateInput.value;
            if (!doctorId || !date) return;
            clearSlots();

            fetch(window.APP_BASE + '/appointments/slots?doctor_id=' + encodeURIComponent(doctorId) + '&date=' + encodeURIComponent(date), { headers: { 'X-Requested-With': 'fetch' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var slots = data.slots || [];
                    if (!slots.length) {
                        slotList.innerHTML = '<p class="text-muted">No available slots for this doctor on that date.</p>';
                        return;
                    }
                    slots.forEach(function (s) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'slot-chip';
                        b.textContent = s.label;
                        b.dataset.time = s.start;
                        b.addEventListener('click', function () {
                            slotList.querySelectorAll('.slot-chip').forEach(function (c) { c.classList.remove('active'); });
                            b.classList.add('active');
                            timeHidden.value = s.start;
                            if (submitBtn) submitBtn.disabled = false;
                        });
                        slotList.appendChild(b);
                    });
                })
                .catch(function () {
                    slotList.innerHTML = '<p class="text-muted">Could not load slots. Try again.</p>';
                });
        }

        if (deptSel) {
            deptSel.addEventListener('change', function () {
                resetDoctors();
                loadDoctors();
            });
        }
        docSel.addEventListener('change', function () {
            dateInput.value = '';
            dateInput.disabled = !docSel.value;
            clearSlots();
        });
        dateInput.addEventListener('change', loadSlots);
    }

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = String(s == null ? '' : s);
        return div.innerHTML;
    }
})();