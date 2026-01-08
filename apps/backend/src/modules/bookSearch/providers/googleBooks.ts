import axios from 'axios';
import { BookProvider, BookSearchResult } from './base.provider.js';
import { env } from '../../../config/env.js';

export class GoogleBooksProvider implements BookProvider {
  name = 'Google Books';
  private baseUrl = 'https://www.googleapis.com/books/v1/volumes';

  async search(query: string): Promise<BookSearchResult[]> {
    try {
      const params: any = {
        q: query,
        maxResults: 10,
      };

      if (env.GOOGLE_BOOKS_API_KEY) {
        params.key = env.GOOGLE_BOOKS_API_KEY;
      }

      const response = await axios.get(this.baseUrl, { params });

      if (!response.data.items) {
        return [];
      }

      return response.data.items.map((item: any) => this.mapToBookResult(item));
    } catch (error) {
      console.error('Google Books API error:', error);
      throw error;
    }
  }

  async searchByISBN(isbn: string): Promise<BookSearchResult | null> {
    try {
      const params: any = {
        q: `isbn:${isbn}`,
      };

      if (env.GOOGLE_BOOKS_API_KEY) {
        params.key = env.GOOGLE_BOOKS_API_KEY;
      }

      const response = await axios.get(this.baseUrl, { params });

      if (!response.data.items || response.data.items.length === 0) {
        return null;
      }

      return this.mapToBookResult(response.data.items[0]);
    } catch (error) {
      console.error('Google Books API error:', error);
      throw error;
    }
  }

  private mapToBookResult(item: any): BookSearchResult {
    const volumeInfo = item.volumeInfo;
    const identifiers = volumeInfo.industryIdentifiers || [];

    const isbn13 = identifiers.find((i: any) => i.type === 'ISBN_13')?.identifier;
    const isbn10 = identifiers.find((i: any) => i.type === 'ISBN_10')?.identifier;

    return {
      title: volumeInfo.title || '',
      author: volumeInfo.authors?.join(', ') || '',
      isbn: isbn10,
      isbn13: isbn13,
      publisher: volumeInfo.publisher,
      publishedDate: volumeInfo.publishedDate,
      description: volumeInfo.description,
      pageCount: volumeInfo.pageCount,
      language: volumeInfo.language,
      coverUrl: volumeInfo.imageLinks?.large || volumeInfo.imageLinks?.medium,
      thumbnail: volumeInfo.imageLinks?.thumbnail || volumeInfo.imageLinks?.smallThumbnail,
      source: 'google',
      externalId: item.id,
    };
  }
}
