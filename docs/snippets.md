# Code Snippets op dev.lifesites.nl — inventaris en advies (7 november 2026)

Doel: alle maatwerk-PHP in git (het thema) krijgen, zodat de plugin
Code Snippets weg kan (zie security-review, bevinding 1).

> De code van #1–#6 kon tijdens deze review niet worden uitgelezen
> (Imunify360 blokkeerde de geautomatiseerde browser). De lijst hieronder
> komt uit het snippet-overzicht van eerder die dag. ❓ = nog te verifiëren.

| # | Naam | Bron | Actief | Advies |
|---|---|---|---|---|
| 1 | Example HTML shortcode | voorbeeld van de plugin | ❓ (standaard uit) | verwijderen |
| 2 | Example CSS snippet | voorbeeld van de plugin | ❓ (standaard uit) | verwijderen |
| 3 | Example JavaScript snippet | voorbeeld van de plugin | ❓ (standaard uit) | verwijderen |
| 4 | Order snippets by name | voorbeeld van de plugin (stuurt alleen de sortering in het snippet-scherm) | ❓ | verwijderen; vervalt met de plugin |
| 5 | Order snippets by date | idem | ❓ | verwijderen |
| 6 | **verhaal widget** — "Widget die een verhaal pagina verwijzing maakt: plaatje, titel, korte omschrijving en link" | eigen maatwerk (2020) | ❓ | zie hieronder |
| 7 | Gutenberg blocks in rest calls | eigen maatwerk | **uit** sinds 7-11-2026 | staat in thema: `inc/rest-blocks.php` → snippet verwijderen |
| 8 | Agenda: kale `<p>` in REST description | eigen maatwerk | **uit** sinds 7-11-2026 | staat in thema: `inc/rest-events.php` → snippet verwijderen |

## #6 "verhaal widget"

Een klassieke WordPress-widget (sidebar/widget-gebied) die naar een
verhaalpagina verwijst. Overwegingen:
- De Angular-app gebruikt **geen widgets**; widgets verschijnen alleen op de
  WP-voorkant (`dev.lifesites.nl`), en het thema heeft één widget-gebied
  (`sidebar-1`) dat in de templates nauwelijks wordt gebruikt.
- Als het snippet **uit** staat of de widget nergens geplaatst is
  (Weergave → Widgets): verwijderen.
- Als hij wel gebruikt wordt: de code als `inc/widget-verhaal.php` in het
  thema zetten (één `require` in `functions.php`), zoals bij #7/#8.

## Werkwijze om af te ronden (± 10 minuten)

1. WP-admin → Snippets: per snippet de code bekijken en de status noteren.
2. #1–#5 en #7–#8: verwijderen (Trash).
3. #6: gebruikt? → naar thema (zie boven), anders verwijderen.
4. Als de lijst leeg is: plugin Code Snippets deactiveren en verwijderen.
5. Na de volgende thema-deploy de REST-checks uit `docs/DEPLOY.md` draaien.

Kan het allemaal in het thema? **Ja.** Alle eigen snippets zijn gewone
`add_action`/`add_filter`/widget-code zonder afhankelijkheid van de plugin;
de voorbeeld-snippets zijn overbodig.
