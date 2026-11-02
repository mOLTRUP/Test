#!/usr/bin/env python3
"""Opdater et kontrolleret, dateret befolkningssnapshot fra Verdensbanken.

Kilde: World Bank Indicators API v2, SP.POP.TOTL (CC BY 4.0).
Kun ISO-2-koder, som allerede findes i data/countries.json, medtages.
"""
from __future__ import annotations

import json
import math
from datetime import datetime, timezone
from pathlib import Path
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[1]
COUNTRIES = ROOT / "data" / "countries.json"
DESTINATION = ROOT / "data" / "population-worldbank.json"
URL = (
    "https://api.worldbank.org/v2/country/all/indicator/SP.POP.TOTL"
    "?format=json&mrnev=1&per_page=500"
)


def build_snapshot(payload: object, valid_codes: set[str], now: datetime) -> dict:
    if not (
        isinstance(payload, list)
        and len(payload) == 2
        and isinstance(payload[0], dict)
        and isinstance(payload[1], list)
    ):
        raise ValueError("Uventet svarformat fra Verdensbanken")
    metadata, observations = payload
    if int(metadata.get("pages", 1)) != 1:
        raise ValueError("Ufuldstændigt API-svar: flere sider end forventet")

    by_code: dict[str, tuple[int, str]] = {}
    for item in observations:
        if not isinstance(item, dict):
            continue
        country = item.get("country")
        code = country.get("id") if isinstance(country, dict) else None
        year = str(item.get("date", ""))
        value = item.get("value")
        if (
            code not in valid_codes
            or not (len(year) == 4 and year.isdigit())
            or not (1950 <= int(year) <= now.year)
            or isinstance(value, bool)
            or not isinstance(value, (int, float))
            or not math.isfinite(value)
            or not (value > 0 and float(value).is_integer())
        ):
            continue
        population = int(value)
        previous = by_code.get(code)
        if previous is None or year > previous[1]:
            by_code[code] = (population, year)

    if len(by_code) < 150:
        raise ValueError(
            f"Kun {len(by_code)} lande med gyldige tal; snapshot overskrives ikke"
        )

    return {
        "indicator": "SP.POP.TOTL",
        "source": "World Bank Indicators API v2",
        "sourceUpdated": metadata.get("lastupdated"),
        "retrievedAt": now.date().isoformat(),
        "rows": [
            [code, population, year]
            for code, (population, year) in sorted(by_code.items())
        ],
    }


def main() -> None:
    countries = json.loads(COUNTRIES.read_text(encoding="utf-8"))
    valid_codes = {
        item["cca2"]
        for item in countries
        if isinstance(item, dict)
        and isinstance(item.get("cca2"), str)
        and len(item["cca2"]) == 2
    }
    request = Request(URL, headers={"User-Agent": "VerdensLande/1.0 (public data snapshot)"})
    with urlopen(request, timeout=40) as response:
        payload = json.load(response)
    snapshot = build_snapshot(payload, valid_codes, datetime.now(timezone.utc))
    DESTINATION.write_text(
        json.dumps(snapshot, ensure_ascii=False, separators=(",", ":")) + "\n",
        encoding="utf-8",
    )
    print(f"Opdaterede {len(snapshot['rows'])} daterede befolkningstal i {DESTINATION}")


if __name__ == "__main__":
    main()
