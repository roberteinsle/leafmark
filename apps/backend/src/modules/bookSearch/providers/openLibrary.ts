import axios from 'axios';
import { BookProvider, BookSearchResult } from './base.provider.js';

export class OpenLibraryProvider implements BookProvider {
  name = 'Open Library';
  private baseUrl = 'https://openlibrary.org';

  async search(query: string): Promise<BookSearchResult[]> {
    try {
      const response = await axios.get(`${this.baseUrl}/search.json`, {
        params: {
          q: query,
          limit: 10,
        },
      });

      if (!response.data.docs || response.data.docs.length === 0) {
        return [];
      }

      return response.data.docs.map((doc: any) => this.mapToBookResult(doc));
    } catch (error) {
      console.error('Open Library API error:', error);
      throw error;
    }
  }

  async searchByISBN(isbn: string): Promise<BookSearchResult | null> {
    try {
      const cleanIsbn = isbn.replace(/[- ]/g, '');
      const response = await axios.get(`${this.baseUrl}/api/books`, {
        params: {
          bibkeys: `ISBN:${cleanIsbn}`,
          format: 'json',
          jscmd: 'data',
        },
      });

      const key = `ISBN:${cleanIsbn}`;
      if (!response.data[key]) {
        return null;
      }

      return this.mapToBookResultFromAPI(response.data[key], cleanIsbn);
    } catch (error) {
      console.error('Open Library API error:', error);
      throw error;
    }
  }

  private mapToBookResult(doc: any): BookSearchResult {
    const isbn13 = doc.isbn?.[0];
    const isbn10 = doc.isbn?.find((i: string) => i.length === 10);
    const coverId = doc.cover_i;

    return {
      title: doc.title || '',
      author: doc.author_name?.join(', ') || '',
      isbn: isbn10,
      isbn13: isbn13 || isbn10,
      publisher: doc.publisher?.[0],
      publishedDate: doc.first_publish_year?.toString(),
      description: doc.first_sentence?.join(' '),
      pageCount: doc.number_of_pages_median,
      language: doc.language?.[0],
      coverUrl: coverId ? `https://covers.openlibrary.org/b/id/${coverId}-L.jpg` : undefined,
      thumbnail: coverId ? `https://covers.openlibrary.org/b/id/${coverId}-M.jpg` : undefined,
      source: 'openlibrary',
      externalId: doc.key || doc.edition_key?.[0] || '',
    };
  }

  private mapToBookResultFromAPI(data: any, isbn: string): BookSearchResult {
    const coverId = data.cover?.large || data.cover?.medium;

    return {
      title: data.title || '',
      author: data.authors?.map((a: any) => a.name).join(', ') || '',
      isbn: isbn.length === 10 ? isbn : undefined,
      isbn13: isbn.length === 13 ? isbn : undefined,
      publisher: data.publishers?.[0]?.name,
      publishedDate: data.publish_date,
      description: data.notes || data.subtitle,
      pageCount: data.number_of_pages,
      language: undefined,
      coverUrl: data.cover?.large,
      thumbnail: data.cover?.medium || data.cover?.small,
      source: 'openlibrary',
      externalId: data.key || '',
    };
  }
}
