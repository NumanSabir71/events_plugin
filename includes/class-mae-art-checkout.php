<?php
defined('ABSPATH') || exit;

class MAE_Art_Checkout {

    public function __construct() {
        add_action('template_redirect',                    [$this, 'maybe_render'], 1);
        add_action('wp_ajax_mae_art_stripe_intent',        [$this, 'stripe_intent']);
        add_action('wp_ajax_nopriv_mae_art_stripe_intent', [$this, 'stripe_intent']);
        add_action('wp_ajax_mae_art_purchase',             [$this, 'purchase']);
        add_action('wp_ajax_nopriv_mae_art_purchase',      [$this, 'purchase']);
    }

    /** Returns the art-piece checkout URL. */
    public static function url($event_id, $artist_idx, $art_id) {
        return add_query_arg([
            'mae_art_checkout' => 1,
            'event_id'         => absint($event_id),
            'artist_idx'       => absint($artist_idx),
            'art_id'           => absint($art_id),
        ], home_url('/'));
    }

    /** Intercepts ?mae_art_checkout=1 and renders the purchase page. */
    public function maybe_render() {
        if (empty($_GET['mae_art_checkout'])) return;

        $event_id   = absint($_GET['event_id']   ?? 0);
        $artist_idx = absint($_GET['artist_idx'] ?? 0);
        $art_id     = absint($_GET['art_id']     ?? 0);

        $artists    = $event_id ? (get_post_meta($event_id, '_mae_artists', true) ?: []) : [];
        $artist     = $artists[$artist_idx] ?? null;

        $error = '';
        if (!$event_id || !$art_id || !$artist) {
            $error = 'Art piece not found.';
        } else {
            $prices = $artist['art_prices'] ?? [];
            $amount = (float) ($prices[$art_id] ?? 0);
            if ($amount <= 0) $error = 'This art piece does not have a price set yet.';
        }

        // Enqueue scripts before get_header() calls wp_head()
        if (!$error) {
            $captions  = $artist['art_captions'] ?? [];
            $art_title = $captions[$art_id] ?? (wp_get_attachment_caption($art_id) ?: '');
            $img_url   = wp_get_attachment_image_url($art_id, 'large');
            $full_url  = wp_get_attachment_image_url($art_id, 'full');

            wp_enqueue_style('mae-events', MAE_URL . 'assets/css/mae-events.css', [], MAE_VERSION);
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
            wp_enqueue_script('mae-art-checkout', MAE_URL . 'assets/js/mae-art-checkout.js', ['jquery'], MAE_VERSION, true);
            wp_localize_script('mae-art-checkout', 'mae_art_vars', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'nonce'          => wp_create_nonce('mae_nonce'),
                'event_id'       => $event_id,
                'artist_idx'     => $artist_idx,
                'art_id'         => $art_id,
                'amount'         => $amount,
                'stripe_pub_key' => get_option('mae_stripe_publishable_key', ''),
            ]);
        }

        status_header(200);
        get_header();

        if ($error) {
            echo '<div style="max-width:600px;margin:4rem auto;padding:2rem;text-align:center;">';
            echo '<p style="color:#b32d2e;">' . esc_html($error) . '</p>';
            echo '<a href="' . esc_url(home_url('/')) . '">&larr; Go back</a>';
            echo '</div>';
            get_footer();
            exit;
        }

        $event_title = get_the_title($event_id);
        $event_url   = get_permalink($event_id);
        ?>
        <div class="mae-art-co-pg">

            <!-- Hero -->
            <section class="mae-art-co-hero"<?php if ($full_url ?: $img_url) : ?> style="--hero-img:url('<?php echo esc_url($full_url ?: $img_url); ?>')"<?php endif; ?>>
                <div class="mae-art-co-hero__overlay"></div>
                <div class="mae-art-co-hero__inner">
                    <a href="<?php echo esc_url($event_url); ?>" class="mae-art-co-hero__back">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                        <?php echo esc_html($event_title); ?>
                    </a>
                    <p class="mae-art-co-hero__kicker">Art Purchase</p>
                    <h1 class="mae-art-co-hero__title"><?php echo esc_html($art_title ?: 'Original Artwork'); ?></h1>
                    <div class="mae-art-co-hero__divider"></div>
                    <div class="mae-art-co-hero__pills">
                        <span class="mae-art-co-hero__pill">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <?php echo esc_html($artist['name']); ?>
                        </span>
                        <span class="mae-art-co-hero__pill mae-art-co-hero__pill--price">
                            $<?php echo number_format($amount, 2); ?>
                        </span>
                    </div>
                </div>
            </section>

