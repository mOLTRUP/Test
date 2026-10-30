# ITD Salgsadministration – designudspil 30.10.2026

**Status: klikbar designprototype med fiktive data, ikke et produktionsklart WordPress-system.**

## Åbn

- [Designprototype: Chef, Leder og Sælger](./index.html)
- [Eksisterende WordPress-baseret salgsadministration, version 1.3 beta](../index.html)
- [Eksisterende kravstatus](../KRAVSTATUS.md)
- [Eksisterende kode og installation](../README.md)

GitHub Pages, hvis aktiveret for `main` og `/(root)`: https://moltrup.github.io/Test/salgsadministration/design-udspil-2026-10-30/

## Hvad demonstrerer dette udspil?

- **Chef:** internationalt overblik, omsætnings- og budget-KPI'er, omkostninger, landesammenligning og muligheder for at identificere risici.
- **Leder:** team-/landepipeline, mål og opfølgninger, indsatsområder og medarbejderperformance.
- **Sælger:** dagens prioriterede opfølgninger, egne leads, næste handling og mål på eget salgsarbejde.
- Klikbare visninger, grafer, filtre og formulareksempler er **design** og benytter fiktive tal; rollevalget på en offentlig statisk side giver **ingen servermæssig adgangsbeskyttelse**.

## Forhold til den gældende kravspecifikation

Grundlag: **Kravspecifikation_salgsstyringssystem.pdf v0.9.1 af 20.10.2026**. Målet for WordPress version 1.0 er både samtlige SKAL- og BØR-krav: adgangsstyring, alle leadfelter, opfølgninger, historik, pipeline, roller, rapporter, eksport, sprog, logning, mobil, datamigrering og sikker drift.

**Bemærk:** Den eksisterende 1.3-beta har fortsat åbne krav, se `../KRAVSTATUS.md`: bl.a. fuld flersprogethed (10 navngivne sprog men 11 i acceptkriteriet), fælles SSO, Excel/CSV-import, bilagsupload, WP-accepttest og driftsbackup/GDPR. Der må ikke annonceres produktionsklarhed, før acceptkriterierne ACC-01–10 er testet.

## Supplerende økonomimodul (nyt scope)

Dette designudspil foreslår en separat budget-/økonomimodel med:

1. Budget pr. år og måned, land, kontor, evt. sælger og produkt.
2. Realiseret omsætning fra kvalitetssikrede økonomidata (først Excel/CSV, senere ERP/API).
3. Sidste års omsætning på samme dimensionsniveau og periode.
4. Omkostninger og dækningsbidrag som særskilte dataserier.
5. KPI'er: budgetafvigelse, vækst mod sidste år, pipeline-dækning, konverteringsrate og salgsomkostning pr. indtægtsenhed.
6. Beregningslogik med entydig valuta-/periodedefinition og datakilde. **Lead-værdi må ikke automatisk betragtes som faktisk omsætning.**

Dette **økonomimodul er endnu ikke implementeret** i WordPress-betaen. Grafer og værdier i designprototypen er illustrative.

## Plan for integration til eksisterende WP-projekt

1. Godkend Chef-, Leder- og Sælger-layoutet.
2. Tilpas eksisterende WordPress-roller og SQL-/REST-adgang, aldrig kun frontend-filtre.
3. Udbyg datamodel med budget, realiserede salg, sidste år og omkostningsposter samt sikre import- og valideringsflows.
4. Udbyg de relevante rolleforsider i `../plugin/assets/app.js` og `../plugin/assets/app.css`.
5. Tilføj enhedstests og integrationstests, herunder test af begrænset dataadgang og økonomiberegninger.
6. Først efter godkendelse og databeskyttelses-/sikkerhedsgennemgang kan data fra virkelige kunder anvendes.

Denne prototype er placeret i en **ny undermappe** og ændrer ikke eksisterende version 1.3 af WordPress-koden.

## Revideret 30.10.2026 – brugerens ti rettelser

**Personas i designet:** Sælger, Landechef og Ledelse. Den tekniske WordPress-administrator holdes adskilt fra disse tre arbejdsflader.

| Rettelse | Status i denne klikbare designprototype | Status i WordPress 1.3-beta |
|---|---|---|
| Sælger: landeoversigt med antal og status | Visuel landepipeline med aggregerede data | Aggregater findes allerede |
| Sælger: landepipeline | Ny separat menu »Landepipeline« | Eksisterende landepipeline |
| Filer og links pr. lead | Filvælger og HTTPS-link; **filnavne i browserhukommelse**, ikke serverupload | HTTPS-links; ingen binær filupload |
| Opret/vælg kontor og kontakttelefon | Nyt redigerbart kontorfelt og kontakttelefon | Backend-felter findes; kontor-stamdata styres af admin |
| Smartere pipeline med seks faser | Nyt Kanban-board inkl. Resultat; desktop drag-and-drop | Eksisterende pipeline er ikke ændret |
| Fasenote | Egen note pr. fase i demonstrationssessionen | Kræver datamodel og API-udvidelse |
| Sortering | Næste opfølgning, sandsynlighed og forventet værdi | Funktionalitet skal verificeres i WP |
| I dag og kommende | Separate lister fra demoens faktiske opfølgningsdatoer | Opfølgningsoversigt findes |
| Computer og mobil | Responsivt layout; mobil fasevælger og vandret faseoversigt | Eksisterende responsive WP-visning |
| To produkter + afslutningskrav | To forskellige produkter på formular; Vundet/Tabt kræver årsag og dato | Flere produkter og afslutningsvalidering findes |

**Vigtigt:** GitHub Pages er kun udviklings- og designmiljø; det håndterer ikke autentificerede kundeoplysninger. Designprototypen gemmer ikke til WordPress. Godkendte layoutændringer skal implementeres særskilt i `../plugin/` og godkendes med ACC-01–10, inden reel anvendelse.

Browserens regressionskontrol ligger i `../tests/design-udspil-playwright.cjs` og bliver kørt af GitHub Actions' visuelle workflow.
