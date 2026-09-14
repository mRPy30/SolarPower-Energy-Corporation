<link rel="stylesheet" href="assets/assessment-calendar.css">
<script src="assets/assessment-calendar.js" defer></script>
<label for="assessmentDate" class="form-label fw-semibold small text-uppercase">Preferred Assessment Date</label>
<div class="assessment-picker">
    <input type="text" id="assessmentDate" name="inspection_date" class="form-control" placeholder="YYYY-MM-DD" autocomplete="off" required aria-describedby="assessmentDateHelp" aria-haspopup="dialog" aria-expanded="false">
    <div class="assessment-calendar" role="dialog" aria-label="Choose assessment date" hidden>
        <div class="assessment-notice">
            <i class="fas fa-info-circle"></i> Bookings require min. 3 days advance notice. Sundays closed.
        </div>
        <div class="assessment-calendar-header">
            <button type="button" data-month-step="-1" aria-label="Previous month">&#8249;</button>
            <strong class="assessment-month" aria-live="polite"></strong>
            <button type="button" data-month-step="1" aria-label="Next month">&#8250;</button>
        </div>
        <div class="assessment-weekdays" aria-hidden="true"><span class="sun-header">Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
        <div class="assessment-days"></div>
        <div class="assessment-calendar-footer">
            <button type="button" data-clear-date class="btn-clear">Clear</button>
            <button type="button" data-close-calendar class="btn-close-cal">Close</button>
        </div>
    </div>
</div>
<small id="assessmentDateHelp" class="form-text text-muted">Book at least 3 days ahead. Sundays are unavailable.</small>
