# Salgsadministration – WordPress + GitHub demo

**Version:** 1.0.0-beta.2 · 29. oktober 2026 · **Status: prototype / ikke produktionsgodkendt**

Udviklet ud fra `Kravspecifikation_salgsstyringssystem.pdf` v0.9.1 (20.10.2026).

## Oversigt

- `index.html` er en **offentlig statisk GitHub Pages-demo** med 16 **fiktive** leads. Dens rolle-vælger illustrerer forskellige visninger; den **giver ingen reel sikkerhed**. Ændringer lagres kun i den aktuelle browsers `localStorage`, som kan nulstilles.
- `downloads/salgsadministration-wordpress-plugin.zip` er en **installérbar ZIP-pakke**, bygget fra samme kildefiler som demoen. GitHub-kodekontrollen sammenholder pakkens indhold med kildekoden.
- `plugin/` er et **selvstændigt WordPress-plugin** med egne MySQL-tabeller, adgangskontrol i alle private REST-endpoints, adgangslog, revisionshistorik, CSV-eksport og en shortcode.
- Demo og plugin deler brugerflade (`plugin/assets/app.js` og `plugin/assets/app.css`). CSS er isoleret til pluginets app-element, så værtswebsidens generelle styling ikke ændres. Ingen reelle kundeoplysninger må lægges på GitHub Pages.

## Installér WordPress-versionen

