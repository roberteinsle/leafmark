@extends('layouts.app')

@section('title', 'Impressum')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white shadow-sm rounded-lg px-6 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Impressum</h1>

        <div class="prose prose-blue max-w-none">
            <!-- Legal Information (§ 5 DDG) -->
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-4">Angaben gemäß § 5 DDG</h2>
            <p class="text-gray-700">
                Robert Einsle<br>
                Heerdestieg 3<br>
                22145 Braak<br>
                Deutschland
            </p>

            <!-- Contact Details -->
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Kontakt</h2>
            <p class="text-gray-700">
                <strong>E-Mail:</strong> <a href="mailto:ews@einsle.com" class="text-blue-600 hover:text-blue-800">ews@einsle.com</a><br>
                <strong>Telefon:</strong> <a href="tel:+4940414312534" class="text-blue-600 hover:text-blue-800">040 / 414 312 534</a>
            </p>

            <!-- VAT Information -->
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Umsatzsteuer-Identifikationsnummer</h2>
            <p class="text-gray-700">
                Umsatzsteuer-Identifikationsnummer gemäß §27a UStG:<br>
                <strong>DE294130331</strong>
            </p>

            <!-- Responsible for Content -->
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
            <p class="text-gray-700">
                Robert Einsle<br>
                Heerdestieg 3<br>
                22145 Braak<br>
                Deutschland
            </p>

            <!-- Disclaimer Section -->
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Haftungsausschluss</h2>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Haftung für Inhalte</h3>
            <p class="text-gray-700 mb-4">
                Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Haftung für Links</h3>
            <p class="text-gray-700 mb-4">
                Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Urheberrecht</h3>
            <p class="text-gray-700 mb-4">
                Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.
            </p>

            <!-- EU Dispute Resolution -->
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">EU-Streitschlichtung</h2>
            <p class="text-gray-700 mb-4">
                Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
                <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800">https://ec.europa.eu/consumers/odr/</a>.<br>
                Unsere E-Mail-Adresse finden Sie oben im Impressum.
            </p>

            <!-- Consumer Dispute Resolution -->
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Verbraucherstreitbeilegung / Universalschlichtungsstelle</h2>
            <p class="text-gray-700 mb-4">
                Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="/" class="text-blue-600 hover:text-blue-800 font-medium">← Zurück zur Startseite</a>
        </div>
    </div>
</div>
@endsection
