<?php
defined('ABSPATH') || exit;

class MAE_Settings {

    private static $options = [
        'mae_admin_email',
        'mae_payment_gateway',
        'mae_stripe_publishable_key',
        'mae_stripe_secret_key',
        'mae_paypal_mode',
        'mae_paypal_client_id',
        'mae_paypal_secret',
    ];

    public function __construct() {
        add_action('admin_menu',  [$this, 'menu']);
        add_action('admin_init',  [$this, 'register']);
    }

    public function menu() {
        add_submenu_page(
            'edit.php?post_type=mae_event',
            'Payment Settings',
            'Settings',
            'manage_options',
            'mae-settings',
            [$this, 'page']
        );
    }

    public function register() {
        foreach (self::$options as $opt) {
            register_setting('mae_settings_group', $opt, ['sanitize_callback' => 'sanitize_text_field']);
        }
    }

    public function page() {
        $gateway = get_option('mae_payment_gateway', 'stripe');
        $mode    = get_option('mae_paypal_mode', 'sandbox');
        ?>
        <div class="wrap">
            <h1>Event Booking Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('mae_settings_group'); ?>
                <table class="form-table" role="presentation">

                    <tr>
                        <th scope="row"><label for="mae_admin_email">Notification Email</label></th>
                        <td>
                            <input type="email" id="mae_admin_email" name="mae_admin_email"
                                   class="regular-text"
                                   value="<?php echo esc_attr(get_option('mae_admin_email')); ?>">
                            <p class="description">Admin receives new registration alerts at this address.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="mae_payment_gateway">Payment Gateway</label></th>
                        <td>
                            <select id="mae_payment_gateway" name="mae_payment_gateway">
                                <option value="stripe" <?php selected($gateway, 'stripe'); ?>>Stripe</option>
                                <option value="paypal" <?php selected($gateway, 'paypal'); ?>>PayPal</option>
                            </select>
                        </td>
                    </tr>

                </table>

                <h2 class="title">Stripe</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="mae_stripe_publishable_key">Publishable Key</label></th>
                        <td>
                            <input type="text" id="mae_stripe_publishable_key"
                                   name="mae_stripe_publishable_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('mae_stripe_publishable_key')); ?>"
                                   placeholder="pk_live_...">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mae_stripe_secret_key">Secret Key</label></th>
                        <td>
                            <input type="password" id="mae_stripe_secret_key"
                                   name="mae_stripe_secret_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('mae_stripe_secret_key')); ?>"
                                   placeholder="sk_live_...">
                        </td>
                    </tr>
                </table>

                <h2 class="title">PayPal</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="mae_paypal_mode">Mode</label></th>
                        <td>
                            <select id="mae_paypal_mode" name="mae_paypal_mode">
                                <option value="sandbox" <?php selected($mode, 'sandbox'); ?>>Sandbox (Testing)</option>
                                <option value="live"    <?php selected($mode, 'live');    ?>>Live</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mae_paypal_client_id">Client ID</label></th>
                        <td>
                            <input type="text" id="mae_paypal_client_id" name="mae_paypal_client_id"
                                   class="regular-text"
                                   value="<?php echo esc_attr(get_option('mae_paypal_client_id')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mae_paypal_secret">Secret</label></th>
                        <td>
                            <input type="password" id="mae_paypal_secret" name="mae_paypal_secret"
                                   class="regular-text"
                                   value="<?php echo esc_attr(get_option('mae_paypal_secret')); ?>">
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }
}
