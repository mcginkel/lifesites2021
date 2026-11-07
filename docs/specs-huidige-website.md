# LifeSites — specificatie van de huidige website (stand 7 november 2026)

Doel van dit document: vastleggen **wat de huidige website doet en hoe**, als
startpunt voor een requirements-inventarisatie bij een eventuele herbouw.
Het beschrijft de *bestaande* situatie, inclusief tekortkomingen. Punten die
nog niet zijn vastgesteld staan gemarkeerd met ❓.

---

## 1. Doel en gebruikers

| | |
|---|---|
| Organisatie | Life Voorthuizen (kerkelijke gemeente) |
| Publieke site | https://lifesites.nl |
| Bezoekers | (nieuwe) bezoekers van samenkomsten, gemeenteleden, jongeren, ouders |
| Redacteuren | vrijwilligers die pagina's en agenda bijhouden in WordPress ❓ (aantal, rollen) |
| Beheer | Kees (WordPress-backend, thema), Martijn Lutgens / Lutsoft (Angular-app) |

Kernbehoeften die de site nu invult:
1. Informeren: wie zijn we, samenkomsten, praktische info ("Nieuw hier?"), contact, ANBI.
2. Agenda: komende activiteiten met datum, tijd, locatie, beschrijving, afbeelding.
3. Groei/Samen-op/Over Life: inhoudspagina's per bediening (Youth, Kidsjam, Ladies, Alpha, …).
4. Uitnodigen: preekseries, campagnes (carrousel op de homepage).
5. Eigen huisstijl die precies klopt, met redactie in een vertrouwde CMS.

## 2. Architectuur

```
Bezoeker ──► https://lifesites.nl  (Angular 13 + Ionic 6 SPA, statisch, openresty)
                     │  XHR (anoniem, JSON)
                     ▼
            https://dev.lifesites.nl/wp-json/…   (WordPress 7.1.3, PHP 8.5, Apache, cPanel)
                     ▲
Redacteur ───────────┘  wp-admin (blok-editor)
```

- **Headless WordPress**: WordPress is alleen bron van content. De publieke
  site is een losse Angular-app die de REST API leest en de blok-markup zelf
  rendert.
- **Naamgeving**: `dev.lifesites.nl` is ondanks de naam de *productie*-backend.
  De Angular-app gebruikt hem in alle omgevingen (`environment*.ts`).
- WordPress heeft daarnaast een eigen thema (`lifesites2021`) en publiceert
  dezelfde content óók als gewone website op `dev.lifesites.nl` (zie §8).

### Hosting en deploy

| Onderdeel | Hosting | Code | Deploy |
|---|---|---|---|
| Angular-app | lifesites.nl (openresty) | Bitbucket `lifesites/life-frontend` | Bitbucket Pipelines → FTP (`master` → productie, `develop` → test) |
| WordPress | cPanel, `/home/lifesi1q/dev.lifesites.nl`, Imunify360 | — (core + plugins via wp-admin) | updates via wp-admin |
| Thema | idem | GitHub `mcginkel/lifesites2021` | GitHub-release → zip → WP-admin upload (zie `docs/DEPLOY.md`) |
| Code Snippets | in de WP-database | — | via wp-admin |

## 3. Contentmodel (WordPress)

| Type | Gebruik | Velden die de app gebruikt |
|---|---|---|
| **Pagina** | alle inhoudspagina's; homepage = ingestelde voorpagina | `slug`, `template`, `blocks` (ruwe blok-markup), uitgelichte afbeelding via media |
| **Bericht** | ❓ in gebruik? app heeft `posts/{id}` in de service maar geen route | — |
| **Evenement** (The Events Calendar) | agenda | `title`, `description`, `start_date(_details)`, `end_date(_details)`, `all_day`, `venue.{venue,address,city}`, `image.url` |
| **Locatie / Organisator** (TEC) | bij evenementen | `venue` genest in event |
| **Media** | afbeeldingen | `wp/v2/media/{id}` (maten/srcset) |
| **Menu** | hoofdmenu `primary` | `menus/v1/menus/primary` (plugin WP-REST-API V2 Menus) |