            <!-- Body -->
            <div class="mae-art-co-body">
                <div class="mae-art-co-container">
                    <div class="mae-art-co-layout">

                        <!-- Left: art piece card -->
                        <div class="mae-art-co-preview">
                            <?php if ($img_url) : ?>
                            <a href="<?php echo esc_url($full_url ?: $img_url); ?>" target="_blank" rel="noopener" class="mae-art-co-img-link">
                                <img src="<?php echo esc_url($img_url); ?>"
                                     alt="<?php echo esc_attr($art_title ?: 'Art piece'); ?>"
                                     class="mae-art-co-img">
                            </a>
                            <?php endif; ?>

                            <div class="mae-art-co-meta">
                                <?php if ($art_title) : ?>
                                <h2 class="mae-art-co-title"><?php echo esc_html($art_title); ?></h2>
                                <?php endif; ?>
                                <p class="mae-art-co-artist">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <?php echo esc_html($artist['name']); ?>
                                </p>
                                <div class="mae-art-co-price-tag">
                                    $<?php echo number_format($amount, 2); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right: checkout form -->
                        <div class="mae-art-co-form-wrap">
                            <h1 class="mae-art-co-form-title">Complete Your Purchase</h1>

                            <div id="mae-art-msg" class="mae-co-msg" style="display:none;"></div>

                            <form id="mae-art-checkout-form" novalidate>
                                <?php wp_nonce_field('mae_nonce', '_wpnonce'); ?>
                                <input type="hidden" name="action"      value="mae_art_purchase">
                                <input type="hidden" name="nonce"       value="<?php echo wp_create_nonce('mae_nonce'); ?>">
                                <input type="hidden" name="event_id"    value="<?php echo esc_attr($event_id); ?>">
                                <input type="hidden" name="artist_idx"  value="<?php echo esc_attr($artist_idx); ?>">
                                <input type="hidden" name="art_id"      value="<?php echo esc_attr($art_id); ?>">
                                <input type="hidden" name="payment_id"  id="mae-art-payment-id" value="">

                                <div class="mae-co-section-title">Your Details</div>

                                <div class="mae-field-group">
                                    <label class="mae-label" for="mae-art-name">Full Name <span class="mae-req">*</span></label>
                                    <input type="text" id="mae-art-name" name="buyer_name" class="mae-input" required placeholder="Jane Smith">
                                </div>

                                <div class="mae-art-co-field-row">
                                    <div class="mae-field-group">
                                        <label class="mae-label" for="mae-art-email">Email <span class="mae-req">*</span></label>
                                        <input type="email" id="mae-art-email" name="buyer_email" class="mae-input" required placeholder="jane@example.com">
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label" for="mae-art-phone">Phone</label>
                                        <input type="tel" id="mae-art-phone" name="buyer_phone" class="mae-input" placeholder="+1 (555) 000-0000">
                                    </div>
                                </div>

                                <div class="mae-co-section-title" style="margin-top:1.75rem;">Payment</div>

                                <div class="mae-art-co-amount-row">
                                    <span>Total</span>
                                    <strong>$<?php echo number_format($amount, 2); ?></strong>
                                </div>

                                <div id="mae-art-stripe-error" class="mae-co-stripe-error" style="display:none;"></div>
                                <div id="mae-art-payment-element" class="mae-co-payment-element"></div>

                                <button type="submit" id="mae-art-submit" class="mae-art-co-submit-btn">
                                    <span id="mae-art-submit-label">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                        Pay $<?php echo number_format($amount, 2); ?>
                                    </span>
                                    <span id="mae-art-spinner" style="display:none;">
                                        <span class="mae-spinner"></span> Processing&hellip;
                                    </span>
                                </button>

                                <p class="mae-co-secure-note">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Secure payment powered by Stripe
                                </p>
                            </form>
                        </div>

                    </div><!-- /.mae-art-co-layout -->
                </div><!-- /.mae-art-co-container -->
            </div><!-- /.mae-art-co-body -->

