# Installation und Betrieb

Leafmark wird nach dem Muster **GitHub → GHCR → Coolify** betrieben: GitHub Actions baut das Image, die GitHub Container Registry speichert es, Coolify zieht und startet es nur. Coolify baut nie selbst.

## Bausteine

| Baustein | Aufgabe |
|---|---|
| App-Container | `serversideup/php` mit PHP 8.5, Nginx und PHP-FPM; lauscht auf Port 8080 |
| PostgreSQL 16 | eigene Coolify-Resource, nicht Teil des App-Compose |
| Volume `app-storage` | `storage/` der App: Cover, Logs, Sessions, Caches |

Ein Worker oder Scheduler ist nicht nötig: Leafmark stellt keine Jobs in eine Warteschlange und plant keine Aufgaben.

## Einrichtung in Coolify

1. **GHCR-Zugriff für den Server** (einmalig): einen GitHub-Token (classic) nur mit `read:packages` anlegen und auf dem Server anmelden:
   ```bash
   echo '<TOKEN>' | docker login ghcr.io -u <github-account> --password-stdin
   ```
2. **Postgres-Resource:** New Resource → PostgreSQL 16, nicht öffentlich erreichbar. Den internen Hostnamen aus „Postgres URL (internal)“ notieren.
3. **App-Resource:** New Resource → Docker Compose Empty, Inhalt von `deploy/docker-compose.coolify.yml` einfügen. Die App hängt im Netz `coolify` und erreicht darüber Postgres.
4. **Umgebungsvariablen** setzen (Tabelle unten).
5. **Domain** am Service `app` mit internem Port **8080**.
6. **Backups** an der Postgres-Resource aktivieren. Das Volume `app-storage` sichert das Server-Backup.

### Umgebungsvariablen

| Variable | Pflicht | Bedeutung |
|---|---|---|
| `APP_IMAGE` | ja | z. B. `ghcr.io/<owner>/leafmark` |
| `APP_TAG` | nein | leer = `latest`; für Rollbacks ein älterer Tag |
| `APP_KEY` | ja | `php artisan key:generate --show`. **Nie mehr ändern**: damit sind 2FA-Schlüssel, SMTP-Passwort und API-Schlüssel verschlüsselt |
| `APP_URL` | ja | öffentliche Adresse, z. B. `https://books.example.com`; Passkeys sind daran gebunden |
| `DB_HOST` | ja | interner Hostname der Postgres-Resource |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | ja | Zugangsdaten der Postgres-Resource |
| `DB_PORT` | nein | Standard 5432 |
| `APP_LOCALE` | nein | Standardsprache: `en`, `de`, `fr`, `it`, `pl`, `es` |
| `ADMIN_EMAIL` | nein | wer sich mit dieser Adresse registriert, wird Admin |
| `GOOGLE_BOOKS_API_KEY` | nein | Ersatz, falls kein Schlüssel in den Systemeinstellungen steht |
| `GOOGLE_SITE_VERIFICATION` | nein | Token der Google Search Console, erzeugt das passende Meta-Tag |

SMTP und die übrigen API-Schlüssel werden in der Oberfläche gesetzt, siehe [Administration](Administration.md).

## Deployment

Ein Merge nach `main` startet die GitHub Action: Tests gegen Postgres, Image bauen, nach GHCR schieben, PHP-Version ausgeben, Frontend auf Schlüssel prüfen. Ist die Repository-Variable `AUTO_DEPLOY=true` gesetzt, stößt die Action danach Coolify über den Webhook an (`COOLIFY_WEBHOOK`, `COOLIFY_TOKEN` als Secrets); sonst in Coolify auf **Deploy** klicken.

Beim Start führt der Container Migrationen aus, legt den Storage-Link an und baut die Caches. Die App ist dabei einige Sekunden nicht erreichbar.

**Prüfen nach dem Deploy:** `<APP_URL>/up` liefert 200, und `docker exec <container> php -v` zeigt die aktuelle PHP-Version.

## Releases und Rollback

Ein Git-Tag `v1.2.3` erzeugt zusätzlich die Image-Tags `1.2.3` und `1.2`. Jeder Build bekommt außerdem `sha-<commit>`.

**Rollback:** `APP_TAG` in Coolify auf einen älteren Tag setzen und deployen; zurück auf aktuell mit leerem `APP_TAG`. Datenbank-Migrationen werden dabei nicht zurückgerollt – vor einem Rollback über eine Migration hinweg erst das Datenbank-Backup einspielen. Nie auf ein Image mit ungepatchter PHP-Version zurückrollen (die Version steht im Log der Action).

## PHP-Updates

- **Kritischer Patch:** in GitHub unter Actions → Build & Push → **Run workflow** mit `no_cache` starten, PHP-Version im Log prüfen, deployen.
- **Normaler Patch:** nichts zu tun, der wöchentliche Rebuild (montags) holt ihn.
- **Neue Minor-Version:** `Dockerfile` (`ARG PHP_VERSION`), `composer.json` (`"php"`) und `.devcontainer/devcontainer.json` im selben Commit ändern, `composer update`, Tests, mergen, Release-Tag setzen.

## Fehlerbehebung

**Container startet ständig neu, Meldung „storage is not writable“:** Coolify hat das Volume `app-storage` leer und mit Besitzer `root` angelegt. Einmal auf dem Server:

```bash
docker run --rm -u root -v <resource-uuid>_app-storage:/s --entrypoint sh <APP_IMAGE>:latest -c 'chown -R 82:82 /s'
```

Danach den Container neu starten. Fehlende Unterordner legt Leafmark beim Start selbst an.

**„could not translate host name“ beim Start:** `DB_HOST` prüfen und ob die Postgres-Resource auf demselben Server im Netz `coolify` läuft.

**Google Books liefert nichts:** in den Systemeinstellungen **Google-Books-Schlüssel testen**; meist passt die IP-Einschränkung nicht oder das Kontingent ist erschöpft.

**Logs:** in Coolify unter der Resource → Logs, oder `docker logs <container>`.
