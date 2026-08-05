/* Mithila Art Events – Checkout page */
(function ($) {
    'use strict';

    if (typeof mae_co_vars === 'undefined') return;

    var v           = mae_co_vars;
    var stripe      = null;
    var elements    = null;
    var payEl       = null;
    var stripeReady = false;
    var stripeInit  = false;

    var ticketPrice = parseFloat(v.event_price) || 0;

    // ── Quantity buttons ──────────────────────────────────────────────────────

    $(document).on('click', '#mae-co-qty-minus', function () {
        var qty = parseInt($('#mae-co-qty').val(), 10);
        if (qty > 1) { $('#mae-co-qty').val(qty - 1); refreshTotal(); }
    });

    $(document).on('click', '#mae-co-qty-plus', function () {
        var qty = parseInt($('#mae-co-qty').val(), 10);
        if (qty < 20) { $('#mae-co-qty').val(qty + 1); refreshTotal(); }
    });

    function refreshTotal() {
        var qty   = parseInt($('#mae-co-qty').val(), 10) || 1;
        var total = (ticketPrice * qty).toFixed(2);
        $('#mae-co-total').text('$' + total);

        if (v.gateway === 'stripe') {
            stripeReady = false;
            stripeInit  = false;
            initStripe();
        }
    }

    // ── Stripe ────────────────────────────────────────────────────────────────

    function initStripe() {
        if (typeof Stripe === 'undefined') {
            showMsg('Stripe.js failed to load. Please refresh and try again.', 'error');
            return;
        }
        if (!v.stripe_pub_key) {
            showMsg('Stripe is not configured. Please contact the site administrator.', 'error');
            return;
        }
        if (stripeInit || stripeReady) return;

        stripeInit = true;
        $('#mae-co-payment-element').html(
            '<div class="mae-pay-loading"><span class="mae-spinner mae-spinner-dark"></span> Setting up secure payment&hellip;</div>'
        );

        $.post(v.ajax_url, {
            action:   'mae_stripe_intent',
            nonce:    v.nonce,
            event_id: v.event_id,
            quantity: parseInt($('#mae-co-qty').val(), 10) || 1
        })
        .done(function (res) {
            stripeInit = false;
            if (!res.success) {
                $('#mae-co-stripe-error').html(res.data.message).show();
                $('#mae-co-payment-element').empty();
                return;
            }
            if (!stripe) stripe = Stripe(v.stripe_pub_key);
            elements = stripe.elements({ clientSecret: res.data.client_secret });
            if (payEl) payEl.destroy();
            $('#mae-co-payment-element').empty();
            payEl = elements.create('payment', { layout: 'tabs' });
            payEl.mount('#mae-co-payment-element');
            stripeReady = true;
        })
        .fail(function () {
            stripeInit = false;
            $('#mae-co-stripe-error').html('Could not connect to payment server. Please try again.').show();
            $('#mae-co-payment-element').empty();
        });
    }

    // ── PayPal ────────────────────────────────────────────────────────────────

    function initPayPal() {
        if (typeof paypal === 'undefined') return;

        paypal.Buttons({
            style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'pay' },

            createOrder: function () {
                return $.post(v.ajax_url, {
                    action:   'mae_paypal_order',
                    nonce:    v.nonce,
                    event_id: v.event_id,
                    quantity: parseInt($('#mae-co-qty').val(), 10) || 1
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
                    $('#mae-co-payment-id').val(res.data.payment_id);
                    submitForm();
                });
            },

            onError: function (err) { showMsg('PayPal error: ' + err, 'error'); }
        }).render('#mae-co-paypal-btn');
    }

    // ── Initialise payment on load for paid events ────────────────────────────

    if (v.event_type === 'paid') {
        if (v.gateway === 'stripe') {
            initStripe();
        } else if (v.gateway === 'paypal') {
            $(function () { initPayPal(); });
        }
    }

    // ── Attendance date validation ────────────────────────────────────────────

    function validateAttendDate() {
        var $input = $('#mae-attend-date');
        if (!$input.length) return true;

        var val = $input.val();
        var min = $input.attr('min');
        var max = $input.attr('max');
        var $err = $('#mae-date-error');

        if (!val || (min && val < min) || (max && val > max)) {
            $input.addClass('mae-input-error');
            $err.show();
            $input[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        $input.removeClass('mae-input-error');
        $err.hide();
        return true;
    }

    $(document).on('change', '#mae-attend-date', function () {
        var val = $(this).val();
        var min = $(this).attr('min');
        var max = $(this).attr('max');
        var $err = $('#mae-date-error');

        if (val && (!min || val >= min) && (!max || val <= max)) {
            $(this).removeClass('mae-input-error');
            $err.hide();
        } else if (val) {
            $(this).addClass('mae-input-error');
            $err.show();
        }
    });

    // ── Form submit ───────────────────────────────────────────────────────────

    $(document).on('submit', '#mae-checkout-form', function (e) {
        e.preventDefault();

        if (!validateAttendDate()) return;

        if (v.event_type === 'paid') {
            if (v.gateway === 'stripe') {
                handleStripe();
            } else if (v.gateway === 'paypal') {
                if (!$('#mae-co-payment-id').val()) {
                    showMsg('Please complete payment using the PayPal button above.', 'error');
                }
            } else {
                submitForm();
            }
        } else {
            submitForm();
        }
    });

    function handleStripe() {
        if (stripeInit) {
            showMsg('Payment is still loading — please wait a moment and try again.', 'error');
            return;
        }
        if (!stripe || !elements) {
            showMsg('Payment could not be initialised. Please refresh the page and try again.', 'error');
            return;
        }
        setLoading(true);

        stripe.confirmPayment({
            elements:      elements,
            confirmParams: { return_url: window.location.href },
            redirect:      'if_required'
        }).then(function (result) {
            if (result.error) {
                showMsg(result.error.message, 'error');
                setLoading(false);
                return;
            }
            $('#mae-co-payment-id').val(result.paymentIntent.id);
            submitForm();
        });
    }

    function submitForm() {
        setLoading(true);

        var fd = new FormData(document.getElementById('mae-checkout-form'));

        $.ajax({
            url:         v.ajax_url,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            success: function (res) {
                setLoading(false);
                if (res.success) {
                    showMsg(res.data.message, 'success');
                    document.getElementById('mae-checkout-form').reset();
                } else {
                    showMsg(res.data.message, 'error');
                }
            },
            error: function () {
                setLoading(false);
                showMsg('Something went wrong. Please try again.', 'error');
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    function setLoading(on) {
        $('#mae-co-submit').prop('disabled', on);
        $('#mae-co-submit-label').toggle(!on);
        $('#mae-co-spinner').toggle(on);
    }

    function showMsg(text, type) {
        var $el = $('#mae-co-msg');
        $el.removeClass('mae-msg-success mae-msg-error')
           .addClass('mae-msg-' + type)
           .html(text)
           .slideDown(200);
        if (type !== 'error') {
            setTimeout(function () { $el.slideUp(); }, 8000);
        }
    }

})(jQuery);
