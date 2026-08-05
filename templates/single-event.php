<?php
/**
 * Single event template.
 *
 * @package Mithila_Art_Events
 * @version 2.2.0
 */
defined('ABSPATH') || exit;

get_header();

$event_id = get_the_ID();
$date     = get_post_meta($event_id, '_mae_date',     true);
$end_date = get_post_meta($event_id, '_mae_end_date', true);
$time     = get_post_meta($event_id, '_mae_time',     true);
$location = get_post_meta($event_id, '_mae_location', true);
$type     = get_post_meta($event_id, '_mae_type',     true) ?: 'free';
$price    = (float) get_post_meta($event_id, '_mae_price', true);
$is_paid  = $type === 'paid';
$cats     = get_the_terms($event_id, 'mae_event_cat');
$cat      = ($cats && !is_wp_error($cats)) ? $cats[0] : null;
$artists  = get_post_meta($event_id, '_mae_artists', true) ?: [];
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="mae-ev-pg">

    <!-- ══ Meta strip ════════════════════════════════════════════ -->
    <div class="mae-ev-pg__strip">
        <div class="mae-ev-pg__strip-inner">
            <div class="mae-ev-pg__strip-meta">
                <?php if ($date) : ?>
                <span class="mae-ev-pg__strip-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?>
                </span>
                <?php endif; ?>
                <?php if ($time) : ?>
                <span class="mae-ev-pg__strip-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo date('g:i A', strtotime($time)); ?>
                </span>
                <?php endif; ?>
                <?php if ($location) : ?>
                <span class="mae-ev-pg__strip-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?php echo esc_html($location); ?>
                </span>
                <?php endif; ?>
            </div>
            <span class="mae-ev-pg__strip-price <?php echo $is_paid ? 'is-paid' : 'is-free'; ?>">
                <?php echo $is_paid ? '$' . number_format($price, 2) . ' / ticket' : 'Free Entry'; ?>
            </span>
        </div>
    </div>

    <!-- ══ Body ══════════════════════════════════════════════════ -->
    <div class="mae-ev-pg__body">
        <div class="mae-ev-pg__container">
            <div class="mae-ev-pg__layout">

                <!-- Main content -->
                <main class="mae-ev-pg__content">
                    <?php if ($cat) : ?>
                    <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="mae-ev-pg__cat-link">
                        &larr; <?php echo esc_html($cat->name); ?>
                    </a>
                    <?php endif; ?>

                    <!-- Featured image -->
                    <?php if (has_post_thumbnail()) : ?>
                    <div class="mae-ev-pg__thumb">
                        <?php the_post_thumbnail('large', ['class' => 'mae-ev-pg__thumb-img']); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Event title (visible on mobile, hidden on desktop where sidebar shows it) -->
                    <h1 class="mae-ev-pg__mobile-title"><?php the_title(); ?></h1>

                    <!-- Event description -->
                    <div class="mae-ev-pg__desc">
                        <?php the_content(); ?>
                    </div>

                    <!-- ══ Featured Artists ═══════════════════════════ -->
                    <?php if (!empty($artists)) : ?>
                    <div class="mae-ev-artists-section">
                        <h2 class="mae-ev-artists-section__title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Featured Artists and Arts
                        </h2>

                        <div class="mae-ev-accordion">
                        <?php foreach ($artists as $i => $artist) :
                            $art_ids    = array_filter(array_map('intval', explode(',', $artist['art_pieces'] ?? '')));
                            $art_prices = $artist['art_prices'] ?? [];
                            $photo_id   = (int) ($artist['photo_id'] ?? 0);
                            $photo_url  = $photo_id ? wp_get_attachment_image_url($photo_id, 'medium') : '';
                            // Dynamic slug: custom slug overrides auto-generated one from name
                            $custom_slug  = sanitize_title($artist['slug'] ?? '');
                            $artist_slug  = $custom_slug ?: sanitize_title($artist['name'] ?? ('artist-' . $i));
                            $panel_id     = 'mae-ac-panel-' . $artist_slug;
                            $trigger_id   = 'mae-ac-trigger-' . $artist_slug;
                        ?>
                        <div class="mae-ac-item" id="<?php echo esc_attr($artist_slug); ?>">

                            <!-- Accordion trigger: photo + name -->
                            <button class="mae-ac-trigger" id="<?php echo $trigger_id; ?>"
                                    aria-expanded="false"
                                    aria-controls="<?php echo $panel_id; ?>">
                                <div class="mae-ac-trigger__left">
                                    <?php if ($photo_url) : ?>
                                    <div class="mae-ac-avatar mae-ac-avatar--photo">
                                        <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($artist['name']); ?>">
                                    </div>
                                    <?php else : ?>
                                    <div class="mae-ac-avatar">
                                        <?php echo esc_html(strtoupper(substr($artist['name'], 0, 1))); ?>
                                    </div>
                                    <?php endif; ?>
                                    <span class="mae-ac-trigger__name"><?php echo esc_html($artist['name']); ?></span>
                                </div>
                                <svg class="mae-ac-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>

                            <!-- Accordion panel -->
                            <div class="mae-ac-panel" id="<?php echo $panel_id; ?>" role="region" aria-labelledby="<?php echo $trigger_id; ?>" hidden>
                                <div class="mae-ac-panel__inner">

                                    <?php if (!empty($artist['bio'])) : ?>
                                    <p class="mae-ev-artist__bio"><?php echo nl2br(esc_html($artist['bio'])); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($art_ids)) : ?>
                                    <div class="mae-ev-artpiece-list">
                                        <?php
                                        $stored_captions = $artist['art_captions']     ?? [];
                                        $stored_descs    = $artist['art_descriptions'] ?? [];
                                        $stored_dims     = $artist['art_dimensions']   ?? [];
                                        foreach ($art_ids as $att_id) :
                                            $img_url    = wp_get_attachment_image_url($att_id, 'medium_large');
                                            $full_url   = wp_get_attachment_image_url($att_id, 'full');
                                            $desc       = !empty($stored_captions[$att_id])
                                                ? $stored_captions[$att_id]
                                                : (wp_get_attachment_caption($att_id) ?: '');
                                            $art_desc   = $stored_descs[$att_id] ?? '';
                                            $art_dim    = $stored_dims[$att_id]  ?? '';
                                            $art_price  = (float) ($art_prices[$att_id] ?? 0);
                                            $co_url     = MAE_Art_Checkout::url($event_id, $i, $att_id);
                                            if (!$img_url) continue;
                                        ?>
                                        <div class="mae-ev-artpiece">
                                            <div class="mae-ev-artpiece__row">
                                                <a href="<?php echo esc_url($full_url); ?>" class="mae-ev-artpiece__img-link" data-mae-zoom aria-label="View larger image">
                                                    <img src="<?php echo esc_url($img_url); ?>"
                                                         alt="<?php echo esc_attr($desc ?: $artist['name'] . ' artwork'); ?>"
                                                         class="mae-ev-artpiece__img"
                                                         loading="lazy">
                                                    <span class="mae-ev-artpiece__zoom" aria-hidden="true">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                                    </span>
                                                </a>
                                                <div class="mae-ev-artpiece__info">
                                                    <?php if ($desc) : ?>
                                                    <p class="mae-ev-artpiece__desc"><?php echo esc_html($desc); ?></p>
                                                    <?php endif; ?>
                                                    <?php if ($art_desc) : ?>
                                                    <div class="mae-ev-artpiece__field">
                                                        <span class="mae-ev-artpiece__label">Description</span>
                                                        <p class="mae-ev-artpiece__description"><?php echo nl2br(esc_html($art_desc)); ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ($art_dim) : ?>
                                                    <div class="mae-ev-artpiece__field">
                                                        <span class="mae-ev-artpiece__label">Dimension</span>
                                                        <span class="mae-ev-artpiece__dim">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 6H3M21 6v12M3 6v12m18 0H3"/><path d="M7 6v3M12 6v3M17 6v3"/></svg>
                                                            <?php echo esc_html($art_dim); ?>
                                                        </span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ($art_price > 0) : ?>
                                                    <span class="mae-ev-artpiece__price">$<?php echo number_format($art_price, 2); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if ($art_price > 0) : ?>
                                            <a href="<?php echo esc_url($co_url); ?>" class="mae-ev-artpiece__btn">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                                Checkout — $<?php echo number_format($art_price, 2); ?>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </div>
                        <?php endforeach; ?>
                        </div><!-- /.mae-ev-accordion -->
                    </div>
                    <?php endif; ?>

                </main>

                <!-- Booking sidebar -->
                <aside class="mae-ev-pg__sidebar">
                    <div class="mae-ev-pg__card">

                        <div class="mae-ev-pg__card-header">
                            <h2 class="mae-ev-pg__card-event-title"><?php the_title(); ?></h2>
                            <?php if ($cat) : ?>
                            <span class="mae-ev-pg__card-cat"><?php echo esc_html($cat->name); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mae-ev-pg__card-price">
                            <?php if ($is_paid) : ?>
                            <span class="mae-ev-pg__card-amount">$<?php echo number_format($price, 2); ?></span>
                            <span class="mae-ev-pg__card-per">per ticket</span>
                            <?php else : ?>
                            <span class="mae-ev-pg__card-amount mae-ev-pg__card-amount--free">Free</span>
                            <span class="mae-ev-pg__card-per">Entry</span>
                            <?php endif; ?>
                        </div>

                        <div class="mae-ev-pg__card-divider"></div>

                        <ul class="mae-ev-pg__card-details">
                            <?php if ($date) : ?>
                            <li class="mae-ev-pg__card-row">
                                <span class="mae-ev-pg__card-icon">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                                <div class="mae-ev-pg__card-row-text">
                                    <span class="mae-ev-pg__card-row-label">Date</span>
                                    <span class="mae-ev-pg__card-row-val"><?php echo mae_format_date_range($date, $end_date); ?></span>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php if ($time) : ?>
                            <li class="mae-ev-pg__card-row">
                                <span class="mae-ev-pg__card-icon">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <div class="mae-ev-pg__card-row-text">
                                    <span class="mae-ev-pg__card-row-label">Time</span>
                                    <span class="mae-ev-pg__card-row-val"><?php echo date('g:i A', strtotime($time)); ?></span>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php if ($location) : ?>
                            <li class="mae-ev-pg__card-row">
                                <span class="mae-ev-pg__card-icon">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </span>
                                <div class="mae-ev-pg__card-row-text">
                                    <span class="mae-ev-pg__card-row-label">Location</span>
                                    <span class="mae-ev-pg__card-row-val"><?php echo esc_html($location); ?></span>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <?php if (mae_is_registration_closed($event_id)) : ?>
                        <span class="mae-ev-pg__apply-btn mae-ev-pg__apply-btn--closed" id="close-btn">
                            Registration Closed
                        </span>
                        <?php else : ?>
                        <a href="<?php echo esc_url(MAE_Checkout::url($event_id)); ?>" class="mae-ev-pg__apply-btn" id="registration-btn">
                            <?php if ($is_paid) : ?>
                            Register &mdash; $<?php echo number_format($price, 2); ?>
                            <?php else : ?>
                            Register Now &mdash; Free
                            <?php endif; ?>
                        </a>
                        <?php endif; ?>

                        <p class="mae-ev-pg__card-note">Secure registration &middot; Instant confirmation</p>
                    </div>
                </aside>

            </div>
        </div>
    </div>

