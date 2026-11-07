# Styling: WP-thema tegenover de Angular-app (7 november 2026)

Vraag: kan het thema beter op de app lijken, en is dat de moeite waard?

## Waar het thema voor dient

De bezoeker ziet alleen de app (lifesites.nl). Het thema bepaalt twee dingen:
1. de **WP-voorkant** op dev.lifesites.nl (eigenlijk niet bedoeld voor
   bezoekers, zie security-review punt 4);
2. hoe de **blok-editor** eruitziet voor redacteuren.

Punt 2 is waar betere styling echt iets oplevert: een redacteur ziet dan
tijdens het schrijven ongeveer wat de bezoeker straks ziet. Punt 1 verdient
geen investering; die voorkant kan beter worden afgeschermd.

## Verschillen (gemeten in de code)

| Aspect | App (`life-frontend/src/global.scss`) | Thema (`style.css`) | Editor (vóór) |
|---|---|---|---|
| Lettertype | DM Sans 400/500/700 (zelf gehost) | DM Sans 400/700 (Google) + system-fallbacks eerst | WP-standaard |
| Contentbreedte | 1020px + 30px zijmarge | Underscores-standaard | WP-standaard |
| Koppen | h1 2rem, h2 1.125rem, h3 1.25rem, 600 | Underscores | WP-standaard |
| Links | erven kleur, vet, geen onderstreping | rood/standaard | WP-standaard |
| Quote/pullquote | rood, 1.5rem, max 800px | anders | WP-standaard |
| Template geel | **witte** tekst | zwarte tekst | geen kleur |
| Template rood/groen | wit op kleur | wit op kleur | geen kleur |
| Kleurklassen rood/groen | `fb5-045`, `166-772` (**matcht WP niet**) | `fb-5045`, `166772` (correct) | — |

## Wat er nu gedaan is (branch `review-2026`, niet gedeployd)

- `editor-style.css`: de regels van de app (font, 1020px kolom, 30px marges,
  koppen, links, scheidingslijn, quotes, paletklassen) voor het editor-canvas.
  WordPress past dit alleen toe binnen de editor.
- `js/editor-template-class.js`: kleurt het canvas rood/groen/geel zodra de
  redacteur een paginatemplate kiest, net als de app.
- DM Sans (met 500/600) ook in de editor geladen.

Nog niet getest in de live editor; eerst testen na deploy (of op een kopie).

## Wat het nog beter maakt (aanbevolen volgorde)

1. **Toegestane blokken beperken** tot wat de app kan tonen
   (`allowed_block_types_all`): paragraaf, kop, lijst, afbeelding, cover,
   kolommen, groep, quote, pullquote, scheidingslijn, carousel. Dan kan een
   redacteur niets meer maken dat in de app stil verdwijnt. *Grootste winst
   voor redacteuren.* Vergt een beslissing (welke blokken), daarom niet
   automatisch gedaan.
2. **Previewknop naar de app**: "Bekijk" laten wijzen naar
   `https://lifesites.nl/<slug>` in plaats van de WP-voorkant (kan met een
   filter op `preview_post_link`; werkt alleen voor gepubliceerde pagina's,
   concepten kan de app niet lezen).
3. **Kleurklassen rechtzetten in de app** (`fb5-045` → `fb-5045`,
   `166-772` → `166772`): klein, maar app-werk (Martijn).
4. **Geel**: kies zwarte tekst op geel in de app (contrast 13:1 i.p.v. 1.4:1).
5. Op termijn `theme.json` gebruiken voor palet, lettertype en breedtes;
   dat vergt nieuwe paletslugs (zonder `#`) en dus migratie van bestaande
   content — alleen zinvol bij een herbouw.

## Conclusie

Ja, beter te krijgen, en het is de moeite waard **voor de editor**, niet
voor de WP-voorkant. De eerste stap (editor-stijl) staat klaar; de grootste
volgende winst is het beperken van blokken tot wat de app ondersteunt.
