import { GoogleBooksProvider } from './providers/googleBooks.js';
import { OpenLibraryProvider } from './providers/openLibrary.js';
import { ISBNdbProvider } from './providers/isbndb.js';
import type { BookSearchResult } from './providers/base.provider.js';

export class BookSearchService {
  private providers = [
    new GoogleBooksProvider(),
    new OpenLibraryProvider(),
    new ISBNdbProvider(),
  ];

  async searchBooks(query: string): Promise<BookSearchResult[]> {
    for (const provider of this.providers) {
      try {
        console.log(`Searching with ${provider.name}...`);
        const results = await provider.search(query);

        if (results.length > 0) {
          console.log(`Found ${results.length} results from ${provider.name}`);
          return results;
        }
      } catch (error) {
        console.error(`${provider.name} failed:`, error);
        // Continue to next provider
      }
    }

    console.log('No results found from any provider');
    return [];
  }

  async searchByISBN(isbn: string): Promise<BookSearchResult | null> {
    for (const provider of this.providers) {
      try {
        console.log(`Searching ISBN with ${provider.name}...`);
        const result = await provider.searchByISBN(isbn);

        if (result) {
          console.log(`Found result from ${provider.name}`);
          return result;
        }
      } catch (error) {
        console.error(`${provider.name} failed:`, error);
        // Continue to next provider
      }
    }

    console.log('No results found from any provider');
    return null;
  }
}
