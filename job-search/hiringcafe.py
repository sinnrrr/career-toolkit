#!/usr/bin/env python3
"""Parse a hiring.cafe search URL into JSON, full job descriptions included.

Usage:
  python3 hiringcafe.py "<hiring.cafe url>" [-o out.json] [--slim] [--no-desc]

No args -> uses the baked-in default URL. --slim keeps only the useful fields.
--no-desc skips the per-job description fetch (faster, list data only).
"""
import sys, json, argparse, urllib.parse, urllib.request, re, html, time
from concurrent.futures import ThreadPoolExecutor

UA = {"User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
      "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0 Safari/537.36"}

DEFAULT_URL = ("https://hiring.cafe/?searchState=%7B%22locations%22%3A%5B%7B%22id%22%3A%22bxc1yZQBoEtHp_8UIwX7%22%2C%22types%22%3A%5B%22locality%22%5D%2C%22address_components%22%3A%5B%7B%22long_name%22%3A%22Vancouver%22%2C%22short_name%22%3A%22Vancouver%22%2C%22types%22%3A%5B%22locality%22%5D%7D%2C%7B%22long_name%22%3A%22British+Columbia%22%2C%22short_name%22%3A%2202%22%2C%22types%22%3A%5B%22administrative_area_level_1%22%5D%7D%2C%7B%22long_name%22%3A%22Canada%22%2C%22short_name%22%3A%22CA%22%2C%22types%22%3A%5B%22country%22%5D%7D%5D%2C%22geometry%22%3A%7B%22location%22%3A%7B%22lat%22%3A49.24966%2C%22lon%22%3A-123.11934%7D%7D%2C%22formatted_address%22%3A%22Vancouver%2C+British+Columbia%2C+CA%22%2C%22population%22%3A600000%2C%22workplace_types%22%3A%5B%22Remote%22%2C%22Hybrid%22%2C%22Onsite%22%2C%22Field%22%5D%2C%22options%22%3A%7B%22radius%22%3A50%2C%22radius_unit%22%3A%22miles%22%2C%22ignore_radius%22%3Afalse%7D%7D%2C%7B%22types%22%3A%5B%22continent%22%5D%2C%22formatted_address%22%3A%22North+America%22%2C%22address_components%22%3A%5B%5D%2C%22workplace_types%22%3A%5B%22Remote%22%5D%2C%22options%22%3A%7B%7D%2C%22id%22%3A%22North+Americacontinent%22%7D%5D%2C%22searchQuery%22%3A%22engineer%22%2C%22dateFetchedPastNDays%22%3A4%2C%22departments%22%3A%5B%22Engineering%22%2C%22Software+Development%22%5D%2C%22roleYoeRange%22%3A%5B6%2C11%5D%2C%22calcFrequency%22%3A%22Hourly%22%2C%22maxCompensationLowEnd%22%3A%22150%22%2C%22jobTitleQuery%22%3A%22%28%5C%22AI%5C%22+OR+%5C%22ML%5C%22+OR+%5C%22LLM%5C%22+OR+%5C%22Software%5C%22+OR+%5C%22DevOps%5C%22+OR+%5C%22Infra%5C%22+OR+%5C%22Platform%5C%22+OR+%5C%22Agent%5C%22%29%22%7D")


def search_state(url):
    qs = urllib.parse.urlparse(url).query
    decoded = urllib.parse.parse_qs(qs)["searchState"][0]  # parse_qs url-decodes
    return urllib.parse.quote(decoded)  # re-encode for the request


def build_id():
    html = urllib.request.urlopen(
        urllib.request.Request("https://hiring.cafe/", headers=UA)).read().decode()
    return re.search(r'"buildId":"([^"]+)"', html).group(1)


def fetch(bid, ss_enc, page):
    url = (f"https://hiring.cafe/_next/data/{bid}/index.json"
           f"?searchState={ss_enc}&page={page}")
    req = urllib.request.Request(url, headers=UA)
    return json.load(urllib.request.urlopen(req))["pageProps"]


