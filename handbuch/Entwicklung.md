# Entwicklung

## Umgebung

**GitHub Codespaces / Devcontainer:** PHP 8.5, Node 22 und Docker-in-Docker. Beim Erstellen installiert `.devcontainer/setup.sh` die Abhängigkeiten, startet Postgres aus `docker-compose.yml`, legt die Datenbanken `leafmark` und `leafmark_test` an und migriert.

```bash
php artisan serve        # App auf Port 8000
npm run dev              # Vite mit Hot Reload
php artisan test         # Tests gegen die Datenbank leafmark_test
docker compose up -d --build   # Produktions-Image lokal auf Port 8080
```

**Lokal ohne Codespaces:** PHP 8.5, Composer, Node 22 und Docker für Postgres; dann wie im README beschrieben.

### Lokale Datenbank

| Einstellung | Wert |
|---|---|
| Host, Port | `127.0.0.1`, `5432` |
| Datenbanken | `leafmark` (App), `leafmark_test` (Tests) |
| Benutzer / Passwort | `leafmark` / `leafmark` |

Datenbank-Shell: `docker compose exec db psql -U leafmark`. Komplett zurücksetzen: `docker compose down -v && docker compose up -d db`, dann `leafmark_test` neu anlegen (`docker compose exec db createdb -U leafmark leafmark_test`) und `php artisan migrate`.

### E-Mails und Passkeys lokal

- `MAIL_MAILER=log` schreibt Mails nach `storage/logs/laravel.log`. Der E-Mail-Link erscheint auf der Anmeldeseite nur, wenn SMTP in **Admin → Systemeinstellungen** aktiviert ist.
- Passkeys verlangen, dass die Seite unter der Adresse aus `APP_URL` läuft. In Codespaces `APP_URL` in `.env` auf die weitergeleitete Adresse von Port 8000 setzen (`https://<codespace>-8000.app.github.dev`).

### Codespaces-Kosten

Das kostenlose Kontingent sind 120 Core-Stunden im Monat (60 Stunden auf einer 2-Core-Maschine). Empfohlen: Idle-Timeout 30 Minuten, Aufbewahrung 7 Tage (GitHub → Settings → Codespaces).

## Tests und CI

- Die Tests laufen ausschließlich gegen **Postgres**, wie in Produktion. Die Zugangsdaten stehen in `phpunit.xml`; lokal muss der Postgres-Container laufen.
- Die GitHub Action `.github/workflows/build.yml` testet jeden Push und Pull Request, baut danach das Image und schiebt es bei Pushes auf `main` nach GHCR.
- Externe Dienste (Google Books usw.) werden in Tests mit `Http::fake()` simuliert.

## Grundregeln

- **Nur Postgres.** `LIKE` unterscheidet dort Groß- und Kleinschreibung; für Suchen `whereLike()` verwenden.
- **Book-URLs nutzen den Zeitstempel von `added_at`**, nicht die ID.
- **`env()` nur in `config/`**, weil der Container die Konfiguration cacht.
- **Neue Texte** zuerst in `lang/en/app.php`, dieselben Schlüssel in `de`, `fr`, `it`, `pl`, `es`.
- **Keine persönlichen Daten des Maintainers** im Repository: Platzhalter (`<owner>`, `example.com`) und Umgebungsvariablen verwenden.
- Ein Merge nach `main` geht direkt in Produktion.

Weitere technische Hinweise stehen in `CLAUDE.md`.

## Handbuch und Wiki

`handbuch/` ist die Quelle des GitHub-Wikis. Das Wiki wird nie von Hand bearbeitet.

```bash
python3 scripts/build-wiki.py --out /tmp/wiki   # nur erzeugen, zum Ansehen
python3 scripts/build-wiki.py --push            # erzeugen und ins Wiki-Repository pushen
```

Das Skript:

- übernimmt jede Datei `handbuch/<Seite>.md` als Wiki-Seite `<Seite>`,
- wandelt Links wie `[Text](Seite.md#abschnitt)` in Wiki-Links um,
- erzeugt die Seitenleiste in der Reihenfolge der Links auf `Home.md`,
- ergänzt auf jeder Seite einen Hinweis, dass sie aus `handbuch/` erzeugt wird,
- entfernt Wiki-Seiten, die es in `handbuch/` nicht mehr gibt.

Das Wiki-Repository wird aus der Git-Adresse `origin` abgeleitet (`<repo>.wiki.git`); `--repo` setzt es explizit.
