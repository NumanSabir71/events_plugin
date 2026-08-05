<?php
defined('ABSPATH') || exit;

class MAE_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
    }

    public function menu() {
        add_submenu_page(
            'edit.php?post_type=mae_event',
            'Registrations',
            'Registrations',
            'manage_options',
            'mae-registrations',
            [$this, 'page']
        );
        add_submenu_page(
            'edit.php?post_type=mae_event',
            'Art Orders',
            'Art Orders',
            'manage_options',
            'mae-art-orders',
            [$this, 'art_orders_page']
        );
    }

    public function page() {
        global $wpdb;
        $table = $wpdb->prefix . 'mae_registrations';

        $event_filter = absint($_GET['event_id'] ?? 0);
        $where        = $event_filter
            ? $wpdb->prepare('WHERE r.event_id = %d', $event_filter)
            : '';

        $rows = $wpdb->get_results(
            "SELECT r.*, p.post_title AS event_title
             FROM {$table} r
             LEFT JOIN {$wpdb->posts} p ON p.ID = r.event_id
             {$where}
             ORDER BY r.created_at DESC"
        );

        $events = get_posts([
            'post_type'   => 'mae_event',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Event Registrations</h1>
            <hr class="wp-header-end">

            <form method="get" style="margin:15px 0;">
                <input type="hidden" name="post_type" value="mae_event">
                <input type="hidden" name="page"      value="mae-registrations">
                <select name="event_id" onchange="this.form.submit()" style="min-width:220px;height:32px;">
                    <option value="">All Events</option>
                    <?php foreach ($events as $e) : ?>
                        <option value="<?php echo $e->ID; ?>"
                            <?php selected($event_filter, $e->ID); ?>>
                            <?php echo esc_html($e->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span style="margin-left:10px;color:#666;"><?php echo count($rows); ?> registration(s)</span>
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Event</th>
                        <th style="width:90px">Type</th>
                        <th>Name / Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width:80px">Tickets</th>
                        <th style="width:90px">Amount</th>
                        <th style="width:90px">Payment</th>
                        <th style="width:100px">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)) : ?>
                        <tr>
                            <td colspan="10" style="text-align:center;padding:30px;color:#666;">
                                No registrations found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r) :
                        $type    = $r->registration_type;
                        $is_vol  = $type === 'volunteer';
                        $is_spon = $type === 'sponsor';
                        $is_att  = $type === 'attendee';

                        if ($is_vol || $is_att) {
                            $name  = $r->volunteer_name;
                            $email = $r->volunteer_email;
                            $phone = $r->volunteer_phone;
                        } else {
                            $name  = $r->company_name;
                            $email = $r->sponsor_email;
                            $phone = $r->sponsor_phone;
                        }

                        if ($is_vol) {
                            $type_label = 'Volunteer';
                        } elseif ($is_spon) {
                            $type_label = 'Sponsor';
                        } elseif ($is_att) {
                            $type_label = 'Attendee';
                        } else {
                            $type_label = ucfirst($type);
                        }
                    ?>
                        <tr>
                            <td><?php echo $r->id; ?></td>
                            <td><strong><?php echo esc_html($r->event_title); ?></strong></td>
                            <td>
                                <span class="mae-badge <?php echo esc_attr($type); ?>">
                                    <?php echo esc_html($type_label); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($name); ?></td>
                            <td><?php echo esc_html($email); ?></td>
                            <td><?php echo esc_html($phone); ?></td>
                            <td><?php echo $r->ticket_quantity ?: '—'; ?></td>
                            <td><?php echo $r->amount > 0 ? '$' . number_format($r->amount, 2) : 'Free'; ?></td>
                            <td>
                                <span class="mae-status <?php echo esc_attr($r->payment_status); ?>">
                                    <?php echo ucfirst($r->payment_status); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($r->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
    }

    public function art_orders_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'mae_art_orders';

        $event_filter = absint($_GET['event_id'] ?? 0);
        $where        = $event_filter
            ? $wpdb->prepare('WHERE o.event_id = %d', $event_filter)
            : '';

        $rows = $wpdb->get_results(
            "SELECT o.*, p.post_title AS event_title
             FROM {$table} o
             LEFT JOIN {$wpdb->posts} p ON p.ID = o.event_id
             {$where}
             ORDER BY o.created_at DESC"
        );

        $events = get_posts([
            'post_type'   => 'mae_event',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Art Orders</h1>
            <hr class="wp-header-end">

            <form method="get" style="margin:15px 0;">
                <input type="hidden" name="post_type" value="mae_event">
                <input type="hidden" name="page"      value="mae-art-orders">
                <select name="event_id" onchange="this.form.submit()" style="min-width:220px;height:32px;">
                    <option value="">All Events</option>
                    <?php foreach ($events as $e) : ?>
                        <option value="<?php echo $e->ID; ?>"
                            <?php selected($event_filter, $e->ID); ?>>
                            <?php echo esc_html($e->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span style="margin-left:10px;color:#666;"><?php echo count($rows); ?> order(s)</span>
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Event</th>
                        <th>Artist</th>
                        <th>Art Piece</th>
                        <th>Buyer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width:90px">Amount</th>
                        <th style="width:90px">Status</th>
                        <th style="width:100px">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)) : ?>
                        <tr>
                            <td colspan="10" style="text-align:center;padding:30px;color:#666;">
                                No art orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r) : ?>
                        <tr>
                            <td><?php echo $r->id; ?></td>
                            <td><strong><?php echo esc_html($r->event_title); ?></strong></td>
                            <td><?php echo esc_html($r->artist_name); ?></td>
                            <td>
                                <?php
                                $thumb = $r->art_id ? wp_get_attachment_image_url($r->art_id, [40, 40]) : '';
                                if ($thumb) echo '<img src="' . esc_url($thumb) . '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:6px;">';
                                echo esc_html($r->art_title ?: '—');
                                ?>
                            </td>
                            <td><?php echo esc_html($r->buyer_name); ?></td>
                            <td><?php echo esc_html($r->buyer_email); ?></td>
                            <td><?php echo esc_html($r->buyer_phone ?: '—'); ?></td>
                            <td><?php echo $r->art_price > 0 ? '$' . number_format($r->art_price, 2) : '—'; ?></td>
                            <td>
                                <span class="mae-status <?php echo esc_attr($r->payment_status); ?>">
                                    <?php echo ucfirst($r->payment_status); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($r->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <style>
        .mae-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        .mae-badge.volunteer { background: #d4edda; color: #155724; }
        .mae-badge.sponsor   { background: #cce5ff; color: #004085; }
        .mae-badge.attendee  { background: #ede9fe; color: #4c1d95; }
        .mae-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .mae-status.completed { background: #d4edda; color: #155724; }
        .mae-status.pending   { background: #fff3cd; color: #856404; }
        .mae-status.free      { background: #e9ecef; color: #495057; }
        .mae-status.failed    { background: #f8d7da; color: #721c24; }
        </style>
        <?php
    }
}
