<?php

return [
    // Navigation
    'nav' => [
        'books' => 'Books',
        'tags' => 'Tags',
        'add_book' => 'Add Book',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register',
    ],

    // Books
    'books' => [
        'title' => 'My Books',
        'add_new' => 'Add a New Book',
        'search_placeholder' => 'Enter ISBN, title, or author...',
        'search' => 'Search',
        'no_results' => 'No books found',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
        'back_to_books' => 'Back to Books',
        'back_to_book' => 'Back to Book',

        // Book details
        'title_label' => 'Title',
        'author' => 'Author',
        'series' => 'Series',
        'series_position' => 'Series Position',
        'isbn' => 'ISBN',
        'isbn13' => 'ISBN-13',
        'publisher' => 'Publisher',
        'published_date' => 'Published Date',
        'description' => 'Description',
        'page_count' => 'Page Count',
        'language' => 'Language',
        'format' => 'Format',
        'purchase_date' => 'Purchase Date',
        'purchase_price' => 'Purchase Price',
        'currency' => 'Currency',
        'current_page' => 'Current Page',

        // Status
        'status' => 'Status',
        'want_to_read' => 'Want to Read',
        'currently_reading' => 'Currently Reading',
        'read' => 'Read',

        // Reading progress
        'reading_progress' => 'Reading Progress',
        'update_progress' => 'Update Progress',
        'started_reading' => 'Started Reading',
        'finished_reading' => 'Finished Reading',

        // Covers
        'cover_images' => 'Cover Images',
        'current_covers' => 'Current covers (hover to delete or set as default)',
        'upload_new_cover' => 'Upload New Cover',
        'upload_covers' => 'Upload Cover(s)',
        'click_to_upload' => 'Click to upload',
        'drag_and_drop' => 'or drag and drop',
        'image_requirements' => 'JPEG, PNG, GIF, or WebP. Max 25MB. You can select multiple files.',
        'default' => 'Default',
        'set_default' => 'Set Default',
        'delete_cover' => 'Delete Cover',

        // Search providers
        'provider' => 'Provider',
        'openlibrary' => 'Open Library',
        'google_books' => 'Google Books',
        'all_sources' => 'All Sources',

        // Messages
        'delete_confirm' => 'Are you sure you want to delete this book?',
        'delete_cover_confirm' => 'Are you sure you want to delete this cover?',
        'book_added' => 'Book added successfully!',
        'book_updated' => 'Book updated successfully!',
        'book_deleted' => 'Book deleted successfully!',
        'cover_uploaded' => ':count cover(s) uploaded successfully!',
        'cover_deleted' => 'Cover deleted successfully!',
        'primary_updated' => 'Primary cover updated!',
    ],

    // Settings
    'settings' => [
        'title' => 'Settings',
        'subtitle' => 'Manage your account preferences',
        'manage_preferences' => 'Manage your account preferences',
        'name' => 'Name',
        'email' => 'Email',
        'preferred_language' => 'Preferred Language',
        'language_help' => 'This will be used as the default language for book searches and UI',
        'google_books_api_key' => 'Google Books API Key',
        'api_key_placeholder' => 'Enter your Google Books API key (optional)',
        'api_key_help' => 'Optional: Provide your own API key for Google Books searches.',
        'get_api_key' => 'Get an API key',
        'save_changes' => 'Save Changes',
        'cancel' => 'Cancel',
        'settings_updated' => 'Settings updated successfully!',
    ],

    // Forms
    'forms' => [
        'required' => 'Required',
        'optional' => 'Optional',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'create' => 'Create',
        'update' => 'Update',
        'submit' => 'Submit',
        'search' => 'Search',
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
        'yes' => 'Yes',
        'no' => 'No',
        'loading' => 'Loading...',
        'error' => 'Error',
        'success' => 'Success',
        'warning' => 'Warning',
        'info' => 'Info',
    ],
];