def fetch_description(object_id):
    url = "https://hiring.cafe/api/job-description?id=" + urllib.parse.quote(object_id, safe="")
    for attempt in range(5):  # endpoint rate-limits (429); back off and retry
        try:
            req = urllib.request.Request(url, headers=UA)
            job = json.load(urllib.request.urlopen(req)).get("job") or {}
            return (job.get("job_information") or {}).get("description")
        except urllib.error.HTTPError as e:
            if e.code == 429 and attempt < 4:
                time.sleep(1.5 * (attempt + 1))
                continue
            print(f"  desc fail {object_id[:40]}: {e}", file=sys.stderr)
            return None
        except Exception as e:  # a dead listing shouldn't kill the whole run
            print(f"  desc fail {object_id[:40]}: {e}", file=sys.stderr)
            return None


def hydrate(hits):
    ids = [h["objectID"] for h in hits]
    with ThreadPoolExecutor(max_workers=4) as ex:  # ponytail: 4 stays under the rate limit
        descs = list(ex.map(fetch_description, ids))
    got = 0
    for h, d in zip(hits, descs):
        if d:
            h.setdefault("job_information", {})["description"] = d
            got += 1
    print(f"descriptions: {got}/{len(hits)}", file=sys.stderr)
    return hits


def strip_html(s):
    if not s:
        return None
    return html.unescape(re.sub(r"<[^>]+>", " ", s)).strip()  # ponytail: naive, fine for text


def scrape(url):
    ss_enc = search_state(url)
    bid = build_id()
    hits, seen, page = [], set(), 0
    while True:
        pp = fetch(bid, ss_enc, page)
        total = pp["ssrTotalCount"]  # raw pre-collapse count hiring.cafe shows
        added = 0
        for h in pp["ssrHits"]:  # collapse_key merges the same posting across locations
            if h["collapse_key"] in seen:
                continue
            seen.add(h["collapse_key"])
            hits.append(h)
            added += 1
        print(f"page {page}: +{added} unique ({len(hits)} of ~{total})", file=sys.stderr)
        if pp["ssrIsLastPage"] or not pp["ssrHits"] or len(hits) >= total:
            break
        page += 1
    return hits, total


def slim(h):
    v = h.get("v5_processed_job_data", {}) or {}
    c = h.get("enriched_company_data", {}) or {}
    return {
        "id": h.get("id"),
        "title": (h.get("job_information") or {}).get("title") or v.get("core_job_title"),
        "company": c.get("name"),
        "workplace": v.get("workplace_type"),
        "location": v.get("formatted_workplace_location"),
        "comp": v.get("listed_compensation_frequency") and {
            "min": v.get("yearly_min_compensation"),
            "max": v.get("yearly_max_compensation"),
            "freq": v.get("listed_compensation_frequency"),
        },
        "yoe": v.get("yoe_range"),
        "posted": v.get("estimated_publish_date"),
        "apply_url": h.get("apply_url"),
        "description": strip_html((h.get("job_information") or {}).get("description")),
    }


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("url", nargs="?", default=DEFAULT_URL)
    ap.add_argument("-o", "--out", default="jobs.json")
    ap.add_argument("--slim", action="store_true", help="keep only key fields")
    ap.add_argument("--no-desc", action="store_true", help="skip full descriptions")
    a = ap.parse_args()

    hits, total = scrape(a.url)
    if not a.no_desc:
        hydrate(hits)
    out = [slim(h) for h in hits] if a.slim else hits
    with open(a.out, "w") as f:
        json.dump(out, f, indent=2, ensure_ascii=False)
    print(f"wrote {len(out)} jobs (of {total}) -> {a.out}", file=sys.stderr)


def selftest():
    # searchState round-trips out of the URL and decodes to valid JSON
    ss = urllib.parse.unquote(search_state(DEFAULT_URL))
    d = json.loads(ss)
    assert d["searchQuery"] == "engineer", d.get("searchQuery")
    assert d["roleYoeRange"] == [6, 11]
    assert slim({"job_information": {"title": "X"}, "id": "1"})["title"] == "X"
    print("selftest ok")


if __name__ == "__main__":
    if "--selftest" in sys.argv:
        selftest()
    else:
        main()
