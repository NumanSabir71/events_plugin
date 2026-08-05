<?php
/**
 * Recognition archive template — rich Mithila-branded layout.
 *
 * @package Mithila_Art_Events
 * @version 1.0.0
 */
defined('ABSPATH') || exit;

get_header();

// Fetch all recognition posts for stats
$all_rec = get_posts([
    'post_type'   => 'mae_recognition',
    'numberposts' => -1,
    'fields'      => 'ids',
    'post_status' => 'publish',
]);
$total_rec = count($all_rec);

// Collect unique recognition types for pills
$all_types = get_terms(['taxonomy' => 'mae_recognition_type', 'hide_empty' => true]);
$type_names = [];
if (!is_wp_error($all_types)) {
    foreach ($all_types as $t) $type_names[] = $t->name;
}
?>

<div class="mae-rec-archive mae-tax-rich">

<!-- ═══════════════════ HERO ═══════════════════ -->
<section class="mae-rec-hero">
    <div class="mae-rec-hero__bg-pattern"></div>
    <div class="mae-rec-hero__inner mae-tax-container">
        <p class="mae-rec-hero__kicker">Recognition &amp; Awards</p>
        <h1 class="mae-rec-hero__title">Recognised on the<br>World Stage</h1>
        <div class="mae-rec-hero__divider"></div>
        <p class="mae-rec-hero__desc">Mithila Center USA and the artists we champion have received recognition from some of the world&rsquo;s most prestigious cultural and intergovernmental institutions — from the United Nations and UNESCO to the Smithsonian and the European Union. Each recognition is a testament to the enduring power and global relevance of Mithila art.</p>
        <div class="mae-rec-hero__pills">
            <?php if ($total_rec > 0) : ?>
            <span class="mae-rec-hero__pill mae-rec-hero__pill--accent"><?php echo $total_rec; ?> Recognitions</span>
            <?php endif; ?>
            <?php foreach (array_slice($type_names, 0, 3) as $tn) : ?>
            <span class="mae-rec-hero__pill"><?php echo esc_html($tn); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════ STATS ═══════════════════ -->
<section class="mae-rec-stats">
    <div class="mae-tax-container">
        <div class="mae-rec-stats__grid">
            <div class="mae-rec-stats__item">
                <div class="mae-rec-stats__num-row">
                    <span class="mae-rec-stats__number" data-count="15">0</span><span class="mae-rec-stats__suffix">+</span>
                </div>
                <span class="mae-rec-stats__label">Years of Recognition</span>
            </div>
            <div class="mae-rec-stats__item">
                <div class="mae-rec-stats__num-row">
                    <span class="mae-rec-stats__number" data-count="<?php echo esc_attr(max($total_rec, 20)); ?>">0</span><span class="mae-rec-stats__suffix">+</span>
                </div>
                <span class="mae-rec-stats__label">Awards &amp; Acknowledgements</span>
            </div>
            <div class="mae-rec-stats__item">
                <div class="mae-rec-stats__num-row">
                    <span class="mae-rec-stats__number" data-count="5">0</span>
                </div>
                <span class="mae-rec-stats__label">Continents Reached</span>
            </div>
            <div class="mae-rec-stats__item">
                <div class="mae-rec-stats__num-row">
                    <span class="mae-rec-stats__number" data-count="10">0</span><span class="mae-rec-stats__suffix">+</span>
                </div>
                <span class="mae-rec-stats__label">Global Institutions</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════ ABOUT STRIP ═══════════════════ -->
<section class="mae-rec-about">
    <div class="mae-tax-container mae-rec-about__inner">
        <div class="mae-rec-about__text">
            <p class="mae-rec-about__kicker">Why It Matters</p>
            <h2 class="mae-rec-about__heading">Global Validation for a Living Tradition</h2>
            <div class="mae-rec-about__divider"></div>
            <p>Mithila art is one of the world&rsquo;s oldest continuously practiced folk art traditions. For centuries, it was created primarily by women on the walls and floors of homes in Bihar and Nepal — a living visual language passed from mother to daughter, celebrating weddings, harvests, and the cycles of nature. Today, Mithila Center USA works to ensure this tradition receives the international recognition it deserves.</p>
            <p>When UNESCO, the United Nations, or the Smithsonian Institution acknowledges Mithila art, it does more than confer prestige — it creates pathways for preservation funding, academic study, and cross-cultural dialogue. Recognition on the global stage means that future generations of Mithila artists, whether in Bihar, Nepal, or New York, can carry forward their tradition with the support it deserves.</p>
            <a href="<?php echo esc_url(home_url('/about/')); ?>" class="mae-rec-about__btn">Our Story &#x2192;</a>
        </div>
        <div class="mae-rec-about__highlights">
            <div class="mae-rec-about__highlight">
                <div class="mae-rec-about__hl-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <div>
                    <strong>UNESCO Recognition</strong>
                    <p>Acknowledged by UNESCO as a significant intangible cultural heritage tradition with global importance.</p>
                </div>
            </div>
            <div class="mae-rec-about__highlight">
                <div class="mae-rec-about__hl-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
                </div>
                <div>
                    <strong>United Nations &amp; European Union</strong>
                    <p>Mithila art has been presented at UN Headquarters in New York and the EU in Brussels — advocacy at the highest levels.</p>
                </div>
            </div>
            <div class="mae-rec-about__highlight">
                <div class="mae-rec-about__hl-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
                <div>
                    <strong>Smithsonian &amp; Google Arts</strong>
                    <p>Featured by the Smithsonian Institution and Google Arts &amp; Culture, bringing Mithila art to millions worldwide.</p>
                </div>
            </div>
            <div class="mae-rec-about__highlight">
                <div class="mae-rec-about__hl-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <div>
                    <strong>Community &amp; Partner Validation</strong>
                    <p>Recognised by diaspora organisations, cultural bodies, and partner institutions across the United States and South Asia.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════ RECOGNITION CARDS ═══════════════════ -->