### Paginatemplates (kleur van de hele pagina)

| Template | App | WP-thema |
|---|---|---|
| standaard | wit | wit |
| `template-red-page.php` | rood `#fb5045`, witte tekst | rood, witte tekst |
| `template-green-page.php` | groen `#166772`, witte tekst | groen, witte tekst |
| `template-yellow-page.php` | geel `#ffd559`, **witte** tekst (laag contrast) | geel, **zwarte** tekst |
| `template-landing-page.php` | ❓ geen speciale behandeling in de app | kale pagina zonder header/footer |

### Kleurpalet (editor)

Rood `#fb5045`, groen `#166772`, geel `#ffd559`, wit, zwart. Eigen kleuren
zijn uitgeschakeld. De slugs bevatten een `#` (`life-red-#fb5045`), wat leidt
tot rare classnamen (`has-life-red-fb-5045-color`).

## 4. Publieke site (Angular-app)

### Routes

| Route | Inhoud | Bron |
|---|---|---|
| `/` → `/home` | voorpagina | `wp/v2/frontpage` (plugin WP REST API Frontpage) |
| `/agenda` | agenda | `tribe/events/v1/events` |
| `/:slug` | elke pagina | `wp/v2/pages?slug=…` |
| `/404` | niet gevonden | — |

Alle pagina's hangen op het hoogste niveau (`/slug`); de WordPress-hiërarchie
van pagina's wordt in de URL niet gebruikt.

### Header en menu
- Logo (80px) links, menu rechts; submenu's tot 2 niveaus als dropdown.
- Onder 870px: hamburger + zijmenu (Ionic menu).
- Menu-items komen uit het WP-menu `primary`.

### Footer (hard gecodeerd in de app, niet beheerbaar in WP)
- Contact: tel 0342 47 33 29, info@lifesites.nl, link ANBI (`/geven-en-delen/`).
- Postadres: Postbus 82, 3780 BB Voorthuizen.
- Diensten: De Eng, Rochetstraat 2, 3781 CZ Voorthuizen; link naar agenda.
- Social: Facebook, YouTube.

### Ondersteunde blokken
De app vertaalt blok-markup zelf naar componenten. Alleen deze blokken worden
weergegeven; **alle andere blokken verdwijnen zonder melding**:

| Blok | Weergave in app |
|---|---|
| Paragraaf, Kop, Lijst | tekst in contentkolom (max 1020px, 30px marge) |
| Afbeelding | volle breedte, max 60vh |
| Cover | afbeelding met tekst eroverheen |
| Kolommen / Kolom | flex, stapelen onder 670px |
| Groep | blok met padding |
| Citaat, Pullquote | rood, groot, max 800px |
| Scheidingslijn | dunne lijn 30% |
| Carousel (`cb/carousel`, plugin Carousel Slider Block) | swiper, enkel- of meer-slides |

Niet ondersteund o.a.: knoppen, embed (YouTube), video, tabel, child-pages-blok
van het thema, TEC-blokken, patronen met andere blokken. ❓ welke daarvan
redacteuren nu (proberen te) gebruiken.

### Agenda
- Lijst van komende evenementen (standaard eerste pagina van de API, ❓ max aantal).
- Eerste item standaard uitgeklapt; klik klapt een ander item uit.
- Per item: titel, eerste alinea van de beschrijving, datum (+ einddatum bij
  meerdaagse hele-dag-events), tijd, locatie, afbeelding.
- Beschrijving: alleen de **eerste alinea** wordt getoond.

### Typografie en stijl
- Lettertype DM Sans (400/500/700, zelf gehost in de app).
- h1 2rem, h2 1.125rem, h3 1.25rem, gewicht 600; links erven kleur, vet.
- Contentbreedte 1020px, zijmarge 30px.

## 5. Redactie (wp-admin)

- Blok-editor (Gutenberg) voor pagina's; TEC-editor voor evenementen.
- Paginatemplate kiezen bepaalt achtergrondkleur in de app.
- Geen preview van hoe het in de app wordt (preview toont het WP-thema).
- Patterncategorie "LIFE" bestaat (❓ welke patronen).
- Plugins voor redactie: Secondary Title, Carousel Slider Block, Smash Balloon YouTube Feed (❓ gebruikt door de app? niet in de app-code gevonden).

