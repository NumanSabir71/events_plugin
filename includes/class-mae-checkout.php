<?php
defined('ABSPATH') || exit;

class MAE_Checkout {

    public function __construct() {
        add_shortcode('mae_checkout', [$this, 'render']);
        add_action('wp_ajax_mae_checkout_submit',        [$this, 'handle']);
        add_action('wp_ajax_nopriv_mae_checkout_submit', [$this, 'handle']);
        add_action('admin_init', [$this, 'maybe_create_page']);
        add_filter('body_class',  [$this, 'add_body_class']);
    }

    /** Adds mae-checkout-page body class so CSS can widen the theme container. */
    public function add_body_class($classes) {
        $page_id = (int) get_option('mae_checkout_page_id', 0);
        if ($page_id && is_page($page_id)) {
            $classes[] = 'mae-checkout-page';
        }
        return $classes;
    }

    /** Returns the checkout URL for a given event. */
    public static function url($event_id) {
        $page_id = (int) get_option('mae_checkout_page_id', 0);
        if (!$page_id || !get_post($page_id)) return '#';
        return add_query_arg('event_id', absint($event_id), get_permalink($page_id));
    }

    /** Creates the checkout page once if it doesn't exist yet. */
    public function maybe_create_page() {
        $page_id = (int) get_option('mae_checkout_page_id', 0);
        if ($page_id && get_post($page_id)) return;

        $id = wp_insert_post([
            'post_title'   => 'Event Registration',
            'post_name'    => 'event-registration',
            'post_content' => '[mae_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
        if (!is_wp_error($id)) {
            update_option('mae_checkout_page_id', $id);
        }
    }

    /** [mae_checkout] shortcode — renders the checkout form. */
    public function render() {
        $event_id = absint($_GET['event_id'] ?? 0);

        if (!$event_id) {
            return '<div class="mae-checkout-notice"><p>No event specified. <a href="' . esc_url(get_post_type_archive_link('mae_event')) . '">← Browse events</a></p></div>';
        }

        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'mae_event') {
            return '<div class="mae-checkout-notice"><p>Event not found. <a href="' . esc_url(get_post_type_archive_link('mae_event')) . '">← Browse events</a></p></div>';
        }

        $date     = get_post_meta($event_id, '_mae_date',     true);
        $end_date = get_post_meta($event_id, '_mae_end_date', true);
        $time     = get_post_meta($event_id, '_mae_time',     true);
        $loc      = get_post_meta($event_id, '_mae_location', true);
        $type    = get_post_meta($event_id, '_mae_type',     true) ?: 'free';
        $price   = (float) get_post_meta($event_id, '_mae_price', true);
        $is_paid = $type === 'paid';
        $gateway = get_option('mae_payment_gateway', 'stripe');

        if ($is_paid) {
            if ($gateway === 'stripe') {
                wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
            } elseif ($gateway === 'paypal') {
                $cid = get_option('mae_paypal_client_id', '');
                if ($cid) {
                    wp_enqueue_script('paypal-sdk', "https://www.paypal.com/sdk/js?client-id={$cid}&currency=USD", [], null, true);
                }
            }
        }

        wp_enqueue_script('mae-checkout', MAE_URL . 'assets/js/mae-checkout.js', ['jquery'], MAE_VERSION, true);
        wp_localize_script('mae-checkout', 'mae_co_vars', [
            'ajax_url'       => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('mae_nonce'),
            'event_id'       => $event_id,
            'event_type'     => $type,
            'event_price'    => $price,
            'gateway'        => $gateway,
            'stripe_pub_key' => get_option('mae_stripe_publishable_key', ''),
        ]);

        ob_start();
        $thumb_url = get_the_post_thumbnail_url($event_id, 'medium_large');
        ?>
        <div class="mae-co-pg">

            <!-- ── Brown meta strip ───────────────────────────── -->
            <div class="mae-co-pg__strip">
                <div class="mae-co-pg__strip-inner">
                    <div class="mae-co-pg__strip-meta">
                        <?php if ($date) : ?>
                        <span class="mae-co-pg__strip-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?>
                            <?php if ($time) : ?>&ensp;&middot;&ensp;<?php echo date('g:i A', strtotime($time)); ?><?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($loc) : ?>
                        <span class="mae-co-pg__strip-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?php echo esc_html($loc); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <span class="mae-co-pg__strip-badge <?php echo $is_paid ? 'is-paid' : 'is-free'; ?>">
                        <?php echo $is_paid ? '$' . number_format($price, 2) . ' / ticket' : 'Free Entry'; ?>
                    </span>
                </div>
            </div>

            <!-- ── Body: form + sidebar ───────────────────────── -->
            <div class="mae-co-pg__body">
                <div class="mae-co-pg__container">
                    <div class="mae-co-pg__layout">

                        <!-- Form column -->
                        <div class="mae-co-pg__form-col">
                            <form id="mae-checkout-form" class="mae-checkout-form" novalidate>
                                <input type="hidden" name="action"     value="mae_checkout_submit">
                                <input type="hidden" name="nonce"      value="<?php echo wp_create_nonce('mae_nonce'); ?>">
                                <input type="hidden" name="event_id"   value="<?php echo $event_id; ?>">
                                <input type="hidden" name="payment_id" id="mae-co-payment-id" value="">

                                <!-- Attendance Date -->
                                <?php if ($date) : ?>
                                <?php if ($end_date && $end_date !== $date) : ?>
                                <div class="mae-attend-date-section">
                                    <h3 class="mae-checkout-section-title">Select Attendance Date</h3>
                                    <div class="mae-attend-date-info">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        This event runs <strong>&nbsp;<?php echo mae_format_date_range($date, $end_date); ?></strong>. Choose the day you plan to attend.
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label" for="mae-attend-date">Your Attendance Date <span class="mae-req">*</span></label>
                                        <input type="date"
                                               id="mae-attend-date"
                                               name="attendee_date"
                                               class="mae-input mae-attend-date-input"
                                               min="<?php echo esc_attr($date); ?>"
                                               max="<?php echo esc_attr($end_date); ?>"
                                               required>
                                        <p id="mae-date-error" class="mae-attend-date-error" style="display:none;" role="alert">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                            Please pick a date between <strong>&nbsp;<?php echo date('M j', strtotime($date)); ?></strong> and <strong>&nbsp;<?php echo date('M j, Y', strtotime($end_date)); ?></strong>.
                                        </p>
                                    </div>
                                </div>
                                <?php else : ?>
                                <input type="hidden" name="attendee_date" value="<?php echo esc_attr($date); ?>">
                                <?php endif; ?>
                                <?php endif; ?>

                                <!-- Personal Details -->
                                <h3 class="mae-checkout-section-title">Personal Details</h3>

                                <div class="mae-row-2">
                                    <div class="mae-field-group">
                                        <label class="mae-label">Full Name <span class="mae-req">*</span></label>
                                        <input type="text" name="attendee_name" class="mae-input" placeholder="Your full name" required>
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label">Email Address <span class="mae-req">*</span></label>
                                        <input type="email" name="attendee_email" class="mae-input" placeholder="your@email.com" required>
                                    </div>
                                </div>

                                <div class="mae-row-2">
                                    <div class="mae-field-group">
                                        <label class="mae-label">Phone Number <span class="mae-req">*</span></label>
                                        <input type="tel" name="attendee_phone" class="mae-input" placeholder="+1 555 000 0000" required>
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label">City / Town <span class="mae-req">*</span></label>
                                        <input type="text" name="attendee_city" class="mae-input" placeholder="e.g. Kathmandu" required>
                                    </div>
                                </div>

                                <div class="mae-row-2">
                                    <div class="mae-field-group">
                                        <label class="mae-label">Country <span class="mae-req">*</span></label>
                                        <select name="attendee_country" class="mae-select" required>
                                            <option value="">Select country…</option>
                                            <option>Afghanistan</option><option>Albania</option><option>Algeria</option>
                                            <option>Argentina</option><option>Australia</option><option>Austria</option>
                                            <option>Bangladesh</option><option>Belgium</option><option>Bhutan</option>
                                            <option>Brazil</option><option>Canada</option><option>China</option>
                                            <option>Denmark</option><option>Egypt</option><option>Finland</option>
                                            <option>France</option><option>Germany</option><option>Ghana</option>
                                            <option>Greece</option><option>India</option><option>Indonesia</option>
                                            <option>Iran</option><option>Iraq</option><option>Ireland</option>
                                            <option>Israel</option><option>Italy</option><option>Japan</option>
                                            <option>Jordan</option><option>Kenya</option><option>Malaysia</option>
                                            <option>Mexico</option><option>Morocco</option><option>Myanmar</option>
                                            <option>Nepal</option><option>Netherlands</option><option>New Zealand</option>
                                            <option>Nigeria</option><option>Norway</option><option>Pakistan</option>
                                            <option>Philippines</option><option>Poland</option><option>Portugal</option>
                                            <option>Qatar</option><option>Russia</option><option>Saudi Arabia</option>
                                            <option>Singapore</option><option>South Africa</option><option>South Korea</option>
                                            <option>Spain</option><option>Sri Lanka</option><option>Sweden</option>
                                            <option>Switzerland</option><option>Thailand</option><option>Turkey</option>
                                            <option>Ukraine</option><option>United Arab Emirates</option>
                                            <option>United Kingdom</option><option>United States</option>
                                            <option>Vietnam</option><option>Other</option>
                                        </select>
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label">Gender <span class="mae-optional">(optional)</span></label>
                                        <select name="attendee_gender" class="mae-select">
                                            <option value="">Prefer not to say</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="non-binary">Non-binary</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mae-row-2">
                                    <div class="mae-field-group">
                                        <label class="mae-label">Age Group <span class="mae-optional">(optional)</span></label>
                                        <select name="attendee_age_group" class="mae-select">
                                            <option value="">Select…</option>
                                            <option value="under-18">Under 18</option>
                                            <option value="18-25">18 – 25</option>
                                            <option value="26-35">26 – 35</option>
                                            <option value="36-50">36 – 50</option>
                                            <option value="51-65">51 – 65</option>
                                            <option value="over-65">Over 65</option>
                                        </select>
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label">How did you hear about us? <span class="mae-optional">(optional)</span></label>
                                        <select name="attendee_source" class="mae-select">
                                            <option value="">Select…</option>
                                            <option value="facebook">Facebook</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="twitter-x">Twitter / X</option>
                                            <option value="whatsapp">WhatsApp / Messaging</option>
                                            <option value="friend-family">Friend or Family</option>
                                            <option value="google">Google Search</option>
                                            <option value="website">Our Website</option>
                                            <option value="email-newsletter">Email Newsletter</option>
                                            <option value="poster-flyer">Poster / Flyer</option>
                                            <option value="newspaper">Newspaper / Magazine</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Emergency Contact -->
                                <h3 class="mae-checkout-section-title">Emergency Contact</h3>

                                <div class="mae-row-2">
                                    <div class="mae-field-group">
                                        <label class="mae-label">Contact Name <span class="mae-req">*</span></label>
                                        <input type="text" name="attendee_emergency_name" class="mae-input"
                                               placeholder="Full name of emergency contact" required>
                                    </div>
                                    <div class="mae-field-group">
                                        <label class="mae-label">Contact Phone <span class="mae-req">*</span></label>
                                        <input type="tel" name="attendee_emergency_phone" class="mae-input"
                                               placeholder="+1 555 000 0000" required>
                                    </div>
                                </div>

                                <!-- Special Requirements -->
                                <h3 class="mae-checkout-section-title">Special Requirements</h3>

                                <div class="mae-field-group">
                                    <label class="mae-label">Dietary, accessibility or other needs <span class="mae-optional">(optional)</span></label>
                                    <textarea name="attendee_notes" class="mae-textarea" rows="3"
                                              placeholder="e.g. Vegetarian meal, wheelchair access, hearing loop…"></textarea>
                                </div>

                                <?php if ($is_paid) : ?>
                                <!-- Tickets -->
                                <h3 class="mae-checkout-section-title">Tickets</h3>
                                <div class="mae-ticket-card">
                                    <div class="mae-ticket-info">
                                        <div class="mae-ticket-label">Price per ticket</div>
                                        <div class="mae-ticket-price">$<?php echo number_format($price, 2); ?></div>
                                    </div>
                                    <div class="mae-qty-wrap">
                                        <button type="button" class="mae-qty-btn" id="mae-co-qty-minus">−</button>
                                        <input type="number" name="ticket_quantity" id="mae-co-qty" value="1" min="1" max="20" class="mae-qty-input" readonly>
                                        <button type="button" class="mae-qty-btn" id="mae-co-qty-plus">+</button>
                                    </div>
                                </div>
                                <div class="mae-total-row">
                                    <span>Total</span>
                                    <strong id="mae-co-total">$<?php echo number_format($price, 2); ?></strong>
                                </div>

                                <!-- Payment -->
                                <h3 class="mae-checkout-section-title">Payment</h3>
                                <?php if ($gateway === 'stripe') : ?>
                                <div id="mae-co-payment-element" class="mae-payment-element"></div>
                                <div id="mae-co-stripe-error" class="mae-msg mae-msg-error" style="display:none;"></div>
                                <?php elseif ($gateway === 'paypal') : ?>
                                <div id="mae-co-paypal-btn"></div>
                                <?php endif; ?>
                                <?php endif; ?>

                                <div id="mae-co-msg" class="mae-msg" style="display:none;"></div>

                                <div class="mae-checkout-submit-wrap">
                                    <button type="submit" id="mae-co-submit" class="mae-btn-submit">
                                        <span id="mae-co-submit-label">
                                            <?php echo $is_paid ? 'Pay &amp; Register' : 'Complete Registration'; ?>
                                        </span>
                                        <span id="mae-co-spinner" class="mae-spinner" style="display:none;"></span>
                                    </button>
                                </div>

                            </form>
                        </div><!-- /.mae-co-pg__form-col -->

                        <!-- Summary sidebar -->
                        <aside class="mae-co-pg__aside">
                            <div class="mae-co-summary">

                                <?php if ($thumb_url) : ?>
                                <div class="mae-co-summary__img-wrap">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($event->post_title); ?>" class="mae-co-summary__img">
                                </div>
                                <?php endif; ?>

                                <div class="mae-co-summary__body">
                                    <p class="mae-co-summary__kicker">You&rsquo;re registering for</p>
                                    <h3 class="mae-co-summary__title"><?php echo esc_html($event->post_title); ?></h3>

                                    <ul class="mae-co-summary__details">
                                        <?php if ($date) : ?>
                                        <li class="mae-co-summary__detail">
                                            <span class="mae-co-summary__icon">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            </span>
                                            <span><?php echo mae_format_date_range($date, $end_date); ?><?php if ($time) : ?><br><small><?php echo date('g:i A', strtotime($time)); ?></small><?php endif; ?></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($loc) : ?>
                                        <li class="mae-co-summary__detail">
                                            <span class="mae-co-summary__icon">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                            </span>
                                            <span><?php echo esc_html($loc); ?></span>
                                        </li>
                                        <?php endif; ?>
                                    </ul>

                                    <div class="mae-co-summary__price-row">
                                        <span class="mae-co-summary__price-label">Entry</span>
                                        <span class="mae-co-summary__price-val <?php echo $is_paid ? '' : 'is-free'; ?>">
                                            <?php echo $is_paid ? '$' . number_format($price, 2) . ' / ticket' : 'Free'; ?>
                                        </span>
                                    </div>

                                    <div class="mae-co-summary__trust">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        Secure &amp; encrypted registration
                                    </div>
                                </div>
                            </div>
                        </aside>

                    </div><!-- /.mae-co-pg__layout -->
                </div><!-- /.mae-co-pg__container -->
            </div><!-- /.mae-co-pg__body -->

        </div><!-- /.mae-co-pg -->
        <?php
        return ob_get_clean();
    }

    /** AJAX handler for checkout form submission. */
    public function handle() {
        check_ajax_referer('mae_nonce', 'nonce');

        $event_id = absint($_POST['event_id'] ?? 0);
        $event    = get_post($event_id);
        if (!$event || $event->post_type !== 'mae_event') {
            wp_send_json_error(['message' => 'Invalid event.']);
        }

        $required = [
            'attendee_name', 'attendee_email', 'attendee_phone',
            'attendee_city', 'attendee_country',
            'attendee_emergency_name', 'attendee_emergency_phone',
        ];
        foreach ($required as $f) {
            if (empty(trim($_POST[$f] ?? ''))) {
                wp_send_json_error(['message' => 'Please fill in all required fields.']);
            }
        }

        if (!is_email($_POST['attendee_email'] ?? '')) {
            wp_send_json_error(['message' => 'Please enter a valid email address.']);
        }

        $start_date    = get_post_meta($event_id, '_mae_date',     true);
        $end_date_meta = get_post_meta($event_id, '_mae_end_date', true);
        $attendee_date = sanitize_text_field($_POST['attendee_date'] ?? '');

        if ($end_date_meta && $end_date_meta !== $start_date) {
            if (!$attendee_date) {
                wp_send_json_error(['message' => 'Please select your attendance date.']);
            }
            if ($attendee_date < $start_date || $attendee_date > $end_date_meta) {
                wp_send_json_error(['message' => sprintf(
                    'Please select a date between %s and %s.',
                    date('M j', strtotime($start_date)),
                    date('M j, Y', strtotime($end_date_meta))
                )]);
            }
        } else {
            $attendee_date = $start_date;
        }

        $event_type  = get_post_meta($event_id, '_mae_type',  true);
        $event_price = (float) get_post_meta($event_id, '_mae_price', true);

        global $wpdb;
        $name  = sanitize_text_field($_POST['attendee_name']);
        $email = sanitize_email($_POST['attendee_email']);

        $data = [
            'event_id'               => $event_id,
            'registration_type'      => 'attendee',
            'volunteer_name'         => $name,
            'volunteer_email'        => $email,
            'volunteer_phone'        => sanitize_text_field($_POST['attendee_phone']),
            'attendee_city'          => sanitize_text_field($_POST['attendee_city']),
            'attendee_country'       => sanitize_text_field($_POST['attendee_country']),
            'attendee_gender'        => sanitize_text_field($_POST['attendee_gender'] ?? ''),
            'attendee_age_group'     => sanitize_text_field($_POST['attendee_age_group'] ?? ''),
            'attendee_source'        => sanitize_text_field($_POST['attendee_source'] ?? ''),
            'attendee_notes'         => sanitize_textarea_field($_POST['attendee_notes'] ?? ''),
            'attendee_emergency_name'  => sanitize_text_field($_POST['attendee_emergency_name']),
            'attendee_emergency_phone' => sanitize_text_field($_POST['attendee_emergency_phone']),
            'attendee_date'          => $attendee_date ?: null,
            'created_at'             => current_time('mysql'),
        ];

        if ($event_type === 'paid') {
            $quantity   = max(1, absint($_POST['ticket_quantity'] ?? 1));
            $amount     = round($event_price * $quantity, 2);
            $payment_id = sanitize_text_field($_POST['payment_id'] ?? '');

            if (empty($payment_id)) {
                wp_send_json_error(['message' => 'Payment not completed. Please complete payment first.']);
            }

            $data['ticket_quantity'] = $quantity;
            $data['amount']          = $amount;
            $data['payment_id']      = $payment_id;
            $data['payment_method']  = get_option('mae_payment_gateway', 'stripe');
            $data['payment_status']  = 'completed';
        } else {
            $data['payment_status'] = 'free';
            $data['payment_method'] = 'free';
        }

        $wpdb->insert($wpdb->prefix . 'mae_registrations', $data);
        $reg_id = $wpdb->insert_id;

        if (!$reg_id) {
            wp_send_json_error(['message' => 'Registration failed. Please try again.']);
        }

        $title       = get_the_title($event);
        $admin_email = get_option('mae_admin_email', get_option('admin_email'));
        $city        = $data['attendee_city'];
        $country     = $data['attendee_country'];
        $emerg_name  = $data['attendee_emergency_name'];
        $emerg_phone = $data['attendee_emergency_phone'];

        wp_mail(
            $admin_email,
            "New Registration: {$title} (#{$reg_id})",
            "Name: {$name}\nEmail: {$email}\nLocation: {$city}, {$country}\nEmergency Contact: {$emerg_name} ({$emerg_phone})\nEvent: {$title}\n\nView all: " . admin_url('admin.php?page=mae-registrations')
        );
        wp_mail(
            $email,
            "Registration Confirmed – {$title}",
            "Hi {$name},\n\nThank you for registering for \"{$title}\"!\n\nRegistration ID: #{$reg_id}\n\nWe'll be in touch with further details.\n\nWarm regards,\nMithila Art Team"
        );

        wp_send_json_success(['message' => 'Registration successful! A confirmation has been sent to your email.']);
    }
}
