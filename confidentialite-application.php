<?php
/**
 * Politique de confidentialité de l'application mobile « IAT Niger ».
 * Adresse à déclarer dans la console Google Play : /confidentialite-application
 */

require_once __DIR__ . '/config/config.php';

$page_title = "Confidentialité de l'application mobile | IAT Niger";
$page_desc = "Quelles données l'application mobile IAT Niger utilise, pourquoi, et comment supprimer votre compte.";
$page_slug = 'confidentialite-application';
$active = '';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Confidentialité de l\'application', 'url' => url('confidentialite-application')],
];
$hero_titre = "Confidentialité de l'application mobile";
$hero_texte = 'Dernière mise à jour : 16 octobre 2026';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<article class="section">
  <div class="container" style="max-width: 820px;">
    <div class="article-body">

      <h2>Qui sommes-nous ?</h2>
      <p>L'application « IAT Niger » est éditée par l'Institut Africain de Technologie (IAT), BP 412, Rond-Point Gadafawa, Yantala – Commune 1, Niamey, Niger. Contact : <a href="mailto:info@iatniger.org">info@iatniger.org</a> — (+227) 20 75 29 40.</p>

      <h2>À quoi sert l'application ?</h2>
      <ul>
        <li><strong>Espace public</strong>, sans compte : présentation de l'institut, formations, actualités, contacts.</li>
        <li><strong>Espace Étudiants &amp; Parents</strong>, avec compte : scolarité et paiements, notes, absences et emploi du temps de l'étudiant.</li>
      </ul>

      <h2>Données utilisées</h2>
      <h3>Sans compte</h3>
      <p>Aucune donnée personnelle n'est demandée. Comme tout service en ligne, nos serveurs reçoivent l'adresse IP de l'appareil lorsqu'il charge les actualités et les informations de l'institut.</p>
      <h3>Avec un compte Étudiant / Parent</h3>
      <ul>
        <li><strong>Matricule de l'étudiant et mot de passe</strong> : pour vous identifier. Le mot de passe n'est jamais conservé sur le téléphone ; le serveur n'en garde qu'une empreinte chiffrée.</li>
        <li><strong>Numéro de téléphone déjà enregistré à l'inscription</strong> (étudiant ou parent) : pour envoyer par SMS le code de première connexion ou de mot de passe oublié.</li>
        <li><strong>Identifiant technique de l'installation, type d'appareil, adresse IP</strong> : pour sécuriser la session et permettre de la révoquer.</li>
        <li><strong>Notifications (facultatives)</strong> : un identifiant de notification attribué au téléphone par Google Firebase Cloud Messaging, pour prévenir d'un nouveau paiement, d'une absence ou de nouvelles notes. La notification ne contient ni note ni montant : seulement le prénom de l'étudiant et, pour les notes, le nom des matières. Vous pouvez la refuser ou la désactiver à tout moment dans les réglages du téléphone ; l'application fonctionne sans.</li>
      </ul>
      <p>Les informations affichées (nom, classe, notes, absences, paiements, emploi du temps) sont celles que l'institut détient déjà dans son système de gestion de la scolarité. L'application les affiche ; elle n'en collecte pas de nouvelles.</p>

      <h2>Ce que l'application ne fait pas</h2>
      <ul>
        <li>Pas de publicité, pas de traceur publicitaire, pas d'outil de mesure d'audience.</li>
        <li>Aucune donnée n'est vendue ni transmise à des tiers. Seuls nos prestataires techniques interviennent : le prestataire d'envoi de SMS reçoit le numéro de téléphone et le code de vérification ; Google Firebase Cloud Messaging achemine les notifications (identifiant de notification et texte court de l'alerte).</li>
        <li>Pas d'accès aux contacts, à la position, aux photos, au micro ni à la caméra.</li>
      </ul>

      <h2>Sécurité</h2>
      <ul>
        <li>Tous les échanges passent par une connexion chiffrée (HTTPS).</li>
        <li>Les jetons de session sont conservés dans le stockage chiffré du téléphone.</li>
        <li>Une copie des dernières données consultées est gardée, chiffrée, pour une lecture sans réseau ; elle est effacée à la déconnexion.</li>
        <li>Les captures d'écran sont bloquées dans l'espace personnel.</li>
        <li>Verrouillage facultatif par empreinte ou code du téléphone. L'empreinte reste gérée par le téléphone : l'application n'y a jamais accès.</li>
        <li>Après plusieurs mots de passe erronés, le compte est bloqué temporairement.</li>
      </ul>

      <h2>Durée de conservation</h2>
      <ul>
        <li>Session sur un téléphone : 30 jours sans utilisation, ou jusqu'à la déconnexion.</li>
        <li>Compte portail : jusqu'à sa suppression.</li>
        <li>Dossier scolaire : conservé par l'institut selon ses obligations administratives, indépendamment de l'application.</li>
      </ul>

      <h2 id="suppression">Vos droits — supprimer votre compte</h2>
      <p><strong>Dans l'application :</strong> Mon espace → menu (⋮) → Supprimer mon compte. Le mot de passe et tous les accès sont supprimés immédiatement, sur tous les téléphones.</p>
      <p><strong>Sans l'application :</strong> écrivez à <a href="mailto:info@iatniger.org">info@iatniger.org</a> en indiquant le matricule de l'étudiant.</p>
      <p>La suppression du compte ne supprime pas le dossier scolaire, que l'institut doit conserver. Pour consulter ou faire rectifier ce dossier, adressez-vous à l'administration de l'IAT.</p>

      <h2>Mineurs</h2>
      <p>Lorsque l'étudiant est mineur, le compte est utilisé par lui-même ou par son parent ou tuteur, à qui l'institut remet le matricule.</p>

      <h2>Modifications</h2>
      <p>Toute modification de cette politique sera publiée sur cette page, avec sa date de mise à jour.</p>

    </div>
  </div>
</article>

<?php require __DIR__ . '/includes/footer.php'; ?>
