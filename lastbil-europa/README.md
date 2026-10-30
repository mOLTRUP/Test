# Lastbil Europa – interaktivt regelsæt for tung vejgodstransport

**Researchversion 1.0, gennemgået 30. oktober 2026.**

Søgbar dansk prototype for lastbilkørsel i Europa: EU-fælles regler og et indledende udvalg af verificerede nationale regler om køretider, takografer, vejafgifter, kørselsforbud, adgang til markedet, cabotage, udstationering, ADR, særtransport, dokumenter og sikkerhed.

## Hjemmesiden

Åbn [index.html](./index.html) gennem et statisk webmiljø eller repositoryets GitHub Pages-adresse, hvis Pages er slået til. Projektsiden kan under normal GitHub Pages-opsætning ligge på `https://moltrup.github.io/Test/lastbil-europa/`.

- **Overblik:** kategorier, statistikker og nye kontrolpunkter.
- **Alle regler:** fri søgning, land, kategori og kritiske betingelser.
- **Europakort:** zoom til vejnavne med OpenStreetMap/Leaflet og søgning via Nominatim; kortet er ikke lastbilnavigation og viser ikke geografisk afgrænsede forbud.
- **Landeoversigt:** landekort viser antal *nationalt verificerede* opslag; grå streg betyder ikke gennemgået.
- **Rute-tjek:** brugeren vælger selv afgangs-, transit- og destinationslande; der beregnes ikke en rute.
- **Kilderegister:** kilder til hvert opslag åbnes direkte, og alt kan eksporteres til JSON/CSV.

## Datastruktur

| Fil | Funktion |
| --- | --- |
| `index.html` | Hele brugergrænsefladen; kortvisning bruger Leaflet fra CDN |
| `data.js` | Strukturerede registreringer, kilder, lande og kategorier, der indlæses af browseren |
| `regler.json` | Maskinlæsbar kopi af alle regler |
| `kilder.json` | Maskinlæsbar kildeoversigt med url, udgiver og kontroltidspunkt |
| `KILDER.md` | Læseligt kilderegister |
| `README.md` | Dokumentation og vedligeholdelsesprincipper |

## Hvad et opslag betyder

Et opslag er en **kort sammenfatning** af en regel, ikke den fulde lovtekst. `country=EU` betyder EU-regler. `country=EU+` viser EU/EØS/Schweiz-grundregler for køre-/hviletid og CMR i denne prototype; konkret retlig anvendelse kan afhænge af transportens art, destination og overenskomst. Øvrige lande har egen lande-id.

`checked` dokumenterer kun, at kilden blev gennemgået i forbindelse med opbygning 30. oktober 2026. Det er **ikke** en garanti for, at forholdene fortsat gælder på enhver senere køredato.

**Dækningsgrad:** 74 opslag / 65 kilder / 44 lande, heraf 24 lande med mindst ét specifikt nationalt opslag. Lande uden opslag kan stadig have mange restriktioner.

## Vigtige afgrænsninger

- Ikke komplet land-for-land-lovsamling.
- Ingen live kørselsforbud, ferie-/helligdagskalender, ruteopmåling, miljøzonekort, afgiftspriser eller ADR-klassifikation.
- Lokale vejskilte, begrænsninger, undtagelser, sæsontider, nationale cabotageregler, vejafgifter og grænseprocedurer kan ændre resultatet.
- Kørsler uden for EU/EØS/Schweiz kan være omfattet af AETR, særlige internationale aftaler og national ret.
- Oplysningerne må ikke bruges som eneste afgørelsesgrundlag for en konkret transport.

## Princip for videreudvikling

1. Opret ét faktabaseret opslag pr. tydelig regel med `title`, `summary`, `condition`, `topic`, `country`, `validFrom` og `refs`.
2. Enhver regel skal referere til mindst én identificerbar kilde, helst en myndighed eller original retsakt.
3. Tilføj kontroltidspunkt, gyldighedsområde, køretøjsklasse og kendte undtagelser.
4. Oprethold forskellen på **gældende**, **vedtaget fremtidig** og **planlagt/betinget** regulering i ordlyden.
5. Gennemgå altid eksisterende registre efter lovændringer; ændr ikke data ud fra antagelser.

Uafhængigt testprojekt i `mOLTRUP/Test/lastbil-europa/`, uden ændringer i øvrige projekter.

## Kildeopdatering 30. oktober 2026

Tilføjet R066–R071 (6 opslag) om britiske afgiftssatser, britiske takografundtagelser, digitale ECMT-tilladelser, polske undtagelser, bulgarske helligdagsforbud og ungarsk transitvejnet. Fire nye officielle kilder (UK06, UK07, PL03, HU03); eksisterende UK02 og UK03 er genbrugt. Uoplyst ikrafttrædelsesdato betyder, at præcis juridisk startdato ikke er fastslået.

### Opdatering 30. oktober 2026 – vintersæson og chaufføruddannelse
Tilføjet otte kildeunderbyggede opslag R075–R082 og syv officielle kilder AT03–AT04, NO02–NO03, SE02–SE03 og PL05. Omfatter vinterdæk og snekæder i Østrig og Norge, svenske vinterdæk- og vægtfordelingskrav samt polsk uddannelsesbekendtgørelse med ikrafttræden 3. november 2026. Regler med 2026/27-sæsonstart angiver start på den relevante sæson, ikke nødvendigvis den oprindelige lovs vedtagelsesdato. Kortet har endnu ikke verificerede vejsegment-geometrier for disse regler.

### Opdatering 30. oktober 2026 – Frankrig og Slovenien
Tilføjet fire opslag R083–R086 og fire officielle kilder FR03–FR04 og SI02–SI03. Frankrig: vinterudstyr i præfektudpegede bjergzoner 1. november–31. marts, med særskilt kædekrav til vogntog med påhæng. Slovenien: vinterudstyr 15. november–15. marts (og ved vinterføre uden for perioden), samt betinget kørselsforbud for vogntog, ADR og særtransporter på veje uden for motorveje og motortrafikveje ved vinterføre. De angivne 2026-datoer markerer sæsonstart, ikke oprindelig ikrafttrædelse. Kortet viser endnu ikke de præcise geografiske zoner eller live vejforhold. Kilder kontrolleret 30. oktober 2026.
