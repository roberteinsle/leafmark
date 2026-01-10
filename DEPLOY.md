# Deployment Anleitung

## Manuelle Deployment auf Hetzner Server

Da der Server durch eine Firewall geschützt ist und GitHub Actions keinen Zugriff hat, erfolgt das Deployment manuell über ein Shell-Script.

### Voraussetzungen

- SSH-Zugriff auf den Hetzner-Server (65.108.241.237)
- User: `deploy`
- Docker und Docker Compose auf dem Server installiert
- Repository in `~/leafmark/app-source` geklont

### Deployment durchführen

1. **SSH-Verbindung zum Server herstellen:**
   ```bash
   ssh deploy@65.108.241.237
   ```

2. **Zum Projektverzeichnis wechseln:**
   ```bash
   cd ~/leafmark/app-source
   ```

3. **Deployment-Script ausführen:**
   ```bash
   ./deploy.sh
   ```

### Was macht das Script?

Das `deploy.sh` Script führt automatisch folgende Schritte aus:

1. ✅ Wechselt ins Anwendungsverzeichnis
2. ✅ Pullt die neuesten Änderungen von GitHub (`main` Branch)
3. ✅ Stoppt die laufenden Docker-Container
4. ✅ Baut die Container neu und startet sie
5. ✅ Führt Datenbank-Migrationen aus (`php artisan migrate --force`)
6. ✅ Cached die Konfiguration (`php artisan config:cache`)
7. ✅ Cached die Routen (`php artisan route:cache`)
8. ✅ Zeigt den Container-Status an

### Schnell-Deployment (One-Liner)

Wenn Sie das Deployment in einem Befehl durchführen möchten:

```bash
ssh deploy@65.108.241.237 'cd ~/leafmark/app-source && ./deploy.sh'
```

### Troubleshooting

**Problem: Script ist nicht ausführbar**
```bash
chmod +x ~/leafmark/app-source/deploy.sh
```

**Problem: Git pull schlägt fehl**
```bash
cd ~/leafmark/app-source
git status
git stash  # Falls lokale Änderungen vorhanden sind
git pull origin main
```

**Problem: Container starten nicht**
```bash
docker compose logs -f app
```

**Problem: Migrationen schlagen fehl**
```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan migrate --force
```

### Logs anzeigen

**Anwendungs-Logs:**
```bash
docker compose logs -f app
```

**Alle Container-Logs:**
```bash
docker compose logs -f
```

**Laravel-Logs:**
```bash
docker compose exec app tail -f storage/logs/laravel.log
```

### Container-Status prüfen

```bash
docker compose ps
```

### Notfall-Befehle

**Containers neu starten (ohne rebuild):**
```bash
docker compose restart
```

**Containers komplett neu aufsetzen:**
```bash
docker compose down
docker compose up -d --build --force-recreate
```

**Cache komplett löschen:**
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

## Automatisches Deployment (Optional - wenn Firewall konfiguriert)

Falls die Firewall später so konfiguriert wird, dass GitHub Actions Zugriff hat:

1. GitHub Secrets einrichten (siehe README.md)
2. Push auf `main` Branch
3. GitHub Actions führt automatisch das Deployment durch

Der Workflow ist bereits vorbereitet in `.github/workflows/deploy.yml`
