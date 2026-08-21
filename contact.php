<?php
/** Contact : coordonnées, formulaire (anti-spam honeypot + CSRF), carte. */

require_once __DIR__ . '/config/config.php';

$page_title = 'Contact — Nous écrire ou nous rendre visite | IAT Niger';
$page_desc = "Contactez l'IAT Niger : rond-point Gadafawa, Yantala, Niamey. Tél. (+227) 20 75 29 40 / 96 97 07 92 — info@iatniger.org.";
$page_slug = 'contact';
$active = 'contact';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Contact', 'url' => url('contact')],
];
$hero_titre = 'Parlons de votre avenir';
$hero_texte = "Une question sur une filière, une inscription, un partenariat ? Notre équipe vous répond rapidement.";
cms_apply_page('contact', $page_title, $page_desc, $hero_titre, $hero_texte);

/* ----- Coordonnées éditables (CMS avec fallback sur les constantes SITE_*) ----- */
$contact_address = setting('site_address', SITE_ADDRESS);
$contact_phone_1 = setting('site_phone_1', SITE_PHONE_1);
$contact_phone_2 = setting('site_phone_2', SITE_PHONE_2);
$contact_email = setting('site_email', SITE_EMAIL);
$contact_facebook = setting('site_facebook', SITE_FACEBOOK);

$coords_kicker = cms_texte('contact_coords_kicker', 'Nos coordonnées');
$coords_titre = cms_texte('contact_coords_titre', 'Rendez-nous visite ou appelez-nous');
$form_titre = cms_texte('contact_form_titre', 'Envoyez-nous un message');
$form_btn = cms_texte('contact_form_btn', 'Envoyer le message');
$form_success = cms_texte('contact_form_success', 'Message envoyé ! Merci de nous avoir écrit — nous vous répondrons dans les meilleurs délais.');
$contact_sujets = cms_texte_extra('contact_sujets')['items'] ?? [
    "Demande d'information",
    'Inscription / Admission',
    'CSP Algoza',
    'Partenariat',
    'Presse & médias',
    'Autre',
];
$map_kicker = cms_texte('contact_map_kicker', 'Localisation');
$map_link = cms_texte('contact_map_link', 'Ouvrir la carte en grand');

