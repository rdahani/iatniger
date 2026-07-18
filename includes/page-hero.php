<?php
/**
 * Bandeau d'en-tête des pages intérieures avec fil d'Ariane.
 * Variables : $hero_titre, $hero_texte (optionnel), $breadcrumbs
 */
?>
<section class="page-hero">
  <div class="container">
    <?php if (!empty($breadcrumbs)) : ?>
    <nav class="breadcrumb" aria-label="Fil d'Ariane">
      <ol>
        <?php $last = count($breadcrumbs) - 1;
        foreach ($breadcrumbs as $i => $bc) : ?>
        <li>
          <?php if ($i < $last) : ?>
            <a href="<?= e($bc['url']) ?>"><?= e($bc['label']) ?></a>
            <?= icon('chevron-right', 14) ?>
          <?php else : ?>
            <span aria-current="page"><?= e($bc['label']) ?></span>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <?php endif; ?>
    <h1 class="page-hero-title"><?= e($hero_titre ?? '') ?></h1>
    <?php if (!empty($hero_texte)) : ?>
    <p class="page-hero-text"><?= e($hero_texte) ?></p>
    <?php endif; ?>
  </div>
</section>
