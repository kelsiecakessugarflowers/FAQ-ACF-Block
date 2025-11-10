<?php
if (!defined('ABSPATH')) exit;

/**
 * Kelsie FAQ Block Render Template
 * - Displays FAQs from ACF Repeater
 * - Local-only search and category filter
 * - Works with Rank Math schema integration
 */

// ACF safety guard
if (!function_exists('get_field')) {
    if (!empty($is_preview)) {
        echo '<div class="kelsie-faq-list__empty"><em>ACF is inactive. Activate ACF to display FAQs.</em></div>';
    }
    return;
}

// Wrapper + block attributes
$block_id   = 'faq-list-' . ($block['id'] ?? uniqid());
$anchor     = !empty($block['anchor']) ? $block['anchor'] : $block_id;
$class_name = 'kelsie-faq-list';
if (!empty($block['className'])) $class_name .= ' ' . $block['className'];
if (!empty($block['align']))     $class_name .= ' align' . $block['align'];

// Get FAQ rows (post first, fallback to options)
$current_post_id = get_the_ID();
$rows = get_field(KELSIE_FAQ_REPEATER, $current_post_id);
$source = 'post';
if (empty($rows)) {
    $rows = get_field(KELSIE_FAQ_REPEATER, KELSIE_OPTIONS_ID);
    $source = 'option';
}

// Show note in editor preview
if (!empty($is_preview) && !empty($rows)) {
    echo '<div style="font:12px/1.4 system-ui;opacity:.75;margin-bottom:.5rem;">Rendering FAQs from <strong>' .
         esc_html($source === 'post' ? 'this post' : 'Options Page') .
         '</strong>.</div>';
}
?>

<section id="<?php echo esc_attr($anchor); ?>" class="<?php echo esc_attr($class_name); ?>">

<?php if (!empty($rows)) : ?>

  <!-- Local-only search + filter toolbar -->
  <div class="kelsie-faq-search" role="search" aria-label="Search FAQs">
    <label class="kelsie-faq-search__label" for="<?php echo esc_attr($anchor); ?>-faq-search">Search FAQs</label>
    <div class="kelsie-faq-search__controls">
      <input
        id="<?php echo esc_attr($anchor); ?>-faq-search"
        type="search"
        class="kelsie-faq-search__input"
        placeholder="Type to filter questions..."
        autocomplete="off"
        spellcheck="false"
        aria-describedby="<?php echo esc_attr($anchor); ?>-faq-count"
      />
      <button type="button" class="kelsie-faq-search__clear" aria-label="Clear search">Clear</button>
    </div>
    <div id="<?php echo esc_attr($anchor); ?>-faq-count" class="kelsie-faq-search__count" aria-live="polite"></div>
  </div>

  <!-- Category filter dropdown -->
  <div class="kelsie-faq-list__toolbar" aria-label="FAQ filters">
    <label class="kelsie-faq-list__control">
      <span class="kelsie-faq-list__control-label">Category</span>
      <select class="kelsie-faq-list__filter" aria-controls="<?php echo esc_attr($anchor); ?>">
        <option value="">All</option>
        <!-- JS will populate unique categories at runtime -->
      </select>
    </label>
    <span class="kelsie-faq-list__count kelsie-faq-list__count--secondary" aria-live="polite"></span>
  </div>

  <!-- FAQ items -->
  <div class="kelsie-faq-list__items" role="list">
    <?php $i = 0;
    foreach ($rows as $row) :
        $i++;
        $q = isset($row[KELSIE_FAQ_QUESTION]) ? wp_strip_all_tags($row[KELSIE_FAQ_QUESTION]) : '';
        $a_html = wpautop($row[KELSIE_FAQ_ANSWER] ?? '');
        $cats = !empty($row[KELSIE_FAQ_CATEGORY]) && is_array($row[KELSIE_FAQ_CATEGORY])
            ? array_map('sanitize_text_field', $row[KELSIE_FAQ_CATEGORY])
            : [];
        $panel_id   = esc_attr($anchor . '-item-' . $i);
        $summary_id = esc_attr($panel_id . '-summary');
        $cats_attr = $cats ? strtolower(implode('|', array_map('sanitize_title', $cats))) : '';
    ?>
      <details
        class="kelsie-faq-list__item"
        id="<?php echo $panel_id; ?>"
        role="listitem"
        data-cats="<?php echo esc_attr($cats_attr); ?>"
      >
        <summary id="<?php echo $summary_id; ?>" class="kelsie-faq-list__question">
          <?php echo esc_html($q ?: 'Untitled question'); ?>
        </summary>
        <div class="kelsie-faq-list__answer">
          <?php echo $a_html ?: '<p>(No answer yet.)</p>'; ?>
          <?php if ($cats) : ?>
            <div class="kelsie-faq-list__meta">
              <span class="kelsie-faq-list__label">Category:</span>
              <?php foreach ($cats as $c) : ?>
                <span class="kelsie-faq-list__chip"><?php echo esc_html($c); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </details>
    <?php endforeach; ?>
  </div>

<?php else : ?>

  <?php if (!empty($is_preview)) : ?>
    <div class="kelsie-faq-list__empty"><em>No FAQs found. Add rows on this post or in the Options Page.</em></div>
  <?php endif; ?>

<?php endif; ?>

</section>
