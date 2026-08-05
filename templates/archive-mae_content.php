<?php
/**
 * CPT archive — Leadership, Partner, Press Release, Recognition, Artist.
 *
 * @package Mithila_Art_Events
 * @version 2.0.0
 */
defined('ABSPATH') || exit;

get_header();

$post_type = get_queried_object()->name ?? 'mae_leadership';
$type_map  = [
    'mae_leadership'    => ['key' => 'leadership',    'heading' => 'Our Leadership Team',      'subtext' => 'Meet the dedicated people driving our mission forward.',                     'read_more' => 'Read More',        'kicker' => 'The People Behind the Mission'],
    'mae_partner'       => ['key' => 'partner',       'heading' => 'Partners & Collaborators', 'subtext' => 'Organisations that share our commitment to Mithila art and cultural heritage.','read_more' => 'Learn More',       'kicker' => 'Building Together'],
    'mae_press_release' => ['key' => 'press-release', 'heading' => 'Press &amp; Media',        'subtext' => 'News, announcements, and press releases from Mithila Art Foundation.',        'read_more' => 'Read Full Release', 'kicker' => 'Latest News'],
    'mae_recognition'   => ['key' => 'recognition',   'heading' => 'Recognition &amp; Awards', 'subtext' => 'Institutions and organisations that have recognised and honoured our work.',   'read_more' => 'Learn More',       'kicker' => 'Honours & Recognition'],
    'mae_artist'        => ['key' => 'artist',        'heading' => 'Associated Artists',       'subtext' => 'Talented Mithila artists connected with the foundation.',                    'read_more' => 'View Profile',     'kicker' => 'The Artists'],
];
$cfg      = $type_map[$post_type] ?? $type_map['mae_leadership'];
$type_key = $cfg['key'];
$svg_icon = MAE_Content_CPTs::cpt_icon($type_key);
?>

<div class="mae-cpt-arch mae-cpt-arch--<?php echo esc_attr($type_key); ?>">

    <!-- ══ Hero ══════════════════════════════════════════════════ -->
    <section class="mae-cpt-arch__hero" aria-labelledby="mae-cpt-arch-title">
        <div class="mae-cpt-arch__hero-pattern" aria-hidden="true"></div>
        <div class="mae-cpt-arch__hero-inner">
            <p class="mae-cpt-arch__hero-kicker"><?php echo esc_html($cfg['kicker']); ?></p>
            <h1 id="mae-cpt-arch-title" class="mae-cpt-arch__hero-title"><?php echo $cfg['heading']; ?></h1>
            <div class="mae-cpt-arch__hero-divider" aria-hidden="true"></div>
            <p class="mae-cpt-arch__hero-sub"><?php echo esc_html($cfg['subtext']); ?></p>
        </div>
    </section>

    <!-- ══ Cards grid ════════════════════════════════════════════ -->
    <section class="mae-cpt-arch__body">
        <div class="mae-cpt-arch__container">

            <?php if (have_posts()) : ?>
            <div class="mae-cpt-arch__grid">

                <?php while (have_posts()) : the_post();
                    $post_id = get_the_ID();
                    $excerpt = get_post_field('post_excerpt', $post_id) ?: get_the_excerpt();

                    if ($post_type === 'mae_recognition') {
                        $terms    = get_the_terms($post_id, 'mae_recognition_type');
                        $meta_val = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
                        $pr_date  = '';
                    } elseif ($post_type === 'mae_artist') {
                        $terms    = get_the_terms($post_id, 'mae_art_style');
                        $meta_val = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
                        $pr_date  = '';
                    } else {
                        $meta_keys = [
                            'mae_leadership'    => '_mae_cpt_position',
                            'mae_partner'       => '_mae_cpt_partner_type',
                            'mae_press_release' => '_mae_cpt_pr_source',
                        ];
                        $meta_val = get_post_meta($post_id, $meta_keys[$post_type] ?? '', true);
                        $pr_date  = ($post_type === 'mae_press_release')
                            ? get_post_meta($post_id, '_mae_cpt_pr_date', true) : '';
                    }

                    $website = ($post_type === 'mae_partner')
                        ? get_post_meta($post_id, '_mae_cpt_website', true) : '';
                ?>
                <article class="mae-cpt-arch-card mae-cpt-arch-card--<?php echo esc_attr($type_key); ?>">

                    <!-- Image -->
                    <a href="<?php the_permalink(); ?>" class="mae-cpt-arch-card__img-link" tabindex="-1" aria-hidden="true">
                        <?php if (has_post_thumbnail()) :
                            the_post_thumbnail('medium_large', ['class' => 'mae-cpt-arch-card__img']);
                        else : ?>
                        <div class="mae-cpt-arch-card__placeholder">
                            <span aria-hidden="true"><?php echo $svg_icon; ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($meta_val) : ?>
                        <span class="mae-cpt-arch-card__badge"><?php echo esc_html($meta_val); ?></span>
                        <?php elseif ($pr_date) : ?>
                        <span class="mae-cpt-arch-card__badge"><?php echo esc_html(date('M j, Y', strtotime($pr_date))); ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Body -->
                    <div class="mae-cpt-arch-card__body">
                        <h2 class="mae-cpt-arch-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <?php if ($excerpt) : ?>
                        <p class="mae-cpt-arch-card__excerpt">
                            <?php echo esc_html(wp_trim_words($excerpt, 20, '…')); ?>
                        </p>
                        <?php endif; ?>

                        <div class="mae-cpt-arch-card__footer">
                            <?php if ($website) : ?>
                            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer" class="mae-cpt-arch-card__site">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                Visit Website
                            </a>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="mae-cpt-arch-card__btn">
                                <?php echo esc_html($cfg['read_more']); ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                    </div>

                </article>
                <?php endwhile; ?>

            </div><!-- /.mae-cpt-arch__grid -->

            <?php if (function_exists('the_posts_pagination')) : ?>
            <div class="mae-cpt-arch__pagination">
                <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => '← Previous', 'next_text' => 'Next →']); ?>
            </div>
            <?php endif; ?>

            <?php else : ?>
            <div class="mae-cpt-arch__empty">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
                <h3>Nothing here yet</h3>
                <p>Check back soon.</p>
            </div>
            <?php endif; ?>

        </div>
    </section>

</div><!-- /.mae-cpt-arch -->

<?php get_footer(); ?>
