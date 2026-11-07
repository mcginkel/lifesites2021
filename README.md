# LifeSites2021

WordPress-thema voor de backend van de website van **Life Voorthuizen**.

De publieke website (https://lifesites.nl) is een aparte Angular-app die
alle content via de REST API uit deze WordPress-installatie haalt
(https://dev.lifesites.nl). WordPress is dus vooral de plek waar
redacteuren pagina's en de agenda bijhouden; dit thema zorgt voor:

- de REST-velden en aanpassingen die de app nodig heeft (`inc/rest-*.php`);
- een blok-editor die zoveel mogelijk lijkt op de app (`editor-style.css`);
- paginatemplates (rood, groen, geel, landingspagina) die de app herkent;
- een eenvoudige eigen weergave van de site op de WordPress-host zelf.

Het thema is in 2021 gestart vanaf [Underscores](https://underscores.me/).

## Structuur

| Pad | Inhoud |
|---|---|
| `style.css` | themakop (naam, versie) en stijlen van de WP-voorkant |
| `functions.php` | thema-instellingen, laadt de bestanden in `inc/` en `blocks/` |
| `inc/` | REST-aanpassingen voor de app en hulpfuncties |
| `blocks/` | eigen blok (onderliggende pagina's) |
| `templates/` | paginatemplates |
| `editor-style.css`, `js/` | styling en scripts voor de blok-editor |
| `bin/build-zip.sh` | bouwt de installeerbare themazip |
| `docs/` | documentatie (niet in de zip) |

## Deployen

Kort: versie ophogen, GitHub-release maken, de zip van de release uploaden
in WP-admin. Zie [docs/DEPLOY.md](docs/DEPLOY.md).

## Documentatie

- [docs/DEPLOY.md](docs/DEPLOY.md) — bouwen, uploaden, controleren, terugrollen
- [docs/specs-huidige-website.md](docs/specs-huidige-website.md) — wat de huidige site doet
- [docs/security-review-2026.md](docs/security-review-2026.md) — risico's en prioriteiten
- [docs/styling-theme-vs-app.md](docs/styling-theme-vs-app.md) — thema/editor tegenover de app
- [docs/snippets.md](docs/snippets.md) — Code Snippets en wat naar het thema gaat

## Licentie

GPLv2 of later, zie `LICENSE`.
