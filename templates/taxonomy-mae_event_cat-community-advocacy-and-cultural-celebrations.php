<?php
/**
 * Event category template — Community Advocacy & Cultural Celebrations
 * Overrides taxonomy-mae_event_cat.php for this specific term.
 *
 * @package Mithila_Art_Events
 * @version 1.0.0
 */
defined('ABSPATH') || exit;

get_header();

$term    = get_queried_object();
$today   = date('Y-m-d');

$all_events = get_posts([
    'post_type'   => 'mae_event',
    'numberposts' => -1,
    'tax_query'   => [['taxonomy' => 'mae_event_cat', 'terms' => $term->term_id]],
    'meta_key'    => '_mae_date',
    'orderby'     => 'meta_value',
    'order'       => 'ASC',
]);

$upcoming = [];
$past     = [];
foreach ($all_events as $ev) {
    $d = get_post_meta($ev->ID, '_mae_date', true);
    if (!$d || $d >= $today) $upcoming[] = $ev;
    else $past[] = $ev;
}
$past_reversed = array_reverse($past);

$cat_img_id  = (int) get_term_meta($term->term_id, '_mae_cat_image_id', true);
$cat_img_url = '';
if (!$cat_img_id) {
    foreach ($all_events as $ev) {
        if (has_post_thumbnail($ev->ID)) { $cat_img_id = get_post_thumbnail_id($ev->ID); break; }
    }
}
if ($cat_img_id) {
    $src = wp_get_attachment_image_src($cat_img_id, 'large');
    if ($src) $cat_img_url = $src[0];
}

// About section image
$about_img_id  = (int) get_term_meta($term->term_id, '_mae_cat_about_image_id', true);
$about_img_url = '';
if ($about_img_id) {
    $src = wp_get_attachment_image_src($about_img_id, 'large');
    if ($src) $about_img_url = $src[0];
}
if (!$about_img_url) {
    foreach (array_reverse($all_events) as $ev) {
        if (has_post_thumbnail($ev->ID) && get_post_thumbnail_id($ev->ID) !== $cat_img_id) {
            $src = wp_get_attachment_image_src(get_post_thumbnail_id($ev->ID), 'large');
            if ($src) { $about_img_url = $src[0]; break; }
        }
    }
}

$total_count    = count($all_events);
$upcoming_count = count($upcoming);
$past_count     = count($past);
$donate_url     = function_exists('mithila_art_get_donations_page_url') ? mithila_art_get_donations_page_url() : home_url('/donation/');
?>

<div class="mae-tax-page mae-tax-rich">

