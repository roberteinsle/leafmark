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
                    <div class="mt-5 max-w-2xl mx-auto sm:flex sm:justify-center md:mt-8">
                        <div class="rounded-md shadow flex-1 sm:max-w-xs">
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-12 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-16 whitespace-nowrap" x-text="t('start_tracking')"></a>
                        </div>
                        <div class="mt-3 rounded-md shadow flex-1 sm:max-w-xs sm:mt-0 sm:ml-3">
                            <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-12 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-16" x-text="t('sign_in')"></a>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-20">
                    <h3 class="text-3xl font-bold text-gray-900 text-center mb-12" x-text="t('features_title')"></h3>
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
                                    <div class="text-4xl mb-4">🔍</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature2_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature2_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🏷️</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature3_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature3_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">📊</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature4_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature4_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🎯</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature5_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature5_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">👥</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature6_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature6_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🌍</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature7_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature7_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">📚</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature8_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature8_desc')"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">⭐</div>
                                    <h3 class="text-lg font-medium text-gray-900" x-text="t('feature9_title')"></h3>
                                    <p class="mt-2 text-sm text-gray-500" x-text="t('feature9_desc')"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center space-y-4">
                    <div class="flex space-x-6 text-sm">
                        <a href="/impressum" class="text-gray-600 hover:text-gray-900" x-text="t('impressum')"></a>
                        <span class="text-gray-400">·</span>
                        <a href="/datenschutz" class="text-gray-600 hover:text-gray-900" x-text="t('privacy')"></a>
                        <span class="text-gray-400">·</span>
                        <a href="/kontakt" class="text-gray-600 hover:text-gray-900" x-text="t('contact')"></a>
                    </div>
                    <p class="text-center text-gray-500 text-sm" x-html="t('footer')"></p>
                </div>
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
                        start_tracking: 'Register now for free',
                        sign_in: 'Sign In',
                        features_title: 'Everything you need to manage your reading',
                        feature1_title: 'Track Reading Progress',
                        feature1_desc: 'Keep track of which books you\'re reading, want to read, or have finished with detailed progress tracking.',
                        feature2_title: 'Smart Book Import',
                        feature2_desc: 'Search and import book details from Google Books, Open Library, Amazon, and BookBrainz automatically.',
                        feature3_title: 'Organize with Tags',
                        feature3_desc: 'Create custom colored tags to organize your books by genre, mood, or any category you like.',
                        feature4_title: 'Page Progress Tracking',
                        feature4_desc: 'Track your reading page by page and see beautiful graphs of your progress over time.',
                        feature5_title: 'Reading Challenges',
                        feature5_desc: 'Set yearly reading goals and track your progress with monthly achievements and statistics.',
                        feature6_title: 'Multi-User Support',
                        feature6_desc: 'Perfect for families, book clubs, or organizations with individual collections and admin controls.',
                        feature7_title: 'Multi-Language',
                        feature7_desc: 'Available in English, German, French, Italian, Spanish, and Polish with language-aware search.',
                        feature8_title: 'Series Tracking',
                        feature8_desc: 'Organize your books by series and track your position in multi-volume collections.',
                        feature9_title: 'Ratings & Reviews',
                        feature9_desc: 'Rate books with stars and write personal reviews to remember your thoughts.',
                        impressum: 'Imprint',
                        privacy: 'Privacy',
                        contact: 'Contact',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Made with ❤️ in Hamburg'
                    },
                    de: {
                        title: 'Leafmark - Verfolge deine Leserreise',
                        login: 'Anmelden',
                        get_started: 'Jetzt starten',
                        hero_title: 'Verfolge deine Leserreise',
                        hero_subtitle: 'Organisiere deine Buchsammlung, verfolge deinen Lesefortschritt und entdecke dein nächstes Lieblingsbuch mit Leafmark.',
                        start_tracking: 'Jetzt kostenlos registrieren',
                        sign_in: 'Anmelden',
                        features_title: 'Alles, was du zum Verwalten deiner Lektüre brauchst',
                        feature1_title: 'Lesefortschritt verfolgen',
                        feature1_desc: 'Behalte den Überblick, welche Bücher du liest, lesen möchtest oder beendet hast mit detaillierter Fortschrittsverfolgung.',
                        feature2_title: 'Intelligenter Buchimport',
                        feature2_desc: 'Suche und importiere Buchdetails automatisch von Google Books, Open Library, Amazon und BookBrainz.',
                        feature3_title: 'Mit Tags organisieren',
                        feature3_desc: 'Erstelle farbige Tags, um deine Bücher nach Genre, Stimmung oder jeder beliebigen Kategorie zu organisieren.',
                        feature4_title: 'Seitenfortschritt tracken',
                        feature4_desc: 'Verfolge dein Lesen Seite für Seite und sieh dir schöne Diagramme deines Fortschritts im Zeitverlauf an.',
                        feature5_title: 'Lese-Challenges',
                        feature5_desc: 'Setze dir jährliche Leseziele und verfolge deinen Fortschritt mit monatlichen Erfolgen und Statistiken.',
                        feature6_title: 'Multi-User-Unterstützung',
                        feature6_desc: 'Perfekt für Familien, Buchclubs oder Organisationen mit individuellen Sammlungen und Admin-Kontrollen.',
                        feature7_title: 'Mehrsprachig',
                        feature7_desc: 'Verfügbar in Englisch, Deutsch, Französisch, Italienisch, Spanisch und Polnisch mit sprachbewusster Suche.',
                        feature8_title: 'Serien-Tracking',
                        feature8_desc: 'Organisiere deine Bücher nach Serien und verfolge deine Position in mehrbändigen Sammlungen.',
                        feature9_title: 'Bewertungen & Rezensionen',
                        feature9_desc: 'Bewerte Bücher mit Sternen und schreibe persönliche Rezensionen, um deine Gedanken festzuhalten.',
                        impressum: 'Impressum',
                        privacy: 'Datenschutz',
                        contact: 'Kontakt',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Made with ❤️ in Hamburg'
                    },
                    es: {
                        title: 'Leafmark - Sigue tu viaje de lectura',
                        login: 'Iniciar sesión',
                        get_started: 'Comenzar',
                        hero_title: 'Sigue tu viaje de lectura',
                        hero_subtitle: 'Organiza tu colección de libros, sigue tu progreso de lectura y descubre tu próximo libro favorito con Leafmark.',
                        start_tracking: 'Regístrate ahora gratis',
                        sign_in: 'Iniciar sesión',
                        features_title: 'Todo lo que necesitas para gestionar tu lectura',
                        feature1_title: 'Seguimiento del progreso',
                        feature1_desc: 'Lleva un registro de qué libros estás leyendo, quieres leer o has terminado con seguimiento detallado.',
                        feature2_title: 'Importación inteligente',
                        feature2_desc: 'Busca e importa detalles de libros automáticamente desde Google Books, Open Library, Amazon y BookBrainz.',
                        feature3_title: 'Organizar con etiquetas',
                        feature3_desc: 'Crea etiquetas de colores personalizadas para organizar tus libros por género, estado de ánimo o cualquier categoría.',
                        feature4_title: 'Seguimiento de páginas',
                        feature4_desc: 'Rastrea tu lectura página por página y ve gráficos hermosos de tu progreso a lo largo del tiempo.',
                        feature5_title: 'Desafíos de lectura',
                        feature5_desc: 'Establece metas anuales de lectura y rastrea tu progreso con logros mensuales y estadísticas.',
                        feature6_title: 'Soporte multiusuario',
                        feature6_desc: 'Perfecto para familias, clubes de lectura u organizaciones con colecciones individuales y controles de administrador.',
                        feature7_title: 'Multilingüe',
                        feature7_desc: 'Disponible en inglés, alemán, francés, italiano, español y polaco con búsqueda consciente del idioma.',
                        feature8_title: 'Seguimiento de series',
                        feature8_desc: 'Organiza tus libros por series y rastrea tu posición en colecciones de varios volúmenes.',
                        feature9_title: 'Calificaciones y reseñas',
                        feature9_desc: 'Califica libros con estrellas y escribe reseñas personales para recordar tus pensamientos.',
                        impressum: 'Aviso legal',
                        privacy: 'Privacidad',
                        contact: 'Contacto',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Made with ❤️ in Hamburg'
                    },
                    fr: {
                        title: 'Leafmark - Suivez votre parcours de lecture',
                        login: 'Connexion',
                        get_started: 'Commencer',
                        hero_title: 'Suivez votre parcours de lecture',
                        hero_subtitle: 'Organisez votre collection de livres, suivez votre progression de lecture et découvrez votre prochain livre préféré avec Leafmark.',
                        start_tracking: 'Inscrivez-vous gratuitement',
                        sign_in: 'Se connecter',
                        features_title: 'Tout ce dont vous avez besoin pour gérer votre lecture',
                        feature1_title: 'Suivre la progression',
                        feature1_desc: 'Gardez une trace des livres que vous lisez, voulez lire ou avez terminés avec un suivi détaillé.',
                        feature2_title: 'Importation intelligente',
                        feature2_desc: 'Recherchez et importez automatiquement les détails des livres depuis Google Books, Open Library, Amazon et BookBrainz.',
                        feature3_title: 'Organiser avec des tags',
                        feature3_desc: 'Créez des tags colorés personnalisés pour organiser vos livres par genre, humeur ou toute catégorie.',
                        feature4_title: 'Suivi des pages',
                        feature4_desc: 'Suivez votre lecture page par page et visualisez de beaux graphiques de votre progression au fil du temps.',
                        feature5_title: 'Défis de lecture',
                        feature5_desc: 'Définissez des objectifs de lecture annuels et suivez votre progression avec des réalisations mensuelles et des statistiques.',
                        feature6_title: 'Support multi-utilisateurs',
                        feature6_desc: 'Parfait pour les familles, les clubs de lecture ou les organisations avec des collections individuelles et des contrôles administrateur.',
                        feature7_title: 'Multilingue',
                        feature7_desc: 'Disponible en anglais, allemand, français, italien, espagnol et polonais avec recherche adaptée à la langue.',
                        feature8_title: 'Suivi des séries',
                        feature8_desc: 'Organisez vos livres par séries et suivez votre position dans les collections en plusieurs volumes.',
                        feature9_title: 'Notes et critiques',
                        feature9_desc: 'Notez les livres avec des étoiles et écrivez des critiques personnelles pour mémoriser vos pensées.',
                        impressum: 'Mentions légales',
                        privacy: 'Confidentialité',
                        contact: 'Contact',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Made with ❤️ in Hamburg'
                    },
                    it: {
                        title: 'Leafmark - Segui il tuo percorso di lettura',
                        login: 'Accedi',
                        get_started: 'Inizia',
                        hero_title: 'Segui il tuo percorso di lettura',
                        hero_subtitle: 'Organizza la tua collezione di libri, traccia i tuoi progressi di lettura e scopri il tuo prossimo libro preferito con Leafmark.',
                        start_tracking: 'Registrati ora gratis',
                        sign_in: 'Accedi',
                        features_title: 'Tutto ciò di cui hai bisogno per gestire la tua lettura',
                        feature1_title: 'Traccia i progressi',
                        feature1_desc: 'Tieni traccia di quali libri stai leggendo, vuoi leggere o hai finito con un tracciamento dettagliato.',
                        feature2_title: 'Importazione intelligente',
                        feature2_desc: 'Cerca e importa automaticamente i dettagli dei libri da Google Books, Open Library, Amazon e BookBrainz.',
                        feature3_title: 'Organizza con tag',
                        feature3_desc: 'Crea tag colorati personalizzati per organizzare i tuoi libri per genere, umore o qualsiasi categoria.',
                        feature4_title: 'Tracciamento pagine',
                        feature4_desc: 'Traccia la tua lettura pagina per pagina e visualizza bellissimi grafici del tuo progresso nel tempo.',
                        feature5_title: 'Sfide di lettura',
                        feature5_desc: 'Imposta obiettivi di lettura annuali e traccia il tuo progresso con risultati mensili e statistiche.',
                        feature6_title: 'Supporto multi-utente',
                        feature6_desc: 'Perfetto per famiglie, club del libro o organizzazioni con collezioni individuali e controlli amministrativi.',
                        feature7_title: 'Multilingue',
                        feature7_desc: 'Disponibile in inglese, tedesco, francese, italiano, spagnolo e polacco con ricerca consapevole della lingua.',
                        feature8_title: 'Tracciamento serie',
                        feature8_desc: 'Organizza i tuoi libri per serie e traccia la tua posizione nelle collezioni multi-volume.',
                        feature9_title: 'Valutazioni e recensioni',
                        feature9_desc: 'Valuta i libri con le stelle e scrivi recensioni personali per ricordare i tuoi pensieri.',
                        impressum: 'Note legali',
                        privacy: 'Privacy',
                        contact: 'Contatto',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Made with ❤️ in Hamburg'
                    },
                    pl: {
                        title: 'Leafmark - Śledź swoją podróż czytelniczą',
                        login: 'Zaloguj się',
                        get_started: 'Rozpocznij',
                        hero_title: 'Śledź swoją podróż czytelniczą',
                        hero_subtitle: 'Organizuj swoją kolekcję książek, śledź postępy w czytaniu i odkryj swoją następną ulubioną książkę z Leafmark.',
                        start_tracking: 'Zarejestruj się teraz za darmo',
                        sign_in: 'Zaloguj się',
                        features_title: 'Wszystko, czego potrzebujesz do zarządzania swoim czytaniem',
                        feature1_title: 'Śledź postępy',
                        feature1_desc: 'Śledź, które książki czytasz, chcesz przeczytać lub ukończyłeś ze szczegółowym śledzeniem.',
                        feature2_title: 'Inteligentny import',
                        feature2_desc: 'Wyszukuj i automatycznie importuj szczegóły książek z Google Books, Open Library, Amazon i BookBrainz.',
                        feature3_title: 'Organizuj za pomocą tagów',
                        feature3_desc: 'Twórz kolorowe niestandardowe tagi, aby organizować swoje książki według gatunku, nastroju lub dowolnej kategorii.',
                        feature4_title: 'Śledzenie stron',
                        feature4_desc: 'Śledź swoje czytanie strona po stronie i przeglądaj piękne wykresy swojego postępu w czasie.',
                        feature5_title: 'Wyzwania czytelnicze',
                        feature5_desc: 'Ustaw roczne cele czytelnicze i śledź swoje postępy z miesięcznymi osiągnięciami i statystykami.',
                        feature6_title: 'Wsparcie wielu użytkowników',
                        feature6_desc: 'Idealne dla rodzin, klubów książki lub organizacji z indywidualnymi kolekcjami i kontrolami administratora.',
                        feature7_title: 'Wielojęzyczny',
                        feature7_desc: 'Dostępny w języku angielskim, niemieckim, francuskim, włoskim, hiszpańskim i polskim z wyszukiwaniem świadomym języka.',
                        feature8_title: 'Śledzenie serii',
                        feature8_desc: 'Organizuj swoje książki według serii i śledź swoją pozycję w kolekcjach wielotomowych.',
                        feature9_title: 'Oceny i recenzje',
                        feature9_desc: 'Oceniaj książki gwiazdkami i pisz osobiste recenzje, aby zapamiętać swoje myśli.',
                        impressum: 'Nota prawna',
                        privacy: 'Prywatność',
                        contact: 'Kontakt',
                        footer: '&copy; ' + new Date().getFullYear() + ' Leafmark. Made with ❤️ in Hamburg'
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
