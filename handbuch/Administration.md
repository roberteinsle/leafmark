# Administration

Admins erreichen den Bereich über das Nutzermenü oben rechts → **Admin**.

## Wer ist Admin?

- Der **erste Nutzer**, der sich registriert, wird automatisch Admin.
- Ebenso, wer sich mit der Adresse aus `ADMIN_EMAIL` registriert (siehe [Installation und Betrieb](Installation-und-Betrieb.md)).
- Weitere Admins ernennt ein Admin unter **Nutzer**.

Admins sehen alle Nutzer, aber nicht deren Bücher.

## Nutzer

Unter **Nutzer** stehen alle Konten mit Zahl der Bücher und Registrierungsdatum. Pro Nutzer:

- Name, E-Mail und Admin-Status bearbeiten
- Admin-Rechte geben oder entziehen (nicht für dich selbst)
- **2FA zurücksetzen**, wenn jemand Gerät und Wiederherstellungscodes verloren hat
- Nutzer löschen (nicht dich selbst); dabei werden alle Bücher, Tags und Challenges des Nutzers gelöscht

## Systemeinstellungen

### Registrierung

| Einstellung | Wirkung |
|---|---|
| Registrierung aktiviert | ausgeschaltet kann sich niemand neu registrieren |
| Offen für alle | jeder kann sich registrieren |
| Nur bestimmte Domains | nur Adressen aus der Liste, z. B. `example.com, example.org` |
| Registrierungscode | nur mit dem eingestellten Code |

### E-Mail (SMTP)

Nötig für Anmeldelinks per E-Mail. Ohne SMTP melden sich Nutzer mit Passkey oder Passwort + Zwei-Faktor an.

| Feld | Hinweis |
|---|---|
| E-Mails über SMTP versenden | Hauptschalter |
| SMTP-Server, Port | vom Mailanbieter |
| Verschlüsselung | **STARTTLS** (meist Port 587) oder **SSL/TLS** (meist Port 465) |
| Benutzername, Passwort | das Passwort wird verschlüsselt gespeichert und nie angezeigt; leer lassen behält das gespeicherte |
| Absenderadresse, Absendername | erscheinen als Absender der Mails |

**Testmail senden** schickt mit den gespeicherten Einstellungen eine Mail an deine eigene Adresse. Erst speichern, dann testen.

### API-Schlüssel

Google Books und Big Book API erscheinen erst als Buchquelle, wenn ein Schlüssel gesetzt ist. Die Schlüssel werden verschlüsselt gespeichert; ein leeres Feld behält den gespeicherten Schlüssel, **Gespeicherten Wert entfernen** löscht ihn.

**Google Books einrichten:**

1. In der Google Cloud Console ein Projekt anlegen und unter **APIs und Dienste → Bibliothek** die **Books API** aktivieren.
2. Unter **Anmeldedaten** einen API-Schlüssel erstellen.
3. **API-Einschränkungen:** nur **Books API** zulassen.
4. **Anwendungseinschränkungen:** **IP-Adressen** und die *ausgehende* Adresse des Servers eintragen. Bei IPv6 das ganze Netz angeben (z. B. `2001:db8:1234:5678::/64`), nicht eine einzelne Adresse: Die tatsächliche Absenderadresse eines Containers liegt oft irgendwo im Netz.
5. Den Schlüssel in Leafmark eintragen, speichern und **Google-Books-Schlüssel testen** klicken.

Der Test zeigt Googles Antwort im Klartext und in Klammern den genauen Grund:

| Grund | Bedeutung | Abhilfe |
|---|---|---|
| `API_KEY_IP_ADDRESS_BLOCKED` | Die Anfrage kam von einer IP, die nicht in der Einschränkung steht | ausgehende IP des Servers eintragen, bei IPv6 das `/64`-Netz |
| `API_KEY_SERVICE_BLOCKED` | Die API-Einschränkung des Schlüssels erlaubt die Books API nicht | unter „API-Einschränkungen“ **Books API** auswählen |
| `SERVICE_DISABLED` | Die Books API ist im Projekt des Schlüssels nicht aktiviert | **APIs und Dienste → Bibliothek → Books API → Aktivieren** |
| `API_KEY_INVALID` | Schlüssel falsch kopiert oder gelöscht | Schlüssel neu eintragen |
| Status 429 | Tageskontingent aufgebraucht | bis morgen warten oder in der Cloud Console ein höheres Kontingent beantragen |

Änderungen in der Google Cloud Console brauchen bis zu 5 Minuten, bis sie wirken.

**Ausgehende IP des Servers herausfinden** (auf dem Server, `<container>` ist der App-Container):

```bash
docker exec <container> curl -s -4 https://ifconfig.co   # IPv4
docker exec <container> curl -s -6 https://ifconfig.co   # IPv6 (leer, wenn der Container kein IPv6 nutzt)
``` Ohne gültigen Schlüssel ist Google Books praktisch nicht nutzbar: Das gemeinsame Kontingent für Anfragen ohne Schlüssel ist meist erschöpft.

Statt in der Oberfläche kann der Google-Schlüssel auch als Umgebungsvariable `GOOGLE_BOOKS_API_KEY` gesetzt werden; ein Schlüssel aus den Systemeinstellungen hat Vorrang.

**Big Book API:** Schlüssel von bigbookapi.com (kostenlos: 60 Anfragen pro Minute). Diese Quelle filtert nicht nach Sprache.
