<?php
/**
 * Tableau de bord admin — vue de pilotage premium.
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_permission('dashboard');

$pdo = db();
$nom = (string) ($_SESSION['admin_nom'] ?? 'Admin');
$role_label = admin_roles_meta()[admin_role()]['label'] ?? 'Administrateur';

$heure = (int) date('G');
$salutation = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');

$jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$mois = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$date_longue = ucfirst($jours[(int) date('w')]) . ' ' . (int) date('j') . ' ' . $mois[(int) date('n')] . ' ' . date('Y');

$stats = [
    'actualites' => 0, 'actualites_publiees' => 0,
    'messages' => 0, 'messages_non_lus' => 0,
    'preinscriptions' => 0, 'preinscriptions_attente' => 0,
    'newsletter' => 0, 'formations' => 0, 'faq' => 0,
    'galerie' => 0, 'partenaires' => 0, 'videos' => 0, 'documents' => 0,
    'users' => 0,
];
$recent_messages = [];
$recent_preins = [];
$recent_actus = [];
$chart_7j = array_fill(0, 7, ['label' => '', 'messages' => 0, 'preins' => 0]);

if ($pdo !== null) {
    try {
        $stats['actualites'] = (int) $pdo->query('SELECT COUNT(*) FROM actualites')->fetchColumn();
        $stats['actualites_publiees'] = (int) $pdo->query('SELECT COUNT(*) FROM actualites WHERE publie = 1')->fetchColumn();
        $stats['messages'] = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
        $stats['messages_non_lus'] = (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE lu = 0')->fetchColumn();
        $stats['preinscriptions'] = (int) $pdo->query('SELECT COUNT(*) FROM preinscriptions')->fetchColumn();
        $stats['preinscriptions_attente'] = (int) $pdo->query('SELECT COUNT(*) FROM preinscriptions WHERE traite = 0')->fetchColumn();
        $stats['newsletter'] = (int) $pdo->query('SELECT COUNT(*) FROM newsletter')->fetchColumn();
        $stats['users'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

        $recent_messages = $pdo->query('SELECT id, nom, sujet, lu, recu_le FROM messages ORDER BY recu_le DESC LIMIT 5')->fetchAll();
        $recent_preins = $pdo->query('SELECT id, nom, prenom, formation, niveau, traite, recu_le FROM preinscriptions ORDER BY recu_le DESC LIMIT 5')->fetchAll();
        $recent_actus = $pdo->query('SELECT id, titre, categorie, publie, date_publication FROM actualites ORDER BY date_publication DESC LIMIT 4')->fetchAll();

        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $idx = 6 - $i;
            $chart_7j[$idx]['label'] = date('d/m', strtotime($d));
            $st = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE DATE(recu_le) = ?');
            $st->execute([$d]);
            $chart_7j[$idx]['messages'] = (int) $st->fetchColumn();
            $st = $pdo->prepare('SELECT COUNT(*) FROM preinscriptions WHERE DATE(recu_le) = ?');
            $st->execute([$d]);
            $chart_7j[$idx]['preins'] = (int) $st->fetchColumn();
        }
    } catch (PDOException $e) {
    }

    if (cms_ready()) {
        try {
            $stats['formations'] = (int) $pdo->query('SELECT COUNT(*) FROM cms_formations')->fetchColumn();
            $stats['faq'] = (int) $pdo->query("SELECT COUNT(*) FROM cms_items WHERE type = 'faq'")->fetchColumn();
            $stats['galerie'] = (int) $pdo->query("SELECT COUNT(*) FROM cms_items WHERE type = 'galerie'")->fetchColumn();
            $stats['partenaires'] = (int) $pdo->query("SELECT COUNT(*) FROM cms_items WHERE type = 'partenaire'")->fetchColumn();
            $stats['videos'] = (int) $pdo->query("SELECT COUNT(*) FROM cms_items WHERE type = 'video'")->fetchColumn();
            $stats['documents'] = (int) $pdo->query("SELECT COUNT(*) FROM cms_items WHERE type = 'document'")->fetchColumn();
        } catch (PDOException $e) {
        }
    }
}

$max_chart = 1;
foreach ($chart_7j as $j) {
    $max_chart = max($max_chart, $j['messages'] + $j['preins']);
}

$a_traiter = $stats['messages_non_lus'] + $stats['preinscriptions_attente'];
$cms_ok = cms_ready();

$kpis = [
    [
        'perm' => 'actualites', 'href' => 'admin/actualites.php', 'icon' => 'newspaper',
        'value' => $stats['actualites_publiees'], 'label' => 'Actualités publiées',
        'hint' => $stats['actualites'] . ' au total', 'tone' => 'blue',
    ],
    [
        'perm' => 'messages', 'href' => 'admin/messages.php', 'icon' => 'inbox',
        'value' => $stats['messages'], 'label' => 'Messages contact',
        'hint' => $stats['messages_non_lus'] > 0 ? $stats['messages_non_lus'] . ' non lus' : 'Tout lu',
        'tone' => $stats['messages_non_lus'] > 0 ? 'amber' : 'blue', 'alert' => $stats['messages_non_lus'] > 0,
    ],
    [
        'perm' => 'preinscriptions', 'href' => 'admin/preinscriptions.php', 'icon' => 'user-plus',
        'value' => $stats['preinscriptions'], 'label' => 'Préinscriptions',
        'hint' => $stats['preinscriptions_attente'] > 0 ? $stats['preinscriptions_attente'] . ' en attente' : 'À jour',
        'tone' => $stats['preinscriptions_attente'] > 0 ? 'amber' : 'green', 'alert' => $stats['preinscriptions_attente'] > 0,
    ],
    [
        'perm' => 'formations', 'href' => 'admin/formations.php', 'icon' => 'graduation-cap',
        'value' => $stats['formations'], 'label' => 'Formations',
        'hint' => 'Catalogue LMD', 'tone' => 'indigo',
    ],
    [
        'perm' => 'newsletter', 'href' => 'admin/newsletter.php', 'icon' => 'mail',
        'value' => $stats['newsletter'], 'label' => 'Abonnés newsletter',
        'hint' => 'Audience digitale', 'tone' => 'green',
    ],
    [
        'perm' => 'faq', 'href' => 'admin/contenu.php?type=faq', 'icon' => 'help-circle',
        'value' => $stats['faq'], 'label' => 'Questions FAQ',
        'hint' => 'Support candidats', 'tone' => 'blue',
    ],
];

$contenu_sante = [
    ['perm' => 'galerie', 'label' => 'Galerie', 'n' => $stats['galerie'], 'href' => 'admin/contenu.php?type=galerie', 'icon' => 'image'],
    ['perm' => 'partenaires', 'label' => 'Partenaires', 'n' => $stats['partenaires'], 'href' => 'admin/contenu.php?type=partenaire', 'icon' => 'handshake'],
    ['perm' => 'web-tv', 'label' => 'WEB TV', 'n' => $stats['videos'], 'href' => 'admin/contenu.php?type=video', 'icon' => 'play'],
    ['perm' => 'documents', 'label' => 'Documents', 'n' => $stats['documents'], 'href' => 'admin/contenu.php?type=document', 'icon' => 'download'],
];

$raccourcis = [
    ['perm' => 'actualites', 'href' => 'admin/actualites.php?action=nouvelle', 'icon' => 'plus', 'label' => 'Nouvelle actualité', 'primary' => true],
    ['perm' => 'messages', 'href' => 'admin/messages.php', 'icon' => 'inbox', 'label' => 'Messages'],
    ['perm' => 'preinscriptions', 'href' => 'admin/preinscriptions.php', 'icon' => 'user-plus', 'label' => 'Préinscriptions'],
    ['perm' => 'formations', 'href' => 'admin/formations.php', 'icon' => 'graduation-cap', 'label' => 'Formations'],
    ['perm' => 'medias', 'href' => 'admin/medias.php', 'icon' => 'folder', 'label' => 'Médiathèque'],
    ['perm' => 'parametres', 'href' => 'admin/parametres.php', 'icon' => 'settings', 'label' => 'Paramètres'],
    ['perm' => 'accueil', 'href' => 'admin/accueil.php', 'icon' => 'home', 'label' => 'Page d\'accueil'],
    ['perm' => 'pages', 'href' => 'admin/pages.php', 'icon' => 'file-text', 'label' => 'SEO & pages'],
];

admin_head('Tableau de bord');

$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
$is_local = str_contains($host, 'localhost') || $host === '127.0.0.1' || str_starts_with($host, '192.168.');
$prod_alerts = [];
if (!$is_local && admin_can('utilisateurs')) {
    if (DB_USER === 'root' || DB_PASS === '') {
        $prod_alerts[] = 'Identifiants MySQL encore en mode développement (root / vide). Créez un utilisateur dédié dans config/config.php.';
    }
    if (SITE_BASE === '/iatniger') {
        $prod_alerts[] = 'SITE_BASE vaut encore /iatniger. À la racine du domaine, ajoutez define(\'SITE_BASE\', \'\'); en tête de config.';
    }
    $prod_alerts[] = 'Changez le mot de passe admin par défaut (Utilisateurs) et activez HTTPS.';
}
?>
<div class="admin-layout">
  <?php admin_sidebar('dashboard'); ?>
  <main class="admin-main dash">

    <?php if ($prod_alerts) : ?>
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
      <?= icon('alert-triangle', 18) ?>
      <div>
        <strong>Checklist production</strong>
        <ul style="margin: 0.45rem 0 0; padding-left: 1.15rem;">
          <?php foreach ($prod_alerts as $a) : ?><li><?= e($a) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>

    <!-- Hero -->
    <section class="dash-hero reveal-dash">
      <div class="dash-hero-copy">
        <p class="dash-hero-kicker"><?= icon('sparkles', 15) ?> Centre de pilotage IAT Niger</p>
        <h1><?= e($salutation) ?>, <span><?= e(explode(' ', $nom)[0]) ?></span></h1>
        <p class="dash-hero-sub"><?= e($date_longue) ?> · Rôle <?= e($role_label) ?></p>
        <div class="dash-hero-chips">
          <?php if ($cms_ok) : ?>
          <span class="dash-chip dash-chip-ok"><?= icon('check-circle', 14) ?> CMS actif</span>
          <?php else : ?>
          <a class="dash-chip dash-chip-warn" href="<?= url('admin/install-cms.php') ?>"><?= icon('alert-triangle', 14) ?> Installer le CMS</a>
          <?php endif; ?>
          <?php if ($a_traiter > 0) : ?>
          <span class="dash-chip dash-chip-alert"><?= icon('bell', 14) ?> <?= $a_traiter ?> élément<?= $a_traiter > 1 ? 's' : '' ?> à traiter</span>
          <?php else : ?>
          <span class="dash-chip dash-chip-ok"><?= icon('check', 14) ?> File d'attente à jour</span>
          <?php endif; ?>
          <a class="dash-chip" href="<?= url() ?>" target="_blank"><?= icon('external-link', 14) ?> Voir le site</a>
        </div>
      </div>
      <div class="dash-hero-aside">
        <div class="dash-hero-stat">
          <strong data-count-up="<?= (int) $a_traiter ?>"><?= (int) $a_traiter ?></strong>
          <span>actions prioritaires</span>
        </div>
        <div class="dash-hero-stat">
          <strong data-count-up="<?= (int) $stats['formations'] ?>"><?= (int) $stats['formations'] ?></strong>
          <span>filières en ligne</span>
        </div>
      </div>
    </section>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible. Démarrez MySQL dans XAMPP et importez <code>database/iatniger.sql</code>.</div></div>
    <?php else : ?>

    <!-- KPI -->
    <section class="dash-kpi-grid" aria-label="Indicateurs clés">
      <?php foreach ($kpis as $i => $k) :
          if (!admin_can($k['perm'])) {
              continue;
          } ?>
      <a class="dash-kpi dash-kpi-<?= e($k['tone']) ?> reveal-dash" style="--d: <?= $i * 0.05 ?>s" href="<?= url($k['href']) ?>">
        <div class="dash-kpi-top">
          <span class="dash-kpi-icon"><?= icon($k['icon'], 22) ?></span>
          <?php if (!empty($k['alert'])) : ?><span class="dash-kpi-pulse" aria-hidden="true"></span><?php endif; ?>
        </div>
        <p class="dash-kpi-value" data-count-up="<?= (int) $k['value'] ?>"><?= (int) $k['value'] ?></p>
        <p class="dash-kpi-label"><?= e($k['label']) ?></p>
        <p class="dash-kpi-hint"><?= e($k['hint']) ?></p>
      </a>
      <?php endforeach; ?>
    </section>

    <div class="dash-columns">
      <!-- Activité 7 jours -->
      <section class="dash-panel reveal-dash">
        <div class="dash-panel-head">
          <div>
            <h2>Activité des 7 derniers jours</h2>
            <p class="caption">Messages &amp; préinscriptions reçus</p>
          </div>
        </div>
        <div class="dash-chart" role="img" aria-label="Graphique d'activité sur 7 jours">
          <?php foreach ($chart_7j as $bar) :
              $total = $bar['messages'] + $bar['preins'];
              $h = $max_chart > 0 ? max(8, (int) round(($total / $max_chart) * 100)) : 8;
              $hm = $max_chart > 0 ? (int) round(($bar['messages'] / $max_chart) * 100) : 0;
              ?>
          <div class="dash-bar">
            <div class="dash-bar-stack" title="<?= (int) $bar['messages'] ?> msg · <?= (int) $bar['preins'] ?> préins.">
              <span class="dash-bar-fill dash-bar-msg" style="height: <?= max(4, $hm) ?>%"></span>
              <span class="dash-bar-fill dash-bar-pre" style="height: <?= max(4, $h - $hm) ?>%"></span>
            </div>
            <span class="dash-bar-label"><?= e($bar['label']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="dash-legend">
          <span><i class="dash-dot dash-dot-msg"></i> Messages</span>
          <span><i class="dash-dot dash-dot-pre"></i> Préinscriptions</span>
        </div>
      </section>

      <!-- À traiter -->
      <section class="dash-panel dash-panel-priority reveal-dash">
        <div class="dash-panel-head">
          <div>
            <h2>File prioritaire</h2>
            <p class="caption">Ce qui demande votre attention</p>
          </div>
        </div>
        <ul class="dash-priority-list">
          <?php if (admin_can('messages')) : ?>
          <li>
            <a href="<?= url('admin/messages.php') ?>">
              <span class="dash-prio-icon"><?= icon('inbox', 18) ?></span>
              <span class="dash-prio-text">
                <strong>Messages non lus</strong>
                <small>Formulaire contact du site</small>
              </span>
              <span class="dash-prio-badge <?= $stats['messages_non_lus'] > 0 ? 'is-hot' : '' ?>"><?= (int) $stats['messages_non_lus'] ?></span>
            </a>
          </li>
          <?php endif; ?>
          <?php if (admin_can('preinscriptions')) : ?>
          <li>
            <a href="<?= url('admin/preinscriptions.php') ?>">
              <span class="dash-prio-icon"><?= icon('user-plus', 18) ?></span>
              <span class="dash-prio-text">
                <strong>Préinscriptions en attente</strong>
                <small>Candidats à rappeler</small>
              </span>
              <span class="dash-prio-badge <?= $stats['preinscriptions_attente'] > 0 ? 'is-hot' : '' ?>"><?= (int) $stats['preinscriptions_attente'] ?></span>
            </a>
          </li>
          <?php endif; ?>
          <?php if (admin_can('actualites')) : ?>
          <li>
            <a href="<?= url('admin/actualites.php') ?>">
              <span class="dash-prio-icon"><?= icon('newspaper', 18) ?></span>
              <span class="dash-prio-text">
                <strong>Actualités en brouillon</strong>
                <small>Non publiées</small>
              </span>
              <span class="dash-prio-badge"><?= max(0, $stats['actualites'] - $stats['actualites_publiees']) ?></span>
            </a>
          </li>
          <?php endif; ?>
          <?php if (!$cms_ok && admin_can('parametres')) : ?>
          <li>
            <a href="<?= url('admin/install-cms.php') ?>">
              <span class="dash-prio-icon"><?= icon('alert-triangle', 18) ?></span>
              <span class="dash-prio-text">
                <strong>CMS non installé</strong>
                <small>Activer l'édition complète</small>
              </span>
              <span class="dash-prio-badge is-hot">!</span>
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </section>
    </div>

    <div class="dash-columns dash-columns-3">
      <!-- Messages récents -->
      <?php if (admin_can('messages')) : ?>
      <section class="dash-panel reveal-dash">
        <div class="dash-panel-head">
          <h2>Derniers messages</h2>
          <a class="dash-link" href="<?= url('admin/messages.php') ?>">Tout voir <?= icon('arrow-right', 14) ?></a>
        </div>
        <?php if (!$recent_messages) : ?>
        <p class="caption">Aucun message pour le moment.</p>
        <?php else : ?>
        <ul class="dash-feed">
          <?php foreach ($recent_messages as $m) : ?>
          <li class="<?= (int) $m['lu'] === 0 ? 'is-unread' : '' ?>">
            <div>
              <strong><?= e($m['nom']) ?></strong>
              <span><?= e(mb_strimwidth((string) $m['sujet'], 0, 42, '…')) ?></span>
            </div>
            <time datetime="<?= e((string) $m['recu_le']) ?>"><?= e(date('d/m H:i', strtotime((string) $m['recu_le']))) ?></time>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </section>
      <?php endif; ?>

      <!-- Préinscriptions -->
      <?php if (admin_can('preinscriptions')) : ?>
      <section class="dash-panel reveal-dash">
        <div class="dash-panel-head">
          <h2>Dernières candidatures</h2>
          <a class="dash-link" href="<?= url('admin/preinscriptions.php') ?>">Tout voir <?= icon('arrow-right', 14) ?></a>
        </div>
        <?php if (!$recent_preins) : ?>
        <p class="caption">Aucune préinscription récente.</p>
        <?php else : ?>
        <ul class="dash-feed">
          <?php foreach ($recent_preins as $p) : ?>
          <li class="<?= (int) $p['traite'] === 0 ? 'is-unread' : '' ?>">
            <div>
              <strong><?= e($p['prenom'] . ' ' . $p['nom']) ?></strong>
              <span><?= e((string) $p['formation']) ?> · <?= e((string) $p['niveau']) ?></span>
            </div>
            <time><?= (int) $p['traite'] === 1 ? 'Traité' : 'Nouveau' ?></time>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </section>
      <?php endif; ?>

      <!-- Actualités -->
      <?php if (admin_can('actualites')) : ?>
      <section class="dash-panel reveal-dash">
        <div class="dash-panel-head">
          <h2>Actualités récentes</h2>
          <a class="dash-link" href="<?= url('admin/actualites.php') ?>">Gérer <?= icon('arrow-right', 14) ?></a>
        </div>
        <?php if (!$recent_actus) : ?>
        <p class="caption">Aucune actualité.</p>
        <?php else : ?>
        <ul class="dash-feed">
          <?php foreach ($recent_actus as $a) : ?>
          <li>
            <div>
              <strong><?= e(mb_strimwidth((string) $a['titre'], 0, 48, '…')) ?></strong>
              <span><?= e((string) $a['categorie']) ?> · <?= (int) $a['publie'] === 1 ? 'Publiée' : 'Brouillon' ?></span>
            </div>
            <time><?= e(date('d/m', strtotime((string) $a['date_publication']))) ?></time>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </section>
      <?php endif; ?>
    </div>

    <!-- Santé contenu + raccourcis -->
    <div class="dash-columns">
      <section class="dash-panel reveal-dash">
        <div class="dash-panel-head">
          <div>
            <h2>Santé du contenu</h2>
            <p class="caption">Inventaire éditorial du site public</p>
          </div>
        </div>
        <div class="dash-health">
          <?php foreach ($contenu_sante as $c) :
              if (!admin_can($c['perm'])) {
                  continue;
              } ?>
          <a class="dash-health-item" href="<?= url($c['href']) ?>">
            <span class="dash-health-icon"><?= icon($c['icon'], 18) ?></span>
            <span class="dash-health-label"><?= e($c['label']) ?></span>
            <strong data-count-up="<?= (int) $c['n'] ?>"><?= (int) $c['n'] ?></strong>
          </a>
          <?php endforeach; ?>
          <?php if (admin_can('utilisateurs')) : ?>
          <a class="dash-health-item" href="<?= url('admin/utilisateurs.php') ?>">
            <span class="dash-health-icon"><?= icon('users', 18) ?></span>
            <span class="dash-health-label">Utilisateurs</span>
            <strong><?= (int) $stats['users'] ?></strong>
          </a>
          <?php endif; ?>
        </div>
      </section>

      <section class="dash-panel reveal-dash">
        <div class="dash-panel-head">
          <div>
            <h2>Actions rapides</h2>
            <p class="caption">Accès directs aux tâches fréquentes</p>
          </div>
        </div>
        <div class="dash-actions">
          <?php foreach ($raccourcis as $r) :
              if (!admin_can($r['perm'])) {
                  continue;
              } ?>
          <a class="dash-action <?= !empty($r['primary']) ? 'is-primary' : '' ?>" href="<?= url($r['href']) ?>">
            <?= icon($r['icon'], 18) ?>
            <span><?= e($r['label']) ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <?php endif; ?>
  </main>
</div>
<script>
(function () {
  /* Compteurs animés */
  function animate(el) {
    var target = parseInt(el.getAttribute('data-count-up') || '0', 10);
    if (!target) { el.textContent = '0'; return; }
    var start = 0, dur = 700, t0 = null;
    function step(ts) {
      if (!t0) t0 = ts;
      var p = Math.min(1, (ts - t0) / dur);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(start + (target - start) * eased);
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  document.querySelectorAll('[data-count-up]').forEach(animate);

  /* Apparition douce */
  document.querySelectorAll('.reveal-dash').forEach(function (el, i) {
    el.style.animationDelay = (i * 0.04) + 's';
    el.classList.add('is-in');
  });
})();
</script>
</body>
</html>
