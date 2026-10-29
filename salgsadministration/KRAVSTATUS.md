# Kravstatus – foreløbig udviklingsopgørelse (ikke acceptgodkendt)

**Statusnøgle:** Kode = implementeret i beta-kildekode, **ikke** testet i produktions-WordPress; Delvis = dele eller prototype; Mangler = ikke implementeret.

| ID | Prioritet | Status | Bemærkning |
|---|---|---|---|
| ADG-01 | SKAL | Kode | Personligt WP-login; ekstern SSO ikke implementeret |
| ADG-02 | SKAL | Kode | Sælger SQL-scope + objektkontrol; eksport begrænses tilsvarende |
| ADG-03 | SKAL | Kode | Tildelte lande og kontorer, kontrol på serveren |
| ADG-04 | SKAL | Kode | Ledelse fuld læseadgang, ingen redigering |
| ADG-05 | SKAL | Kode | Admin brugeroprettelse, tildeling og deaktivering |
| ADG-06 | SKAL | Delvis | Login success/fail, afvist API-adgang og ændringer i rettigheder; ikke en komplet log over alle HTTP-forespørgsler |
| ADG-07 | BØR | Mangler | Fælles login/SSO afhænger af ekstern identitetsløsning |
| ADG-08 | BØR | Kode | Overførsel med årsag, i revisionslog |
| ADG-09 | BØR | Kode | Aktivt dublet-VAT blokeres på serveren |
| AGD-10 | BØR | Kode | Aggregeret landepipeline til alle med adgang |
| DAT-01–17 | SKAL | Kode | Felter, datatyper og validering; datoer og systemmetadata; fritekstnoter overskrives ved redigering |
| FUN-01–08 | SKAL | Kode | CRUD uden sletning; reminderoversigt, udfald og revisionslog |
| FUN-09 | BØR | Kode | Overførsel kræver begrundelse |
| FUN-10 | BØR | Delvis | HTTPS-link til bilag; ikke filupload |
| RAP-01 | SKAL | Delvis | UI-filtre på alle dimensioner i pipeline og rapporter; periode er oprettelsesdato, og stort datavolumen kræver server-paginering |
| RAP-02–03 | SKAL | Kode | Aktive antal, EUR og værdi × sandsynlighed |
| RAP-04–05 | SKAL | Delvis | Geografi/udfald/konvertering findes; fuld sammenligning på sælger/kontor kræver udbygning |
| RAP-06 | BØR | Kode | CSV eksport, områdeafgrænset på serveren |
| RAP-07 | BØR | Delvis | Forfald og inaktivitet; leads uden dato afvises, hvis aktive |
| UI-01–02 | SKAL | Kode | Browser, responsiv formular og validering |
| UI-03–05 | SKAL | Delvis | 10 sprogvælgere; sekundære tekster mangler fuld oversættelse og verifikation, 11. sprog uafklaret |
| UI-06 | BØR | Kode | Fritekstnoter oversættes ikke automatisk |
| UI-07–08 | SKAL | Delvis | Dansk dato og EUR; responsiv mobil; ikke fuld lokaliserings-/tilgængelighedstest |
| Logning/GDPR | Driftskrav | Delvis | Audit findes; formaliseret retention, DPA og slettepolitik mangler |
| Backup | Driftskrav | Mangler | Løses i hosting; kræver dokumenteret gendannelsestest |
| CSV-import/IMP-0x | Migrering | Mangler | Importassistent og mapping skal specificeres med landecheferne |
| ACC-01–10 | Accept | Ikke godkendt | Skal gennemføres i faktisk WordPress med repræsentative testdata |

## Vigtige uklarheder

1. Kravteksten nævner 10 sprog, men ACC-09 henviser til 11. Hvilket yderligere sprog ønskes?
2. Skal landechefen have fuld adgang til alle leads i et land, også når der samtidig er en kontorafgrænsning? Beta bruger union af tildelte lande **eller** tildelte specifikke kontorer.
3. Skal globale statistikker på sælgernavne kunne vises til alle brugere, eller kun til landechefer og ledelse? Beta viser aggregerede antal og sælgernavne uden personlige leaddetaljer.
4. Hvilke tidsintervaller ønskes for ændringer og historik, og hvor længe må informationer gemmes?