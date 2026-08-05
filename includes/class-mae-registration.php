<?php
defined('ABSPATH') || exit;

class MAE_Registration {

    public function __construct() {
        add_action('wp_enqueue_scripts',               [$this, 'enqueue']);
        add_action('wp_ajax_mae_submit',               [$this, 'handle']);
        add_action('wp_ajax_nopriv_mae_submit',        [$this, 'handle']);
        add_action('wp_ajax_mae_stripe_intent',        [$this, 'stripe_intent']);
        add_action('wp_ajax_nopriv_mae_stripe_intent', [$this, 'stripe_intent']);
    }

    public function enqueue() {
        if (is_admin()) return;

        $gateway   = get_option('mae_payment_gateway', 'stripe');
        $is_single = is_singular('mae_event');
        $is_tax    = is_tax('mae_event_cat');
        $is_arch   = is_post_type_archive('mae_event');

        // Detect event data for single pages; use defaults elsewhere.
        if ($is_single) {
            $event_id = get_the_ID();
            $type     = get_post_meta($event_id, '_mae_type',  true);
            $price    = (float) get_post_meta($event_id, '_mae_price', true);
        } else {
            $event_id = 0;
            $type     = '';
            $price    = 0;
        }

        // CSS loads on every page (avoids caching/rewrite-flush issues on live servers).
        wp_enqueue_style('mae-events', MAE_URL . 'assets/css/mae-events.css', [], MAE_VERSION);

        // Payment SDKs only on pages that have the registration modal.
        $has_modal   = $is_single || $is_tax || $is_arch;
        $load_stripe = $has_modal && $gateway === 'stripe';
        $load_paypal = $has_modal && $gateway === 'paypal';

        if ($load_stripe) {
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
        }
        if ($load_paypal) {
            $client_id = get_option('mae_paypal_client_id', '');
            if ($client_id) {
                wp_enqueue_script('paypal-sdk',
                    "https://www.paypal.com/sdk/js?client-id={$client_id}&currency=USD",
                    [], null, true);
            }
        }

        // JS + localized data load on every frontend page so the modal always works.
        wp_enqueue_script('mae-events', MAE_URL . 'assets/js/mae-events.js', ['jquery'], MAE_VERSION, true);
        wp_localize_script('mae-events', 'mae_vars', [
            'ajax_url'       => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('mae_nonce'),
            'event_id'       => $event_id,
            'event_type'     => $type,
            'event_price'    => $price,
            'gateway'        => $gateway,
            'stripe_pub_key' => get_option('mae_stripe_publishable_key', ''),
            'is_tax'         => ($is_tax || $is_arch) ? '1' : '0',
        ]);
    }

    public function handle() {
        check_ajax_referer('mae_nonce', 'nonce');

        $reg_type = sanitize_text_field($_POST['registration_type'] ?? '');
        if (!in_array($reg_type, ['volunteer', 'sponsor'], true)) {
            wp_send_json_error(['message' => 'Invalid registration type.']);
        }

        $event_id = absint($_POST['event_id'] ?? 0);

        if ($event_id > 0) {
            $event = get_post($event_id);
            if (!$event || $event->post_type !== 'mae_event') {
                wp_send_json_error(['message' => 'Invalid event.']);
            }
            $event_type  = get_post_meta($event_id, '_mae_type',  true);
            $event_price = (float) get_post_meta($event_id, '_mae_price', true);
        } else {
            $event       = null;
            $event_type  = 'free';
            $event_price = 0.0;
        }

        global $wpdb;
        $data = [
            'event_id'          => $event_id,
            'registration_type' => $reg_type,
            'created_at'        => current_time('mysql'),
        ];

        if ($reg_type === 'volunteer') {
            $required = [
                'volunteer_name', 'volunteer_email', 'volunteer_phone',
                'volunteer_city', 'volunteer_help_area', 'volunteer_availability',
                'volunteer_skills', 'volunteer_motivation',
            ];
            foreach ($required as $f) {
                if (empty(trim($_POST[$f] ?? ''))) {
                    wp_send_json_error(['message' => 'Please fill in all required fields.']);
                }
            }
            if (empty($_POST['volunteer_agreement'])) {
                wp_send_json_error(['message' => 'You must accept the volunteer commitment to continue.']);
            }

            $data['volunteer_name']         = sanitize_text_field($_POST['volunteer_name']);
            $data['volunteer_email']        = sanitize_email($_POST['volunteer_email']);
            $data['volunteer_phone']        = sanitize_text_field($_POST['volunteer_phone']);
            $data['volunteer_city']         = sanitize_text_field($_POST['volunteer_city']);
            $data['volunteer_university']   = sanitize_text_field($_POST['volunteer_university'] ?? '');
            $data['volunteer_help_area']    = sanitize_text_field($_POST['volunteer_help_area']);
            $data['volunteer_availability'] = sanitize_text_field($_POST['volunteer_availability']);
            $data['volunteer_skills']       = sanitize_textarea_field($_POST['volunteer_skills']);
            $data['volunteer_experience']   = sanitize_textarea_field($_POST['volunteer_experience'] ?? '');
            $data['volunteer_motivation']   = sanitize_textarea_field($_POST['volunteer_motivation']);
            $data['volunteer_gain']         = sanitize_textarea_field($_POST['volunteer_gain'] ?? '');

            $user_email = $data['volunteer_email'];
            $user_name  = $data['volunteer_name'];

        } else {
            $required = [
                'company_name', 'org_type', 'contact_person',
                'sponsor_email', 'sponsor_phone', 'sponsorship_type', 'budget',
            ];
            foreach ($required as $f) {
                if (empty(trim($_POST[$f] ?? ''))) {
                    wp_send_json_error(['message' => 'Please fill in all required fields.']);
                }
            }

            // participation_interests is a checkbox array
            $interests = [];
            if (!empty($_POST['participation_interests']) && is_array($_POST['participation_interests'])) {
                $interests = array_map('sanitize_text_field', $_POST['participation_interests']);
            }
            if (empty($interests)) {
                wp_send_json_error(['message' => 'Please select at least one area of participation.']);
            }

            $data['company_name']            = sanitize_text_field($_POST['company_name']);
            $data['org_type']                = sanitize_text_field($_POST['org_type']);
            $data['contact_person']          = sanitize_text_field($_POST['contact_person']);
            $data['sponsor_email']           = sanitize_email($_POST['sponsor_email']);
            $data['sponsor_phone']           = sanitize_text_field($_POST['sponsor_phone']);
            $data['website']                 = esc_url_raw($_POST['website'] ?? '');
            $data['participation_interests'] = implode(', ', $interests);
            $data['sponsorship_type']        = sanitize_text_field($_POST['sponsorship_type']);
            $data['budget']                  = sanitize_text_field($_POST['budget']);
            $data['sponsor_message']         = sanitize_textarea_field($_POST['sponsor_message'] ?? '');

            if (!empty($_FILES['sponsor_logo']['name'])) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $uploaded = wp_handle_upload($_FILES['sponsor_logo'], ['test_form' => false]);
                if (isset($uploaded['url'])) {
                    $data['logo_path'] = $uploaded['url'];
                }
            }

            $user_email = $data['sponsor_email'];
            $user_name  = $data['contact_person'];
        }

