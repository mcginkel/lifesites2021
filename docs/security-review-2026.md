# Security review LifeSites (headless WordPress + Angular) — 7 november 2026

Scope: het concept (WordPress als headless backend voor de Angular-app) en de
WordPress-installatie op `dev.lifesites.nl` zoals die nu draait. Uitgevoerd
met lees-acties: REST-aanvragen, publieke URL's, wp-admin (ingelogd, alleen
bekijken) en de broncode van thema en app. Geen actieve aanvalstests.

> Tijdens de review blokkeerde Imunify360 na een reeks aanvragen het IP van de
> reviewer ("One moment, please…", ook voor wp-admin via automatisering).
> Punten die daardoor niet zijn geverifieerd staan gemarkeerd met ❓.

## Samenvatting

Het headless-concept is op zich veilig: de publieke site (lifesites.nl) is
statisch en heeft nette security-headers (CSP met nonces, X-Frame-Options,
nosniff). **Maar het voordeel van headless wordt niet benut**: de volledige
WordPress-installatie staat óók publiek als gewone website, met login,
plugins en beheer op dezelfde host. Het risico zit daarom vrijwel geheel in
WordPress zelf, en daar zijn de grootste open punten:

1. **Eén gekaapt beheerdersaccount = volledige server-overname**, via Code
   Snippets en de thema-bestandseditor.
2. **Verouderde en verlaten plugins**, plus inactieve plugins met een slechte
   beveiligingsgeschiedenis die nog op de server staan.
3. **Geen backups** van database of bestanden (geen backupplugin; hosting ❓).

## Bevindingen

Ernst: 🔴 hoog · 🟠 middel · 🟡 laag · ✅ in orde

### 🔴 1. Code-executie vanuit wp-admin
- **Code Snippets** (actief) en de **thema-bestandseditor** (Weergave →
  Thema-bestandseditor, werkt; `DISALLOW_FILE_EDIT` staat niet aan) laten elke
  beheerder willekeurige PHP uitvoeren op de server.
- De beheerdersnaam is publiek af te leiden: `/?author=1` → `/author/liferoot/`.
- Wat er dan gebeurt: wachtwoord geraden/gelekt of sessie gestolen →
  webshell → toegang tot de database en alle sites in het cPanel-account
  (`/home/lifesi1q/`).
- **Maatregelen**
  - `define( 'DISALLOW_FILE_EDIT', true );` in `wp-config.php`.
  - Snippets naar het thema verhuizen (zie `snippets.md`) en Code Snippets
    verwijderen. Daarmee staat al het maatwerk in git en is niets meer via de
    browser aan te passen.
  - 2FA voor alle beheerders (bv. plugin *Two Factor*), sterk uniek wachtwoord,
    zo min mogelijk accounts met rol Beheerder. ❓ aantal beheerders.
  - Auteursarchieven uitzetten of auteur-slug ongelijk aan gebruikersnaam.

### 🔴 2. Verouderde, verlaten en ongebruikte plugins
| Plugin | Status | Risico |
|---|---|---|
| The Events Calendar 5.16.4 | actief, 6.18.3.1 beschikbaar | 4 jaar achter; in 6.x zijn meerdere beveiligingsfixes uitgebracht |
| WP REST API Frontpage 1.1.0 | actief | al jaren niet onderhouden |
| WP-REST-API V2 Menus 0.12.1 | actief | klein, beperkt onderhouden |
| Iptanus File Upload 5.2.1 | **inactief, geïnstalleerd** | plugin met een geschiedenis van ernstige lekken; bestanden staan nog op de server en zijn direct aanroepbaar (readme gaf 200) |
| Modern Events Calendar Lite 6.4.5 | **inactief, geïnstalleerd** | idem, meerdere lekken in het verleden |
| Smash Balloon Instagram Feed, Akismet | inactief | onnodig |
- **Maatregelen**: inactieve plugins **verwijderen** (niet alleen
  deactiveren); TEC bijwerken (gepland, zie aparte stap); WP REST API
  Frontpage en V2 Menus vervangen door een paar regels in het thema
  (beide zijn eenvoudig na te bouwen met `register_rest_route`); automatische
  updates aanzetten voor plugins die niet aan de app-koppeling raken.

### 🔴 3. Geen backups
- Geen backupplugin actief; Core Rollback zet alleen WP-core terug.
- ❓ Of de hosting (cPanel / JetBackup) dagelijks backupt is niet vastgesteld.
- **Maatregel**: hostingbackup controleren en minstens één onafhankelijke
  backup (bv. UpdraftPlus naar externe opslag, of `wp db export` via cron).

### 🟠 4. WordPress staat dubbel publiek
- `dev.lifesites.nl` toont dezelfde content als gewone site (eigen thema),
  inclusief `wp-login.php`, `readme.html`, `license.txt`.
- Dat vergroot het aanvalsoppervlak en geeft dubbele content voor
  zoekmachines. ❓ indexering.
