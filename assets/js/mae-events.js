/* Mithila Art Events – popup triggers + volunteer/sponsor form submission */
(function ($) {
    'use strict';

    if (typeof mae_vars === 'undefined') return;

    var v = mae_vars;

    // ── Generic popup open ────────────────────────────────────────────────────

    $(document).on('click', '.mae-popup-trigger', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn    = $(this);
        var target  = $btn.data('target');
        if (target) {
            var $overlay   = $(target);
            var eventId    = $btn.data('event-id');
            var eventName  = $btn.data('event-name') || '';

            // Inject event context when triggered from an event-list shortcode
            if (eventId) {
                $overlay.find('input[name="event_id"]').val(eventId);
            }
            if (eventName) {
                $overlay.find('.mae-popup-event-name').text(' – ' + eventName);
                $overlay.find('.mae-popup-event-label').text(eventName);
            }

            $overlay.fadeIn(200).attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        }
    });

    // ── Generic popup close ───────────────────────────────────────────────────

    $(document).on('click', '.mae-popup-close', function () {
        closePopup($(this).closest('.mae-popup-overlay'));
    });

    $(document).on('click', '.mae-popup-overlay', function (e) {
        if ($(e.target).is('.mae-popup-overlay')) {
            closePopup($(this));
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closePopup($('.mae-popup-overlay:visible'));
        }
    });

    function closePopup($overlay) {
        $overlay.fadeOut(200).attr('aria-hidden', 'true');
        $('body').css('overflow', '');
        // Clear dynamic event fields so a re-opened popup starts fresh
        $overlay.find('.mae-popup-event-name').text('');
        $overlay.find('.mae-popup-event-label').text('');
    }

    // ── File upload label update ──────────────────────────────────────────────

    $(document).on('change', '.mae-spon-logo-input', function () {
        var name = this.files[0] ? this.files[0].name : 'Upload logo (PNG, JPG, SVG)';
        $(this).closest('.mae-file-zone').find('.mae-file-label-text').text(name);
    });

    // ── Volunteer form submit ─────────────────────────────────────────────────

    $(document).on('submit', '.mae-vol-form', function (e) {
        e.preventDefault();
        actualSubmit($(this));
    });

    // ── Sponsor form: amount input → init payment ─────────────────────────────

    // Per-form payment state stored on the form DOM node
    var sponTimer = null;

    $(document).on('input', '.mae-spon-amount-input', function () {
        var $form   = $(this).closest('.mae-spon-form');
        var amount  = parseFloat($(this).val()) || 0;
        var $wrap   = $form.find('.mae-spon-payment-wrap');
        var gateway = $form.data('gateway') || v.gateway;

        clearTimeout(sponTimer);
        resetSponPayment($form);

        if (amount >= 1) {
            $wrap.slideDown(220);
            // Update submit label to reflect payment
            $form.find('.mae-submit-label').text('Pay $' + amount.toFixed(2) + ' & Submit');

            sponTimer = setTimeout(function () {
                if (gateway === 'stripe') initSponStripe($form, amount);
                else if (gateway === 'paypal') initSponPayPal($form, amount);
            }, 700);
        } else {
            $wrap.slideUp(220);
            $form.find('.mae-submit-label').text('Send Partnership Enquiry');
        }
    });

    function resetSponPayment($form) {
        var state = getSponState($form);
        state.ready = false;
        state.init  = false;
        state.ppDone = false;
        if (state.payEl) { state.payEl.destroy(); state.payEl = null; }
        state.stripe   = null;
        state.elements = null;
        $form.find('.mae-spon-payment-element').empty();
        $form.find('.mae-spon-paypal-btn').empty();
        $form.find('.mae-spon-stripe-error').hide().empty();
        $form.find('.mae-spon-payment-id').val('');
    }

    // Lightweight per-form state stored as a jQuery data object
    function getSponState($form) {
        if (!$form.data('_spon_state')) {
            $form.data('_spon_state', {
                stripe: null, elements: null, payEl: null,
                ready: false, init: false, ppDone: false
            });
        }
        return $form.data('_spon_state');
    }

    function initSponStripe($form, amount) {
        if (typeof Stripe === 'undefined' || !v.stripe_pub_key) {
            $form.find('.mae-spon-stripe-error').html('Stripe is not configured. Please contact the site administrator.').show();
            return;
        }
        var state = getSponState($form);
        if (state.init || state.ready) return;

        state.init = true;
        $form.find('.mae-spon-payment-element').html(
            '<div class="mae-pay-loading"><span class="mae-spinner mae-spinner-dark"></span> Setting up secure payment&hellip;</div>'
        );

        $.post(v.ajax_url, {
            action:               'mae_stripe_intent',
            nonce:                v.nonce,
            event_id:             0,
            custom_amount_cents:  Math.round(amount * 100)
        })
        .done(function (res) {
            state.init = false;
            if (!res.success) {
                $form.find('.mae-spon-stripe-error').html(res.data.message).show();
                $form.find('.mae-spon-payment-element').empty();
                return;
            }
            if (!state.stripe) state.stripe = Stripe(v.stripe_pub_key);
            state.elements = state.stripe.elements({ clientSecret: res.data.client_secret });
            if (state.payEl) state.payEl.destroy();
            $form.find('.mae-spon-payment-element').empty();
            state.payEl = state.elements.create('payment', { layout: 'tabs' });
            state.payEl.mount($form.find('.mae-spon-payment-element')[0]);
            state.ready = true;
        })
        .fail(function () {
            state.init = false;
            $form.find('.mae-spon-stripe-error').html('Could not connect to payment server. Please try again.').show();
            $form.find('.mae-spon-payment-element').empty();
        });
    }

    function initSponPayPal($form, amount) {
        if (typeof paypal === 'undefined') return;
        var $btn = $form.find('.mae-spon-paypal-btn');
        if (!$btn.length || $btn.children().length) return;
        var state = getSponState($form);

        paypal.Buttons({
            style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'pay' },
            createOrder: function () {
                return $.post(v.ajax_url, {
                    action:        'mae_paypal_order',
                    nonce:         v.nonce,
                    event_id:      0,
                    custom_amount: amount
                }).then(function (res) {
                    if (!res.success) throw new Error(res.data.message);
                    return res.data.order_id;
                });
            },
            onApprove: function (data) {
                return $.post(v.ajax_url, {
                    action:   'mae_paypal_capture',
                    nonce:    v.nonce,
                    order_id: data.orderID
                }).then(function (res) {
                    if (!res.success) throw new Error(res.data.message);
                    state.ppDone = true;
                    $form.find('.mae-spon-payment-id').val(res.data.payment_id);
                    actualSubmit($form);
                });
            },
            onError: function (err) {
                formMsg($form.find('.mae-form-msg'), 'PayPal error: ' + err, 'error');
            }
        }).render($btn[0]);
    }

    // ── Sponsor form submit ───────────────────────────────────────────────────

    $(document).on('submit', '.mae-spon-form', function (e) {
        e.preventDefault();
        var $form   = $(this);
        var gateway = $form.data('gateway') || v.gateway;
        var amount  = parseFloat($form.find('.mae-spon-amount-input').val()) || 0;
        var state   = getSponState($form);

        if (amount >= 1) {
            if (gateway === 'stripe') {
                handleSponStripe($form);
                return;
            }
            if (gateway === 'paypal' && !state.ppDone) {
                formMsg($form.find('.mae-form-msg'), 'Please complete payment using the PayPal button above.', 'error');
                return;
            }
        }

        actualSubmit($form);
    });

    function handleSponStripe($form) {
        var state = getSponState($form);
        var $msg  = $form.find('.mae-form-msg');

        if (state.init) {
            formMsg($msg, 'Payment is still loading — please wait a moment and try again.', 'error');
            return;
        }
        if (!state.stripe || !state.elements) {
            formMsg($msg, 'Payment is not ready. Please enter your contribution amount above.', 'error');
            return;
        }

        setFormLoading($form, true);

        state.stripe.confirmPayment({
            elements:      state.elements,
            confirmParams: { return_url: window.location.href },
            redirect:      'if_required'
        }).then(function (result) {
            if (result.error) {
                setFormLoading($form, false);
                formMsg($msg, result.error.message, 'error');
                return;
            }
            $form.find('.mae-spon-payment-id').val(result.paymentIntent.id);
            actualSubmit($form);
        });
    }

    // ── Shared AJAX submit ────────────────────────────────────────────────────

    function actualSubmit($form) {
        var $overlay = $form.closest('.mae-popup-overlay');
        var $msg     = $form.find('.mae-form-msg');

        setFormLoading($form, true);
        $msg.hide().removeClass('mae-msg-success mae-msg-error');

        var fd = new FormData($form[0]);
        fd.set('action', 'mae_submit');
        fd.set('nonce',  v.nonce);

        $.ajax({
            url:         v.ajax_url,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            success: function (res) {
                setFormLoading($form, false);
                if (res.success) {
                    formMsg($msg, res.data.message, 'success');
                    $form[0].reset();
                    // Reset sponsor payment UI
                    if ($form.hasClass('mae-spon-form')) {
                        $form.find('.mae-spon-payment-wrap').hide();
                        $form.find('.mae-submit-label').text('Send Partnership Enquiry');
                        resetSponPayment($form);
                    }
                    setTimeout(function () {
                        $overlay.fadeOut(200).attr('aria-hidden', 'true');
                        $('body').css('overflow', '');
                        $msg.hide();
                    }, 4000);
                } else {
                    formMsg($msg, res.data.message, 'error');
                }
            },
            error: function () {
                setFormLoading($form, false);
                formMsg($msg, 'Something went wrong. Please try again.', 'error');
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    function setFormLoading($form, on) {
        $form.find('.mae-btn-submit').prop('disabled', on);
        $form.find('.mae-submit-label').toggle(!on);
        $form.find('.mae-submit-spinner').toggle(on);
    }

    function formMsg($el, text, type) {
        $el.removeClass('mae-msg-success mae-msg-error')
           .addClass('mae-msg-' + type)
           .html(text)
           .slideDown(200);
        if (type !== 'error') setTimeout(function () { $el.slideUp(); }, 6000);
    }

})(jQuery);
