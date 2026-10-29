# Accepttest for Salgsadministration – arbejdsgrundlag

Test må udføres på særskilt WordPress-staging med fiktive leads. Registrér udført dato, testbruger, forventet/faktisk resultat samt fejl i en testrapport.

| Test | Fremgangsmåde | Forventning |
|---|---|---|
| ACC-01 | Log ind som sælger A. Opret lead med alle obligatoriske felter, to produkter, opfølgning i morgen. | Lead gemmes og er synligt i egen pipeline. |
| ACC-02 | Opret lead som sælger B. Log ind som A og forsøg via liste, direkte `GET /leads/{id}`, `PUT /leads/{id}`, `/audit/{id}` og CSV. | B's lead kan ikke tilgås eller eksporteres fra A. |
| ACC-03 | Tildel landechef DK + kontoret DE/Hamborg. Opret leads i DK, DE/Hamborg, DE/Berlin, PL. | DK og DE/Hamborg synlige, DE/Berlin og PL skjult. |
| ACC-04 | Log ind som ledelse. Filtrér på land, kontor, ejer, sandsynlighed. | Globale data kan ses, men ikke redigeres. |
| ACC-05 | Opret aktivt lead med begrundet fortidig opfølgningsdato. | Det fremgår tydeligt som forfaldent. |
| ACC-06 | Vælg Vundet/Tabt uden dato eller årsag. Gentag med Andet uden note. | Lagring afvises af API og formular. |
| ACC-07 | Rediger ejer, værdi, sandsynlighed og status. Åbn audit. | Gammel/ny værdi samt bruger/tid registreres. |
| ACC-08 | Brug 3 testleads: EUR 100.000@20%, EUR 200.000@50%, EUR 300.000@0%. | Pipeline EUR 600.000, vægtet EUR 120.000, antal 3. |
| ACC-09 | Vælg alle 10 navngivne sprog og afklar sprog nr. 11. Gennemgå menu, formularer, validering og valg. | Intet mangler, ingen danske fallback-tekster på andre sprog. **Ikke opfyldt i beta.** |
| ACC-10 | Deaktiver bruger og afprøv nyt login/REST; log ind som leder. | Login afvises; leads og audit bevares. |

## Ekstra tests for beta.2

| Test | Fremgangsmåde | Forventning |
|---|---|---|
| EXT-01 | Som administrator: ændr årsagslister for vundet og tabt, og gem. | Alle nye valglister vises i formularen, og ugyldige årsager afvises også af API. |
| EXT-02 | Gennemfør flere afslutninger i indeværende år og åbn landeniveau. | Årets vundet/tabt og gennemsnit pr. forløben uge stemmer med testdata. |
| EXT-03 | Integrér pluginet via `[salgsadministration]` i et fremmed WordPress-tema. | Resten af WordPress-siden beholder sin egen CSS. |
| EXT-04 | Hent plugin-ZIP og kontrollér installation, aktivering og afinstallation på staging. | Plugin aktiveres uden PHP-fejl og efterlader ikke forsøg med virkelige kundeoplysninger. |
| EXT-05 | Vælg fanen Udvikling & kontrol i demoen og WordPress. | Forbedringer, mangler og ikke-godkendte tests står tydeligt i brugerfladen. |

Ekstra sikkerhedstests: omgå JavaScript-filtre, ændr `owner_id` i POST-body, falsk `country`/`office`, manglende nonce, CSV-formelinjekode, direkte REST-kald som uautoriseret rolle, WordPress-administrator under brugerstyring, ugyldigt email/VAT/link og stor datamængde.

**Automatiseret kodekontrol**: PHP- og JS-syntaks, Node-smoketests, ZIP-integritet og CSS-afgrænsning gennemføres i GitHub Actions. **Ingen test er påstået udført på WordPress-staging i denne levering.**