- **Maatregelen** (oplopend in effect):
  - `noindex` + canonical naar lifesites.nl op de WP-voorkant.
  - Voorkant voor niet-ingelogde bezoekers doorsturen naar lifesites.nl;
    alleen `/wp-json/*`, `/wp-admin/*`, `/wp-login.php` en uploads open laten.
  - wp-admin/wp-login beperken (IP-allowlist in .htaccess of Imunify, of
    alleen via VPN).
  - Hernoemen naar bv. `cms.lifesites.nl` om verwarring "dev = productie"
    weg te nemen (app-config aanpassen; dat is werk voor de app-beheerder).

### 🟠 5. Inhoud van met wachtwoord beveiligde pagina's lekte via REST — **opgelost op branch**
- Het eigen REST-veld `blocks` gaf `content.raw` terug, ook voor pagina's
  met een wachtwoord, terwijl WordPress `content.rendered` dan juist
  afschermt. Iedereen kon de inhoud lezen via `/wp-json/wp/v2/pages`.
- **Opgelost** in `inc/rest-blocks.php` (`post_password_required`), commit
  `9dfdb63` op branch `review-2026`. Nog niet gedeployd. Snippet #7 (de oude
  versie) staat uit. ❓ of er pagina's met een wachtwoord bestaan.

### 🟠 6. Onversleutelde FTP
- Transmit-favorieten voor de lifesites-hosting gebruiken **FTP** (niet SFTP/FTPS).
- De app-pipeline (Bitbucket, `atlassian/ftp-deploy`) deployt via FTP met
  wachtwoorden in pipeline-variabelen, op een `node:14`-image (end-of-life).
- **Maatregel**: SFTP of FTPS gebruiken; ongebruikte FTP-accounts in cPanel
  opruimen (o.a. de oude `verhuis@…`-accounts); app-pipeline moderniseren
  (app-beheerder).

### 🟡 7. Informatielekken en headers op dev.lifesites.nl
- `X-Powered-By: PHP/8.5.12`, `readme.html`, `license.txt` publiek.
- Geen HSTS, X-Frame-Options, X-Content-Type-Options, CSP op de WP-host
  (de app-host heeft ze wel).
- ❓ directory-listing van `/wp-content/uploads/` (niet te verifiëren door blokkade).
- ❓ `/wp-json/wp/v2/users` (gebruikersenumeratie via REST).
- **Maatregel**: headers via `.htaccess`; `expose_php = Off`; readme/license
  blokkeren; users-endpoint voor anonieme bezoekers dichtzetten (de app gebruikt
  het niet).

### 🟡 8. Planning Center-proxy `/api/`
- De app bevat een (ongebruikte) service naar `dev.lifesites.nl/api/services/…`.
- ❓ Wat er op `/api/` draait en of daar API-sleutels van Planning Center
  staan. Nagaan; verwijderen als het ongebruikt is.

### 🟡 9. Toegang tot code en accounts
- GitHub: push via fine-grained token, beperkt tot geselecteerde repo's. ✅
- Bitbucket (app): geen eigen toegang meer; ligt bij de app-beheerder.
- De WP-beheerder `liferoot` is een gedeeld/generiek account? ❓
  Persoonlijke accounts maken herleidbaarheid mogelijk.

### ✅ In orde
- WordPress 7.1.3 en PHP 8.5 actueel.
- `xmlrpc.php` uit (404); `.env`, `.git`, `debug.log` niet publiek.
- Login met reCAPTCHA; Imunify360 bot-bescherming actief (en effectief).
- REST: concepten en privé-inhoud niet anoniem zichtbaar; `blocks` niet meer in
  edit-context.
- App: statisch, CSP met nonces, alleen leesacties naar de API.

## Restrisico van het concept

Ook met alle maatregelen blijft het concept twee systemen die exact op elkaar
moeten passen: de app interpreteert ruwe blok-HTML met regexes. Elke
WordPress- of plugin-update kan de koppeling stilletjes breken (dat is in 2026
twee keer gebeurd). Dat is vooral een **beschikbaarheids**risico: het remt
updates af, en uitgestelde updates worden weer een beveiligingsrisico
(zie TEC 5.16.4). Bij een herbouw is een stabiel, expliciet contract
tussen CMS en frontend (vaste velden/blokken, of server-side rendering) de
belangrijkste veiligheidsmaatregel.

## Prioriteiten

| # | Actie | Wie | Moeite |
|---|---|---|---|
| 1 | Backup regelen en testen | Kees / hosting | laag |
| 2 | Inactieve plugins verwijderen | Kees | laag |
| 3 | `DISALLOW_FILE_EDIT`, 2FA, beheerders opschonen | Kees | laag |
| 4 | Thema 1.1.x met wachtwoordfix deployen | Kees | laag |
| 5 | Snippets naar thema, Code Snippets weg | Kees | middel |
| 6 | TEC naar 6.x (met backup, zie stappenplan) | Kees | middel |
| 7 | WP-voorkant afschermen / noindex, wp-admin beperken | Kees | middel |
| 8 | FTP → SFTP, oude FTP-accounts weg | Kees + app-beheerder | laag |
| 9 | Headers, users-endpoint, readme | Kees | laag |
