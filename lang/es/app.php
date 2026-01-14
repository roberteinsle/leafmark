<?php

return [
    // Navigation
    'nav' => [
        'books' => 'Libros',
        'tags' => 'Etiquetas',
        'challenge' => 'Desafío',
        'family' => 'Familia',
        'add_book' => 'Agregar Libro',
        'settings' => 'Configuración',
        'logout' => 'Cerrar sesión',
        'login' => 'Iniciar sesión',
        'register' => 'Registrarse',
    ],

    // Books
    'books' => [
        'title' => 'Mis Libros',
        'add_new' => 'Agregar un Nuevo Libro',
        'add_new_book' => 'Agregar Nuevo Libro',
        'search_for_book' => 'Buscar un libro o agregarlo manualmente',
        'search_for_books' => 'Buscar libros',
        'search_placeholder' => 'Ingrese ISBN, título o autor...',
        'search' => 'Buscar',
        'search_tip' => 'Consejo: La búsqueda detecta automáticamente ISBNs, nombres de autores y títulos de libros',
        'search_by_identifier' => 'También puede buscar por identificador:',
        'no_results' => 'No se encontraron libros',
        'no_results_found' => 'No se encontraron resultados',
        'no_results_message' => 'No pudimos encontrar libros que coincidan con su búsqueda. Puede agregar el libro manualmente a continuación.',
        'search_results' => 'Resultados de Búsqueda',
        'add_to_library' => 'Agregar a la Biblioteca',
        'add_book_manually' => 'Agregar Libro Manualmente',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'view' => 'Ver',
        'back_to_books' => 'Volver a Libros',
        'back_to_book' => 'Volver al Libro',
        'by_author' => 'por',
        'pages' => 'Páginas',

        // Book details
        'title_label' => 'Título',
        'author' => 'Autor',
        'series' => 'Serie',
        'publisher' => 'Editorial',
        'description' => 'Descripción',
        'status' => 'Estado',
        'want_to_read' => 'Quiero leer',
        'currently_reading' => 'Leyendo actualmente',
        'read' => 'Leído',
    ],

    // Family
    'family' => [
        'title' => 'Familia',
        'description' => 'Conéctate con familiares o amigos y comparte tus aventuras de lectura',
        'no_family_title' => 'Aún no estás en una familia',
        'no_family_description' => 'Crea una nueva familia o únete a una existente para compartir tus aventuras de lectura con otros.',
        'create_family' => 'Crear familia',
        'join_existing' => 'Unirse a familia existente',
        'back_to_family' => 'Volver a la familia',

        // Create Family
        'create_description' => 'Crea una nueva familia e invita a amigos o miembros de la familia',
        'family_name' => 'Nombre de la familia',
        'family_name_placeholder' => 'ej. Familia García, Club de lectura Madrid',
        'family_name_help' => 'Elige un nombre que describa tu grupo',
        'what_happens' => '¿Qué sucede?',
        'create_info_1' => 'Serás automáticamente el propietario de la familia',
        'create_info_2' => 'Recibirás un código de unión único',
        'create_info_3' => 'Cada miembro mantiene su propia colección de libros',

        // Join Family
        'join_description' => 'Ingresa el código de unión para unirte a una familia existente',
        'enter_join_code' => 'Código de unión',
        'join_code_help' => 'El código consta de 8 letras',
        'join_note' => 'Nota',
        'join_note_text' => 'Solo puedes ser miembro de una familia. Tu colección de libros permanece privada.',

        // Family Info
        'owner' => 'Propietario',
        'member_count' => '{1} 1 Miembro|[2,*] :count Miembros',
        'books_count' => '{1} 1 Libro|[2,*] :count Libros',
        'members' => 'Miembros',
        'join_code' => 'Código de unión',
        'copy_code' => 'Copiar código',
        'copied' => '¡Copiado!',
        'share_code_info' => 'Comparte este código con personas que quieran unirse a tu familia.',
        'regenerate_code' => 'Generar nuevo código',
        'regenerate_confirm' => '¿Realmente quieres generar un nuevo código de unión? El código anterior se volverá inválido.',

        // Actions
        'leave_family_button' => 'Abandonar familia',
        'join_family_button' => 'Unirse a familia',
        'delete_family' => 'Eliminar familia',
        'leave_confirm' => '¿Realmente quieres abandonar la familia?',
        'delete_confirm' => '¿Realmente quieres eliminar la familia? Todos los miembros perderán su membresía familiar.',

        // Messages
        'family_created' => '¡Familia creada con éxito!',
        'joined_family' => '¡Te has unido a la familia ":name"!',
        'left_family' => 'Has abandonado la familia',
        'family_deleted' => 'Familia eliminada con éxito',
        'code_regenerated' => 'Se ha generado un nuevo código de unión',
        'already_in_family' => 'Ya eres miembro de una familia',
        'already_owns_family' => 'Ya eres propietario de una familia',
        'not_in_family' => 'No estás en una familia',
        'invalid_code' => 'Código de unión inválido',
        'owner_cannot_leave' => 'Como propietario, no puedes abandonar la familia mientras haya otros miembros presentes. Elimina primero la familia o transfiere la propiedad.',
        'only_owner_can_delete' => 'Solo el propietario puede eliminar la familia',
        'only_owner_can_regenerate' => 'Solo el propietario puede regenerar el código de unión',
    ],

    // Settings
    'settings' => [
        'title' => 'Configuración',
        'name' => 'Nombre',
        'email' => 'Correo electrónico',
        'preferred_language' => 'Idioma preferido',
        'save_changes' => 'Guardar cambios',
        'cancel' => 'Cancelar',
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
];