## 6. Integraties en plugins (actief)

| Plugin | Versie | Rol |
|---|---|---|
| The Events Calendar | 5.16.4 (6.18.3.1 beschikbaar) | agenda + REST `tribe/events/v1` |
| WP-REST-API V2 Menus | 0.12.1 | menu via REST |
| WP REST API Frontpage | 1.1.0 | voorpagina via REST |
| Carousel Slider Block | 2.1.6 | carrousel-blok |
| Secondary Title | 2.2.1 | extra titel |
| Smash Balloon YouTube Feed | 2.10.0 | ❓ |
| Code Snippets | 4.0.0 | maatwerk-PHP (zie security-review) |
| Core Rollback | 1.4.3 | terugzetten WP-core |

Inactief maar geïnstalleerd: Akismet, Iptanus File Upload, Modern Events
Calendar Lite, Smash Balloon Instagram Feed.

Maatwerk in thema (`lifesites2021` 1.1.0):
- REST-veld `blocks` (ruwe blok-markup) op posts/pages, alleen view/embed-context.
- TEC-beschrijving met kale `<p>` in REST (de app zoekt letterlijk `<p>`).
- Blok `core/child-pages` (server-side, alleen voor het WP-thema).

Ongebruikt in de app: `LifePcoService` (Planning Center, endpoint
`dev.lifesites.nl/api/`) — code aanwezig, nergens aangeroepen.

## 7. Niet-functioneel (huidige stand)

| Aspect | Stand |
|---|---|
| SEO | SPA zonder server-side rendering; paginatitel is overal "LIFE"; geen meta-descriptions per pagina ❓ |
| Toegankelijkheid | witte tekst op geel; klikbare `div`s in agenda/menu (geen knoppen/links) |
| Prestatie | elke pagina = 2–3 API-calls naar een andere host; geen caching-laag ❓ |
| Beveiliging | zie `security-review-2026.md` |
| Beheerbaarheid | content-afhankelijke parsing met regexes; nieuwe blokken vergen app-release |
| Techniek-leeftijd | Angular 13 / Ionic 6 / Node 14-pipeline (2022) — allemaal end-of-life |

## 8. Bekende problemen en aandachtspunten

1. **Twee publieke sites met dezelfde content**: lifesites.nl (app) en
   dev.lifesites.nl (WP-thema). ❓ Wordt dev.lifesites.nl geïndexeerd?
2. **Stille blokken**: blokken die de app niet kent verdwijnen; redacteur ziet
   het niet in de editor.
3. **Kleurklassen**: app-CSS gebruikt `has-life-red-fb5-045-color` /
   `has-life-green-166-772-color`, WordPress genereert
   `has-life-red-fb-5045-color` / `has-life-green-166772-color`.
   Rood en groen als tekstkleur komen in de app waarschijnlijk niet door.
4. **Fragiele koppelingen**: de app is afhankelijk van exacte HTML (`<p>`,
   blokcommentaren); WP-updates breken dit (gebeurd in 2026, zie
   `inc/rest-events.php`, `inc/rest-blocks.php`).
5. **Footer en contactgegevens** staan in app-code, niet in WP.
6. **Verouderde stack** aan beide kanten; TEC-update naar 6.x staat open.

## 9. Open vragen voor de inventarisatie

- Welke pagina's/secties zijn het belangrijkst (bezoekcijfers)? ❓
- Wie zijn de redacteuren, hoe vaak publiceren ze, wat vinden ze lastig? ❓
- Moet de agenda ook terugkerende evenementen, aanmelden of iCal ondersteunen? ❓
- Is livestream/YouTube-integratie gewenst op de site? ❓
- Planning Center: gewenst (rooster, diensten) of afgevoerd? ❓
- Meertaligheid? ❓
- Moet de huisstijl precies zo blijven, of mag een herbouw die herzien? ❓
- Wie beheert het straks: één persoon of een team; welke hostingkosten zijn acceptabel? ❓
