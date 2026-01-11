@extends('layouts.app')

@section('title', 'Kontakt')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white shadow-sm rounded-lg px-6 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Kontakt</h1>

        <div class="prose prose-blue max-w-none">
            <p class="text-gray-700 mb-6">
                Hast du Fragen, Anregungen oder Feedback zu Leafmark? Wir freuen uns über deine Nachricht!
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Kontaktdaten</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Name</p>
                        <p class="text-gray-900">Robert Einsle</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">E-Mail</p>
                        <p class="text-gray-900">
                            <a href="mailto:robert@einsle.com" class="text-blue-600 hover:text-blue-800">
                                robert@einsle.com
                            </a>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Standort</p>
                        <p class="text-gray-900">Hamburg, Deutschland</p>
                    </div>
                </div>
            </div>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Häufige Anfragen</h2>

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">📚 Support & Hilfe</h3>
                    <p class="text-gray-700">
                        Wenn du Fragen zur Nutzung von Leafmark hast oder technische Probleme meldest möchtest, schreib uns einfach eine E-Mail. Wir helfen dir gerne weiter!
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">💡 Feature-Wünsche</h3>
                    <p class="text-gray-700">
                        Du hast eine Idee für eine neue Funktion oder Verbesserung? Wir freuen uns über deine Vorschläge und Anregungen zur Weiterentwicklung von Leafmark.
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">🐛 Fehler melden</h3>
                    <p class="text-gray-700">
                        Wenn du einen Fehler oder ein Problem entdeckt hast, lass es uns wissen. Bitte beschreibe möglichst genau, was passiert ist und unter welchen Umständen der Fehler aufgetreten ist.
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">🔒 Datenschutz & Sicherheit</h3>
                    <p class="text-gray-700">
                        Fragen zu Datenschutz und Sicherheit sind uns wichtig. Kontaktiere uns bei Bedenken oder Fragen zu deinen Daten.
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">💌 Schreib uns eine E-Mail</h3>
                <p class="text-gray-700 mb-4">
                    Die schnellste Möglichkeit, uns zu erreichen, ist per E-Mail an:
                </p>
                <a href="mailto:robert@einsle.com" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    E-Mail senden
                </a>
            </div>

            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-gray-700">
                    <strong>Hinweis:</strong> Bitte beachte, dass wir keine Support-Anfragen per Telefon entgegennehmen. E-Mail ist der beste Weg, um mit uns in Kontakt zu treten.
                </p>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="/" class="text-blue-600 hover:text-blue-800 font-medium">← Zurück zur Startseite</a>
        </div>
    </div>
</div>
@endsection