1. Hent [plugin-ZIP](https://github.com/mOLTRUP/Test/raw/refs/heads/main/salgsadministration/downloads/salgsadministration-wordpress-plugin.zip), og upload ZIP-filen `salgsadministration-wordpress-plugin.zip` i **WordPress → Plugins → Tilføj plugin → Upload plugin**, og aktivér.
2. En WordPress-administrator kan åbne menupunktet **Salgsadministration** eller indsætte shortcoden `[salgsadministration]` på en privat side.
3. Åbn **Administration** inde i systemet, opret sælgere og landechefer, og tilknyt deres lande/kontorer. En oprettet bruger modtager en WordPress-meddelelse om at sætte sin adgangskode, hvis mailopsætningen fungerer.
4. Aktivér HTTPS, kontroller e-mail-opsætning, sikker backup og gendannelsestest, gennemgå oversættelser, og gennemfør alle accepttest før drift.

En anden WordPress-installation er **ikke** nødvendig. Der er **ingen automatisk synkronisering af brugere eller fælles login** til andre WP-installationer i denne beta. Dette kan tilføjes via en fremtidig, dokumenteret REST/SSO-integration med mapping af eksterne bruger-id'er.

## Arkitektur og sikkerhed

| Komponent | Funktion |
|---|---|
| WordPress-roller | `sa_seller`, `sa_manager`, `sa_leadership`, `sa_administrator` og eksisterende WP-administrator |
| SQL-tabeller | `{prefix}sa_leads` og `{prefix}sa_audit` |
| Brugerområder | User meta: `sa_countries`, `sa_offices` (`LAND|KONTOR`), `sa_lang`, `sa_disabled` |
| REST API | `/wp-json/salgsadmin/v1/...` med autentifikation, nonce og `permission_callback` |
| Leads | SQL-afgrænsning for sælger (owner) og landechef (land/kontor), kontrol også på objektniveau |
| Landestatistik | Aggregerede oplysninger til alle med adgang, ikke andre sælgeres personlige lead-rækker |
| Eksport | CSV, samme serverbaserede områdebegrænsning, logges |
| Revisionslog | Oprettelse samt ændringer af ejer, værdi, sandsynlighed, status og udfald med bruger/tid |
| Filer | HTTPS-links til eksterne bilag – ikke egentlig filupload |

**Vigtigt:** WordPress-roller kan ikke alene beskytte en statisk GitHub-side. Anvend derfor kun WordPress-pluginnet til rigtige data. Pluginets PHP er lintet; fuld integrationstest og sikkerhedsgennemgang på en WordPress-server mangler.

## Betingelser og afklaringer

- **Sprog:** Der nævnes **10 navngivne sprog**, mens ACC-09 siger **11**. De 10 valgmuligheder findes i demoen, men engelsk/dansk er mest gennemarbejdede. De øvrige mangler fuldstændig sproglig kvalitetssikring og oversættelse af en del sekundære tekster.
- **Euro:** beløb gemmes i EUR, vises i dansk format `1.000.000 €`; procent vælges i 10%-trin i formularen (API validerer 0-100).
- **Reminders:** opfølgningsliste på startside og særskilt opfølgningsvisning, ingen e-mails/kalender.
- **Udfald:** vundet/tabt kræver årsag og dato; `Andet` kræver note. Lukkede leads bevares.
- **CSV/Excel-import:** beskrevet som planlagt datamigrering i kravspecifikationen, ikke implementeret som importassistent.
- **GDPR:** persondata, retentionsperiode og databehandleraftale skal godkendes af driftsansvarlige; pluginet sletter ikke automatisk gamle leads.
- **Backup:** skal etableres og testes på den WordPress-server, der anvendes.
- **Skal/Bør:** se `KRAVSTATUS.md` for præcis status på delkrav.

## GitHub Pages

Demoens offentlige side ligger i undermappen `salgsadministration/index.html`, uden at ændre det eksisterende `index.html` i repositoryets rod. Hvis GitHub Pages publicerer `main`-branchens rod, vil URL normalt være:

`https://moltrup.github.io/Test/salgsadministration/`

Bemærk, at GitHub Pages skal være aktiveret i **Settings → Pages → Deploy from a branch → main → /(root)**. URL'en er først live, når GitHub har publiceret den.

## Nye forbedringer i beta.2 (29.10.2026)

- Administrator kan redigere separate **årsagslister for vundet og tabt**, og serveren afviser årsager uden for den relevante liste.
- Landepipeline viser **vundne og tabte i indeværende år**, nyoprettede leads de seneste 7 dage og **ugentlige gennemsnit** for vundet/tabt pr. sælger.
- En ny synlig fane **Udvikling & kontrol** viser tilføjelser, åbne punkter, status for krav og test på både demo og WordPress.
- Pluginets CSS er afgrænset til `#sa-app`, så den kan indlejres i et andet WordPress-tema uden globale stilændringer.
- GitHub Actions tester PHP, JavaScript, demo-flow, ZIP-integritet og CSS-afgrænsning ved ændringer.

## Tests

- `php -l plugin/salgsadministration.php`
- `node --check plugin/assets/app.js`
- `node tests/smoke-node.js` (fiktive data, egen-leads-afgrænsning, landetal, oprettelsesdialog, udviklingsstatus og årsagslister)
- Accepttest ACC-01...ACC-10: se `TESTPLAN.md`.
- En afprøvning i rigtig WordPress med mindst 2 sælgere, 2 lande og 1 landechef er **nødvendig** før godkendelse.

Den automatiske kontrol køres i [GitHub Actions](https://github.com/mOLTRUP/Test/actions/workflows/salgsadministration-ci.yml). Dette er **kodekontrol**, ikke fuld WordPress-accepttest.

## Forslag til næste version

1. **Samlet WP-login/SSO** med sikker identitetsmapping og automatisk profilopdatering fra organisationsdata.
2. **Forløb pr. kontakt** med selvstændig notelog i stedet for ét overskrevet notefelt.
3. **Importguide til Excel/CSV** med forhåndsvisning, mapping, dry-run og dubletafklaring.
4. **E-mail- og kalenderpåmindelser**, hvis samtykke, databeskyttelse og infrastruktur er afklaret.
5. **Sprogrevision og test** af samtlige oversættelser, tilgængelighed og tastaturnavigation.
6. **Rapporteksport og performance** med serverbaseret pagination, særskilt tabel for aktiviteter og integrationstest.

Ingen af disse punkter er nødvendige for at se den visuelle demo, men især korrekt sprog, backups, retention og SSO-afklaring skal håndteres, før en produktionsidriftsættelse.