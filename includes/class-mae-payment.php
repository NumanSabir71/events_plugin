<?php
defined('ABSPATH') || exit;

class MAE_Payment {

    public function __construct() {
        add_action('wp_ajax_mae_paypal_order',               [$this, 'create_order']);
        add_action('wp_ajax_nopriv_mae_paypal_order',        [$this, 'create_order']);
        add_action('wp_ajax_mae_paypal_capture',             [$this, 'capture_order']);
        add_action('wp_ajax_nopriv_mae_paypal_capture',      [$this, 'capture_order']);
    }

    private function access_token() {
        $client_id = get_option('mae_paypal_client_id', '');
        $secret    = get_option('mae_paypal_secret',    '');
        $base      = $this->base_url();

        $response = wp_remote_post("{$base}/v1/oauth2/token", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("{$client_id}:{$secret}"),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type=client_credentials',
        ]);

        if (is_wp_error($response)) return null;
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['access_token'] ?? null;
    }

    private function base_url() {
        return get_option('mae_paypal_mode', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function create_order() {
        check_ajax_referer('mae_nonce', 'nonce');

        $event_id      = absint($_POST['event_id'] ?? 0);
        $custom_amount = (float) ($_POST['custom_amount'] ?? 0);

        if ($custom_amount > 0) {
            $total       = number_format($custom_amount, 2, '.', '');
            $description = 'Sponsorship Contribution';
        } else {
            $quantity    = max(1, absint($_POST['quantity'] ?? 1));
            $price       = (float) get_post_meta($event_id, '_mae_price', true);
            $total       = number_format($price * $quantity, 2, '.', '');
            $description = $event_id ? get_the_title($event_id) : 'Event Registration';
        }

        $token = $this->access_token();
        if (!$token) {
            wp_send_json_error(['message' => 'PayPal authentication failed. Check your credentials.']);
        }

        $response = wp_remote_post($this->base_url() . '/v2/checkout/orders', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'description' => $description,
                    'amount'      => ['currency_code' => 'USD', 'value' => $total],
                ]],
            ]),
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Could not create PayPal order.']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['id'])) {
            wp_send_json_error(['message' => $body['message'] ?? 'PayPal order error.']);
        }

        wp_send_json_success(['order_id' => $body['id']]);
    }

    public function capture_order() {
        check_ajax_referer('mae_nonce', 'nonce');

        $order_id = sanitize_text_field($_POST['order_id'] ?? '');
        if (!$order_id) {
            wp_send_json_error(['message' => 'Missing order ID.']);
        }

        $token = $this->access_token();
        if (!$token) {
            wp_send_json_error(['message' => 'PayPal authentication failed.']);
        }

        $response = wp_remote_post($this->base_url() . "/v2/checkout/orders/{$order_id}/capture", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => 'application/json',
            ],
            'body' => '{}',
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Payment capture failed.']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (($body['status'] ?? '') !== 'COMPLETED') {
            wp_send_json_error(['message' => 'Payment was not completed.']);
        }

        wp_send_json_success(['payment_id' => $order_id]);
    }
}