<?php
// Re-run the main query for the archive loop
$rec_query = new WP_Query([
    'post_type'      => 'mae_recognition',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

if ($rec_query->have_posts()) :
?>
<section class="mae-rec-grid-section">
    <div class="mae-tax-container">
        <p class="mae-rec-grid__kicker">Awards, Partnerships &amp; More</p>
        <h2 class="mae-rec-grid__heading">All Recognitions</h2>
        <div class="mae-rec-grid__divider"></div>


        <div class="mae-rec-slider-wrap">
            <button class="mae-rec-nav mae-rec-nav--prev" aria-label="Previous recognitions">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="mae-rec-slider">
                <div class="mae-rec-track" id="mae-rec-page-track">
                    <?php while ($rec_query->have_posts()) : $rec_query->the_post();
                        $pid      = get_the_ID();
                        $excerpt  = get_the_excerpt();
                        $ext_url  = get_post_meta($pid, '_mae_rec_url', true);
                        $year     = get_the_date('Y');
                        $terms    = get_the_terms($pid, 'mae_recognition_type');
                        $type_lbl = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
                        $type_slug= (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->slug : '';
                        $card_url = $ext_url ?: get_permalink();
                        $is_ext   = !empty($ext_url);
                    ?>
                    <article class="mae-rec-card" data-type="<?php echo esc_attr($type_slug); ?>">
                        <a href="<?php echo esc_url($card_url); ?>"
                           class="mae-rec-card__img-wrap"
                           <?php if ($is_ext) echo 'target="_blank" rel="noopener noreferrer"'; ?>>
                            <?php if (has_post_thumbnail()) :
                                the_post_thumbnail('medium_large', ['class' => 'mae-rec-card__img']);
                            else : ?>
                            <div class="mae-rec-card__img-placeholder">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="44" height="44"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V17H9c-.55 0-1 .45-1 1v2H7v2h10v-2h-1v-2c0-.55-.45-1-1-1h-2v-2.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
                            </div>
                            <?php endif; ?>
                            <?php if ($type_lbl) : ?>
                            <span class="mae-rec-card__type-badge"><?php echo esc_html($type_lbl); ?></span>
                            <?php endif; ?>
                            <?php if ($is_ext) : ?>
                            <span class="mae-rec-card__ext-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                            </span>
                            <?php endif; ?>
                        </a>

                        <div class="mae-rec-card__body">
                            <div class="mae-rec-card__meta">
                                <?php if ($year) : ?>
                                <span class="mae-rec-card__year"><?php echo esc_html($year); ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="mae-rec-card__title">
                                <a href="<?php echo esc_url($card_url); ?>"
                                   <?php if ($is_ext) echo 'target="_blank" rel="noopener noreferrer"'; ?>>
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <?php if ($excerpt) : ?>
                            <p class="mae-rec-card__excerpt"><?php echo esc_html(wp_trim_words($excerpt, 18, '…')); ?></p>
                            <?php endif; ?>
                            <div class="mae-rec-card__footer">
                                <a href="<?php echo esc_url($card_url); ?>"
                                   class="mae-rec-card__btn"
                                   <?php if ($is_ext) echo 'target="_blank" rel="noopener noreferrer"'; ?>>
                                    Learn More
                                    <?php if ($is_ext) : ?>
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                                    <?php else : ?>
                                    &#x2192;
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
            <button class="mae-rec-nav mae-rec-nav--next" aria-label="Next recognitions">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
        <div class="mae-rec-dots" role="tablist" aria-label="Recognition slides"></div>
    </div>
</section>


<?php else : ?>
<section class="mae-rec-grid-section">
    <div class="mae-tax-container">
        <div class="mae-empty-state">
            <div class="mae-empty-icon">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V17H9c-.55 0-1 .45-1 1v2H7v2h10v-2h-1v-2c0-.55-.45-1-1-1h-2v-2.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2z"/></svg>
            </div>
            <h3>No recognitions listed yet</h3>
            <p>Check back soon — new recognitions are added regularly.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════ CTA ═══════════════════ -->
<section class="mae-rec-cta">
    <div class="mae-rec-cta__bg-pattern"></div>
    <div class="mae-tax-container mae-rec-cta__inner">
        <p class="mae-rec-cta__kicker">Keep the Momentum Going</p>
        <h2 class="mae-rec-cta__heading">Help Us Earn the Next Recognition</h2>
        <div class="mae-rec-cta__divider"></div>
        <p class="mae-rec-cta__desc">Every award, partnership, and acknowledgement we receive amplifies the voice of Mithila artists and the communities that sustain this tradition. Your support — as a donor, volunteer, or partner — makes the next recognition possible.</p>
        <div class="mae-rec-cta__actions">
            <a href="<?php echo esc_url(home_url('/partner-with-us/')); ?>" class="mae-rec-cta__btn mae-rec-cta__btn--primary">Partner With Us</a>
            <a href="<?php echo esc_url(home_url('/volunteer/')); ?>" class="mae-rec-cta__btn mae-rec-cta__btn--secondary">Get Involved</a>
        </div>
    </div>
</section>

</div><!-- .mae-rec-archive -->

<?php get_footer(); ?>
