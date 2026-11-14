# Anmeldung und Sicherheit

Leafmark bietet drei Wege zur Anmeldung, in dieser Reihenfolge:

1. **Passkey**: Fingerabdruck, Gesicht oder Geräte-PIN
2. **E-Mail-Link**: ein Anmeldelink per Mail, nur wenn die Instanz E-Mails versenden kann
3. **Passwort**, immer zusammen mit einem Zwei-Faktor-Code

## Passkeys

Ein Passkey ersetzt das Passwort. Er liegt auf deinem Gerät (oder in deinem Passwortmanager) und kann nicht abgefischt werden.

**Einrichten:** **Einstellungen → Sicherheit → Anmeldung & Sicherheit → Passkey hinzufügen**. Vorher fragt Leafmark einmal nach deinem Passwort. Gib dem Passkey einen Namen (z. B. „iPhone“), damit du ihn später wiederfindest. Mehrere Passkeys für verschiedene Geräte sind möglich.

**Anmelden:** Auf der Anmeldeseite **Mit Passkey anmelden** wählen. Viele Browser bieten den Passkey auch direkt im E-Mail-Feld an.

**Entfernen:** In derselben Übersicht neben dem Passkey auf **Entfernen**.

Passkeys sind an die Adresse der Instanz gebunden (`APP_URL`). Ändert sich die Domain, müssen sie neu eingerichtet werden.

## E-Mail-Link

Steht nur zur Verfügung, wenn ein Admin SMTP eingerichtet hat (siehe [Administration](Administration.md#e-mail-smtp)).

1. **Mit E-Mail-Link anmelden** wählen und die Adresse eingeben.
2. Die Mail enthält einen Link, der **15 Minuten** gültig ist und **nur einmal** funktioniert. Ein neuer Link macht ältere ungültig.
3. Der Link öffnet eine Bestätigungsseite; erst der Klick auf **Jetzt anmelden** meldet an. So können Link-Scanner in Mailprogrammen den Link nicht verbrauchen.

Die Antwort auf die Anfrage ist immer gleich, egal ob es die Adresse gibt. Nach der ersten Anmeldung per E-Mail-Link bietet Leafmark einmalig an, einen Passkey einzurichten.

## Passwort mit Zwei-Faktor

Wer sich mit Passwort anmeldet, braucht immer einen zweiten Faktor: einen 6-stelligen Code aus einer Authenticator-App (z. B. 2FAS, Aegis, Google Authenticator).

**Erste Anmeldung mit Passwort:** Nach dem Passwort zeigt Leafmark einen QR-Code. Mit der App scannen, den angezeigten Code eingeben, fertig. Danach erscheinen **8 Wiederherstellungscodes**: sicher aufbewahren, sie werden nur dieses eine Mal angezeigt.

**Jede weitere Anmeldung:** Passwort, dann der aktuelle Code aus der App. Jeder Code gilt nur einmal.

**Handy verloren:** Statt des Codes einen Wiederherstellungscode eingeben (jeder funktioniert einmal). Danach unter **Anmeldung & Sicherheit** neue Codes erzeugen oder 2FA neu einrichten. Sind auch die Codes weg, kann ein Admin die 2FA zurücksetzen; bei der nächsten Passwort-Anmeldung wird sie neu eingerichtet.

**2FA deaktivieren:** möglich unter **Anmeldung & Sicherheit**. Bei der nächsten Anmeldung mit Passwort muss sie wieder eingerichtet werden. Wer nur Passkeys oder E-Mail-Links nutzt, braucht sie nicht.

## Schutzmaßnahmen

- Anmeldeversuche (Passwort, E-Mail-Link, 2FA-Code, Passwortbestätigung) sind auf 5 pro Minute je Adresse und IP und 20 pro Stunde je IP begrenzt.
- 2FA-Schlüssel und Wiederherstellungscodes, das SMTP-Passwort und die API-Schlüssel werden verschlüsselt gespeichert.
- Passwort ändern: **Einstellungen → Sicherheit**, mit dem aktuellen Passwort.
