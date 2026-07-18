# IAT Niger — Site web (refonte 2026)

Refonte complète du site de l'**Institut Africain de Technologie** (iatniger.org) : design premium, contenus intégralement repris du site historique, stack **HTML / CSS / JS / PHP / MySQL** pensée pour XAMPP puis pour un hébergement mutualisé classique.

## Installation locale (XAMPP)

1. Placer le dossier dans `C:\xampp\htdocs\iatniger` (déjà fait).
2. Démarrer **Apache** et **MySQL** dans le panneau XAMPP.
3. Importer la base : `phpMyAdmin → Importer → database/iatniger.sql`
   (ou en ligne de commande : `C:\xampp\mysql\bin\mysql.exe -u root < database\iatniger.sql`).
4. Ouvrir <http://localhost/iatniger/>.

## Espace d'administration

- URL : <http://localhost/iatniger/admin/>
- Identifiant : `admin` — Mot de passe : `IatNiger#2026`
- **⚠ À changer avant toute mise en production** (table `users`, hash via `password_hash()`).
- Fonctions : tableau de bord, CRUD des actualités, messages de contact, préinscriptions, compteur newsletter.

## Mise en production

1. Dans `config/config.php` :
   - Si le site est à la **racine** du domaine : `define('SITE_BASE', '');` avant le bloc auto-détecté (ou laisser la détection auto).
   - Renseigner `DB_HOST`, `DB_USER`, `DB_PASS` (utilisateur MySQL dédié, pas `root`).
2. Mettre à jour `RewriteBase /` dans `.htaccess` (au lieu de `/iatniger/`) et la ligne `Sitemap:` de `robots.txt`.
3. **Changer le mot de passe admin** (`Utilisateurs` dans l’admin) — le mot de passe par défaut `IatNiger#2026` ne doit jamais rester en prod.
4. Activer HTTPS et forcer la redirection HTTP → HTTPS côté hébergeur.
5. Supprimer ou protéger `admin/install-cms.php` et `database/seed-cms.php` après installation.

## Architecture

```
iatniger/
├── index.php, a-propos.php, formations.php, admission.php, …   Pages publiques
├── actualite.php, actualites.php          Actualités (BDD + fallback PHP)
├── admin/                                 Espace d'administration (auth + CRUD)
├── assets/css/style.css                   Design system complet (tokens, dark mode)
├── assets/js/main.js                      Interactions (menu, reveal, compteurs…)
├── assets/img/                            Images migrées du site historique
├── config/config.php                      Constantes site + connexion PDO
├── constants/formations.php               Catalogue des 28 formations (source unique)
├── constants/actualites.php               Actualités de secours si MySQL absent
├── includes/                              header, footer, icônes SVG, hero de page
├── database/iatniger.sql                  Schéma + données initiales
├── docs/                                  Audit UX et design system
├── sitemap.php, robots.txt, .htaccess     SEO & réécriture d'URLs
```

## Points clés

- **URLs propres** : `/formations/licence`, `/actualites/{slug}` via `.htaccess`.
- **SEO** : title/description par page, Open Graph, Twitter Cards, JSON-LD (Organisation, Breadcrumb, Article, FAQ), sitemap dynamique.
- **Accessibilité** : WCAG 2.2 AA — skip-link, focus visibles, ARIA, contrastes vérifiés, `prefers-reduced-motion`.
- **Dark mode** : bascule dans la barre de navigation, persistance `localStorage`, respect de la préférence système.
- **Sécurité** : requêtes préparées PDO, échappement systématique (`e()`), CSRF sur tous les formulaires, honeypot anti-spam, en-têtes de sécurité, mots de passe bcrypt.
- **Résilience** : si MySQL est arrêté, le site public reste entièrement fonctionnel (contenus de secours).
