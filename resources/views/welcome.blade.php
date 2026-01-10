<!DOCTYPE html>
<html lang="en" x-data="welcomePage()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title x-text="t('title')"></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">📚 {{ config('app.name') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Language Selector -->
                        <select @change="changeLang($event.target.value)" :value="currentLang"
                                class="text-sm border border-gray-300 rounded-md px-2 py-1">
                            <option value="en">English</option>
                            <option value="de">Deutsch</option>
                            <option value="es">Español</option>
                            <option value="fr">Français</option>
                            <option value="it">Italiano</option>
                            <option value="pl">Polski</option>
                        </select>
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900" x-text="t('login')"></a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700" x-text="t('get_started')"></a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-4xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl" x-text="t('hero_title')"></h2>
                    <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl" x-text="t('hero_subtitle')"></p>
                    <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                        <div class="rounded-md shadow">
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10" x-text="t('start_tracking')"></a>
                        </div>
                        <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                            <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10" x-text="t('sign_in')"></a>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-20">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">📖</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature1_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature1_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🏷️</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature2_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature2_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🔍</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature3_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature3_desc')"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500 text-sm" x-html="t('footer')"></p>
            </div>
        </footer>
    </div>

    <script>
        function welcomePage() {
            return {
                currentLang: 'en',
                translations: {
                    en: {
                        title: 'Leafmark - Track Your Reading Journey',
                        login: 'Login',
                        get_started: 'Get Started',
                        hero_title: 'Track Your Reading Journey',
                        hero_subtitle: 'Organize your book collection, track your reading progress, and discover your next favorite book with Leafmark.',
                        start_tracking: 'Start Tracking',
                        sign_in: 'Sign In',
                        feature1_title: 'Track Reading Progress',
                        feature1_desc: 'Keep track of which books you\'re reading, want to read, or have finished.',
                        feature2_title: 'Organize with Tags',
                        feature2_desc: 'Create custom tags to organize your books by genre, mood, or any category you like.',
                        feature3_title: 'Import from APIs',
                        feature3_desc: 'Search and import book details from Google Books, Open Library, and Amazon.',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Built with Laravel.'
                    },
                    de: {
                        title: 'Leafmark - Verfolgen Sie Ihre Leserreise',
                        login: 'Anmelden',
                        get_started: 'Jetzt starten',
                        hero_title: 'Verfolgen Sie Ihre Leserreise',
                        hero_subtitle: 'Organisieren Sie Ihre Buchsammlung, verfolgen Sie Ihren Lesefortschritt und entdecken Sie Ihr nächstes Lieblingsbuch mit Leafmark.',
                        start_tracking: 'Jetzt starten',
                        sign_in: 'Anmelden',
                        feature1_title: 'Lesefortschritt verfolgen',
                        feature1_desc: 'Behalten Sie den Überblick, welche Bücher Sie lesen, lesen möchten oder beendet haben.',
                        feature2_title: 'Mit Tags organisieren',
                        feature2_desc: 'Erstellen Sie benutzerdefinierte Tags, um Ihre Bücher nach Genre, Stimmung oder jeder beliebigen Kategorie zu organisieren.',
                        feature3_title: 'Von APIs importieren',
                        feature3_desc: 'Suchen und importieren Sie Buchdetails von Google Books, Open Library und Amazon.',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Erstellt mit Laravel.'
                    },
                    es: {
                        title: 'Leafmark - Sigue tu viaje de lectura',
                        login: 'Iniciar sesión',
                        get_started: 'Comenzar',
                        hero_title: 'Sigue tu viaje de lectura',
                        hero_subtitle: 'Organiza tu colección de libros, sigue tu progreso de lectura y descubre tu próximo libro favorito con Leafmark.',
                        start_tracking: 'Comenzar a rastrear',
                        sign_in: 'Iniciar sesión',
                        feature1_title: 'Seguimiento del progreso',
                        feature1_desc: 'Lleva un registro de qué libros estás leyendo, quieres leer o has terminado.',
                        feature2_title: 'Organizar con etiquetas',
                        feature2_desc: 'Crea etiquetas personalizadas para organizar tus libros por género, estado de ánimo o cualquier categoría.',
                        feature3_title: 'Importar desde APIs',
                        feature3_desc: 'Busca e importa detalles de libros desde Google Books, Open Library y Amazon.',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Creado con Laravel.'
                    },
                    fr: {
                        title: 'Leafmark - Suivez votre parcours de lecture',
                        login: 'Connexion',
                        get_started: 'Commencer',
                        hero_title: 'Suivez votre parcours de lecture',
                        hero_subtitle: 'Organisez votre collection de livres, suivez votre progression de lecture et découvrez votre prochain livre préféré avec Leafmark.',
                        start_tracking: 'Commencer le suivi',
                        sign_in: 'Se connecter',
                        feature1_title: 'Suivre la progression',
                        feature1_desc: 'Gardez une trace des livres que vous lisez, voulez lire ou avez terminés.',
                        feature2_title: 'Organiser avec des tags',
                        feature2_desc: 'Créez des tags personnalisés pour organiser vos livres par genre, humeur ou toute catégorie.',
                        feature3_title: 'Importer depuis les APIs',
                        feature3_desc: 'Recherchez et importez les détails des livres depuis Google Books, Open Library et Amazon.',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Créé avec Laravel.'
                    },
                    it: {
                        title: 'Leafmark - Segui il tuo percorso di lettura',
                        login: 'Accedi',
                        get_started: 'Inizia',
                        hero_title: 'Segui il tuo percorso di lettura',
                        hero_subtitle: 'Organizza la tua collezione di libri, traccia i tuoi progressi di lettura e scopri il tuo prossimo libro preferito con Leafmark.',
                        start_tracking: 'Inizia a tracciare',
                        sign_in: 'Accedi',
                        feature1_title: 'Traccia i progressi',
                        feature1_desc: 'Tieni traccia di quali libri stai leggendo, vuoi leggere o hai finito.',
                        feature2_title: 'Organizza con tag',
                        feature2_desc: 'Crea tag personalizzati per organizzare i tuoi libri per genere, umore o qualsiasi categoria.',
                        feature3_title: 'Importa da API',
                        feature3_desc: 'Cerca e importa i dettagli dei libri da Google Books, Open Library e Amazon.',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Creato con Laravel.'
                    },
                    pl: {
                        title: 'Leafmark - Śledź swoją podróż czytelniczą',
                        login: 'Zaloguj się',
                        get_started: 'Rozpocznij',
                        hero_title: 'Śledź swoją podróż czytelniczą',
                        hero_subtitle: 'Organizuj swoją kolekcję książek, śledź postępy w czytaniu i odkryj swoją następną ulubioną książkę z Leafmark.',
                        start_tracking: 'Rozpocznij śledzenie',
                        sign_in: 'Zaloguj się',
                        feature1_title: 'Śledź postępy',
                        feature1_desc: 'Śledź, które książki czytasz, chcesz przeczytać lub ukończyłeś.',
                        feature2_title: 'Organizuj za pomocą tagów',
                        feature2_desc: 'Twórz niestandardowe tagi, aby organizować swoje książki według gatunku, nastroju lub dowolnej kategorii.',
                        feature3_title: 'Importuj z API',
                        feature3_desc: 'Wyszukuj i importuj szczegóły książek z Google Books, Open Library i Amazon.',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Zbudowany z Laravel.'
                    }
                },
                init() {
                    // Detect browser language
                    const browserLang = navigator.language || navigator.userLanguage;
                    const langCode = browserLang.split('-')[0];  // Get 'de' from 'de-DE'

                    // Check if we support this language
                    if (this.translations[langCode]) {
                        this.currentLang = langCode;
                    } else {
                        this.currentLang = 'en';  // fallback to English
                    }

                    // Update document title
                    document.title = this.t('title');
                },
                t(key) {
                    return this.translations[this.currentLang][key] || this.translations.en[key] || key;
                },
                changeLang(lang) {
                    this.currentLang = lang;
                    document.title = this.t('title');
                }
            }
        }
    </script>
</body>
</html>