</div><!-- /.mae-ev-pg -->

<?php endwhile; endif; ?>

<!-- ══ Art image lightbox ════════════════════════════════════ -->
<div class="mae-ev-lightbox" id="mae-ev-lightbox" role="dialog" aria-modal="true" aria-label="Artwork image" hidden>
    <button type="button" class="mae-ev-lightbox__close" id="mae-ev-lightbox-close" aria-label="Close">&times;</button>
    <figure class="mae-ev-lightbox__figure">
        <img src="" alt="" class="mae-ev-lightbox__img" id="mae-ev-lightbox-img">
        <figcaption class="mae-ev-lightbox__caption" id="mae-ev-lightbox-cap"></figcaption>
    </figure>
</div>

<script>
(function () {

    /* ── Helpers ─────────────────────────────────────────────── */
    function closeItem(item) {
        var trigger = item.querySelector('.mae-ac-trigger');
        var panel   = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!trigger || !panel || trigger.getAttribute('aria-expanded') !== 'true') return;
        trigger.setAttribute('aria-expanded', 'false');
        item.classList.remove('is-open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
        requestAnimationFrame(function () { panel.style.maxHeight = '0'; });
        panel.addEventListener('transitionend', function handler() {
            panel.hidden = true;
            panel.style.maxHeight = '';
            panel.removeEventListener('transitionend', handler);
        });
    }

    function openItem(item) {
        var trigger = item.querySelector('.mae-ac-trigger');
        var panel   = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!trigger || !panel || trigger.getAttribute('aria-expanded') === 'true') return;
        trigger.setAttribute('aria-expanded', 'true');
        item.classList.add('is-open');
        panel.hidden = false;
        panel.style.maxHeight = '0';
        requestAnimationFrame(function () { panel.style.maxHeight = panel.scrollHeight + 'px'; });
        panel.addEventListener('transitionend', function handler() {
            panel.style.maxHeight = 'none';
            panel.removeEventListener('transitionend', handler);
        });
    }

    /* ── Click toggle ────────────────────────────────────────── */
    document.querySelectorAll('.mae-ac-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var item   = trigger.closest('.mae-ac-item');
            var isOpen = trigger.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                closeItem(item);
            } else {
                openItem(item);
            }
        });
    });

    /* ── Hash auto-open (QR code deep-link) ─────────────────── */
    function openByHash(hash) {
        if (!hash) return;
        var target = document.getElementById(hash);
        if (!target || !target.classList.contains('mae-ac-item')) return;

        // Make sure the target is open (the other panels stay open too)
        openItem(target);

        // Scroll smoothly with small offset for sticky headers
        setTimeout(function () {
            var offset = 90;
            var top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: top, behavior: 'smooth' });

            // Brief highlight pulse
            target.classList.add('mae-ac-item--highlight');
            setTimeout(function () { target.classList.remove('mae-ac-item--highlight'); }, 2200);
        }, 180);
    }

    // Open all accordion items by default
    document.querySelectorAll('.mae-ev-accordion .mae-ac-item').forEach(function (item) {
        var trigger = item.querySelector('.mae-ac-trigger');
        if (!trigger) return;
        var panel = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!panel) return;
        trigger.setAttribute('aria-expanded', 'true');
        item.classList.add('is-open');
        panel.hidden = false;
        panel.style.maxHeight = 'none';
    });

    // On page load — deep-link scrolls to / highlights the target (all stay open)
    openByHash(window.location.hash.replace('#', ''));

    // On hash change (e.g. browser back/forward)
    window.addEventListener('hashchange', function () {
        openByHash(window.location.hash.replace('#', ''));
    });

    /* ── Art image lightbox ─────────────────────────────────── */
    var lightbox = document.getElementById('mae-ev-lightbox');
    if (lightbox) {
        var lbImg   = document.getElementById('mae-ev-lightbox-img');
        var lbCap   = document.getElementById('mae-ev-lightbox-cap');
        var lbClose = document.getElementById('mae-ev-lightbox-close');

        function openLightbox(src, caption) {
            lbImg.src = src;
            lbImg.alt = caption || '';
            lbCap.textContent = caption || '';
            lbCap.style.display = caption ? '' : 'none';
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(function () { lightbox.classList.add('is-open'); });
            lbClose.focus();
        }

        function closeLightbox() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            setTimeout(function () { lightbox.hidden = true; lbImg.src = ''; }, 200);
        }

        document.querySelectorAll('.mae-ev-artpiece__img-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var img = link.querySelector('img');
                openLightbox(link.getAttribute('href'), img ? img.alt : '');
            });
        });

        lbClose.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox || e.target.classList.contains('mae-ev-lightbox__figure')) {
                closeLightbox();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !lightbox.hidden) closeLightbox();
        });
    }

}());
</script>

<?php get_footer(); ?>
