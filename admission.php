<?php
/** Admission : conditions par niveau + formulaire de préinscription en ligne. */

require_once __DIR__ . '/config/config.php';

$page_title = 'Admission & Inscription — Comment rejoindre l\'IAT | IAT Niger';
$page_desc = "Conditions d'accès, dossier de candidature et préinscription en ligne à l'IAT Niger : Niveau Moyen, BTS/Licence, Master, Doctorat. Inscriptions ouvertes.";
$page_slug = 'admission';
$active = 'admission';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Admission', 'url' => url('admission')],
];
$hero_titre = "Rejoignez l'IAT en 3 étapes simples";
$hero_texte = "Choisissez votre formation, préparez votre dossier, déposez votre préinscription en ligne : notre équipe scolarité vous rappelle.";
cms_apply_page('admission', $page_title, $page_desc, $hero_titre, $hero_texte);

/* ----- Contenu éditable (CMS avec fallback sur les valeurs par défaut) ----- */
$niveaux = niveaux_catalogue();
$etapes = cms_cartes('admission-etapes') ?: [
    ['titre' => 'Choisissez votre formation', 'contenu' => "28 filières du Niveau Moyen au Doctorat. Parcourez le catalogue des formations et identifiez celle qui correspond à votre projet."],
    ['titre' => 'Préparez votre dossier', 'contenu' => "Extrait d'acte de naissance, certificat de nationalité et dernier bulletin ou diplôme. Des pièces complémentaires sont demandées pour le Master de Recherche."],
    ['titre' => 'Déposez votre candidature', 'contenu' => "Préinscrivez-vous en ligne ci-dessous ou rendez-vous directement au campus (rond-point Gadafawa, Yantala). Notre équipe vous recontacte rapidement."],
];