/* ----- Traitement du formulaire ----- */
$form_ok = false;
$form_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypot = trim($_POST['site_web'] ?? '');
    if ($honeypot !== '' || !csrf_check($_POST['csrf'] ?? null)) {
        $form_err = "Votre message n'a pas pu être vérifié. Merci de réessayer.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $tel = trim($_POST['telephone'] ?? '');
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($nom === '' || !$email || $sujet === '' || $message === '') {
            $form_err = 'Merci de remplir tous les champs obligatoires.';
        } else {
            $pdo = db();
            if ($pdo !== null) {
                try {
                    $st = $pdo->prepare('INSERT INTO messages (nom, email, telephone, sujet, message) VALUES (?,?,?,?,?)');
                    $st->execute([$nom, $email, $tel, $sujet, $message]);
                    $form_ok = true;
                } catch (PDOException $e) {
                    $form_err = 'Une erreur est survenue. Merci de réessayer plus tard.';
                }
            } else {
                $form_err = 'Le service est momentanément indisponible. Écrivez-nous à ' . $contact_email . '.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container">
    <div class="grid-2" style="align-items: flex-start;">

      <!-- Coordonnées -->
      <div class="reveal">
        <span class="kicker"><?= icon('map-pin', 15) ?> <?= e($coords_kicker) ?></span>
        <h2 class="h3" style="margin-bottom: 1.5rem;"><?= e($coords_titre) ?></h2>
        <div style="display: grid; gap: 1rem;">
          <div class="card" style="display: flex; gap: 1rem; align-items: flex-start;">
            <span class="card-icon" style="margin: 0;"><?= icon('map-pin', 22) ?></span>
            <div><h3 style="font-size: 1rem;">Adresse</h3><p><?= e($contact_address) ?></p></div>
          </div>
          <div class="card" style="display: flex; gap: 1rem; align-items: flex-start;">
            <span class="card-icon" style="margin: 0;"><?= icon('phone', 22) ?></span>
            <div>
              <h3 style="font-size: 1rem;">Téléphone</h3>
              <p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $contact_phone_1)) ?>"><?= e($contact_phone_1) ?></a><br><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $contact_phone_2)) ?>"><?= e($contact_phone_2) ?></a></p>
            </div>
          </div>
          <div class="card" style="display: flex; gap: 1rem; align-items: flex-start;">
            <span class="card-icon" style="margin: 0;"><?= icon('mail', 22) ?></span>
            <div><h3 style="font-size: 1rem;">E-mail</h3><p><a href="mailto:<?= e($contact_email) ?>"><?= e($contact_email) ?></a></p></div>
          </div>
          <div class="card" style="display: flex; gap: 1rem; align-items: flex-start;">
            <span class="card-icon" style="margin: 0;"><?= icon('facebook', 22) ?></span>
            <div><h3 style="font-size: 1rem;">Réseaux sociaux</h3><p><a href="<?= e($contact_facebook) ?>" target="_blank" rel="noopener">Facebook — IATNIGERGROUPE <?= icon('external-link', 13) ?></a></p></div>
          </div>
        </div>
      </div>

      <!-- Formulaire -->
      <div class="card reveal reveal-delay-1" style="padding: clamp(1.6rem, 4vw, 2.5rem);">
        <h2 class="h3" style="margin-bottom: 1.5rem;"><?= e($form_titre) ?></h2>

        <?php if ($form_ok) : ?>
          <div class="alert alert-success"><?= icon('check-circle', 20) ?><div><?= e($form_success) ?></div></div>
        <?php elseif ($form_err !== '') : ?>
          <div class="alert alert-danger"><?= icon('x', 20) ?><div><?= e($form_err) ?></div></div>
        <?php endif; ?>

        <?php if (!$form_ok) : ?>
        <form method="post" action="<?= url('contact') ?>" data-validate novalidate>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="text" name="site_web" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <div class="form-grid">
            <div class="form-field">
              <label for="ct-nom">Nom complet <span class="req" aria-hidden="true">*</span></label>
              <input type="text" id="ct-nom" name="nom" required autocomplete="name" value="<?= e($_POST['nom'] ?? '') ?>">
              <span class="error-msg">Merci d'indiquer votre nom.</span>
            </div>
            <div class="form-field">
              <label for="ct-email">E-mail <span class="req" aria-hidden="true">*</span></label>
              <input type="email" id="ct-email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
              <span class="error-msg">Adresse e-mail invalide.</span>
            </div>
            <div class="form-field">
              <label for="ct-tel">Téléphone</label>
              <input type="tel" id="ct-tel" name="telephone" autocomplete="tel" value="<?= e($_POST['telephone'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label for="ct-sujet">Sujet <span class="req" aria-hidden="true">*</span></label>
              <select id="ct-sujet" name="sujet" required>
                <option value="">— Choisir —</option>
                <?php foreach ($contact_sujets as $s) : ?>
                <option value="<?= e($s) ?>" <?= ($_POST['sujet'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
              </select>
              <span class="error-msg">Merci de choisir un sujet.</span>
            </div>
            <div class="form-field full">
              <label for="ct-message">Votre message <span class="req" aria-hidden="true">*</span></label>
              <textarea id="ct-message" name="message" required minlength="10"><?= e($_POST['message'] ?? '') ?></textarea>
              <span class="error-msg">Merci de rédiger votre message (10 caractères minimum).</span>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;"><?= e($form_btn) ?> <?= icon('send', 18) ?></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Carte -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('map-pin', 15) ?> <?= e($map_kicker) ?></span>
      <h2 class="h3"><?= e(setting('contact_map_titre', 'Rond-point Gadafawa, Yantala — Niamey')) ?></h2>
    </div>
    <iframe class="map-embed reveal" src="https://www.openstreetmap.org/export/embed.html?bbox=2.066680%2C13.522711%2C2.096680%2C13.542711&amp;layer=mapnik&amp;marker=13.532711%2C2.081680" title="Carte de localisation de l'IAT Niger à Niamey" loading="lazy"></iframe>
    <p class="caption" style="margin-top: 0.8rem;"><a href="https://www.openstreetmap.org/?mlat=13.532711&amp;mlon=2.081680#map=16/13.532711/2.081680" target="_blank" rel="noopener"><?= e($map_link) ?> <?= icon('external-link', 13) ?></a></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
