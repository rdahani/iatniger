# Design System — IAT Niger (refonte 2026)

## Marque
Logo : carte d'Afrique bleue + monogramme IAT bleu/orange. Slogan : « Un pôle d'excellence ».

## Palette (tokens CSS)
| Token | Light | Dark | Usage |
|---|---|---|---|
| `--primary` | #2239A8 (bleu roi affiné) | #6D84F5 | Actions, liens, marque |
| `--primary-deep` | #16226B | #0E1440 | Hero, footer, surfaces marquées |
| `--accent` | #F7941E (orange IAT) | #FFA53B | CTA secondaires, highlights |
| `--bg` | #F7F8FC | #0B1020 | Fond de page |
| `--surface` | #FFFFFF | #131A31 | Cards, panneaux |
| `--text` | #131A31 | #E8ECF8 | Texte principal |
| `--muted` | #5A6280 | #9AA3C0 | Texte secondaire |
| `--success/warning/danger` | #16A34A / #D97706 / #DC2626 | ajustés dark | États |

Contrastes vérifiés WCAG 2.2 AA (texte normal ≥ 4.5:1).

## Typographie (Google Fonts)
- Display / titres : **Plus Jakarta Sans** (700/800) — moderne, institutionnel.
- Corps : **Inter** (400/500/600) — lisibilité maximale.
- Échelle fluide (clamp) : Display 44–72px · H1 36–56 · H2 28–40 · H3 20–24 · Body 16–17 · Caption 13–14.

## Composants
Navbar sticky glassmorphism · mega menu Formations · hero gradient + image · stat counters animés · cards premium (soft shadow, hover lift) · timeline historique · accordion FAQ/filières · tabs niveaux · carousel témoignages · galerie masonry + lightbox · badges (CAMES, LMD) · breadcrumb · footer 4 colonnes + newsletter · formulaires validés (honeypot anti-spam).

## Motion
IntersectionObserver reveal (fade/slide 500ms cubic-bezier), compteurs, hover lift 4px, transitions 200ms, `prefers-reduced-motion` respecté.

## Architecture des pages
Accueil · À propos (historique, mission/vision, valeurs, mot de la direction, enseignants, partenaires) · Formations (hub + niveau-moyen / licence / master / doctorat) · Admission · Vie étudiante (BDE, alumni) · CSP Algoza · Actualités (+ détail) · WEB TV · Galerie · FAQ · Contact · Recherche · 404 · Admin (login, actualités, messages, préinscriptions).
