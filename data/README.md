# Datakilder for Verdens lande

## Lokal landeliste

Filen `data/countries.json` er en reduceret kopi af datasættet [mledoze/countries](https://github.com/mledoze/countries), hentet 28. oktober 2026 fra `dist/countries.json` (kilde-filens Git-blob: `6857077bbc236f61a392cfe8be34c9b8fe272c0a`).

Kildens ophavsmand er Mohammed Le Doze og bidragydere. Databasen er udgivet under **Open Database License 1.0 (ODbL-1.0)**: https://opendatacommons.org/licenses/odbl/1.0/ . Den reducerede database i dette repository stilles til rådighed under samme databaselicens. Kun felter, der er relevante for hjemmesiden, er bevaret (ISO-landekode, navne, hovedstad, region, sprog, valuta, areal, koordinater, flag og selvstændighedsstatus).

**Vigtigt:** Kilden dækker 250 lande og territorier efter ISO 3166-1, ikke kun suveræne stater. Nogle navne og geografiske oplysninger kan være forældede. Landedata er ikke officielle statistikker og indeholder ikke befolkningstal.

## Befolkningstal

For Danmark bruges 6.038.638 indbyggere for september 2026 fra [Danmarks Statistik](https://www.dst.dk/da/Statistik/emner/borgere/befolkning/befolkningstal), senest opdateret 9. oktober 2026.

For andre lande forsøger hjemmesiden først at hente **seneste tilgængelige ikke-tomme årsopgørelse** fra [Verdensbankens indikator SP.POP.TOTL](https://data.worldbank.org/indicator/SP.POP.TOTL) via [Indicators API v2](https://datahelpdesk.worldbank.org/knowledgebase/articles/898581-api-basic-call-structures). API'et returnerer både indbyggertal og referenceår, som vises på landets fane **Indbyggere**. Verdensbankens data er udgivet under CC BY 4.0 og stammer blandt andet fra FN og nationale statistikmyndigheder. Daterede årsopgørelser er ikke live-tal. Danmark bevarer den nyere nationale månedsopgørelse.

Når et gyldigt sæt daterede Verdensbankstal er hentet, gemmer hjemmesiden en lille kopi i den besøgendes egen browser (`localStorage`) i op til **180 dage**. Ved senere besøg vises denne kopi med det samme, mens hjemmesiden forsøger at hente nyere tal. Hvis API'et midlertidigt svigter, kan den lokale kopi fortsat bruges. Cachen er **ikke** en fælles GitHub-database og findes kun for besøgende, som tidligere har indlæst Verdensbankstal. Kopien gemmer landekode, indbyggertal, referenceår og hentetidspunkt; Danmarks nyere officielle tal overskrives ikke. Når der ikke findes en brugbar cache, fortsætter siden som hidtil.

Hvis Verdensbanken er utilgængelig eller mangler et land/område, forsøger siden fortsat at hente et vejledende befolkningstal fra REST Countries v3.1. **Disse reserve-tal har ikke dokumenteret referenceår og mærkes tydeligt som sådan**. Hvis begge kilder mangler tal, vises **Ikke oplyst**. Hjemmesiden må ikke opfinde indbyggertal.

## Fælles befolkningssnapshot (forslag)

Filen `data/population-worldbank.json` oprettes og opdateres af `scripts/update_population.py` via GitHub Actions-workflowet `.github/workflows/update-population.yml` (mandage kl. 05:15 UTC eller manuelt). Der kræves ingen API-nøgle. Den første fil findes først, når workflowet har kørt med succes.

Scriptet henter de seneste **ikke-tomme** årsopgørelser fra Verdensbankens indikator `SP.POP.TOTL`. Kun gyldige landekoder fra den eksisterende lokale landeliste og positive heltal med et referenceår medtages. Hvis færre end 150 lande kan valideres, eller API-svaret er ufuldstændigt, afbrydes kørslen uden at overskrive eksisterende data. JSON-filen indeholder kilde, kildens opdateringsdato, hentningsdato og landekode, tal og referenceår for hvert land. Verdensbankens indikator er licenseret under CC BY 4.0.

Hjemmesiden indlæser dette fælles snapshot efter browserens cache og inden det aktuelle API-kald. Hvis filen endnu ikke findes eller ikke kan læses, bruges den eksisterende live-API og reserveløsning uændret. **Danmarks officielle månedsopgørelse overskrives aldrig.** Snapshot er en cache af officielle årsdata, ikke en live-folketælling. Det opdateres kun automatisk, hvis GitHub Actions er aktiveret, og workflowet har rettighed til at skrive til repository.

## Kort

Kort fra [OpenStreetMap](https://www.openstreetmap.org/copyright), © OpenStreetMap-bidragsydere.
