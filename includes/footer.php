<?php
/** Pied de page commun : CTA, newsletter, colonnes de liens, coordonnées. */
$footer_cta_titre = setting('footer_cta_titre', "Prêt·e à rejoindre un pôle d'excellence ?");
$footer_cta_texte = setting('footer_cta_texte', 'Inscriptions ouvertes — BTS, Licences, Masters et Doctorat. Rejoignez plus de 30 000 diplômés.');
$footer_cta_btn1 = setting('footer_cta_btn1', "Je m'inscris");
$footer_cta_btn2 = setting('footer_cta_btn2', 'Nous contacter');
$footer_about = setting('footer_about', "L'Institut Africain de Technologie forme depuis 1999 les cadres et techniciens qui construisent le Niger et l'Afrique de demain.");
$footer_mention = setting('footer_mention', "Agréé par arrêtés N° 0143 & 0233/MEN/DEPRI/DETFP (1999) · Diplômes accrédités CAMES / ANAQ-SUP");
$footer_newsletter_label = setting('footer_newsletter_label', 'Newsletter — restez informé·e');
$footer_address = setting('site_address', SITE_ADDRESS);
$footer_phone_1 = setting('site_phone_1', SITE_PHONE_1);
$footer_phone_2 = setting('site_phone_2', SITE_PHONE_2);
$footer_email = setting('site_email', SITE_EMAIL);
$footer_facebook = setting('site_facebook', SITE_FACEBOOK);
$footer_full_name = setting('site_full_name', SITE_FULL_NAME);
$footer_tagline = setting('site_tagline', SITE_TAGLINE);
$wa_raw = setting('site_whatsapp', defined('SITE_WHATSAPP') ? SITE_WHATSAPP : '');
$wa_digits = preg_replace('/\D+/', '', (string) $wa_raw) ?: '';
$wa_text = rawurlencode(setting('footer_whatsapp_prefill', 'Bonjour, je souhaite des informations sur l\'IAT Niger.'));
$wa_url = $wa_digits !== '' ? 'https://wa.me/' . $wa_digits . '?text=' . $wa_text : '';
?>
</main>

<footer class="footer">
  <div class="container">

    <div class="footer-cta reveal">
      <div>
        <h2 class="footer-cta-title"><?= e($footer_cta_titre) ?></h2>
        <p><?= e($footer_cta_texte) ?></p>
      </div>
      <div class="footer-cta-actions">
        <a class="btn btn-accent btn-lg" href="<?= url('admission#preinscription') ?>"><?= e($footer_cta_btn1) ?> <?= icon('arrow-right', 18) ?></a>
        <a class="btn btn-ghost-light btn-lg" href="<?= url('contact') ?>"><?= e($footer_cta_btn2) ?></a>
      </div>
    </div>

    <div class="footer-grid">
      <div class="footer-col footer-brand">
        <img src="<?= asset('img/logoiat.png') ?>" alt="Logo IAT Niger" width="150" height="57" loading="lazy">
        <p><?= e($footer_about) ?> <em><?= e($footer_tagline) ?></em>.</p>
        <div class="footer-social">
          <a href="<?= e($footer_facebook) ?>" target="_blank" rel="noopener" aria-label="Facebook IAT Niger"><?= icon('facebook', 18) ?></a>
          <a href="<?= url('web-tv') ?>" aria-label="WEB TV IAT Niger"><?= icon('youtube', 18) ?></a>
        </div>
      </div>

      <nav class="footer-col" aria-label="Formations">
        <h3>Formations</h3>
        <ul>
          <li><a href="<?= url('formations/niveau-moyen') ?>">Niveau Moyen</a></li>
          <li><a href="<?= url('formations/licence') ?>">Licences Professionnelles</a></li>
          <li><a href="<?= url('formations/master') ?>">Masters Professionnels</a></li>
          <li><a href="<?= url('formations/doctorat') ?>">Master de Recherche &amp; Doctorat</a></li>
          <li><a href="<?= url('admission') ?>">Conditions d'admission</a></li>
        </ul>
      </nav>

      <nav class="footer-col" aria-label="L'institut">
        <h3>L'institut</h3>
        <ul>
          <li><a href="<?= url('a-propos') ?>">À propos</a></li>
          <li><a href="<?= url('vie-etudiante') ?>">Vie étudiante</a></li>
          <li><a href="<?= url('csp-algoza') ?>">CSP Algoza</a></li>
          <li><a href="<?= url('actualites') ?>">Actualités</a></li>
          <li><a href="<?= url('partenaires') ?>">Partenaires</a></li>
          <li><a href="<?= url('telechargements') ?>">Téléchargements</a></li>
          <li><a href="<?= url('faq') ?>">FAQ</a></li>
        </ul>
      </nav>

      <div class="footer-col" id="footer-contact">
        <h3>Contact</h3>
        <ul class="footer-contact">
          <li><?= icon('map-pin', 18) ?><span><?= e($footer_address) ?></span></li>
          <li><?= icon('phone', 18) ?><span><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $footer_phone_1)) ?>"><?= e($footer_phone_1) ?></a> · <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $footer_phone_2)) ?>"><?= e($footer_phone_2) ?></a></span></li>
          <li><?= icon('mail', 18) ?><a href="mailto:<?= e($footer_email) ?>"><?= e($footer_email) ?></a></li>
        </ul>
        <form class="newsletter" method="post" action="<?= url('newsletter.php') ?>" aria-label="Inscription à la newsletter">
          <label for="nl-email"><?= e($footer_newsletter_label) ?></label>
          <div class="newsletter-row">
            <input type="email" id="nl-email" name="email" placeholder="Votre adresse e-mail" required autocomplete="email">
            <input type="text" name="site_web" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="btn btn-accent" type="submit" aria-label="S'abonner à la newsletter"><?= icon('send', 16) ?></button>
          </div>
        </form>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= e($footer_full_name) ?> — Tous droits réservés.</p>
      <p><?= e($footer_mention) ?></p>
    </div>
  </div>
</footer>

<?php if ($wa_url !== '') : ?>
<a class="whatsapp-float" href="<?= e($wa_url) ?>" target="_blank" rel="noopener noreferrer" aria-label="Discuter sur WhatsApp">
  <?= icon('whatsapp', 28) ?>
  <span class="whatsapp-float-label">WhatsApp</span>
</a>
<?php endif; ?>

<script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
