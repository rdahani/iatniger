# Audit UX/UI — Site actuel iatniger.org (Joomla)

Date : 17 juillet 2026 — Phase 1 & 2 du workflow de refonte.

## Faiblesses identifiées

### Design & crédibilité
- Template Joomla générique daté (~2015) : aucune identité premium, typographie système, bannières surchargées.
- Hiérarchie visuelle absente : tout est au même niveau, aucun point focal, densité excessive.
- Images non optimisées (JPEG lourds, noms de fichiers WhatsApp), pas de lazy loading.
- Aucune cohérence de palette : bleu/orange du logo noyés dans des rouges, gris et dégradés hétérogènes.

### UX & conversion
- Aucun parcours d'inscription : pas de CTA « S'inscrire », pas de page Admission dédiée.
- Navigation confuse : « Bienvenu », mélange institut / école primaire dans le même menu.
- Contenus clés (conditions d'accès, frais, débouchés) enfouis dans des pages longues non structurées.
- Aucune preuve sociale mise en avant (30 000 diplômés, CAMES, 25 ans — invisibles).
- Pas de recherche, pas de FAQ, pas de breadcrumb.

### Technique
- URLs Joomla `/index.php/...` non propres, pas de sitemap détecté, meta descriptions absentes.
- Non responsive sur plusieurs gabarits, cassures visuelles mobile.
- Aucun dark mode, aucune conformité WCAG (contrastes, focus, alt).

## Décisions de refonte
1. Parcours de conversion central : Hero → Preuves → Formations → Admission (CTA permanent « S'inscrire »).
2. Palette premium dérivée du logo : bleu roi + orange, neutres froids, dark mode.
3. Architecture claire : Institut (supérieur) au centre, CSP Algoza en section dédiée.
4. Stack : HTML/CSS/JS + PHP/MySQL (XAMPP), URLs propres via .htaccess, SEO complet.
