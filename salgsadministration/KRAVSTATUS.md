# Kravstatus – version 1.1.0-beta (ikke acceptgodkendt)

**Statusnøgle:** Kode = implementeret i beta-kildekode, **ikke** testet i produktions-WordPress; Delvis = dele eller prototype; Mangler = ikke implementeret.

| ID | Prioritet | Status | Bemærkning |
|---|---|---|---|
| ADG-01 | SKAL | Kode | Personligt WP-login; ekstern SSO ikke implementeret |
| ADG-02 | SKAL | Kode | Sælger SQL-scope + objektkontrol; eksport begrænses tilsvarende |
| ADG-03 | SKAL | Kode | Landechef: tildelte lande/kontorer. Ny kontorrolle: read-only og kun tildelte kontorer; SQL- og objektkontrol |
| ADG-04 | SKAL | Kode | Ledelse fuld læseadgang, ingen redigering |
| ADG-05 | SKAL | Kode | Admin brugeroprettelse, tildeling og deaktivering |
| ADG-06 | SKAL | Delvis | Login success/fail, afvist API-adgang og ændringer i rettigheder; ikke en komplet log over alle HTTP-forespørgsler |
| ADG-07 | BØR | Mangler | Fælles login/SSO afhænger af ekstern identitetsløsning |
| ADG-08 | BØR | Kode | Overførsel med årsag, i revisionslog |
| ADG-09 | BØR | Kode | Aktivt dublet-VAT blokeres på serveren |
| AGD-10 | BØR | Kode | Aggregeret landepipeline til alle med adgang |
| DAT-01–17 | SKAL | Kode | Felter, validering, datoer og metadata; årsager for vundet/tabt vedligeholdes som stamdata. Fritekstnoter overskrives ved redigering |
| FUN-01–08 | SKAL | Kode | CRUD uden sletning; reminderoversigt, udfald og revisionslog |
| FUN-09 | BØR | Kode | Overførsel kræver begrundelse |
| FUN-10 | BØR | Delvis | HTTPS-link til bilag; ikke filupload |
| RAP-01 | SKAL | Delvis | UI-filtre på alle dimensioner i pipeline og rapporter; periode er oprettelsesdato, og stort datavolumen kræver server-paginering |
| RAP-02–03 | SKAL | Kode | Aktive antal, EUR og værdi × sandsynlighed |
| RAP-04–05 | SKAL | Kode | Rapporttabeller pr. sælger, kontor, land og produkt; vundet/tabt i år og ugentlige gennemsnit pr. sælger; validering på WP-staging udestår |
| RAP-06 | BØR | Kode | CSV eksport, områdeafgrænset på serveren |
| RAP-07 | BØR | Delvis | Forfald og inaktivitet; leads uden dato afvises, hvis aktive |
| UI-01–02 | SKAL | Kode | Browser, responsiv formular, validering, rolleopdelte forsider og forklaring af salgsfaser |
| UI-03–05 | SKAL | Delvis | 10 sprogvælgere; sekundære tekster mangler fuld oversættelse og verifikation, 11. sprog uafklaret |
| UI-06 | BØR | Kode | Fritekstnoter oversættes ikke automatisk |
| UI-07–08 | SKAL | Delvis | Dansk dato og EUR; responsiv mobil; ikke fuld lokaliserings-/tilgængelighedstest |
| Logning/GDPR | Driftskrav | Delvis | Audit findes; formaliseret retention, DPA og slettepolitik mangler |
| Backup | Driftskrav | Mangler | Løses i hosting; kræver dokumenteret gendannelsestest |
| CSV-import/IMP-0x | Migrering | Mangler | Importassistent og mapping skal specificeres med landecheferne |
| ACC-01–10 | Accept | Ikke godkendt | Skal gennemføres i faktisk WordPress med repræsentative testdata |

## Ny udvikling 1.1

- Separat personlig sælgerforside, kontorforside, landechef- og ledelsesforside samt administratorkontrolpanel.
- Office-rollen \`sa_office\` har serverafgrænset **læseadgang** til tildelte kontorer, ingen redigering.
- Kontorvisning med egne leads, pipeline og opfølgning samt klikbare kontorvalg.
- Introduktion til felter og salgsfaser med særlig forklaring på **Tilbud**, **Forhandling**, **Vundet** og **Tabt** i både hjælpeside og leadformular.
- Udviklingskontrol tester visning for flere roller samt kontor-adgang via isolerede PHP-tests, men produktions-WordPress er endnu ikke accepttestet.

## Udført udviklingskontrol

- PHP-syntaks og JavaScript-syntaks kontrolleres af GitHub Actions ved ændringer.
- Node-smoketest af visuel demo omfatter egne leads, landeoversigt, formular, udviklingsstatus og årsagslister.
- Den installerbare plugin-ZIP testes som ZIP og sammenholdes byte-for-byte med PHP/JS/CSS-kilde.
- CSS er scoped, så pluginet ikke globalt formaterer en anden WordPress-hjemmeside.
- **Ingen fuld WordPress-integrationstest er gennemført**, og ACC-01–10 er derfor ikke godkendt.

## Vigtige uklarheder

1. Kravteksten nævner 10 sprog, men ACC-09 henviser til 11. Hvilket yderligere sprog ønskes?
2. Skal landechefen have fuld adgang til alle leads i et land, også når der samtidig er en kontorafgrænsning? Beta bruger union af tildelte lande **eller** tildelte specifikke kontorer.
3. Skal globale statistikker på sælgernavne kunne vises til alle brugere, eller kun til landechefer og ledelse? Beta viser aggregerede antal og sælgernavne uden personlige leaddetaljer.
4. Hvilke tidsintervaller ønskes for ændringer og historik, og hvor længe må informationer gemmes?