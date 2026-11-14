#!/usr/bin/env python3
"""Erzeugt das GitHub-Wiki aus handbuch/ und pusht es optional nach <repo>.wiki.git.

    python3 scripts/build-wiki.py --out /tmp/wiki   # nur erzeugen
    python3 scripts/build-wiki.py --push            # erzeugen, committen, pushen

handbuch/<Seite>.md wird zur Wiki-Seite <Seite>. Links wie [Text](Seite.md#abschnitt)
werden zu Wiki-Links, die Seitenleiste folgt der Reihenfolge der Links auf Home.md.
Das Wiki wird nie von Hand bearbeitet: Seiten, die es in handbuch/ nicht mehr gibt,
werden beim Push entfernt.
"""

from __future__ import annotations

import argparse
import re
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SOURCE = ROOT / "handbuch"
NOTICE = "<!-- Erzeugt aus handbuch/{name}.md mit scripts/build-wiki.py – nicht im Wiki bearbeiten. -->\n\n"
LINK = re.compile(r"\]\((?!https?://|mailto:|#)([^)#\s]+)\.md(#[^)\s]*)?\)")


def rewrite_links(text: str) -> str:
    """[Text](Seite.md#abschnitt) -> [Text](Seite#abschnitt)"""
    return LINK.sub(lambda m: f"]({m.group(1)}{m.group(2) or ''})", text)


def page_title(path: Path) -> str:
    for line in path.read_text(encoding="utf-8").splitlines():
        if line.startswith("# "):
            return line[2:].strip()
    return path.stem.replace("-", " ")


def sidebar(pages: list[Path]) -> str:
    names = {p.stem for p in pages}
    home = SOURCE / "Home.md"
    order: list[str] = []
    if home.exists():
        # Nur Listeneinträge zählen (das Inhaltsverzeichnis), nicht Links im Fließtext
        for line in home.read_text(encoding="utf-8").splitlines():
            if not line.lstrip().startswith(("- ", "* ")):
                continue
            for target in re.findall(r"\]\(([^)#\s]+)\.md", line):
                if target in names and target not in order:
                    order.append(target)
    order += sorted(n for n in names if n not in order and n != "Home")

    lines = ["**[Leafmark-Handbuch](Home)**", ""]
    lines += [f"- [{page_title(SOURCE / f'{name}.md')}]({name})" for name in order]
    return "\n".join(lines) + "\n"


def build(out: Path) -> list[str]:
    pages = sorted(SOURCE.glob("*.md"))
    if not pages:
        sys.exit("handbuch/ enthält keine .md-Dateien")

    out.mkdir(parents=True, exist_ok=True)
    written = []
    for page in pages:
        text = page.read_text(encoding="utf-8")
        (out / page.name).write_text(NOTICE.format(name=page.stem) + rewrite_links(text), encoding="utf-8")
        written.append(page.name)

    (out / "_Sidebar.md").write_text(sidebar(pages), encoding="utf-8")
    written.append("_Sidebar.md")
    return written


def git(*args: str, cwd: Path) -> str:
    return subprocess.run(["git", *args], cwd=cwd, check=True, capture_output=True, text=True).stdout.strip()


def wiki_url() -> str:
    origin = git("remote", "get-url", "origin", cwd=ROOT)
    return re.sub(r"(\.git)?$", ".wiki.git", origin, count=1)


def push(repo: str) -> None:
    with tempfile.TemporaryDirectory() as tmp:
        wiki = Path(tmp) / "wiki"
        subprocess.run(["git", "clone", "--quiet", repo, str(wiki)], check=True)

        # Alles außer .git entfernen, damit gelöschte Handbuch-Seiten auch im Wiki verschwinden
        for item in wiki.iterdir():
            if item.name == ".git":
                continue
            shutil.rmtree(item) if item.is_dir() else item.unlink()

        written = build(wiki)
        git("add", "--all", cwd=wiki)
        if not git("status", "--porcelain", cwd=wiki):
            print("Wiki ist bereits aktuell.")
            return

        source_rev = git("rev-parse", "--short", "HEAD", cwd=ROOT)
        git("commit", "--quiet", "-m", f"Wiki aus handbuch/ erzeugt ({source_rev})", cwd=wiki)
        subprocess.run(["git", "push", "--quiet", "origin", "HEAD"], cwd=wiki, check=True)
        print(f"Wiki aktualisiert: {len(written)} Dateien aus handbuch/ ({source_rev}).")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("--out", type=Path, help="Wiki nur in dieses Verzeichnis erzeugen")
    group.add_argument("--push", action="store_true", help="Wiki-Repository klonen, erzeugen, committen, pushen")
    parser.add_argument("--repo", help="Wiki-Repository (Standard: origin-URL mit .wiki.git)")
    args = parser.parse_args()

    if args.out:
        files = build(args.out)
        print(f"{len(files)} Dateien nach {args.out} geschrieben.")
    else:
        push(args.repo or wiki_url())


if __name__ == "__main__":
    main()
