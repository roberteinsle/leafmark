@extends('layouts.app')

@section('title', 'Datenschutzerklärung')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white shadow-sm rounded-lg px-6 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Datenschutzerklärung</h1>

        <div class="prose prose-blue max-w-none">
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-4">1. Datenschutz auf einen Blick</h2>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Allgemeine Hinweise</h3>
            <p class="text-gray-700 mb-4">
                Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit deinen personenbezogenen Daten passiert, wenn du diese Website besuchst. Personenbezogene Daten sind alle Daten, mit denen du persönlich identifiziert werden kannst. Ausführliche Informationen zum Thema Datenschutz entnimmst du unserer unter diesem Text aufgeführten Datenschutzerklärung.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Datenerfassung auf dieser Website</h3>
            <h4 class="text-base font-semibold text-gray-900 mt-4 mb-2">Wer ist verantwortlich für die Datenerfassung auf dieser Website?</h4>
            <p class="text-gray-700 mb-4">
                Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten kannst du dem Impressum dieser Website entnehmen.
            </p>

            <h4 class="text-base font-semibold text-gray-900 mt-4 mb-2">Wie erfassen wir deine Daten?</h4>
            <p class="text-gray-700 mb-4">
                Deine Daten werden zum einen dadurch erhoben, dass du uns diese mitteilst. Hierbei kann es sich z.B. um Daten handeln, die du in ein Kontaktformular eingibst.
            </p>
            <p class="text-gray-700 mb-4">
                Andere Daten werden automatisch beim Besuch der Website durch unsere IT-Systeme erfasst. Das sind vor allem technische Daten (z.B. Internetbrowser, Betriebssystem oder Uhrzeit des Seitenaufrufs). Die Erfassung dieser Daten erfolgt automatisch, sobald du diese Website betrittst.
            </p>

            <h4 class="text-base font-semibold text-gray-900 mt-4 mb-2">Wofür nutzen wir deine Daten?</h4>
            <p class="text-gray-700 mb-4">
                Die Daten werden erhoben, um eine fehlerfreie Bereitstellung der Website zu gewährleisten und um dir die Funktionen der Anwendung zur Verfügung zu stellen (Buchverwaltung, Lesefortschritt, etc.).
            </p>

            <h4 class="text-base font-semibold text-gray-900 mt-4 mb-2">Welche Rechte hast du bezüglich deiner Daten?</h4>
            <p class="text-gray-700 mb-4">
                Du hast jederzeit das Recht unentgeltlich Auskunft über Herkunft, Empfänger und Zweck deiner gespeicherten personenbezogenen Daten zu erhalten. Du hast außerdem ein Recht, die Berichtigung oder Löschung dieser Daten zu verlangen. Hierzu sowie zu weiteren Fragen zum Thema Datenschutz kannst du dich jederzeit unter der im Impressum angegebenen Adresse an uns wenden.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Hosting</h2>
            <p class="text-gray-700 mb-4">
                Wir hosten die Inhalte unserer Website bei folgendem Anbieter:
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Hetzner Online GmbH</h3>
            <p class="text-gray-700 mb-4">
                Anbieter: <a href="https://link.einsle.cloud/hetzner" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800">Hetzner Online GmbH</a><br>
                Serverstandort: Helsinki, Finnland
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Externes Hosting</h3>
            <p class="text-gray-700 mb-4">
                Diese Website wird bei einem externen Dienstleister gehostet (Hoster). Die personenbezogenen Daten, die auf dieser Website erfasst werden, werden auf den Servern des Hosters gespeichert. Hierbei kann es sich v. a. um IP-Adressen, Kontaktanfragen, Meta- und Kommunikationsdaten, Vertragsdaten, Kontaktdaten, Namen, Webseitenzugriffe und sonstige Daten, die über eine Website generiert werden, handeln.
            </p>
            <p class="text-gray-700 mb-4">
                Der Einsatz des Hosters erfolgt zum Zwecke der Vertragserfüllung gegenüber unseren potenziellen und bestehenden Kunden (Art. 6 Abs. 1 lit. b DSGVO) und im Interesse einer sicheren, schnellen und effizienten Bereitstellung unseres Online-Angebots durch einen professionellen Anbieter (Art. 6 Abs. 1 lit. f DSGVO).
            </p>
            <p class="text-gray-700 mb-4">
                Unser Hoster wird deine Daten nur insoweit verarbeiten, wie dies zur Erfüllung seiner Leistungspflichten erforderlich ist und unsere Weisungen in Bezug auf diese Daten befolgen.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Allgemeine Hinweise und Pflichtinformationen</h2>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Datenschutz</h3>
            <p class="text-gray-700 mb-4">
                Die Betreiber dieser Seiten nehmen den Schutz deiner persönlichen Daten sehr ernst. Wir behandeln deine personenbezogenen Daten vertraulich und entsprechend der gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Hinweis zur verantwortlichen Stelle</h3>
            <p class="text-gray-700 mb-4">
                Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist im <a href="{{ route('impressum') }}" class="text-blue-600 hover:text-blue-800 font-medium">Impressum</a> zu finden.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Widerruf deiner Einwilligung zur Datenverarbeitung</h3>
            <p class="text-gray-700 mb-4">
                Viele Datenverarbeitungsvorgänge sind nur mit deiner ausdrücklichen Einwilligung möglich. Du kannst eine bereits erteilte Einwilligung jederzeit widerrufen. Dazu reicht eine formlose Mitteilung per E-Mail an uns. Die Rechtmäßigkeit der bis zum Widerruf erfolgten Datenverarbeitung bleibt vom Widerruf unberührt.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Recht auf Datenübertragbarkeit</h3>
            <p class="text-gray-700 mb-4">
                Du hast das Recht, Daten, die wir auf Grundlage deiner Einwilligung oder in Erfüllung eines Vertrags automatisiert verarbeiten, an dich oder an einen Dritten in einem gängigen, maschinenlesbaren Format aushändigen zu lassen.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Auskunft, Löschung und Berichtigung</h3>
            <p class="text-gray-700 mb-4">
                Du hast im Rahmen der geltenden gesetzlichen Bestimmungen jederzeit das Recht auf unentgeltliche Auskunft über deine gespeicherten personenbezogenen Daten, deren Herkunft und Empfänger und den Zweck der Datenverarbeitung und ggf. ein Recht auf Berichtigung oder Löschung dieser Daten. Hierzu sowie zu weiteren Fragen zum Thema personenbezogene Daten kannst du dich jederzeit unter der im Impressum angegebenen Adresse an uns wenden.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Datenerfassung auf dieser Website</h2>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Cookies</h3>
            <p class="text-gray-700 mb-4">
                Die Internetseiten verwenden teilweise so genannte Cookies. Cookies richten auf deinem Rechner keinen Schaden an und enthalten keine Viren. Cookies dienen dazu, unser Angebot nutzerfreundlicher, effektiver und sicherer zu machen. Cookies sind kleine Textdateien, die auf deinem Rechner abgelegt werden und die dein Browser speichert.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Server-Log-Dateien</h3>
            <p class="text-gray-700 mb-4">
                Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, die dein Browser automatisch an uns übermittelt. Dies sind:
            </p>
            <ul class="list-disc list-inside text-gray-700 mb-4 ml-4">
                <li>Browsertyp und Browserversion</li>
                <li>verwendetes Betriebssystem</li>
                <li>Referrer URL</li>
                <li>Hostname des zugreifenden Rechners</li>
                <li>Uhrzeit der Serveranfrage</li>
                <li>IP-Adresse</li>
            </ul>
            <p class="text-gray-700 mb-4">
                Eine Zusammenführung dieser Daten mit anderen Datenquellen wird nicht vorgenommen. Die Erfassung dieser Daten erfolgt auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO. Der Websitebetreiber hat ein berechtigtes Interesse an der technisch fehlerfreien Darstellung und der Optimierung seiner Website.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Registrierung auf dieser Website</h3>
            <p class="text-gray-700 mb-4">
                Du kannst dich auf unserer Website registrieren, um zusätzliche Funktionen auf der Seite zu nutzen. Die dazu eingegebenen Daten verwenden wir nur zum Zwecke der Nutzung des jeweiligen Angebotes oder Dienstes, für den du dich registriert hast. Die bei der Registrierung abgefragten Pflichtangaben müssen vollständig angegeben werden. Anderenfalls werden wir die Registrierung ablehnen.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="/" class="text-blue-600 hover:text-blue-800 font-medium">← Zurück zur Startseite</a>
        </div>
    </div>
</div>
@endsection
