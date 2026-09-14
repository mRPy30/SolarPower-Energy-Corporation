(() => {
    'use strict';
    
    // Format date as YYYY-MM-DD
    const format = date => `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, '0')}-${String(date.getUTCDate()).padStart(2, '0')}`;
    
    // Current date in Philippine Time (Asia/Manila)
    const currentDate = () => {
        const parts = new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
        const part = type => Number(parts.find(p => p.type === type).value);
        return new Date(Date.UTC(part('year'), part('month') - 1, part('day')));
    };
    
    // Minimum date allowed: Current date + 3 days gap
    const minimumDate = () => {
        const date = currentDate();
        date.setUTCDate(date.getUTCDate() + 3);
        return date;
    };
    
    const parse = value => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
        const date = new Date(`${value}T00:00:00Z`);
        return !Number.isNaN(date.getTime()) && format(date) === value ? date : null;
    };

    document.querySelectorAll('.assessment-picker').forEach(picker => {
        const input = picker.querySelector('input');
        const calendar = picker.querySelector('.assessment-calendar');
        const days = picker.querySelector('.assessment-days');
        const previous = picker.querySelector('[data-month-step="-1"]');
        let month;
        
        // Allowed rule: Date must be >= minDate AND NOT a Sunday (0)
        const allowed = date => date && date >= minimumDate() && date.getUTCDay() !== 0;
        
        // Set native min attribute on input for fallback/accessibility
        input.setAttribute('min', format(minimumDate()));
        
        const validate = () => {
            if (!input.value) {
                input.setCustomValidity('');
                return;
            }
            const date = parse(input.value);
            if (!date) {
                input.setCustomValidity('Please enter a valid date in YYYY-MM-DD format.');
            } else if (date < minimumDate()) {
                input.setCustomValidity('Assessments require at least 3 days advance booking from today.');
            } else if (date.getUTCDay() === 0) {
                input.setCustomValidity('Sundays are unavailable for assessments.');
            } else {
                input.setCustomValidity('');
            }
        };

        const close = () => {
            calendar.hidden = true;
            input.setAttribute('aria-expanded', 'false');
        };

        const render = () => {
            const today = currentDate();
            const minDate = minimumDate();
            // Earliest month user can view is the month of today (past months/years disabled)
            const earliestMonth = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), 1));
            
            if (!month || month < earliestMonth) month = earliestMonth;
            
            picker.querySelector('.assessment-month').textContent = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(month);
            
            // Disable previous button if at or before earliest month
            previous.disabled = month <= earliestMonth;
            
            days.replaceChildren();
            
            // Pad empty slots for weekday alignment
            for (let i = 0; i < month.getUTCDay(); i++) {
                days.append(document.createElement('span'));
            }
            
            const count = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth() + 1, 0)).getUTCDate();
            
            for (let day = 1; day <= count; day++) {
                const date = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth(), day));
                const value = format(date);
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = day;
                
                const isSunday = date.getUTCDay() === 0;
                const isTooSoon = date < minDate;
                const isAllowed = allowed(date);
                
                button.disabled = !isAllowed;
                
                if (isSunday) {
                    button.classList.add('day-sunday');
                    button.title = 'Sundays are unavailable for assessment';
                } else if (isTooSoon) {
                    button.classList.add('day-too-soon');
                    button.title = 'Requires at least 3 days advance booking';
                }
                
                button.setAttribute('aria-label', new Intl.DateTimeFormat('en-US', { dateStyle: 'full', timeZone: 'UTC' }).format(date));
                button.setAttribute('aria-pressed', String(input.value === value));
                
                if (value === format(today)) {
                    button.setAttribute('aria-current', 'date');
                    button.title = 'Today — unavailable (min 3 days advance booking)';
                }
                
                button.addEventListener('click', () => {
                    if (!isAllowed) { render(); return; }
                    input.value = value;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    close();
                    input.focus();
                });
                
                days.append(button);
            }
        };

        const open = () => {
            const selected = parse(input.value);
            const start = allowed(selected) ? selected : minimumDate();
            month = new Date(Date.UTC(start.getUTCFullYear(), start.getUTCMonth(), 1));
            render();
            calendar.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            calendar.style.left = '0px';
            
            // Constrain popover position within container / modal boundary
            const modalBody = picker.closest('.modal-body, .modal-content, .checkout-shell') || document.body;
            const containerRight = modalBody.getBoundingClientRect().right - 12;
            const calRight = calendar.getBoundingClientRect().right;
            const overflow = calRight - containerRight;
            if (overflow > 0) {
                calendar.style.left = `${-overflow}px`;
            }
        };

        input.addEventListener('click', open);
        input.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                open();
                (days.querySelector('button:not(:disabled)') || picker.querySelector('[data-month-step="1"]')).focus();
            }
        });
        input.addEventListener('input', validate);
        input.addEventListener('change', validate);
        
        picker.querySelectorAll('[data-month-step]').forEach(button => button.addEventListener('click', () => {
            month.setUTCMonth(month.getUTCMonth() + Number(button.dataset.monthStep));
            render();
        }));
        
        const clearBtn = picker.querySelector('[data-clear-date]');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                close();
                input.focus();
            });
        }
        
        const closeBtn = picker.querySelector('[data-close-calendar]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => { close(); input.focus(); });
        }
        
        picker.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !calendar.hidden) { event.preventDefault(); event.stopPropagation(); close(); input.focus(); }
        });
        picker.addEventListener('focusout', event => { if (!picker.contains(event.relatedTarget)) close(); });
        document.addEventListener('click', event => { if (!picker.contains(event.target)) close(); });
        
        if (input.form) {
            input.form.addEventListener('reset', close);
            input.form.addEventListener('submit', event => {
                validate();
                if (!input.checkValidity()) { event.preventDefault(); event.stopImmediatePropagation(); input.reportValidity(); }
            }, true);
        }
    });
})();
