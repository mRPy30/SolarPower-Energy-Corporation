(function () {
    const endpoint = (window.SOLAR_APP_BASE || '') + 'fetch-order-status.php';
    const milestonesFallback = [
        {
            stage: 1,
            title: 'Order Confirmed',
            description: 'Payment verified via Maya/UnionBank'
        },
        {
            stage: 2,
            title: 'Processing',
            description: 'Solar equipment sorting & rigorous QC testing'
        },
        {
            stage: 3,
            title: 'Fleet Delivery',
            description: 'SolarPower Direct Fleet delivery van is en route'
        },
        {
            stage: 4,
            title: 'Delivered',
            description: 'Order successfully delivered'
        }
    ];

    function withJquery(callback, attempt) {
        if (window.jQuery) {
            callback(window.jQuery);
            return;
        }

        if ((attempt || 0) < 40) {
            window.setTimeout(function () {
                withJquery(callback, (attempt || 0) + 1);
            }, 100);
        }
    }

    function ensureModalBridge($) {
        if (!window.bootstrap || $.fn.modal) return;

        $.fn.modal = function (action) {
            return this.each(function () {
                const instance = bootstrap.Modal.getOrCreateInstance(this);
                if (action === 'show') instance.show();
                if (action === 'hide') instance.hide();
                if (action === 'toggle') instance.toggle();
            });
        };
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function money(value) {
        return 'PHP ' + (Number(value) || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function showModal($) {
        ensureModalBridge($);
        $('#orderTrackingModal').modal('show');
        window.setTimeout(function () {
            $('#orderTrackingReference').trigger('focus');
        }, 240);
    }

    function setLoading($, loading) {
        const $btn = $('#orderTrackingSubmit');
        $btn.prop('disabled', loading);
        $btn.html(loading ? '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Tracking' : 'Track');
    }

    function showMessage($, message, type) {
        const $box = $('#orderTrackingMessage');
        if (!message) {
            $box.removeClass('error ok').hide().text('');
            return;
        }

        $box.removeClass('error ok').addClass(type || 'error').text(message).fadeIn(160);
    }

    function renderSummary($, data) {
        const details = [
            data.customer_name ? escapeHtml(data.customer_name) : '',
            data.total_amount ? money(data.total_amount) : '',
            data.current_location ? 'Current location: ' + escapeHtml(data.current_location) : '',
            data.tracking_number ? 'Tracking no: ' + escapeHtml(data.tracking_number) : ''
        ].filter(Boolean);

        $('#orderTrackingSummary')
            .html(
                '<strong>' + escapeHtml(data.reference || '') + '</strong>' +
                '<span>' + details.join(' - ') + '</span>'
            )
            .fadeIn(160);
    }

    function renderStepper($, data) {
        const stage = Number(data.status_stage || data.status || 0);
        const milestones = Array.isArray(data.milestones) && data.milestones.length
            ? data.milestones
            : milestonesFallback.map(function (item) {
                return Object.assign({}, item, {
                    active: stage >= item.stage,
                    current: stage === item.stage,
                    timestamp: ''
                });
            });

        const html = milestones.map(function (item) {
            const active = item.active || stage >= Number(item.stage);
            const current = item.current || stage === Number(item.stage);
            const classes = [
                'tracking-step',
                active ? 'active' : '',
                current ? 'current' : ''
            ].filter(Boolean).join(' ');

            return '' +
                '<div class="' + classes + '">' +
                    '<div class="tracking-step-marker">' + escapeHtml(item.stage) + '</div>' +
                    '<div class="tracking-step-card">' +
                        '<div class="tracking-step-title">' + escapeHtml(item.title) + '</div>' +
                        '<div class="tracking-step-description">' + escapeHtml(item.description) + '</div>' +
                        '<div class="tracking-step-time">' + escapeHtml(item.timestamp || 'Timestamp pending staff update') + '</div>' +
                    '</div>' +
                '</div>';
        }).join('');

        $('#orderTrackingPlaceholder').hide();
        $('#orderTrackingTimeline').html(html).hide().slideDown(220);
    }

    function trackReference($, reference) {
        const ref = String(reference || $('#orderTrackingReference').val() || '').trim();
        if (!ref) {
            showMessage($, 'Please enter your order reference number.', 'error');
            $('#orderTrackingTimeline, #orderTrackingSummary').hide();
            $('#orderTrackingPlaceholder').fadeIn(160);
            return;
        }

        $('#orderTrackingReference').val(ref.toUpperCase());
        showMessage($, '', 'ok');
        setLoading($, true);

        $.ajax({
            url: endpoint,
            method: 'POST',
            dataType: 'json',
            data: {
                order_reference: ref
            }
        })
            .done(function (response) {
                if (!response || !response.success) {
                    showMessage($, response && response.message ? response.message : 'Order reference could not be found.', 'error');
                    $('#orderTrackingTimeline, #orderTrackingSummary').hide();
                    $('#orderTrackingPlaceholder').fadeIn(160);
                    return;
                }

                showMessage($, 'Order found. Here is your latest progress.', 'ok');
                renderSummary($, response);
                renderStepper($, response);
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to check this order right now. Please try again.';
                showMessage($, message, 'error');
                $('#orderTrackingTimeline, #orderTrackingSummary').hide();
                $('#orderTrackingPlaceholder').fadeIn(160);
            })
            .always(function () {
                setLoading($, false);
            });
    }

    function referenceFromTrigger($, trigger) {
        const $trigger = $(trigger);
        const direct = $trigger.data('trackOrderRef') ||
            $trigger.data('orderReference') ||
            $trigger.attr('data-track-order-ref') ||
            $trigger.attr('data-order-reference');

        if (direct) return direct;

        const $row = $trigger.closest('tr, .order-row, .tracking-card, .order-card');
        const rowData = $row.data('orderReference') || $row.attr('data-order-reference');
        if (rowData) return rowData;

        const $referenceNode = $row.find('[data-order-reference], .order-reference, .order-ref, .tracking-order-reference').first();
        return $referenceNode.data('orderReference') || $referenceNode.attr('data-order-reference') || $referenceNode.text();
    }

    withJquery(function ($) {
        $(function () {
            ensureModalBridge($);

            $(document).on('click', '#openOrderTracking, .js-open-order-tracking', function (event) {
                event.preventDefault();
                showModal($);
            });

            $(document).on('submit', '#orderTrackingForm', function (event) {
                event.preventDefault();
                trackReference($);
            });

            $(document).on('click', '#orderTrackingSubmit', function (event) {
                event.preventDefault();
                trackReference($);
            });

            $(document).on('click', '[data-track-order-ref], .js-track-order-reference, .view-full-order-details', function (event) {
                const reference = referenceFromTrigger($, this);
                if (!reference) return;

                event.preventDefault();
                $('#orderTrackingReference').val(String(reference).trim());
                showModal($);
                trackReference($, reference);
            });

            $('#orderTrackingModal').on('shown.bs.modal', function () {
                if (!$('#orderTrackingReference').val().trim()) {
                    $('#orderTrackingSummary, #orderTrackingTimeline').hide();
                    $('#orderTrackingPlaceholder').show();
                    showMessage($, '', 'ok');
                }
            });
        });
    });
})();