/* ----- Traitement de la préinscription ----- */
$form_ok = false;
$form_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypot = trim($_POST['site_web'] ?? '');
    if ($honeypot !== '' || !csrf_check($_POST['csrf'] ?? null)) {
        $form_err = "Votre demande n'a pas pu être vérifiée. Merci de réessayer.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $tel = trim($_POST['telephone'] ?? '');
        $niv = trim($_POST['niveau'] ?? '');
        $formation = trim($_POST['formation'] ?? '');
        $diplome = trim($_POST['dernier_diplome'] ?? '');
        $msg = trim($_POST['message'] ?? '');

        if ($nom === '' || $prenom === '' || !$email || $tel === '' || $niv === '' || $formation === '') {
            $form_err = 'Merci de remplir tous les champs obligatoires.';
        } else {
            $pdo = db();
            if ($pdo !== null) {
                try {
                    $st = $pdo->prepare('INSERT INTO preinscriptions (nom, prenom, email, telephone, niveau, formation, dernier_diplome, message) VALUES (?,?,?,?,?,?,?,?)');
                    $st->execute([$nom, $prenom, $email, $tel, $niv, $formation, $diplome, $msg]);
                    $form_ok = true;
                } catch (PDOException $e) {
                    $form_err = "Une erreur est survenue. Merci de réessayer ou de nous contacter au " . SITE_PHONE_2 . '.';
                }
            } else {
                $form_err = "Le service est momentanément indisponible. Contactez-nous au " . SITE_PHONE_2 . " ou par e-mail : " . SITE_EMAIL . '.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<!-- Étapes -->
<section class="section">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($etapes as $i => $etape) : ?>
      <div class="card reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
        <span class="card-icon" style="font-family: var(--font-display); font-weight: 800; font-size: 1.3rem;"><?= $i + 1 ?></span>
        <h3><?= e($etape['titre']) ?></h3>
        <p><?= e($etape['contenu']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Conditions par niveau -->
<section class="section section-alt" id="conditions">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('clipboard-list', 15) ?> Conditions d'accès</span>
      <h2>Les conditions par niveau</h2>
    </div>
    <div class="table-wrap reveal">
      <table class="table">
        <thead>
          <tr><th scope="col">Niveau</th><th scope="col">Recrutement</th><th scope="col">Durée</th></tr>
        </thead>
        <tbody>
          <?php foreach ($niveaux as $key => $n) : ?>
          <tr>
            <td><a href="<?= url('formations/' . $key) ?>"><strong><?= e($n['titre']) ?></strong></a></td>
            <td><?= e($n['recrutement']) ?></td>
            <td><?= e($n['duree']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="caption" style="margin-top: 1rem;">Dossier commun : extrait d'acte de naissance, certificat de nationalité, dernier bulletin ou diplôme. Master de Recherche : voir les <a href="<?= url('formations/doctorat') ?>">pièces spécifiques</a>.</p>
  </div>
</section>

<!-- Préinscription -->
<section class="section" id="preinscription">
  <div class="container">
    <div class="section-head centered reveal">
      <span class="kicker"><?= icon('user-plus', 15) ?> Préinscription en ligne</span>
      <h2>Déposez votre préinscription</h2>
      <p class="lead">Gratuite et sans engagement — la scolarité vous rappelle pour finaliser votre dossier.</p>
    </div>

    <div class="card reveal" style="max-width: 820px; margin-inline: auto; padding: clamp(1.6rem, 4vw, 2.8rem);">
      <?php if ($form_ok) : ?>
        <div class="alert alert-success"><?= icon('check-circle', 20) ?><div><strong>Préinscription enregistrée !</strong> Merci pour votre confiance. Notre équipe scolarité vous contactera sous 48 h ouvrées au numéro indiqué.</div></div>
      <?php elseif ($form_err !== '') : ?>
        <div class="alert alert-danger"><?= icon('x', 20) ?><div><?= e($form_err) ?></div></div>
      <?php endif; ?>

      <?php if (!$form_ok) : ?>
      <form method="post" action="<?= url('admission#preinscription') ?>" data-validate novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="text" name="site_web" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="form-grid">
          <div class="form-field">
            <label for="pre-nom">Nom <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="pre-nom" name="nom" required autocomplete="family-name" value="<?= e($_POST['nom'] ?? '') ?>">
            <span class="error-msg">Merci d'indiquer votre nom.</span>
          </div>
          <div class="form-field">
            <label for="pre-prenom">Prénom <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="pre-prenom" name="prenom" required autocomplete="given-name" value="<?= e($_POST['prenom'] ?? '') ?>">
            <span class="error-msg">Merci d'indiquer votre prénom.</span>
          </div>
          <div class="form-field">
            <label for="pre-email">E-mail <span class="req" aria-hidden="true">*</span></label>
            <input type="email" id="pre-email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
            <span class="error-msg">Adresse e-mail invalide.</span>
          </div>
          <div class="form-field">
            <label for="pre-tel">Téléphone <span class="req" aria-hidden="true">*</span></label>
            <input type="tel" id="pre-tel" name="telephone" required autocomplete="tel" placeholder="+227 …" value="<?= e($_POST['telephone'] ?? '') ?>">
            <span class="error-msg">Merci d'indiquer un numéro de téléphone.</span>
          </div>
          <div class="form-field">
            <label for="pre-niveau">Niveau visé <span class="req" aria-hidden="true">*</span></label>
            <select id="pre-niveau" name="niveau" required>
              <option value="">— Choisir —</option>
              <?php foreach ($niveaux as $key => $n) : ?>
              <option value="<?= e($key) ?>" <?= ($_POST['niveau'] ?? '') === $key ? 'selected' : '' ?>><?= e($n['titre']) ?></option>
              <?php endforeach; ?>
            </select>
            <span class="error-msg">Merci de choisir un niveau.</span>
          </div>
          <div class="form-field">
            <label for="pre-formation">Filière souhaitée <span class="req" aria-hidden="true">*</span></label>
            <select id="pre-formation" name="formation" required>
              <option value="">— Choisir —</option>
              <?php foreach ($niveaux as $nk => $n) : ?>
              <optgroup label="<?= e($n['titre']) ?>">
                <?php foreach (formations_par_niveau($nk) as $f) : ?>
                <option value="<?= e($f['titre'] . ' (' . $n['titre'] . ')') ?>" <?= ($_POST['formation'] ?? '') === $f['titre'] . ' (' . $n['titre'] . ')' ? 'selected' : '' ?>><?= e($f['titre']) ?></option>
                <?php endforeach; ?>
                <?php if ($nk === 'doctorat') : ?>
                <option value="Master de Recherche / Doctorat">Master de Recherche / Doctorat</option>
                <?php endif; ?>
              </optgroup>
              <?php endforeach; ?>
            </select>
            <span class="error-msg">Merci de choisir une filière.</span>
          </div>
          <div class="form-field full">
            <label for="pre-diplome">Dernier diplôme obtenu</label>
            <input type="text" id="pre-diplome" name="dernier_diplome" placeholder="Ex. : BAC série D, BTS Comptabilité…" value="<?= e($_POST['dernier_diplome'] ?? '') ?>">
          </div>
          <div class="form-field full">
            <label for="pre-msg">Message (facultatif)</label>
            <textarea id="pre-msg" name="message" placeholder="Questions, situation particulière…"><?= e($_POST['message'] ?? '') ?></textarea>
          </div>
        </div>
        <div style="margin-top: 1.6rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
          <button class="btn btn-accent btn-lg" type="submit">Envoyer ma préinscription <?= icon('send', 18) ?></button>
          <span class="caption">Vos données restent confidentielles et ne servent qu'au traitement de votre candidature.</span>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Brochure -->
<section class="section section-alt">
  <div class="container grid-2">
    <div class="reveal">
      <span class="kicker"><?= icon('download', 15) ?> Documentation</span>
      <h2>Brochure officielle 2025-2026</h2>
      <p class="lead" style="margin-top: 1rem;">Retrouvez l'ensemble des filières, conditions et contacts dans la brochure officielle de l'institut, à télécharger et partager.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= asset('docs/depliant-iat-2026-2027.pdf') ?>" download="Depliant-IAT-Niger-2026-2027.pdf"><?= icon('download', 18) ?> Dépliant &amp; modalités de paiement (PDF)</a>
        <a class="btn btn-outline" href="<?= asset('img/brochure-2025-2026.jpg') ?>" download="Brochure-IAT-Niger-2025-2026.jpg"><?= icon('download', 18) ?> Brochure 2025-2026</a>
        <a class="btn btn-outline" href="<?= url('telechargements') ?>">Tous les documents</a>
      </div>
    </div>
    <div class="reveal reveal-delay-1">
      <img src="<?= asset('img/brochure-2025-2026.jpg') ?>" alt="Brochure IAT Niger 2025-2026 : filières et conditions d'admission" loading="lazy" width="700" height="500" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
