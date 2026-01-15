<?php

return [
    // Navigation
    'nav' => [
        'books' => 'Książki',
        'tags' => 'Tagi',
        'challenge' => 'Wyzwanie',
        'family' => 'Rodzina',
        'add_book' => 'Dodaj Książkę',
        'settings' => 'Ustawienia',
        'logout' => 'Wyloguj',
        'login' => 'Zaloguj',
        'register' => 'Zarejestruj się',
    ],

    // Books
    'books' => [
        'title' => 'Moje Książki',
        'add_new' => 'Dodaj Nową Książkę',
        'add_new_book' => 'Dodaj Nową Książkę',
        'search_for_book' => 'Wyszukaj książkę lub dodaj ją ręcznie',
        'search_for_books' => 'Wyszukaj książki',
        'search_placeholder' => 'Wprowadź ISBN, tytuł lub autora...',
        'search' => 'Szukaj',
        'search_tip' => 'Wskazówka: Wyszukiwanie automatycznie wykrywa numery ISBN, nazwiska autorów i tytuły książek',
        'search_by_identifier' => 'Możesz również wyszukiwać według identyfikatora:',
        'no_results' => 'Nie znaleziono książek',
        'no_results_found' => 'Nie znaleziono wyników',
        'no_results_message' => 'Nie mogliśmy znaleźć książek pasujących do Twojego wyszukiwania. Możesz dodać książkę ręcznie poniżej.',
        'search_results' => 'Wyniki Wyszukiwania',
        'add_to_library' => 'Dodaj do Biblioteki',
        'add_book_manually' => 'Dodaj Książkę Ręcznie',
        'edit' => 'Edytuj',
        'delete' => 'Usuń',
        'view' => 'Zobacz',
        'back_to_books' => 'Powrót do Książek',
        'back_to_book' => 'Powrót do Książki',
        'by_author' => 'przez',
        'pages' => 'Strony',

        // Book details
        'title_label' => 'Tytuł',
        'author' => 'Autor',
        'series' => 'Seria',
        'publisher' => 'Wydawca',
        'description' => 'Opis',
        'status' => 'Status',
        'want_to_read' => 'Chcę przeczytać',
        'currently_reading' => 'Obecnie czytam',
        'read' => 'Przeczytane',
    ],

    // Family
    'family' => [
        'title' => 'Rodzina',
        'description' => 'Połącz się z rodziną lub przyjaciółmi i dziel się swoimi przygodami z czytaniem',
        'no_family_title' => 'Nie jesteś jeszcze w rodzinie',
        'no_family_description' => 'Utwórz nową rodzinę lub dołącz do istniejącej, aby dzielić się swoimi przygodami z czytaniem z innymi.',
        'create_family' => 'Utwórz rodzinę',
        'join_existing' => 'Dołącz do istniejącej rodziny',
        'back_to_family' => 'Powrót do rodziny',

        // Create Family
        'create_description' => 'Utwórz nową rodzinę i zaproś przyjaciół lub członków rodziny',
        'family_name' => 'Nazwa rodziny',
        'family_name_placeholder' => 'np. Rodzina Kowalskich, Klub książki Warszawa',
        'family_name_help' => 'Wybierz nazwę opisującą Twoją grupę',
        'what_happens' => 'Co się stanie?',
        'create_info_1' => 'Automatycznie staniesz się właścicielem rodziny',
        'create_info_2' => 'Otrzymasz unikalny kod dołączenia',
        'create_info_3' => 'Każdy członek zachowuje własną kolekcję książek',

        // Join Family
        'join_description' => 'Wprowadź kod dołączenia, aby dołączyć do istniejącej rodziny',
        'enter_join_code' => 'Kod dołączenia',
        'join_code_help' => 'Kod składa się z 8 liter',
        'join_note' => 'Uwaga',
        'join_note_text' => 'Możesz być członkiem tylko jednej rodziny. Twoja kolekcja książek pozostaje prywatna.',

        // Family Info
        'owner' => 'Właściciel',
        'member_count' => '{1} 1 Członek|[2,4] :count Członków|[5,*] :count Członków',
        'books_count' => '{1} 1 Książka|[2,4] :count Książki|[5,*] :count Książek',
        'members' => 'Członkowie',
        'join_code' => 'Kod dołączenia',
        'copy_code' => 'Kopiuj kod',
        'copied' => 'Skopiowano!',
        'share_code_info' => 'Udostępnij ten kod osobom, które chcą dołączyć do Twojej rodziny.',
        'regenerate_code' => 'Wygeneruj nowy kod',
        'regenerate_confirm' => 'Czy na pewno chcesz wygenerować nowy kod dołączenia? Stary kod stanie się nieważny.',

        // Actions
        'leave_family_button' => 'Opuść rodzinę',
        'join_family_button' => 'Dołącz do rodziny',
        'delete_family' => 'Usuń rodzinę',
        'leave_confirm' => 'Czy na pewno chcesz opuścić rodzinę?',
        'delete_confirm' => 'Czy na pewno chcesz usunąć rodzinę? Wszyscy członkowie stracą swoje członkostwo rodzinne.',

        // Messages
        'family_created' => 'Rodzina została pomyślnie utworzona!',
        'joined_family' => 'Dołączyłeś do rodziny ":name"!',
        'left_family' => 'Opuściłeś rodzinę',
        'family_deleted' => 'Rodzina została pomyślnie usunięta',
        'code_regenerated' => 'Wygenerowano nowy kod dołączenia',
        'already_in_family' => 'Jesteś już członkiem rodziny',
        'already_owns_family' => 'Jesteś już właścicielem rodziny',
        'not_in_family' => 'Nie jesteś w rodzinie',
        'invalid_code' => 'Nieprawidłowy kod dołączenia',
        'owner_cannot_leave' => 'Jako właściciel nie możesz opuścić rodziny, dopóki są obecni inni członkowie. Usuń najpierw rodzinę lub przekaż własność.',
        'only_owner_can_delete' => 'Tylko właściciel może usunąć rodzinę',
        'only_owner_can_regenerate' => 'Tylko właściciel może wygenerować nowy kod dołączenia',
    ],

    // Settings
    'settings' => [
        'title' => 'Ustawienia',
        'name' => 'Imię',
        'email' => 'E-mail',
        'preferred_language' => 'Preferowany język',
        'save_changes' => 'Zapisz zmiany',
        'cancel' => 'Anuluj',
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
        'title' => 'Kontakt',
        'description' => 'Masz pytanie, sugestię lub znalazłeś błąd? Chętnie od Ciebie usłyszymy! Wypełnij poniższy formularz, a odpowiemy tak szybko, jak to możliwe.',
        'category_label' => 'Kategoria',
        'category_select' => 'Wybierz kategorię',
        'category_support' => 'Wsparcie i Pomoc',
        'category_feature' => 'Prośba o funkcję',
        'category_bug' => 'Zgłoszenie błędu',
        'category_privacy' => 'Prywatność i Dane',
        'name_label' => 'Twoje imię',
        'name_placeholder' => 'Jan Kowalski',
        'email_label' => 'Twój email',
        'email_placeholder' => 'twoj.email@przyklad.pl',
        'message_label' => 'Twoja wiadomość',
        'message_placeholder' => 'Opisz szczegółowo swoje pytanie, sugestię lub problem...',
        'message_help' => 'Minimum 10 znaków, maksimum 5000 znaków',
        'privacy_text' => 'Przeczytałem i akceptuję <a href=":url" class="text-blue-600 hover:text-blue-800 underline" target="_blank">Politykę prywatności</a>',
        'submit_button' => 'Wyślij wiadomość',
        'back_home' => 'Powrót do strony głównej',
        'success_message' => 'Dziękujemy za wiadomość! Odpowiemy tak szybko, jak to możliwe.',
        'error_message' => 'Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie później lub skontaktuj się z nami bezpośrednio przez email.',
        'turnstile_required' => 'Proszę ukończyć weryfikację bezpieczeństwa.',
        'turnstile_failed' => 'Weryfikacja bezpieczeństwa nie powiodła się. Spróbuj ponownie.',
    ],
];
