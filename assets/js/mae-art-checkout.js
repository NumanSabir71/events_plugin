/* Mithila Art Events – Art piece checkout */
(function ($) {
    'use strict';

    if (typeof mae_art_vars === 'undefined') return;

    var v           = mae_art_vars;
    var stripe      = null;
    var elements    = null;
    var payEl       = null;
    var stripeReady = false;

    // ── Init Stripe on page load ──────────────────────────────────────────────

    function initStripe() {
        if (typeof Stripe === 'undefined') {
            showMsg('Stripe.js failed to load. Please refresh the page.', 'error');
            return;
        }
        if (!v.stripe_pub_key) {
            showMsg('Payment is not configured. Please contact the administrator.', 'error');
            return;
        }

        $('#mae-art-payment-element').html(
            '<div class="mae-pay-loading"><span class="mae-spinner mae-spinner-dark"></span> Setting up secure payment&hellip;</div>'
        );

        $.post(v.ajax_url, {
            action:     'mae_art_stripe_intent',
            nonce:      v.nonce,
            event_id:   v.event_id,
            artist_idx: v.artist_idx,
            art_id:     v.art_id,
        })
        .done(function (res) {
            if (!res.success) {
                $('#mae-art-stripe-error').html(res.data.message).show();
                $('#mae-art-payment-element').empty();
                return;
            }
            if (!stripe) stripe = Stripe(v.stripe_pub_key);
            elements = stripe.elements({ clientSecret: res.data.client_secret });
            if (payEl) payEl.destroy();
            $('#mae-art-payment-element').empty();
            payEl = elements.create('payment', { layout: 'tabs' });
            payEl.mount('#mae-art-payment-element');
            stripeReady = true;
        })
        .fail(function () {
            $('#mae-art-stripe-error').html('Could not connect to payment server. Please try again.').show();
            $('#mae-art-payment-element').empty();
        });
    }

    $(function () { initStripe(); });

    // ── Form submit ───────────────────────────────────────────────────────────

    $(document).on('submit', '#mae-art-checkout-form', function (e) {
        e.preventDefault();

        if (!stripeReady || !stripe || !elements) {
            showMsg('Payment is still loading — please wait a moment and try again.', 'error');
            return;
        }

        setLoading(true);

        stripe.confirmPayment({
            elements:      elements,
            confirmParams: { return_url: window.location.href },
            redirect:      'if_required',
        }).then(function (result) {
            if (result.error) {
                showMsg(result.error.message, 'error');
                setLoading(false);
                return;
            }
            $('#mae-art-payment-id').val(result.paymentIntent.id);
            submitForm();
        });
    });

    function submitForm() {
        var fd = new FormData(document.getElementById('mae-art-checkout-form'));

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
                    document.getElementById('mae-art-checkout-form').reset();
                    $('#mae-art-payment-element').empty();
                    stripeReady = false;
                } else {
                    showMsg(res.data.message, 'error');
                }
            },
            error: function () {
                setLoading(false);
                showMsg('Something went wrong. Please try again.', 'error');
            },
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    function setLoading(on) {
        $('#mae-art-submit').prop('disabled', on);
        $('#mae-art-submit-label').toggle(!on);
        $('#mae-art-spinner').toggle(on);
    }

    function showMsg(text, type) {
        var $el = $('#mae-art-msg');
        $el.removeClass('mae-msg-success mae-msg-error')
           .addClass('mae-msg-' + type)
           .html(text)
           .slideDown(200);
        if (type !== 'error') {
            setTimeout(function () { $el.slideUp(); }, 9000);
        }
    }

})(jQuery);
