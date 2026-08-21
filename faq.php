<?php
/** FAQ avec accordéons et données structurées Schema.org FAQPage. */

require_once __DIR__ . '/config/config.php';

$page_title = 'FAQ — Questions fréquentes | IAT Niger';
$page_desc = "Admission, frais, accréditations, débouchés : toutes les réponses aux questions les plus fréquentes sur l'IAT Niger.";
$page_slug = 'faq';
$active = 'actualites';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'FAQ', 'url' => url('faq')],
];
$hero_titre = 'Questions fréquentes';
$hero_texte = "Tout ce qu'il faut savoir avant de rejoindre l'IAT.";
cms_apply_page('faq', $page_title, $page_desc, $hero_titre, $hero_texte);

$faqs_defaut = [
    ["Quelles sont les conditions pour s'inscrire en Licence ?",
        "Le BAC toutes séries (ou un diplôme équivalent) donne accès à la 1ère année. Les titulaires d'un BTS, DUT ou d'une L2 avec 120 crédits validés peuvent intégrer directement la Licence 3. Le dossier comprend un extrait d'acte de naissance, un certificat de nationalité et le dernier bulletin ou diplôme."],
    ["Puis-je m'inscrire sans le BAC ?",
        "Oui. Le Niveau Moyen (Bac Professionnel et Technique) recrute dès le BEPC ou niveau 3ème, pour une formation en 3 ans dans 6 filières tertiaires et industrielles."],
    ["Les diplômes de l'IAT sont-ils reconnus ?",
        "Oui. L'IAT est agréé par l'État du Niger depuis 1999 (arrêtés N° 0143 et 0233/MEN/DEPRI/DETFP) et 16 de ses diplômes sont accrédités au CAMES, dans le système LMD. Les formations sont également reconnues par l'ANAQ-SUP."],
    ["Comment se déroule la préinscription en ligne ?",
        "Remplissez le formulaire de préinscription sur la page Admission : c'est gratuit et sans engagement. Notre service scolarité vous rappelle sous 48 h ouvrées pour finaliser votre dossier."],
    ["Quels sont les frais de scolarité ?",
        "Les frais varient selon le niveau et la filière. Pour le CSP Algoza : 230 000 F CFA/an au primaire, 340 000 F CFA au collège et 390 000 F CFA au lycée. Pour l'institut supérieur, contactez la scolarité au (+227) 20 75 29 40 pour un devis précis. Le Master de Recherche (Université de Douala) : 300 000 F CFA de frais d'inscription."],
    ["L'IAT propose-t-il un Doctorat ?",
        "Oui, via le partenariat avec l'ESSEC de l'Université de Douala (Cameroun) : après le Master de Recherche, vous pouvez poursuivre en thèse dans l'École Doctorale (Business Economics, Management des organisations, Science Juridique, Science de l'ingénieur)."],
    ["Y a-t-il des travaux pratiques ?",
        "Oui. Deux laboratoires modernes (Génie Électrique et Génie Civil) ont été inaugurés en 2026, et les filières informatiques travaillent sur des environnements professionnels (GNU/Linux, réseaux, bases de données)."],
    ["Où se trouve le campus ?",
        "Au rond-point Gadafawa, quartier Yantala, Commune 1, Niamey (BP 412). Téléphone : (+227) 20 75 29 40 / 96 97 07 92 — info@iatniger.org."],
];
$faqs = cms_faq() ?: $faqs_defaut;

$cta_lead = cms_texte('faq_cta_lead', 'Vous ne trouvez pas votre réponse ?');
$cta_btn = cms_texte('faq_cta_btn', 'Contactez-nous');

require __DIR__ . '/includes/header.php';
?>
<!-- JSON-LD : FAQPage -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php foreach ($faqs as $i => [$q, $a]) : ?>
    {"@type": "Question", "name": <?= json_encode($q, JSON_UNESCAPED_UNICODE) ?>, "acceptedAnswer": {"@type": "Answer", "text": <?= json_encode($a, JSON_UNESCAPED_UNICODE) ?>}}<?= $i < count($faqs) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>

<?php require __DIR__ . '/includes/page-hero.php'; ?>

<section class="section">
  <div class="container" style="max-width: 860px;">
    <div class="accordion">
      <?php foreach ($faqs as [$q, $a]) : ?>
      <div class="accordion-item reveal">
        <button class="accordion-trigger" type="button" aria-expanded="false"><?= e($q) ?> <?= icon('chevron-down', 20) ?></button>
        <div class="accordion-panel"><div><div class="accordion-content"><p><?= e($a) ?></p></div></div></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top: 3rem;">
      <p class="lead" style="margin-bottom: 1.2rem;"><?= e($cta_lead) ?></p>
      <a class="btn btn-primary btn-lg" href="<?= url('contact') ?>"><?= e($cta_btn) ?> <?= icon('arrow-right', 18) ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
