<?php
defined('ABSPATH') || exit;

class MAE_Activator {

    public static function activate() {
        global $wpdb;
        $table   = $wpdb->prefix . 'mae_registrations';
        $charset = $wpdb->get_charset_collate();

        // dbDelta requires no IF NOT EXISTS — it handles create & alter automatically
        $sql = "CREATE TABLE {$table} (
            id              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id        bigint(20) UNSIGNED NOT NULL,
            registration_type varchar(20) NOT NULL,

            volunteer_name         varchar(255) DEFAULT NULL,
            volunteer_email        varchar(255) DEFAULT NULL,
            volunteer_phone        varchar(50)  DEFAULT NULL,
            volunteer_city         varchar(100) DEFAULT NULL,
            volunteer_university   varchar(255) DEFAULT NULL,
            volunteer_help_area    varchar(255) DEFAULT NULL,
            volunteer_availability varchar(100) DEFAULT NULL,
            volunteer_skills       text         DEFAULT NULL,
            volunteer_experience   text         DEFAULT NULL,
            volunteer_motivation   text         DEFAULT NULL,
            volunteer_gain         text         DEFAULT NULL,

            company_name            varchar(255) DEFAULT NULL,
            org_type                varchar(100) DEFAULT NULL,
            contact_person          varchar(255) DEFAULT NULL,
            sponsor_email           varchar(255) DEFAULT NULL,
            sponsor_phone           varchar(50)  DEFAULT NULL,
            website                 varchar(500) DEFAULT NULL,
            participation_interests text         DEFAULT NULL,
            sponsorship_type        varchar(100) DEFAULT NULL,
            budget                  varchar(100) DEFAULT NULL,
            logo_path               varchar(500) DEFAULT NULL,
            sponsor_message         text         DEFAULT NULL,

            attendee_date            date         DEFAULT NULL,
            attendee_city            varchar(100) DEFAULT NULL,
            attendee_country         varchar(100) DEFAULT NULL,
            attendee_age_group       varchar(50)  DEFAULT NULL,
            attendee_gender          varchar(30)  DEFAULT NULL,
            attendee_source          varchar(100) DEFAULT NULL,
            attendee_notes           text         DEFAULT NULL,
            attendee_emergency_name  varchar(255) DEFAULT NULL,
            attendee_emergency_phone varchar(50)  DEFAULT NULL,

            ticket_quantity  int          NOT NULL DEFAULT 0,
            amount           decimal(10,2) NOT NULL DEFAULT 0.00,
            payment_status   varchar(50)  NOT NULL DEFAULT 'pending',
            payment_method   varchar(50)  NOT NULL DEFAULT 'free',
            payment_id       varchar(255) DEFAULT NULL,

            status           varchar(20)  NOT NULL DEFAULT 'pending',
            created_at       datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY (id),
            KEY event_id (event_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $art_table = $wpdb->prefix . 'mae_art_orders';
        $art_sql = "CREATE TABLE {$art_table} (
            id             bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id       bigint(20) UNSIGNED NOT NULL,
            artist_name    varchar(255) NOT NULL DEFAULT '',
            art_id         bigint(20) UNSIGNED NOT NULL,
            art_title      varchar(255) DEFAULT NULL,
            art_price      decimal(10,2) NOT NULL DEFAULT 0.00,
            buyer_name     varchar(255) NOT NULL,
            buyer_email    varchar(255) NOT NULL,
            buyer_phone    varchar(50)  DEFAULT NULL,
            payment_id     varchar(255) DEFAULT NULL,
            payment_status varchar(50)  NOT NULL DEFAULT 'pending',
            created_at     datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_id (event_id)
        ) {$charset};";
        dbDelta($art_sql);

        add_option('mae_payment_gateway',        'stripe');
        add_option('mae_stripe_publishable_key', '');
        add_option('mae_stripe_secret_key',      '');
        add_option('mae_paypal_client_id',       '');
        add_option('mae_paypal_secret',          '');
        add_option('mae_paypal_mode',            'sandbox');
        add_option('mae_admin_email',            get_option('admin_email'));

        flush_rewrite_rules();
    }

    /** Runs dbDelta on every admin load when the stored DB version is behind. */
    public static function maybe_upgrade_db() {
        if (get_option('mae_db_version') === MAE_VERSION) return;
        self::activate();
        update_option('mae_db_version', MAE_VERSION);
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}