<!-- ═══════════════════════════════════════════════════════════
     1. HERO BANNER
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-hero" <?php if ($cat_img_url) echo 'style="--hero-img:url(\'' . esc_url($cat_img_url) . '\')"'; ?>>
    <div class="mae-tax-hero__overlay"></div>
    <div class="mae-tax-container mae-tax-hero__inner">
        <p class="mae-tax-hero__kicker"><?php esc_html_e('Programs &amp; Events', 'mithila-art-events'); ?></p>
        <h1 class="mae-tax-hero__title"><?php echo esc_html($term->name); ?></h1>
        <div class="mae-tax-hero__divider"></div>
        <p class="mae-tax-hero__desc"><?php esc_html_e('Mithila Center USA brings people together to celebrate identity, preserve collective memory, and create greater public visibility for Mithila art — through festivals, parades, cultural observances, and civic advocacy that build awareness, recognition, and community pride across the United States and beyond.', 'mithila-art-events'); ?></p>
        <div class="mae-tax-hero__pills">
            <span class="mae-tax-hero__pill"><?php echo $total_count; ?> <?php esc_html_e('Total Events', 'mithila-art-events'); ?></span>
            <?php if ($upcoming_count > 0) : ?>
            <span class="mae-tax-hero__pill mae-tax-hero__pill--green"><?php echo $upcoming_count; ?> <?php esc_html_e('Upcoming', 'mithila-art-events'); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     2. ABOUT — cream intro
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-about">
    <div class="mae-tax-container mae-tax-about__grid">

        <div class="mae-tax-about__body">
            <p class="mae-tax-about__kicker"><?php esc_html_e('Our Community Programs', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-about__heading"><?php esc_html_e('Building Cultural Visibility &amp; Community Pride', 'mithila-art-events'); ?></h2>
            <div class="mae-tax-about__divider"></div>
            <p><?php esc_html_e('Mithila Center USA\'s community events are not performances — they are living expressions of collective identity. From the annual Mithila Festival USA to Vivah Panchami observances and Jur Sital New Year celebrations, our programming keeps the heartbeat of Mithila culture alive in the diaspora, creating spaces where community members can gather, celebrate, and transmit traditions to the next generation.', 'mithila-art-events'); ?></p>
            <p><?php esc_html_e('Beyond celebration, our programs serve an advocacy mission. Mithila Heritage Parades and public cultural presentations create visibility in civic and multicultural spaces — demanding recognition for an art tradition that is too often overlooked in mainstream cultural conversations. When Mithila art is displayed at the European Union Headquarters or marched through New York streets, it is a statement of presence and belonging.', 'mithila-art-events'); ?></p>
            <p><?php esc_html_e('Our community events are open and welcoming. Whether you are part of the Mithila diaspora reconnecting with your roots, or a curious neighbor discovering this rich tradition for the first time, our doors are open. Community advocacy begins with showing up — and we make it easy to do so.', 'mithila-art-events'); ?></p>
            <ul class="mae-tax-about__features">
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Annual Mithila Festival USA — music, dance, art &amp; cultural celebration', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Heritage Parades bringing Mithila culture into civic &amp; multicultural spaces', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Traditional observances — Vivah Panchami, Jur Sital &amp; seasonal rituals', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Global advocacy at the UN, EU &amp; international platforms', 'mithila-art-events'); ?></span>
                </li>
            </ul>

            <a href="<?php echo esc_url(home_url('/volunteer/')); ?>" class="mae-tax-about__btn"><?php esc_html_e('Join Our Community', 'mithila-art-events'); ?> &#x2192;</a>
        </div>

        <div class="mae-tax-about__img-wrap">
            <?php if ($about_img_url) : ?>
            <img src="<?php echo esc_url($about_img_url); ?>"
                 alt="<?php echo esc_attr($term->name); ?>"
                 class="mae-tax-about__img"
                 loading="lazy">
            <?php else : ?>
            <div class="mae-tax-about__img-placeholder" aria-hidden="true">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                    <rect width="80" height="80" rx="8" fill="rgba(131,77,34,.08)"/>
                    <path d="M20 56l14-18 10 12 8-10 8 16H20z" fill="rgba(131,77,34,.2)"/>
                    <circle cx="54" cy="28" r="7" fill="rgba(131,77,34,.2)"/>
                </svg>
                <p><?php esc_html_e('Add an image via Events &rsaquo; Categories', 'mithila-art-events'); ?></p>
            </div>
            <?php endif; ?>
            <div class="mae-tax-about__img-caption">
                <span class="mae-tax-about__img-caption-dot" aria-hidden="true"></span>
                <?php esc_html_e('Community united through culture, art &amp; shared heritage', 'mithila-art-events'); ?>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     3. UPCOMING EVENTS
═══════════════════════════════════════════════════════════ -->
<?php if (!empty($upcoming)) : ?>
<section class="mae-tax-events-section mae-tax-events-upcoming">
    <div class="mae-tax-container">
        <div class="mae-tax-section-hdr">
            <div>
                <p class="mae-tax-section-kicker"><?php esc_html_e('Mark Your Calendar', 'mithila-art-events'); ?></p>
                <h2 class="mae-tax-section-heading"><?php esc_html_e('Upcoming Events', 'mithila-art-events'); ?></h2>
            </div>
            <span class="mae-tax-count-badge"><?php echo $upcoming_count; ?> <?php esc_html_e('Events', 'mithila-art-events'); ?></span>
        </div>

        <div class="mae-events-grid">
            <?php foreach ($upcoming as $ev) :
                $id       = $ev->ID;
                $date     = get_post_meta($id, '_mae_date',     true);
                $end_date = get_post_meta($id, '_mae_end_date', true);
                $time     = get_post_meta($id, '_mae_time',     true);
                $loc      = get_post_meta($id, '_mae_location', true);
                $type     = get_post_meta($id, '_mae_type',     true) ?: 'free';
                $price    = (float) get_post_meta($id, '_mae_price', true);
                $is_paid  = $type === 'paid';
                $is_today = $date === $today;
                $excerpt  = get_the_excerpt($ev);
            ?>
            <article class="mae-event-card">
                <a href="<?php echo get_permalink($id); ?>" class="mae-card-img-link">
                    <?php if (has_post_thumbnail($id)) : echo get_the_post_thumbnail($id, 'medium_large', ['class' => 'mae-card-img']); else : ?>
                    <div class="mae-card-img-placeholder"></div>
                    <?php endif; ?>
                    <?php if ($is_today) : ?><span class="mae-card-today">Today</span><?php endif; ?>
                    <div class="mae-card-badge <?php echo $is_paid ? 'mae-badge-paid' : 'mae-badge-free'; ?>">
                        <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                    </div>
                </a>
                <div class="mae-card-body">
                    <?php if ($date) : ?>
                    <div class="mae-card-date-strip">
                        <?php $s=strtotime($date);$e=$end_date?strtotime($end_date):0;$sm=$e&&date('nY',$s)===date('nY',$e);?>
                        <span class="mae-card-month"><?php echo date('M',$s);if($e&&!$sm)echo' &ndash; '.date('M',$e);?></span>
                        <span class="mae-card-day"><?php echo date('j',$s);if($sm)echo'&ndash;'.date('j',$e);?></span>
                    </div>
                    <?php endif; ?>
                    <div class="mae-card-content">
                        <h3 class="mae-card-title"><a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a></h3>
                        <div class="mae-card-meta">
                            <?php if ($date) : ?>
                            <div class="mae-card-meta-item">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($time) : ?>
                            <div class="mae-card-meta-item">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <?php echo date('g:i A', strtotime($time)); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($loc) : ?>
                            <div class="mae-card-meta-item">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?php echo esc_html($loc); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($excerpt) : ?>
                        <p class="mae-card-excerpt"><?php echo esc_html($excerpt); ?></p>
                        <?php endif; ?>
                        <div class="mae-card-footer">
                            <div class="mae-card-price-tag <?php echo $is_paid ? 'paid' : 'free'; ?>">
                                <?php if ($is_paid) : ?>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                $<?php echo number_format($price, 2); ?> per ticket
                                <?php else : ?>Free Entry<?php endif; ?>
                            </div>
                            <div class="mae-card-actions">
                                <a href="<?php echo get_permalink($id); ?>" class="mae-card-btn-view">View</a>
                                <a href="<?php echo esc_url(MAE_Checkout::url($id)); ?>" class="mae-card-btn-apply">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     4. WHAT OUR PROGRAMS OFFER — brown feature band
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-why">
    <div class="mae-tax-container">
        <p class="mae-tax-why__kicker"><?php esc_html_e('The Mithila Community Experience', 'mithila-art-events'); ?></p>
        <h2 class="mae-tax-why__heading"><?php esc_html_e('What Our Community Events Offer', 'mithila-art-events'); ?></h2>
        <div class="mae-tax-why__divider"></div>
        <ul class="mae-tax-why__grid">
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Annual Cultural Festivals', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('The Mithila Festival USA is our flagship community event — a multi-day celebration bringing together music, dance, art, food, and cultural programming from across the Mithila diaspora. Open to all, it is a joyful affirmation of shared identity.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M17.63 5.84C17.27 5.33 16.67 5 16 5L5 5.01C3.9 5.01 3 5.9 3 7v10c0 1.1.9 1.99 2 1.99L16 19c.67 0 1.27-.33 1.63-.84L22 12l-4.37-6.16z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Heritage Parades &amp; Public Advocacy', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Our Mithila Heritage Parades take culture into the streets — with performance, costume, art banners, and community spirit creating powerful public visibility for Mithila art in New York and across America\'s multicultural civic landscape.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Traditional Observances', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Vivah Panchami, Jur Sital (Mithila New Year), and other traditional observances keep the ritual calendar of Mithila culture alive in the diaspora — connecting community members to seasonal rhythms and ancestral practices across generations.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Global-Stage Advocacy', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('From the United Nations Headquarters to the European Union in Brussels, Mithila Center USA takes cultural advocacy to the world\'s most prestigious platforms — ensuring Mithila art is seen, heard, and respected at the highest levels of international dialogue.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Inclusive Community Spaces', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Our events are radically welcoming — designed for diaspora families reconnecting with roots, for curious neighbors discovering Mithila culture for the first time, and for everyone in between. Community solidarity begins with showing up together.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Cultural Memory Preservation', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Every festival, parade, and celebration is an act of preservation — keeping collective memory alive outside its geographic origin. When communities gather to celebrate Mithila art in New York or Brussels, a living tradition crosses borders and endures.', 'mithila-art-events'); ?></p>
            </li>
        </ul>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     4b. SPOTLIGHT — image left, text right
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-spotlight">
    <div class="mae-tax-container mae-tax-spotlight__grid">

        <div class="mae-tax-spotlight__img-col">
            <?php if ($cat_img_url) : ?>
            <img src="<?php echo esc_url($cat_img_url); ?>"
                 alt="<?php echo esc_attr($term->name); ?>"
                 class="mae-tax-spotlight__img"
                 loading="lazy">
            <?php else : ?>
            <div class="mae-tax-spotlight__img-ph" aria-hidden="true">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                    <rect width="80" height="80" rx="8" fill="rgba(253,246,227,.15)"/>
                    <path d="M20 56l14-18 10 12 8-10 8 16H20z" fill="rgba(253,246,227,.3)"/>
                    <circle cx="54" cy="28" r="7" fill="rgba(253,246,227,.3)"/>
                </svg>
            </div>
            <?php endif; ?>
            <div class="mae-tax-spotlight__img-badge">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <?php esc_html_e('Community Program', 'mithila-art-events'); ?>
            </div>
        </div>

        <div class="mae-tax-spotlight__text-col">
            <p class="mae-tax-spotlight__kicker"><?php esc_html_e('Culture in Action', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-spotlight__heading"><?php esc_html_e('Where Community Comes Together', 'mithila-art-events'); ?></h2>
            <div class="mae-tax-spotlight__divider"></div>

            <blockquote class="mae-tax-spotlight__quote">
                <?php esc_html_e('"Culture does not survive in archives — it survives in celebrations, in streets, in the hands of people who refuse to let it disappear."', 'mithila-art-events'); ?>
            </blockquote>

            <p class="mae-tax-spotlight__body"><?php esc_html_e('Every Mithila festival, parade, and traditional observance we host is an act of cultural survival. When the diaspora gathers — at Diversity Plaza, at UN headquarters, or in community halls across New York — they are not merely celebrating. They are transmitting. They are ensuring that the songs, rituals, and visual language of Mithila remain alive outside their geographic origin, carried forward by people who love what they come from.', 'mithila-art-events'); ?></p>

            <p class="mae-tax-spotlight__body"><?php esc_html_e('Our community advocacy work extends beyond celebration into civic presence. From Mithila Heritage Parades that bring culture into the streets to presentations at the European Union in Brussels, we demand recognition for an art tradition that has too long been overlooked. Showing up — loudly, joyfully, and publicly — is itself a political act of cultural pride.', 'mithila-art-events'); ?></p>

            <div class="mae-tax-spotlight__stats">
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">10+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Annual Festivals', 'mithila-art-events'); ?></span>
                </div>
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">50K+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Community Members Reached', 'mithila-art-events'); ?></span>
                </div>
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">25+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Civic &amp; Cultural Partners', 'mithila-art-events'); ?></span>
                </div>
            </div>

            <div class="mae-tax-spotlight__actions">
                <a href="<?php echo esc_url(home_url('/volunteer/')); ?>" class="mae-tax-spotlight__btn mae-tax-spotlight__btn--primary">
                    <?php esc_html_e('Join Our Community', 'mithila-art-events'); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="<?php echo esc_url($donate_url); ?>" class="mae-tax-spotlight__btn mae-tax-spotlight__btn--ghost">
                    <?php esc_html_e('Support Our Events', 'mithila-art-events'); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </a>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     5. PAST EVENTS
═══════════════════════════════════════════════════════════ -->
<?php if (!empty($past)) : ?>
<section class="mae-tax-events-section mae-tax-events-past">
    <div class="mae-tax-container">
        <div class="mae-tax-section-hdr">
            <div>
                <p class="mae-tax-section-kicker"><?php esc_html_e('Our History', 'mithila-art-events'); ?></p>
                <h2 class="mae-tax-section-heading mae-tax-past-heading-text"><?php esc_html_e('Past Community Events', 'mithila-art-events'); ?></h2>
            </div>
            <span class="mae-tax-count-badge mae-tax-count-badge--past"><?php echo $past_count; ?> <?php esc_html_e('Completed', 'mithila-art-events'); ?></span>
        </div>

        <div class="mae-events-grid">
            <?php foreach ($past_reversed as $ev) :
                $id       = $ev->ID;
                $date     = get_post_meta($id, '_mae_date',     true);
                $end_date = get_post_meta($id, '_mae_end_date', true);
                $loc      = get_post_meta($id, '_mae_location', true);
                $type    = get_post_meta($id, '_mae_type',     true) ?: 'free';
                $price   = (float) get_post_meta($id, '_mae_price', true);
                $is_paid = $type === 'paid';
                $excerpt = get_the_excerpt($ev);
            ?>
            <article class="mae-event-card mae-card-past mae-card-past--themed">
                <a href="<?php echo get_permalink($id); ?>" class="mae-card-img-link">
                    <?php if (has_post_thumbnail($id)) : echo get_the_post_thumbnail($id, 'medium_large', ['class' => 'mae-card-img']); else : ?>
                    <div class="mae-card-img-placeholder"></div>
                    <?php endif; ?>
                    <div class="mae-card-past-overlay">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                        <?php esc_html_e('Concluded', 'mithila-art-events'); ?>
                    </div>
                    <div class="mae-card-badge <?php echo $is_paid ? 'mae-badge-paid' : 'mae-badge-free'; ?>">
                        <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                    </div>
                </a>
                <div class="mae-card-body">
                    <?php if ($date) : ?>
                    <div class="mae-card-date-strip mae-date-past-themed">
                        <?php $s=strtotime($date);$e=$end_date?strtotime($end_date):0;$sm=$e&&date('nY',$s)===date('nY',$e);?>
                        <span class="mae-card-month"><?php echo date('M',$s);if($e&&!$sm)echo' &ndash; '.date('M',$e);?></span>
                        <span class="mae-card-day"><?php echo date('j',$s);if($sm)echo'&ndash;'.date('j',$e);?></span>
                    </div>
                    <?php endif; ?>
                    <div class="mae-card-content">
                        <h3 class="mae-card-title"><a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a></h3>
                        <div class="mae-card-meta">
                            <?php if ($date) : ?>
                            <div class="mae-card-meta-item">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?php echo mae_format_date_range($date, $end_date); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($loc) : ?>
                            <div class="mae-card-meta-item">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?php echo esc_html($loc); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($excerpt) : ?>
                        <p class="mae-card-excerpt"><?php echo esc_html($excerpt); ?></p>
                        <?php endif; ?>
                        <div class="mae-card-footer">
                            <div class="mae-card-price-tag <?php echo $is_paid ? 'paid' : 'free'; ?>">
                                <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free Entry'; ?>
                            </div>
                            <a href="<?php echo get_permalink($id); ?>" class="mae-card-btn mae-card-btn--past">
                                <?php esc_html_e('View →', 'mithila-art-events'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     6. EMPTY STATE
═══════════════════════════════════════════════════════════ -->
<?php if (empty($upcoming) && empty($past)) : ?>
<section class="mae-tax-events-section">
    <div class="mae-tax-container">
        <div class="mae-empty-state">
            <div class="mae-empty-icon">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3><?php esc_html_e('No events in this category yet', 'mithila-art-events'); ?></h3>
            <p><a href="<?php echo get_post_type_archive_link('mae_event'); ?>"><?php esc_html_e('← Back to all programs', 'mithila-art-events'); ?></a></p>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     7. NEWSLETTER / CTA BAND
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-cta">
    <div class="mae-tax-container mae-tax-cta__inner">
        <div class="mae-tax-cta__text">
            <p class="mae-tax-cta__kicker"><?php esc_html_e('Stay Connected', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-cta__heading"><?php esc_html_e('Be Part of the Celebration', 'mithila-art-events'); ?></h2>
            <p class="mae-tax-cta__desc"><?php esc_html_e('Subscribe to get early invitations to festivals, parades, and cultural observances. Events often reach capacity quickly — subscribe to be first in line and never miss a celebration of Mithila art.', 'mithila-art-events'); ?></p>
        </div>
        <div class="mae-tax-cta__actions">
            <form class="mae-tax-cta__form" method="post" action="#" onsubmit="return false;" novalidate>
                <label for="mae-tax-email-cacc" class="screen-reader-text"><?php esc_html_e('Email address', 'mithila-art-events'); ?></label>
                <input id="mae-tax-email-cacc" type="email" name="email" placeholder="<?php esc_attr_e('Your email address…', 'mithila-art-events'); ?>" class="mae-tax-cta__input" required />
                <button type="submit" class="mae-tax-cta__btn"><?php esc_html_e('Subscribe', 'mithila-art-events'); ?></button>
            </form>
            <a href="<?php echo esc_url($donate_url); ?>" class="mae-tax-cta__donate"><?php esc_html_e('Support Our Events', 'mithila-art-events'); ?> &#x2665;</a>
        </div>
    </div>
</section>

</div><!-- .mae-tax-rich -->

<?php get_footer(); ?>
