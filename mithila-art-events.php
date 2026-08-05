<?php
/**
 * Plugin Name: Mithila Art Events
 * Description: Professional event booking system with volunteer and sponsor registration, Stripe & PayPal payments.
 * Version: 1.2.1
 * Author: Mithila Art
 * License: GPL-2.0+
 * Text Domain: mae
 */

defined('ABSPATH') || exit;

define('MAE_VERSION', '1.5.8');
define('MAE_FILE',    __FILE__);
define('MAE_PATH',    plugin_dir_path(__FILE__));
define('MAE_URL',     plugin_dir_url(__FILE__));

foreach (['activator', 'event-cpt', 'event-meta', 'registration', 'payment', 'admin', 'settings', 'shortcodes', 'checkout', 'art-checkout', 'content-cpts'] as $file) {
    require_once MAE_PATH . "includes/class-mae-{$file}.php";
}

register_activation_hook(__FILE__, ['MAE_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['MAE_Activator', 'deactivate']);
add_action('admin_init', ['MAE_Activator', 'maybe_upgrade_db']);

/**
 * Format a date or date range as human-readable text.
 * e.g. "June 5–20, 2025" or "Jun 5 – Jul 3, 2025" or "Dec 30, 2025 – Jan 2, 2026"
 */
function mae_format_date_range($start, $end = '', $format_single = 'F j, Y') {
    if (!$start) return '';
    $s = strtotime($start);
    if (!$end || $end === $start) {
        return date($format_single, $s);
    }
    $e = strtotime($end);
    if (date('Y', $s) === date('Y', $e)) {
        if (date('n', $s) === date('n', $e)) {
            return date('F j', $s) . '–' . date('j, Y', $e);
        }
        return date('M j', $s) . ' – ' . date('M j, Y', $e);
    }
    return date('M j, Y', $s) . ' – ' . date('M j, Y', $e);
}

/**
 * Temporal status of an event based on its start/end dates versus today.
 *
 * @return string 'upcoming' | 'ongoing' | 'past' | '' (when no start date is set)
 *   - upcoming : current date is before the start date.
 *   - ongoing  : start date <= current date <= end date (end falls back to start
 *                date for single-day events).
 *   - past     : current date is after the end date.
 */
function mae_event_time_status($event_id) {
    $start = get_post_meta($event_id, '_mae_date', true);
    if (!$start) {
        return '';
    }

    $end     = get_post_meta($event_id, '_mae_end_date', true);
    $today   = date('Y-m-d');
    $start_d = date('Y-m-d', strtotime($start));
    $end_d   = $end ? date('Y-m-d', strtotime($end)) : $start_d;

    if ($today < $start_d) {
        return 'upcoming';
    }
    if ($today <= $end_d) {
        return 'ongoing';
    }
    return 'past';
}

/**
 * Whether an event is currently ongoing (start date <= today <= end date).
 */
function mae_event_is_ongoing($event_id) {
    return mae_event_time_status($event_id) === 'ongoing';
}

/**
 * Single source of truth for whether registration is closed for an event.
 *
 * Registration is closed only when the per-event "Registration Closed" setting
 * is enabled. Event dates (ongoing/past) do NOT close registration on their own,
 * so registration can stay open during an event if the admin wants it.
 *
 * This lets the registration status be managed entirely from the event settings
 * in the backend while staying consistent across listings and the detail page.
 */
function mae_is_registration_closed($event_id) {
    return get_post_meta($event_id, '_mae_registration_closed', true) === '1';
}

add_action('plugins_loaded', function () {
    new MAE_Event_CPT();
    new MAE_Event_Meta();
    new MAE_Registration();
    new MAE_Payment();
    new MAE_Admin();
    new MAE_Settings();
    new MAE_Shortcodes();
    new MAE_Checkout();
    new MAE_Art_Checkout();
    new MAE_Content_CPTs();
});
