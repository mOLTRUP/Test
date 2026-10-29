# Design- og kravgennemgang – Salgsadministration 1.2.0-beta

**Dato:** 29. oktober 2026  
**Grundlag:** *Kravspecifikation for salgsstyringssystem*, version 0.9.1 af 20. oktober 2026 (6 sider), samt GitHub-kildekode til WordPress-plugin og demo.  
**Status:** Design og kode er videreudviklet; **ingen acceptgodkendelse i rigtig WordPress-drift**.

## Væsentlige fund og rettelser

| Område / krav | Før gennemgang (1.1) | Implementeret i 1.2 |
|---|---|---|
| UI-01, UI-02, UI-08 / overskuelighed | Brede og høje KPI-kort, paneler stablet ned ad siden | Strammere afstande, mindre kort, kompakte tabeller og responsivt to-kolonnelayout |
| RAP-02, RAP-03 | Tal og tabeller viste pipeline og vægtet værdi | Horisontale, tilgængeligt mærkede søjler for pipeline/salgsfaser og vægtet land/kontor/sælger-værdi |
| RAP-04, RAP-05 | Tabellen over sælgere, kontorer, lande og produkter fyldte meget | Rapporten viser grafer og nøgletal først; detaljerede sammenligninger kan foldes ud |
| RAP-05 / udvikling | Historik blev ikke illustreret | Seks-måneders graf for *oprettede* leads og *afsluttede* vundne/tabte efter korrekt dato |
| FUN-04, FUN-05 / DAT-13, DAT-14 | Opfølgninger viste primært dato-sortering uden klokkeslæt i tabellen | Særskilt opdeling i **forsinkede**, **reminder aktiveret** og **senere** baseret på reminder-dage; HH:mm vises ved opfølgning |
| Brugerroller og kontorer | Rollefordelte startsider, men mange lange lister | Roller har nu fokuserede grafer: sælger/produkt, kontor/sælger, landechef/kontor, ledelse/land |
| Administrator | Lange hjælpetekster og stamdata-formular fyldte permanent | Forklaringer og stamdata kan foldes ud efter behov |
| AGD-10 / landepipeline | Primært tabelvisning | Ekstra aggregeret graf for landenes vægtede pipeline uden private leadoplysninger |

## Metode og databegrænsninger

- **Aktiv pipeline** omfatter status *Nyt lead, Kontaktet, Dialog, Tilbud, Forhandling*. *Vundet* og *Tabt* er afsluttede og indgår ikke i aktiv værdi.
- **Vægtet værdi** beregnes som forventet værdi i EUR × sandsynlighed / 100 (RAP-03).
- Sælger-, kontor-, landechef- og ledelsesgrafer for leadoplysninger opbygges af **den server-afgrænsede liste** som brugeren må se; status/valglister giver ikke nye læserettigheder.
- **Global landegraf** er kun baseret på aggregerede landetal (krav om fælles landepipeline) og viser ikke andre sælgeres personlige leads.
- **Produktgraf** tæller leads pr. produkt: ét lead med flere produkter kan tælle med flere steder. Produktgrafen er ikke en additiv fordeling af pipelineværdi.
- **Udviklingsgraf** bruger `created_at` for nye og `close_date` for vundet/tabt, således månederne ikke sammenblandes.
- **Konverteringsrate** beregnes som vundet / (vundet + tabt), altså afsluttede leads. Brugere skal kende dette grundlag.
- **Tidszoner:** Klokkeslæt vises i appen, og browseren afgør, om tiden er passeret. Der er endnu ikke aftalt fælles tidszone på tværs af lande; dette kræver beslutning og test, inden internationale påmindelser kan betragtes som helt færdige.
- Serverens `bootstrap` henter højst **2.000 leads** for den enkelte indloggede brugers liste. Dermed kan store datasæt medføre ufuldstændige grafer/rapporttabeller. **Dette er en kendt begrænsning og skal løses med server-side filtrering/paginering før bred drift**.

## Kravstatus (vigtigste)

| Krav | Status | Udestående |
|---|---|---|
| ADG-01–05 | Kode | Test roller og adgang i faktisk WordPress-miljø |
| ADG-06 | Delvis | Adgangs-/ændringslog findes, men fuld driftsmæssig logrevision mangler |
| DAT-01–17 | Kode | Godkend forretningsfelter, stamdata og ledelsesfiltre med slutbrugere |
| FUN-01–08 | Kode / delvis | Formelt testforløb; historik er ikke fuld separat kontaktaktivitetslog |
| RAP-01–05 | Kode / delvis | 2.000-leads-grænsen og performance; korrekt fuld historik/rapporter på staging |
| RAP-06–07 | Kode / delvis | CSV-eksport og remindervisning; systematisk test af store datamængder |
| UI-01–02, 07–08 | Kode / delvis | Reelle mobil-/tastaturtests og sprogspecifik kvalitetskontrol mangler |
| UI-03–05, ACC-09 | Delvis | 10 navngivne sprog i teksten, 11 i acceptkrav – manglende 11. sprog afklares; fuld oversættelse mangler |
| Import fra Excel/CSV | Mangler | Importguide, mapping, duplicate handling og IMP-0x specifikation |
| GDPR/backup | Afventer drift | Retention/sletning, hosting-aftale, backup/restore og databehandlerkrav |
| SSO, bilag, integrationer | Delvis / bør | WordPress-login fungerer; ekstern SSO og egentlig filupload er ikke implementeret |

## Næste prioriteter

1. Test med to sælgere, flere lande/kontorer, landechef, kontorbruger, ledelse og administrator på en **staging WordPress-installation**. Test ACC-01–10.
2. Indfør server-side pagination/filtrering og beregn alle ledelsesaggregater fra hele det autoriserede datagrundlag – ikke de første 2.000 leads.
3. Gennemfør mobil- og keyboardtest, inklusive tabeller og modaler; justér ved behov.
4. Oversæt og kvalitetssikr alle centrale skærmbilleder, formularbeskeder, statusser og valglister. Afklar 11. sprog.
5. Design CSV/Excel-import med forhåndsvisning, mapping, datavalidering, VAT-dubletter og korrekt ejer/lande-kobling.
6. Overvej separat, kronologisk kontakt-/notelog, så hver opfølgning er sin egen aktivitet.
7. Fastlæg backup, GDPR og slettelængde før produktion.

## Kontrolmetode

GitHub Actions kører PHP- og JavaScript-syntaks, demo-smoketest, isolerede rolle-/valideringstests og sammenligner distribueret plugin-ZIP med kilden. Det er **ikke** et bevis på produktionsklarhed eller pixelperfekt layout på alle enheder.
