<?php

return [
    // Navigation
    'nav' => [
        'books' => 'Livres',
        'tags' => 'Étiquettes',
        'challenge' => 'Défi',
        'family' => 'Famille',
        'add_book' => 'Ajouter un Livre',
        'settings' => 'Paramètres',
        'logout' => 'Déconnexion',
        'login' => 'Connexion',
        'register' => 'S\'inscrire',
    ],

    // Books
    'books' => [
        'title' => 'Mes Livres',
        'add_new' => 'Ajouter un Nouveau Livre',
        'add_new_book' => 'Ajouter un Nouveau Livre',
        'search_for_book' => 'Rechercher un livre ou l\'ajouter manuellement',
        'search_for_books' => 'Rechercher des livres',
        'search_placeholder' => 'Entrez ISBN, titre ou auteur...',
        'search' => 'Rechercher',
        'search_tip' => 'Conseil : La recherche détecte automatiquement les ISBN, les noms d\'auteurs et les titres de livres',
        'search_by_identifier' => 'Vous pouvez également rechercher par identifiant :',
        'no_results' => 'Aucun livre trouvé',
        'no_results_found' => 'Aucun résultat trouvé',
        'no_results_message' => 'Nous n\'avons trouvé aucun livre correspondant à votre recherche. Vous pouvez ajouter le livre manuellement ci-dessous.',
        'search_results' => 'Résultats de Recherche',
        'add_to_library' => 'Ajouter à la Bibliothèque',
        'add_book_manually' => 'Ajouter un Livre Manuellement',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'view' => 'Voir',
        'back_to_books' => 'Retour aux Livres',
        'back_to_book' => 'Retour au Livre',
        'by_author' => 'par',
        'pages' => 'Pages',

        // Book details
        'title_label' => 'Titre',
        'author' => 'Auteur',
        'series' => 'Série',
        'publisher' => 'Éditeur',
        'description' => 'Description',
        'status' => 'Statut',
        'want_to_read' => 'À lire',
        'currently_reading' => 'En cours de lecture',
        'read' => 'Lu',
    ],

    // Family
    'family' => [
        'title' => 'Famille',
        'description' => 'Connectez-vous avec la famille ou des amis et partagez vos aventures de lecture',
        'no_family_title' => 'Vous n\'êtes pas encore dans une famille',
        'no_family_description' => 'Créez une nouvelle famille ou rejoignez une famille existante pour partager vos aventures de lecture avec d\'autres.',
        'create_family' => 'Créer une famille',
        'join_existing' => 'Rejoindre une famille existante',
        'back_to_family' => 'Retour à la famille',

        // Create Family
        'create_description' => 'Créez une nouvelle famille et invitez des amis ou des membres de la famille',
        'family_name' => 'Nom de la famille',
        'family_name_placeholder' => 'ex. Famille Dupont, Club de lecture Paris',
        'family_name_help' => 'Choisissez un nom qui décrit votre groupe',
        'what_happens' => 'Que se passe-t-il?',
        'create_info_1' => 'Vous deviendrez automatiquement propriétaire de la famille',
        'create_info_2' => 'Vous recevrez un code d\'adhésion unique',
        'create_info_3' => 'Chaque membre conserve sa propre collection de livres',

        // Join Family
        'join_description' => 'Entrez le code d\'adhésion pour rejoindre une famille existante',
        'enter_join_code' => 'Code d\'adhésion',
        'join_code_help' => 'Le code se compose de 8 lettres',
        'join_note' => 'Remarque',
        'join_note_text' => 'Vous ne pouvez être membre que d\'une seule famille. Votre collection de livres reste privée.',

        // Family Info
        'owner' => 'Propriétaire',
        'member_count' => '{1} 1 Membre|[2,*] :count Membres',
        'books_count' => '{1} 1 Livre|[2,*] :count Livres',
        'members' => 'Membres',
        'join_code' => 'Code d\'adhésion',
        'copy_code' => 'Copier le code',
        'copied' => 'Copié!',
        'share_code_info' => 'Partagez ce code avec les personnes qui souhaitent rejoindre votre famille.',
        'regenerate_code' => 'Générer un nouveau code',
        'regenerate_confirm' => 'Voulez-vous vraiment générer un nouveau code d\'adhésion? L\'ancien code deviendra invalide.',

        // Actions
        'leave_family_button' => 'Quitter la famille',
        'join_family_button' => 'Rejoindre la famille',
        'delete_family' => 'Supprimer la famille',
        'leave_confirm' => 'Voulez-vous vraiment quitter la famille?',
        'delete_confirm' => 'Voulez-vous vraiment supprimer la famille? Tous les membres perdront leur adhésion familiale.',

        // Messages
        'family_created' => 'Famille créée avec succès!',
        'joined_family' => 'Vous avez rejoint la famille ":name"!',
        'left_family' => 'Vous avez quitté la famille',
        'family_deleted' => 'Famille supprimée avec succès',
        'code_regenerated' => 'Un nouveau code d\'adhésion a été généré',
        'already_in_family' => 'Vous êtes déjà membre d\'une famille',
        'already_owns_family' => 'Vous possédez déjà une famille',
        'not_in_family' => 'Vous n\'êtes pas dans une famille',
        'invalid_code' => 'Code d\'adhésion invalide',
        'owner_cannot_leave' => 'En tant que propriétaire, vous ne pouvez pas quitter la famille tant que d\'autres membres sont présents. Supprimez d\'abord la famille ou transférez la propriété.',
        'only_owner_can_delete' => 'Seul le propriétaire peut supprimer la famille',
        'only_owner_can_regenerate' => 'Seul le propriétaire peut régénérer le code d\'adhésion',
    ],

    // Settings
    'settings' => [
        'title' => 'Paramètres',
        'subtitle' => 'Gérer les préférences de votre compte',
        'manage_preferences' => 'Gérer les préférences de votre compte',
        'name' => 'Nom',
        'email' => 'E-mail',
        'preferred_language' => 'Langue Préférée',
        'language_help' => 'Cela sera utilisé comme langue par défaut pour les recherches de livres et l\'interface',
        'google_books_api_key' => 'Clé API Google Books',
        'api_key_placeholder' => 'Entrez votre clé API Google Books (optionnel)',
        'api_key_help' => 'Optionnel: Fournissez votre propre clé API pour les recherches Google Books.',
        'get_api_key' => 'Obtenir une clé API',
        'save_changes' => 'Enregistrer les modifications',
        'cancel' => 'Annuler',
        'settings_updated' => 'Paramètres mis à jour avec succès!',
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
