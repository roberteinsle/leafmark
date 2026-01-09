<?php

return [
    // Navigation
    'nav' => [
        'books' => 'Bücher',
        'settings' => 'Einstellungen',
        'logout' => 'Abmelden',
        'login' => 'Anmelden',
        'register' => 'Registrieren',
    ],

    // Books
    'books' => [
        'title' => 'Meine Bücher',
        'add_new' => 'Neues Buch hinzufügen',
        'search_placeholder' => 'ISBN, Titel oder Autor eingeben...',
        'search' => 'Suchen',
        'no_results' => 'Keine Bücher gefunden',
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
        'view' => 'Ansehen',
        'back_to_books' => 'Zurück zu Büchern',
        'back_to_book' => 'Zurück zum Buch',

        // Book details
        'title_label' => 'Titel',
        'author' => 'Autor',
        'series' => 'Serie',
        'series_position' => 'Position in Serie',
        'isbn' => 'ISBN',
        'isbn13' => 'ISBN-13',
        'publisher' => 'Verlag',
        'published_date' => 'Veröffentlichungsdatum',
        'description' => 'Beschreibung',
        'page_count' => 'Seitenzahl',
        'language' => 'Sprache',
        'format' => 'Format',
        'purchase_date' => 'Kaufdatum',
        'purchase_price' => 'Kaufpreis',
        'currency' => 'Währung',
        'current_page' => 'Aktuelle Seite',

        // Status
        'status' => 'Status',
        'want_to_read' => 'Möchte ich lesen',
        'currently_reading' => 'Lese ich gerade',
        'read' => 'Gelesen',

        // Reading progress
        'reading_progress' => 'Lesefortschritt',
        'update_progress' => 'Fortschritt aktualisieren',
        'started_reading' => 'Begonnen zu lesen',
        'finished_reading' => 'Fertig gelesen',

        // Covers
        'cover_images' => 'Cover-Bilder',
        'current_covers' => 'Aktuelle Cover (Hover zum Löschen oder als Standard setzen)',
        'upload_new_cover' => 'Neues Cover hochladen',
        'upload_covers' => 'Cover hochladen',
        'click_to_upload' => 'Klicken zum Hochladen',
        'drag_and_drop' => 'oder Drag & Drop',
        'image_requirements' => 'JPEG, PNG, GIF oder WebP. Max 25MB. Sie können mehrere Dateien auswählen.',
        'default' => 'Standard',
        'set_default' => 'Als Standard setzen',
        'delete_cover' => 'Cover löschen',

        // Search providers
        'provider' => 'Anbieter',
        'openlibrary' => 'Open Library',
        'google_books' => 'Google Books',
        'all_sources' => 'Alle Quellen',

        // Messages
        'delete_confirm' => 'Sind Sie sicher, dass Sie dieses Buch löschen möchten?',
        'delete_cover_confirm' => 'Sind Sie sicher, dass Sie dieses Cover löschen möchten?',
        'book_added' => 'Buch erfolgreich hinzugefügt!',
        'book_updated' => 'Buch erfolgreich aktualisiert!',
        'book_deleted' => 'Buch erfolgreich gelöscht!',
        'cover_uploaded' => ':count Cover erfolgreich hochgeladen!',
        'cover_deleted' => 'Cover erfolgreich gelöscht!',
        'primary_updated' => 'Haupt-Cover aktualisiert!',
    ],

    // Settings
    'settings' => [
        'title' => 'Einstellungen',
        'subtitle' => 'Verwalten Sie Ihre Kontoeinstellungen',
        'name' => 'Name',
        'email' => 'E-Mail',
        'preferred_language' => 'Bevorzugte Sprache',
        'language_help' => 'Dies wird als Standardsprache für Buchsuchen verwendet',
        'google_books_api_key' => 'Google Books API-Schlüssel',
        'api_key_help' => 'Optional: Geben Sie Ihren eigenen API-Schlüssel für Google Books-Suchen an.',
        'get_api_key' => 'API-Schlüssel erhalten',
        'save_changes' => 'Änderungen speichern',
        'cancel' => 'Abbrechen',
        'settings_updated' => 'Einstellungen erfolgreich aktualisiert!',
    ],

    // Forms
    'forms' => [
        'required' => 'Erforderlich',
        'optional' => 'Optional',
        'save' => 'Speichern',
        'cancel' => 'Abbrechen',
        'delete' => 'Löschen',
        'edit' => 'Bearbeiten',
        'create' => 'Erstellen',
        'update' => 'Aktualisieren',
        'submit' => 'Absenden',
        'search' => 'Suchen',
    ],

    // Languages
    'languages' => [
        'en' => 'English',
        'de' => 'Deutsch',
        'es' => 'Español',
        'fr' => 'Français',
        'it' => 'Italiano',
        'pl' => 'Polski',
    ],

    // Common
    'common' => [
        'yes' => 'Ja',
        'no' => 'Nein',
        'loading' => 'Wird geladen...',
        'error' => 'Fehler',
        'success' => 'Erfolg',
        'warning' => 'Warnung',
        'info' => 'Information',
    ],
];
