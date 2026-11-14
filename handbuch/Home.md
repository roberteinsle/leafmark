# Leafmark-Handbuch

Leafmark ist eine Web-App zum Verwalten der eigenen Bücher: Lesestatus und Fortschritt, Tags und Reihen, Lese-Challenges, Statistiken und Familienkonten. Mehrere Nutzer teilen sich eine Instanz, jeder hat seine eigene Bibliothek.

Dieses Handbuch ist die Quelle des Wikis. Das Wiki wird aus `handbuch/` erzeugt, siehe [Entwicklung](Entwicklung.md).

## Inhalt

- [Bücher und Import](Buecher-und-Import.md): Bücher anlegen, Buchquellen, Amazon, Goodreads-CSV, ZIP-Sicherung
- [Lesen und Statistik](Lesen-und-Statistik.md): Status, Fortschritt, Tags, Reihen, Challenge, Statistik, Familie
- [Anmeldung und Sicherheit](Anmeldung-und-Sicherheit.md): Passkeys, E-Mail-Link, Passwort mit Zwei-Faktor
- [Administration](Administration.md): Nutzer, Registrierung, E-Mail (SMTP), API-Schlüssel
- [Installation und Betrieb](Installation-und-Betrieb.md): Coolify, Umgebungsvariablen, Updates, Fehlerbehebung
- [Entwicklung](Entwicklung.md): Devcontainer, Tests, CI, Übersetzungen, Handbuch und Wiki

## Sprachen

Die Oberfläche gibt es auf Englisch, Deutsch, Französisch, Italienisch, Polnisch und Spanisch. Jeder Nutzer wählt seine Sprache unter **Einstellungen → Konto**; ohne Auswahl gilt die Standardsprache der Instanz (`APP_LOCALE`).
