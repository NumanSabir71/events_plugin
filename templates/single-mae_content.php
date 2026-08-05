<?php
/**
 * Single CPT detail page — Leadership, Partner, Press Release, Recognition, Artist.
 *
 * @package Mithila_Art_Events
 * @version 2.0.0
 */
defined('ABSPATH') || exit;

get_header();

$post_type = get_post_type();
$type_map  = [
    'mae_leadership'    => ['key' => 'leadership',    'label' => 'Leadership',          'archive_label' => 'Leadership Team',         'archive_url' => home_url('/mithila-leadership/')],
    'mae_partner'       => ['key' => 'partner',       'label' => 'Partner',             'archive_label' => 'Partners',                'archive_url' => ''],
    'mae_press_release' => ['key' => 'press-release', 'label' => 'Press Release',       'archive_label' => 'Press & Media',           'archive_url' => ''],
    'mae_recognition'   => ['key' => 'recognition',   'label' => 'Recognition & Award', 'archive_label' => 'Recognition & Awards',    'archive_url' => ''],
    'mae_artist'        => ['key' => 'artist',        'label' => 'Artist',              'archive_label' => 'Associated Artists',       'archive_url' => ''],
];
$cfg      = $type_map[$post_type] ?? $type_map['mae_leadership'];
$type_key = $cfg['key'];
$post_id  = get_the_ID();
$svg_icon = MAE_Content_CPTs::cpt_icon($type_key);

$primary_meta = '';
$extra_meta   = [];

if ($post_type === 'mae_leadership') {
    $val = get_post_meta($post_id, '_mae_cpt_position', true);
    if ($val) $primary_meta = $val;
}
if ($post_type === 'mae_partner') {
    $org_type = get_post_meta($post_id, '_mae_cpt_partner_type', true);
    $website  = get_post_meta($post_id, '_mae_cpt_website',      true);
    if ($org_type) $primary_meta = $org_type;
    if ($website)  $extra_meta[] = ['label' => 'Website', 'value' => $website, 'url' => $website];
}
if ($post_type === 'mae_press_release') {
    $pr_date   = get_post_meta($post_id, '_mae_cpt_pr_date',   true);
    $pr_source = get_post_meta($post_id, '_mae_cpt_pr_source', true);
    if ($pr_date)   $extra_meta[] = ['label' => 'Published', 'value' => date('F j, Y', strtotime($pr_date)), 'url' => ''];
    if ($pr_source) $primary_meta = $pr_source;
}
if ($post_type === 'mae_recognition') {
    $terms    = get_the_terms($post_id, 'mae_recognition_type');
    $rec_type = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
    $rec_url  = get_post_meta($post_id, '_mae_rec_url', true);
    if ($rec_type) $primary_meta = $rec_type;
    if ($rec_url)  $extra_meta[] = ['label' => 'Visit', 'value' => $rec_url, 'url' => $rec_url];
}
if ($post_type === 'mae_artist') {
    $terms     = get_the_terms($post_id, 'mae_art_style');
    $style     = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
    $instagram = get_post_meta($post_id, '_mae_artist_instagram', true);
    $website   = get_post_meta($post_id, '_mae_artist_website',   true);
    if ($style)     $primary_meta = $style;
    if ($instagram) $extra_meta[] = ['label' => 'Instagram', 'value' => $instagram, 'url' => $instagram];
    if ($website)   $extra_meta[] = ['label' => 'Website',   'value' => $website,   'url' => $website];
}

// Use explicit archive_url from type_map if set, otherwise fall back to CPT archive
$archive_url = $cfg['archive_url'] ?: get_post_type_archive_link($post_type);
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="mae-cpt-pg mae-cpt-pg--<?php echo esc_attr($type_key); ?>">

    <!-- ══ Type nav bar ══════════════════════════════════════════ -->
    <div class="mae-cpt-pg__nav">
        <div class="mae-cpt-pg__nav-inner">
            <span class="mae-cpt-pg__type-badge">
                <?php echo $svg_icon; ?>
                <?php echo esc_html($cfg['label']); ?>
            </span>
            <?php if ($archive_url) : ?>
            <a href="<?php echo esc_url($archive_url); ?>" class="mae-cpt-pg__back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <?php echo esc_html($cfg['archive_label']); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ Body ══════════════════════════════════════════════════ -->
    <div class="mae-cpt-pg__body">
        <div class="mae-cpt-pg__container">
            <div class="mae-cpt-pg__layout">

                <!-- Main content -->
                <main class="mae-cpt-pg__main">
                    <div class="mae-cpt-pg__content entry-content">
                        <?php
                        $raw = get_the_content();
                        if (trim(strip_tags($raw))) {
                            the_content();
                        } else {
                            // Description is stored as excerpt for most CPTs
                            $excerpt = get_post_field('post_excerpt', $post_id);
                            if ($excerpt) {
                                echo '<p class="mae-cpt-pg__lead">' . nl2br(esc_html($excerpt)) . '</p>';
                            }
                        }
                        ?>
                    </div>
                </main>

                <!-- Info sidebar -->
                <aside class="mae-cpt-pg__sidebar">
                    <div class="mae-cpt-pg__card">

                        <?php if (has_post_thumbnail()) : ?>
                        <div class="mae-cpt-pg__card-img">
                            <?php the_post_thumbnail('medium_large', ['class' => 'mae-cpt-pg__card-img-el']); ?>
                        </div>
                        <?php else : ?>
                        <div class="mae-cpt-pg__card-placeholder mae-cpt-pg__card-placeholder--<?php echo esc_attr($type_key); ?>">
                            <?php echo $svg_icon; ?>
                        </div>
                        <?php endif; ?>

                        <div class="mae-cpt-pg__card-inner">
                            <h2 class="mae-cpt-pg__card-title"><?php the_title(); ?></h2>

                            <?php if ($primary_meta) : ?>
                            <p class="mae-cpt-pg__card-role"><?php echo esc_html($primary_meta); ?></p>
                            <?php endif; ?>

                            <?php foreach ($extra_meta as $m) : ?>
                            <div class="mae-cpt-pg__card-row">
                                <span class="mae-cpt-pg__card-row-label"><?php echo esc_html($m['label']); ?></span>
                                <?php if ($m['url']) : ?>
                                <a href="<?php echo esc_url($m['url']); ?>" target="_blank" rel="noopener noreferrer" class="mae-cpt-pg__card-row-val mae-cpt-pg__link">
                                    <?php echo esc_html($m['value']); ?>
                                </a>
                                <?php else : ?>
                                <span class="mae-cpt-pg__card-row-val"><?php echo esc_html($m['value']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>

                            <?php if ($archive_url) : ?>
                            <a href="<?php echo esc_url($archive_url); ?>" class="mae-cpt-pg__card-back">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                Back to <?php echo esc_html($cfg['archive_label']); ?>
                            </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </aside>

            </div>
        </div>
    </div>

</div><!-- /.mae-cpt-pg -->

<?php endwhile; endif; ?>

<?php get_footer(); ?>