        // Payment
        $payment_id     = sanitize_text_field($_POST['payment_id'] ?? '');
        $sponsor_amount = ($reg_type === 'sponsor')
            ? round((float) ($_POST['sponsor_payment_amount'] ?? 0), 2)
            : 0;

        if ($event_type === 'paid') {
            $quantity = max(1, absint($_POST['ticket_quantity'] ?? 1));
            $amount   = round($event_price * $quantity, 2);

            if (empty($payment_id)) {
                wp_send_json_error(['message' => 'Payment not completed. Please complete payment first.']);
            }

            $data['ticket_quantity'] = $quantity;
            $data['amount']          = $amount;
            $data['payment_id']      = $payment_id;
            $data['payment_method']  = get_option('mae_payment_gateway', 'stripe');
            $data['payment_status']  = 'completed';

        } elseif ($reg_type === 'sponsor' && $sponsor_amount >= 1) {
            if (empty($payment_id)) {
                wp_send_json_error(['message' => 'Payment not completed. Please complete payment first.']);
            }

            $data['amount']         = $sponsor_amount;
            $data['payment_id']     = $payment_id;
            $data['payment_method'] = get_option('mae_payment_gateway', 'stripe');
            $data['payment_status'] = 'completed';

        } else {
            $data['payment_status'] = 'free';
            $data['payment_method'] = 'free';
        }

        $wpdb->insert($wpdb->prefix . 'mae_registrations', $data);
        $reg_id = $wpdb->insert_id;

        if (!$reg_id) {
            wp_send_json_error(['message' => 'Registration failed. Please try again.']);
        }

        $this->notify($event_id > 0 ? $event : null, $reg_id, $data, $user_name, $user_email);

        wp_send_json_success(['message' => 'Registration successful! A confirmation has been sent to your email.']);
    }

    public function stripe_intent() {
        check_ajax_referer('mae_nonce', 'nonce');

        $secret = get_option('mae_stripe_secret_key', '');

        $event_id     = absint($_POST['event_id'] ?? 0);
        $custom_cents = absint($_POST['custom_amount_cents'] ?? 0);
        if ($custom_cents > 0) {
            $amount = $custom_cents;
        } else {
            $quantity = max(1, absint($_POST['quantity'] ?? 1));
            $price    = (float) get_post_meta($event_id, '_mae_price', true);
            $amount   = (int) round($price * $quantity * 100);
        }

        if ($amount < 50) {
            wp_send_json_error(['message' => 'Minimum payment amount is $0.50.']);
        }

        if (!$secret) {
            wp_send_json_error(['message' => 'Stripe is not configured.']);
        }

        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'amount'   => $amount,
                'currency' => 'usd',
                'automatic_payment_methods[enabled]' => 'true',
                'metadata[event_id]' => $event_id,
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Could not initialise payment. Please try again.']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($body['error'])) {
            wp_send_json_error(['message' => $body['error']['message']]);
        }

        wp_send_json_success(['client_secret' => $body['client_secret']]);
    }

    private function notify($event, $reg_id, $data, $name, $email) {
        $title       = $event ? get_the_title($event) : ucfirst($data['registration_type']) . ' Application';
        $admin_email = get_option('mae_admin_email', get_option('admin_email'));
        $type        = ucfirst($data['registration_type']);

        // Admin
        wp_mail(
            $admin_email,
            "New {$type} Registration: {$title} (#{$reg_id})",
            "A new {$data['registration_type']} has registered for \"{$title}\".\n\nID: #{$reg_id}\nName: {$name}\nEmail: {$email}\n\nView all: " . admin_url('admin.php?page=mae-registrations')
        );

        // User
        wp_mail(
            $email,
            "Registration Confirmed – {$title}",
            "Hi {$name},\n\nThank you for registering for \"{$title}\"!\n\nRegistration ID: #{$reg_id}\nType: {$type}\n\nWe will be in touch with further details.\n\nWarm regards,\nMithila Art Team"
        );
    }
}
