<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\GoodreadsImportService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GoodreadsImportTest extends TestCase
{
    private function fixture(): string
    {
        return base_path('tests/Fixtures/goodreads_export.csv');
    }

    private function import(User $user, bool $importTag = false)
    {
        return (new GoodreadsImportService($user))->import($this->fixture(), 'goodreads_export.csv', $importTag);
    }

    private function book(User $user, string $title): Book
    {
        return $user->books()->where('title', $title)->firstOrFail();
    }

    public function test_preview_counts_all_rows(): void
    {
        $preview = (new GoodreadsImportService($this->createUser()))->parseCSV($this->fixture());

        $this->assertSame(8, $preview['total_rows']);
        $this->assertCount(8, $preview['preview']);
        $this->assertSame('book id', $preview['headers'][0]);
    }

    public function test_import_maps_fields(): void
    {
        $user = $this->createUser();
        $history = $this->import($user);

        $this->assertSame(7, $history->fresh()->imported_count);
        $this->assertSame(1, $history->fresh()->failed_count); // Zeile ohne Titel

        $book = $this->book($user, 'Der Schwarm');
        $this->assertSame('Frank Schätzing', $book->author);
        $this->assertSame('9783596164530', $book->isbn13);
        $this->assertSame('3596164532', $book->isbn);
        $this->assertSame('read', $book->status);
        $this->assertSame('2011-01-01', $book->finished_at->format('Y-m-d'));
        $this->assertSame('2005-01-01', $book->published_date->format('Y-m-d'));
        $this->assertSame('paperback', $book->format);
        $this->assertEquals(4, $book->rating);
        $this->assertSame('Spannend, mit "Tiefgang".', $book->review);
        $this->assertSame('1168487', $book->goodreads_id);
    }

    public function test_only_main_author_is_imported(): void
    {
        $user = $this->createUser();
        $this->import($user);

        $this->assertSame('Barack Obama', $this->book($user, 'Ein verheißenes Land')->author);
        $this->assertSame('J.K. Rowling', $this->book($user, 'Harry Potter und der Feuerkelch')->author);
    }

    public function test_series_is_split_from_title(): void
    {
        $user = $this->createUser();
        $this->import($user);

        $hp = $this->book($user, 'Harry Potter und der Feuerkelch');
        $this->assertSame('Harry Potter', $hp->series);
        $this->assertSame(4, $hp->series_position);

        // Sammelband "#1-3": Reihe ja, Band nein
        $lotr = $this->book($user, 'Der Herr der Ringe');
        $this->assertSame('The Lord of the Rings', $lotr->series);
        $this->assertNull($lotr->series_position);
    }

    public function test_shelves_become_tags_but_status_shelves_do_not(): void
    {
        $user = $this->createUser();
        $this->import($user);

        $this->assertEqualsCanonicalizing(['klassiker', 'top100'], $this->book($user, 'Der Herr der Ringe')->tags->pluck('name')->all());
        $this->assertSame(['klassiker'], $this->book($user, 'Anmerkungen zu Hitler')->tags->pluck('name')->all());
        $this->assertFalse($user->tags()->whereIn('name', ['to-read', 'read', 'currently-reading'])->exists());
        $this->assertSame(2, $user->tags()->count());
    }

    public function test_import_tag_only_when_requested(): void
    {
        $withoutTag = $this->createUser();
        $this->assertNull($this->import($withoutTag)->import_tag);

        $withTag = $this->createUser();
        $history = $this->import($withTag, true);
        $this->assertNotNull($history->import_tag);
        $this->assertSame(7, $withTag->tags()->where('name', $history->import_tag)->first()->books()->count());
    }

    public function test_cleans_text_and_year_and_binding(): void
    {
        $user = $this->createUser();
        $this->import($user);

        // Unsichtbares U+200E vor dem Titel entfernt, Reihe erkannt
        $wahl = $this->book($user, '22 Bahnen');
        $this->assertSame('Tilda und Ida', $wahl->series);
        $this->assertSame('digital', $wahl->format);

        // Doppelte Leerzeichen, Tippfehler-Jahr "20015" → ursprüngliches Jahr
        $report = $this->book($user, 'Der CIA Folter Report');
        $this->assertSame('Wolfgang Neskovic', $report->author);
        $this->assertSame('2015-01-01', $report->published_date->format('Y-m-d'));

        // Kein Erscheinungsjahr → ursprüngliches Jahr; Seiten "0" → leer; Hörbuch
        $haffner = $this->book($user, 'Anmerkungen zu Hitler');
        $this->assertSame('1978-01-01', $haffner->published_date->format('Y-m-d'));
        $this->assertNull($haffner->page_count);
        $this->assertSame('audiobook', $haffner->format);
        $this->assertSame('Testnotiz', $haffner->review);
    }

    public function test_currently_reading_gets_start_date(): void
    {
        $user = $this->createUser();
        $this->import($user);

        $book = $this->book($user, 'Ein verheißenes Land');
        $this->assertSame('currently_reading', $book->status);
        $this->assertSame('2025-12-30', $book->started_at->format('Y-m-d'));
    }

    public function test_second_import_skips_everything(): void
    {
        $user = $this->createUser();
        $this->import($user);
        $second = $this->import($user);

        $this->assertSame(0, (int) $second->fresh()->imported_count);
        $this->assertSame(7, $second->fresh()->skipped_count);
        $this->assertSame(7, $user->books()->count());
    }

    public function test_failing_row_does_not_abort_import(): void
    {
        $user = $this->createUser();
        $path = tempnam(sys_get_temp_dir(), 'gr');
        $lines = file($this->fixture());
        // Titel länger als 255 Zeichen: SQL-Fehler auf Postgres in genau dieser Zeile
        $lines[] = '1,'.str_repeat('X', 300).',Autor Name,"Name, Autor",,"=""""","=""""",0,,,,,,,2025/12/30,,,to-read,,,,0,0'."\n";
        file_put_contents($path, implode('', $lines));

        $history = (new GoodreadsImportService($user))->import($path, 'big.csv');
        @unlink($path);

        $this->assertSame('completed', $history->fresh()->status);
        $this->assertSame(7, $history->fresh()->imported_count);
        $this->assertSame(2, $history->fresh()->failed_count);
        $this->assertSame(7, $user->books()->count());
    }

    public function test_upload_preview_and_execute_through_ui(): void
    {
        $user = $this->actingAsUser();
        $file = new UploadedFile($this->fixture(), 'goodreads_library_export.csv', 'text/csv', null, true);

        $this->post(route('import.upload'), ['csv_file' => $file])
            ->assertOk()
            ->assertSee(__('app.import.create_import_tag'));

        $response = $this->post(route('import.execute'), ['create_import_tag' => '1']);
        $response->assertRedirect();
        $tag = $user->tags()->where('name', 'like', 'import%')->firstOrFail();
        $this->followRedirects($response)->assertSee(route('tags.show', $tag), false);

        $this->assertSame(7, $user->books()->count());
        $this->assertTrue($user->tags()->where('name', 'like', 'import%')->exists());
    }
}