        </div><!-- /.mae-art-co-pg -->
        <?php
        get_footer();
        exit;
    }

    /** AJAX: create Stripe PaymentIntent for the art piece amount. */
    public function stripe_intent() {
        check_ajax_referer('mae_nonce', 'nonce');

        $event_id   = absint($_POST['event_id']   ?? 0);
        $artist_idx = absint($_POST['artist_idx'] ?? 0);
        $art_id     = absint($_POST['art_id']     ?? 0);

        $artists = get_post_meta($event_id, '_mae_artists', true) ?: [];
        $artist  = $artists[$artist_idx] ?? null;
        if (!$artist) {
            wp_send_json_error(['message' => 'Art piece not found.']);
        }

        $prices = $artist['art_prices'] ?? [];
        $amount = (float) ($prices[$art_id] ?? 0);
        if ($amount <= 0) {
            wp_send_json_error(['message' => 'No price set for this art piece.']);
        }

        $secret_key = get_option('mae_stripe_secret_key', '');
        if (!$secret_key) {
            wp_send_json_error(['message' => 'Stripe is not configured. Please contact the administrator.']);
        }

        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', [
            'headers' => ['Authorization' => 'Bearer ' . $secret_key],
            'body'    => [
                'amount'      => (int) round($amount * 100),
                'currency'    => 'usd',
                'description' => 'Art purchase – ' . ($artist['art_captions'][$art_id] ?? 'Artwork'),
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Could not connect to Stripe.']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['client_secret'])) {
            wp_send_json_error(['message' => $body['error']['message'] ?? 'Stripe error.']);
        }

        wp_send_json_success(['client_secret' => $body['client_secret']]);
    }

    /** AJAX: save order and send confirmation emails after successful payment. */
    public function purchase() {
        check_ajax_referer('mae_nonce', 'nonce');

        $event_id   = absint($_POST['event_id']   ?? 0);
        $artist_idx = absint($_POST['artist_idx'] ?? 0);
        $art_id     = absint($_POST['art_id']     ?? 0);
        $payment_id = sanitize_text_field($_POST['payment_id'] ?? '');
        $name       = sanitize_text_field($_POST['buyer_name']  ?? '');
        $email      = sanitize_email($_POST['buyer_email']      ?? '');
        $phone      = sanitize_text_field($_POST['buyer_phone'] ?? '');

        if (!$name || !$email) {
            wp_send_json_error(['message' => 'Please fill in your name and email.']);
        }
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Please enter a valid email address.']);
        }
        if (!$payment_id) {
            wp_send_json_error(['message' => 'Payment not completed. Please complete payment first.']);
        }

        $artists = get_post_meta($event_id, '_mae_artists', true) ?: [];
        $artist  = $artists[$artist_idx] ?? null;
        if (!$artist) {
            wp_send_json_error(['message' => 'Art piece not found.']);
        }

        $prices    = $artist['art_prices']   ?? [];
        $captions  = $artist['art_captions'] ?? [];
        $amount    = (float) ($prices[$art_id] ?? 0);
        $art_title = $captions[$art_id] ?? (wp_get_attachment_caption($art_id) ?: 'Artwork');

        global $wpdb;
        $table = $wpdb->prefix . 'mae_art_orders';

        $wpdb->insert($table, [
            'event_id'       => $event_id,
            'artist_name'    => $artist['name'],
            'art_id'         => $art_id,
            'art_title'      => $art_title,
            'art_price'      => $amount,
            'buyer_name'     => $name,
            'buyer_email'    => $email,
            'buyer_phone'    => $phone,
            'payment_id'     => $payment_id,
            'payment_status' => 'completed',
            'created_at'     => current_time('mysql'),
        ]);

        $order_id = $wpdb->insert_id;
        if (!$order_id) {
            wp_send_json_error(['message' => 'Order could not be saved. Please contact us.']);
        }

        $event_title = get_the_title($event_id);
        $admin_email = get_option('mae_admin_email', get_option('admin_email'));

        wp_mail(
            $admin_email,
            "New Art Purchase: {$art_title} (#{$order_id})",
            "Buyer: {$name}\nEmail: {$email}\nPhone: {$phone}\nArtist: {$artist['name']}\nArt: {$art_title}\nAmount: \${$amount}\nPayment ID: {$payment_id}\nEvent: {$event_title}\n\nView all: " . admin_url('admin.php?page=mae-art-orders')
        );
        wp_mail(
            $email,
            "Purchase Confirmed – {$art_title}",
            "Hi {$name},\n\nThank you for purchasing \"{$art_title}\" by {$artist['name']}!\n\nOrder #: {$order_id}\nAmount: \$" . number_format($amount, 2) . "\n\nWe will be in touch with delivery details.\n\nWarm regards,\nMithila Art Team"
        );

        wp_send_json_success(['message' => 'Purchase successful! A confirmation has been sent to your email.']);
    }
}
