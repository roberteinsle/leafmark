# Bücher und Import

## Buch hinzufügen

**Buch hinzufügen** (oben rechts, auf dem Handy das Plus) öffnet die Suche. Gesucht wird nach Titel, Autor oder ISBN:

- Eine **ISBN** (10 oder 13 Stellen, mit oder ohne Bindestriche) wird exakt gesucht.
- Alles andere geht als freie Suche an die gewählte Quelle. Wer gezielt suchen will, kann Präfixe der Google-Books-Suche voranstellen, z. B. `intitle:Der Schwarm` oder `inauthor:Schätzing`.
- Die **Sprache** im Suchformular bevorzugt Treffer in dieser Sprache. Gibt es zu wenige, wird ohne Einschränkung gesucht und die bevorzugte Sprache nach oben sortiert.

Wird nichts Passendes gefunden, lässt sich das Buch auch von Hand anlegen.

### Buchquellen

| Quelle | Schlüssel nötig | Hinweise |
|---|---|---|
| Open Library | nein | Standardquelle |
| BookBrainz | nein | zusätzliche Metadaten |
| Google Books | ja | erscheint nur, wenn ein Schlüssel gesetzt ist |
| Big Book API | ja | ohne Sprachfilter |
| Alle Quellen | – | fasst die Treffer zusammen und entfernt Dubletten anhand der ISBN |

Die Schlüssel setzt ein Admin unter [Administration](Administration.md#api-schlüssel). Kann eine Quelle nicht antworten, zeigt die Suche einen Hinweis über den Treffern, statt einfach „keine Treffer“ zu melden:

- **Kontingent aufgebraucht:** Das Tageslimit bei Google ist erreicht; Treffer aus dieser Quelle fehlen bis zum nächsten Tag.
- **Schlüssel abgelehnt:** Google akzeptiert den Schlüssel nicht, meist wegen einer IP-Einschränkung. Leafmark sucht dann ohne Schlüssel weiter.
- **Nicht erreichbar:** Zeitüberschreitung oder Serverfehler der Quelle.

Externe Abfragen sind pro Nutzer auf 30 pro Minute und 500 pro Tag begrenzt.

### Von Amazon übernehmen

Auf der Suchseite lässt sich eine Produkt-URL von amazon.de oder amazon.com einfügen. Leafmark liest Titel, Autor, ISBN, Verlag, Erscheinungsdatum, Seitenzahl, Sprache, Beschreibung, Reihe und Cover aus und legt das Buch mit Status **Möchte ich lesen** an.

## Buch bearbeiten

Auf der Buchseite lassen sich alle Angaben ändern: Reihe und Band, Bewertung und Rezension, Kaufdatum, Preis, Währung und Format. **Von API neu importieren** holt die Metadaten erneut von der Quelle, aus der das Buch stammt.

### Cover

Pro Buch sind mehrere Cover möglich (JPEG, PNG, GIF, WebP, bis 25 MB je Bild, bis zu 10 auf einmal). Eines davon ist das Hauptcover. Gibt es kein eigenes Cover, nutzt Leafmark das Bild der Buchquelle.

## Mehrere Bücher auf einmal

In der Bücherliste lassen sich Bücher markieren und gemeinsam löschen, mit Tags versehen oder von einem Tag lösen.

## Ansicht der Bücherliste

Jede Liste (Alle, Lese ich gerade, Möchte ich lesen, Gelesen) merkt sich ihre eigene Ansicht: Raster oder Tabelle, sichtbare Spalten und Bücher pro Seite (10, 25, 50, 100). Die Sortierung (z. B. nach Titel, Autor, Bewertung oder Datum) ist wählbar.

## Import aus Goodreads (CSV)

1. Bei Goodreads unter **My Books → Import and export → Export Library** die CSV-Datei herunterladen.
2. In Leafmark unter **Einstellungen → Datenverwaltung → Goodreads CSV-Import** hochladen (CSV, höchstens 10 MB).
3. Die Vorschau prüfen und wählen, ob Dubletten übersprungen werden und ob alle importierten Bücher einen eigenen Tag bekommen.
4. Importieren. Goodreads-Regale werden zu Tags.

Dubletten erkennt Leafmark an ISBN, Titel und Autor. Jeder Import erscheint im Import-Verlauf mit Zahl der importierten, übersprungenen und fehlerhaften Einträge.

## Bibliothek sichern und umziehen (ZIP)

**Einstellungen → Datenverwaltung → Bibliothek exportieren** lädt die ganze Bibliothek als ZIP herunter: Bücher mit allen Angaben, Cover, Tags, Lesefortschritt und Challenges.

**Leafmark-Bibliothek importieren** nimmt so eine ZIP-Datei wieder an (höchstens 50 MB), auch auf einer anderen Leafmark-Instanz. Vor dem Import zeigt eine Vorschau, was enthalten ist. Für Bücher, die es schon gibt, wählst du:

| Strategie | Wirkung |
|---|---|
| Überspringen | vorhandene Bücher bleiben unverändert |
| Überschreiben | vorhandene Bücher werden mit den Daten aus der Datei ersetzt |
| Beide behalten | das Buch aus der Datei wird zusätzlich angelegt |

Export und Import sind pro Nutzer auf 5 pro Minute und 50 pro Tag begrenzt.
