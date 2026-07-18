<?php
/** Sitemap XML dynamique : pages statiques + niveaux de formation + actualités. */

require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    ['loc' => url(), 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => url('a-propos'), 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => url('formations'), 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => url('admission'), 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => url('vie-etudiante'), 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => url('csp-algoza'), 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => url('actualites'), 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => url('web-tv'), 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => url('galerie'), 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => url('partenaires'), 'priority' => '0.5', 'changefreq' => 'yearly'],
    ['loc' => url('telechargements'), 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => url('faq'), 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => url('contact'), 'priority' => '0.7', 'changefreq' => 'yearly'],
];
foreach (array_keys(niveaux_catalogue()) as $niveau) {
    $urls[] = ['loc' => url('formations/' . $niveau), 'priority' => '0.8', 'changefreq' => 'monthly'];
}
foreach (cms_formations() as $f) {
    if (!empty($f['slug'])) {
        $urls[] = ['loc' => url('formation/' . $f['slug']), 'priority' => '0.7', 'changefreq' => 'monthly'];
    }
}
foreach (actualites() as $a) {
    $urls[] = ['loc' => url('actualites/' . $a['slug']), 'priority' => '0.6', 'changefreq' => 'yearly', 'lastmod' => $a['date_publication']];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u) : ?>
  <url>
    <loc><?= e($u['loc']) ?></loc>
    <?php if (isset($u['lastmod'])) : ?><lastmod><?= e($u['lastmod']) ?></lastmod><?php endif; ?>
    <changefreq><?= e($u['changefreq']) ?></changefreq>
    <priority><?= e($u['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
