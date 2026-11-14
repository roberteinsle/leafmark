# Umzug Produktion: Coolify-Application (SQLite) → Compose-Resource (GHCR) + Postgres-Resource

Einmalige Anleitung für den Wechsel von der alten Coolify-Application, die selbst aus dem Dockerfile
gebaut hat, auf den Deployment-Standard. Danach kann diese Datei gelöscht werden.

**Ausfallzeit:** etwa 15–30 Minuten (vom Stoppen der alten App bis zum Umschalten der Domain).

## 0. Vor dem Merge nach `main` – sonst geht die Seite offline

Die alte Coolify-Application baut heute bei jedem Push auf `main` automatisch. Mit dem neuen Dockerfile
würde sie ein Image bauen, das auf Port 8080 statt 80 lauscht und Postgres erwartet. Die Seite wäre down.

1. Coolify → alte Leafmark-Application → **Auto Deploy ausschalten** (bzw. GitHub-Webhook entfernen).
2. Einmalig GHCR-Zugriff für den Server einrichten (Standard 2.1):
   ```bash
   echo '<PAT mit read:packages>' | docker login ghcr.io -u roberteinsle --password-stdin
   ```
3. Erst jetzt den Branch nach `main` mergen. Die Action testet (SQLite + Postgres), baut und schiebt
   `ghcr.io/roberteinsle/leafmark:latest`. Im Action-Log „PHP-Version im Image ausgeben“ prüfen.

## 1. Alte Daten sichern

Per SSH auf dem Coolify-Server, Container-Namen mit `docker ps | grep leafmark` ermitteln:

```bash
OLD=<container der alten App>
mkdir -p ~/leafmark-migration && cd ~/leafmark-migration

# SQLite konsistent kopieren (App kurz stoppen, damit nichts mehr schreibt)
docker stop $OLD
docker cp $OLD:/var/www/html/database/database.sqlite ./database.sqlite
docker cp $OLD:/var/www/html/storage/app/public ./public-storage   # Cover-Bilder
docker start $OLD   # läuft bis zur Umschaltung weiter, nur noch lesend nutzen
```

Ab hier keine Änderungen mehr in der alten App, sonst fehlen sie nach dem Umzug.
Für wirklich null Datenverlust die alte App gestoppt lassen.

Außerdem aus der alten Application unter Environment Variables notieren: `APP_KEY`, `ADMIN_EMAIL`,
`APP_LOCALE`, ggf. `GOOGLE_BOOKS_API_KEY`.

**`APP_KEY` übernehmen und danach nie mehr ändern.** Damit werden die 2FA-Secrets der Nutzer
verschlüsselt; mit einem neuen Key kann sich niemand mehr per Passwort+2FA anmelden. Außerdem bleiben so
die „Angemeldet bleiben“-Cookies gültig.

## 2. Postgres-Resource und neue Compose-Resource anlegen

Postgres läuft als **eigenständige Coolify-Resource**, nicht im Compose der App.

1. Projekt → Environment `production` → New Resource → **PostgreSQL** (Version 16, wie in der CI).
   Datenbank `leafmark`, Benutzer `leafmark`, Passwort zufällig. Starten.
   **Nicht öffentlich erreichbar machen** („Make it publicly available“ aus lassen).
2. Aus der Postgres-Resource den **internen Hostnamen** notieren (steht in „Postgres URL (internal)“
   zwischen `@` und `:5432`, meist eine lange ID).
3. New Resource → **Docker Compose Empty**, Inhalt von `deploy/docker-compose.coolify.yml` einfügen, speichern.
   Die App hängt im Netz `coolify` und erreicht darüber die Postgres-Resource.
4. Environment Variables setzen: `APP_KEY` (alter Wert), `APP_URL=https://www.leafmark.app`,
   `ADMIN_EMAIL`, `APP_LOCALE`, `DB_HOST` (interner Hostname aus Schritt 2), `DB_DATABASE=leafmark`,
   `DB_USERNAME=leafmark`, `DB_PASSWORD` (aus Schritt 1). `APP_TAG` und `DB_PORT` leer lassen.
5. **Noch keine Domain setzen.** Deploy klicken. Beim Start legt AUTORUN die leeren Tabellen in Postgres an.
6. Runtime-Logs prüfen: Migrationen gelaufen, Container healthy. Falls „could not translate host name“:
   `DB_HOST` prüfen und ob die Postgres-Resource auf demselben Server im Netz `coolify` läuft.

## 3. Daten übernehmen

```bash
NEW=<app-container der neuen Resource>
cd ~/leafmark-migration

# SQLite und Cover in das Storage-Volume der neuen App kopieren
docker cp ./database.sqlite $NEW:/var/www/html/storage/legacy.sqlite
docker cp ./public-storage/. $NEW:/var/www/html/storage/app/public/
docker exec -u root $NEW chown -R www-data:www-data /var/www/html/storage

# Import: kopiert alle Tabellen inkl. IDs und prüft die Zeilenzahlen
docker exec $NEW php artisan leafmark:import-sqlite /var/www/html/storage/legacy.sqlite
```

Die Ausgabe endet mit einer Tabelle pro Tabelle (SQLite / Target / Status). Alle Zeilen müssen `OK` zeigen.
Bei `MISMATCH` nicht umschalten, sondern die alte App weiterlaufen lassen und den Fehler untersuchen.

Danach die Kopie entfernen:

```bash
docker exec $NEW rm /var/www/html/storage/legacy.sqlite
```

## 4. Umschalten

1. Alte Application: Domain entfernen, **stoppen** (nicht löschen, sie ist der Rollback).
2. Neue Resource → Service `app`: Domain `https://www.leafmark.app`, Port `8080`. Speichern, Deploy.
3. Prüfen:
   - `https://www.leafmark.app/up` liefert 200.
   - Anmelden, Bücherliste, ein Buch mit Cover öffnen, Statistikseite.
   - `docker exec $NEW php -v` zeigt die gepatchte Version (mindestens 8.5.12).
4. Postgres-Resource → Backups: tägliche Sicherung aktivieren (Ziel S3 oder lokal).
5. Optional Auto-Deploy: Repo-Secrets `COOLIFY_WEBHOOK` und `COOLIFY_TOKEN`, Repo-Variable `AUTO_DEPLOY=true`.

## Rollback (falls nötig)

Domain an der neuen Resource entfernen, alte Application wieder starten und Domain dort setzen.
Die alte App nutzt ihre unveränderten Volumes. Änderungen, die Nutzer seit der Umschaltung gemacht haben,
fehlen dann dort.

Die alte Application und `~/leafmark-migration` nach etwa zwei Wochen ohne Probleme löschen.

## Was sich für die Nutzer ändert

Bitte vor der Umschaltung kurz ankündigen:

- **Neu: Anmeldung mit Passkey** (Fingerabdruck, Gesicht, Geräte-PIN) – einrichten unter
  Einstellungen → Sicherheit → Anmeldung & Sicherheit.
- **Neu: Anmeldung per E-Mail-Link**, sofern SMTP in Admin → System Settings aktiv ist.
- **Passwort-Login nur noch mit Zwei-Faktor:** Wer sich zum ersten Mal wieder mit Passwort anmeldet, muss
  eine Authenticator-App (z. B. 2FAS, Aegis, Google Authenticator) einrichten. Die Wiederherstellungscodes
  gut aufbewahren. Bei Verlust kann ein Admin die 2FA unter Admin → Users → Edit zurücksetzen.
