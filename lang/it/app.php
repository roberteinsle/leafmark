<?php

return [
    // Navigation
    'nav' => [
        'books' => 'Libri',
        'tags' => 'Etichette',
        'challenge' => 'Sfida',
        'family' => 'Famiglia',
        'add_book' => 'Aggiungi Libro',
        'settings' => 'Impostazioni',
        'logout' => 'Disconnetti',
        'login' => 'Accedi',
        'register' => 'Registrati',
    ],

    // Books
    'books' => [
        'title' => 'I Miei Libri',
        'add_new' => 'Aggiungi un Nuovo Libro',
        'add_new_book' => 'Aggiungi Nuovo Libro',
        'search_for_book' => 'Cerca un libro o aggiungilo manualmente',
        'search_for_books' => 'Cerca libri',
        'search_placeholder' => 'Inserisci ISBN, titolo o autore...',
        'search' => 'Cerca',
        'search_tip' => 'Suggerimento: La ricerca rileva automaticamente ISBN, nomi di autori e titoli di libri',
        'search_by_identifier' => 'Puoi anche cercare per identificatore:',
        'no_results' => 'Nessun libro trovato',
        'no_results_found' => 'Nessun risultato trovato',
        'no_results_message' => 'Non siamo riusciti a trovare libri che corrispondono alla tua ricerca. Puoi aggiungere il libro manualmente qui sotto.',
        'search_results' => 'Risultati di Ricerca',
        'add_to_library' => 'Aggiungi alla Biblioteca',
        'add_book_manually' => 'Aggiungi Libro Manualmente',
        'edit' => 'Modifica',
        'delete' => 'Elimina',
        'view' => 'Visualizza',
        'back_to_books' => 'Torna ai Libri',
        'back_to_book' => 'Torna al Libro',
        'by_author' => 'di',
        'pages' => 'Pagine',

        // Book details
        'title_label' => 'Titolo',
        'author' => 'Autore',
        'series' => 'Serie',
        'publisher' => 'Editore',
        'description' => 'Descrizione',
        'status' => 'Stato',
        'want_to_read' => 'Voglio leggere',
        'currently_reading' => 'Sto leggendo',
        'read' => 'Letto',
    ],

    // Family
    'family' => [
        'title' => 'Famiglia',
        'description' => 'Connettiti con la famiglia o gli amici e condividi le tue avventure di lettura',
        'no_family_title' => 'Non sei ancora in una famiglia',
        'no_family_description' => 'Crea una nuova famiglia o unisciti a una esistente per condividere le tue avventure di lettura con gli altri.',
        'create_family' => 'Crea famiglia',
        'join_existing' => 'Unisciti a famiglia esistente',
        'back_to_family' => 'Torna alla famiglia',

        // Create Family
        'create_description' => 'Crea una nuova famiglia e invita amici o membri della famiglia',
        'family_name' => 'Nome della famiglia',
        'family_name_placeholder' => 'es. Famiglia Rossi, Club di lettura Roma',
        'family_name_help' => 'Scegli un nome che descriva il tuo gruppo',
        'what_happens' => 'Cosa succede?',
        'create_info_1' => 'Diventerai automaticamente il proprietario della famiglia',
        'create_info_2' => 'Riceverai un codice di adesione unico',
        'create_info_3' => 'Ogni membro mantiene la propria collezione di libri',

        // Join Family
        'join_description' => 'Inserisci il codice di adesione per unirti a una famiglia esistente',
        'enter_join_code' => 'Codice di adesione',
        'join_code_help' => 'Il codice è composto da 8 lettere',
        'join_note' => 'Nota',
        'join_note_text' => 'Puoi essere membro di una sola famiglia. La tua collezione di libri rimane privata.',

        // Family Info
        'owner' => 'Proprietario',
        'member_count' => '{1} 1 Membro|[2,*] :count Membri',
        'books_count' => '{1} 1 Libro|[2,*] :count Libri',
        'members' => 'Membri',
        'join_code' => 'Codice di adesione',
        'copy_code' => 'Copia codice',
        'copied' => 'Copiato!',
        'share_code_info' => 'Condividi questo codice con le persone che vogliono unirsi alla tua famiglia.',
        'regenerate_code' => 'Genera nuovo codice',
        'regenerate_confirm' => 'Vuoi davvero generare un nuovo codice di adesione? Il vecchio codice diventerà non valido.',

        // Actions
        'leave_family_button' => 'Lascia famiglia',
        'join_family_button' => 'Unisciti alla famiglia',
        'delete_family' => 'Elimina famiglia',
        'leave_confirm' => 'Vuoi davvero lasciare la famiglia?',
        'delete_confirm' => 'Vuoi davvero eliminare la famiglia? Tutti i membri perderanno la loro appartenenza familiare.',

        // Messages
        'family_created' => 'Famiglia creata con successo!',
        'joined_family' => 'Ti sei unito alla famiglia ":name"!',
        'left_family' => 'Hai lasciato la famiglia',
        'family_deleted' => 'Famiglia eliminata con successo',
        'code_regenerated' => 'È stato generato un nuovo codice di adesione',
        'already_in_family' => 'Sei già membro di una famiglia',
        'already_owns_family' => 'Sei già proprietario di una famiglia',
        'not_in_family' => 'Non sei in una famiglia',
        'invalid_code' => 'Codice di adesione non valido',
        'owner_cannot_leave' => 'Come proprietario, non puoi lasciare la famiglia finché sono presenti altri membri. Elimina prima la famiglia o trasferisci la proprietà.',
        'only_owner_can_delete' => 'Solo il proprietario può eliminare la famiglia',
        'only_owner_can_regenerate' => 'Solo il proprietario può rigenerare il codice di adesione',
    ],

    // Settings
    'settings' => [
        'title' => 'Impostazioni',
        'name' => 'Nome',
        'email' => 'E-mail',
        'preferred_language' => 'Lingua preferita',
        'save_changes' => 'Salva modifiche',
        'cancel' => 'Annulla',
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

    // Contact Form
    'contact' => [
        'title' => 'Contattaci',
        'description' => 'Hai una domanda, un suggerimento o hai trovato un bug? Ci piacerebbe sentirti! Compila il modulo qui sotto e ti risponderemo il prima possibile.',
        'category_label' => 'Categoria',
        'category_select' => 'Seleziona una categoria',
        'category_support' => 'Supporto e Aiuto',
        'category_feature' => 'Richiesta di funzionalità',
        'category_bug' => 'Segnalazione bug',
        'category_privacy' => 'Privacy e Dati',
        'name_label' => 'Il tuo nome',
        'name_placeholder' => 'Mario Rossi',
        'email_label' => 'La tua email',
        'email_placeholder' => 'tua.email@esempio.it',
        'message_label' => 'Il tuo messaggio',
        'message_placeholder' => 'Descrivi la tua domanda, suggerimento o problema in dettaglio...',
        'message_help' => 'Minimo 10 caratteri, massimo 5000 caratteri',
        'privacy_text' => 'Ho letto e accetto l\'<a href=":url" class="text-blue-600 hover:text-blue-800 underline" target="_blank">Informativa sulla privacy</a>',
        'submit_button' => 'Invia messaggio',
        'back_home' => 'Torna alla home',
        'success_message' => 'Grazie per il tuo messaggio! Ti risponderemo il prima possibile.',
        'error_message' => 'Si è verificato un errore durante l\'invio del messaggio. Riprova più tardi o contattaci direttamente via email.',
        'turnstile_required' => 'Completa la verifica di sicurezza.',
        'turnstile_failed' => 'Verifica di sicurezza fallita. Riprova.',
    ],
];
