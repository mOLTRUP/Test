# Datakilder for Verdens lande

## Lokal landeliste

Filen `data/countries.json` er en reduceret kopi af datasættet [mledoze/countries](https://github.com/mledoze/countries), hentet 28. oktober 2026 fra `dist/countries.json` (kilde-filens Git-blob: `6857077bbc236f61a392cfe8be34c9b8fe272c0a`).

Kildens ophavsmand er Mohammed Le Doze og bidragydere. Databasen er udgivet under **Open Database License 1.0 (ODbL-1.0)**: https://opendatacommons.org/licenses/odbl/1.0/ . Den reducerede database i dette repository stilles til rådighed under samme databaselicens. Kun felter, der er relevante for hjemmesiden, er bevaret (ISO-landekode, navne, hovedstad, region, sprog, valuta, areal, koordinater, flag og selvstændighedsstatus).

**Vigtigt:** Kilden dækker 250 lande og territorier efter ISO 3166-1, ikke kun suveræne stater. Nogle navne og geografiske oplysninger kan være forældede. Landedata er ikke officielle statistikker og indeholder ikke befolkningstal.

## Befolkningstal

For Danmark bruges 6.038.638 indbyggere for september 2026 fra [Danmarks Statistik](https://www.dst.dk/da/Statistik/emner/borgere/befolkning/befolkningstal), senest opdateret 9. oktober 2026.

For andre lande forsøger hjemmesiden at hente vejledende befolkningstal fra en ekstern kilde (REST Countries v3.1). Kilden angiver ikke et pålideligt referenceår for hvert land; hvis den er utilgængelig, vises **Ikke oplyst** frem for et opdigtet tal. Disse tal må ikke bruges som aktuelle officielle befolkningstal.

## Kort

Kort fra [OpenStreetMap](https://www.openstreetmap.org/copyright), © OpenStreetMap-bidragsydere.
