import axios from 'axios';
import { BookProvider, BookSearchResult } from './base.provider.js';
import { env } from '../../../config/env.js';

export class ISBNdbProvider implements BookProvider {
  name = 'ISBNdb';
  private baseUrl = 'https://api2.isbndb.com';

  async search(query: string): Promise<BookSearchResult[]> {
    if (!env.ISBNDB_API_KEY) {
      console.warn('ISBNdb API key not configured, skipping');
      throw new Error('ISBNdb API key not configured');
    }

    try {
      const response = await axios.get(`${this.baseUrl}/search/books`, {
        params: {
          query: query,
          page: 1,
          pageSize: 10,
        },
        headers: {
          Authorization: env.ISBNDB_API_KEY,
        },
      });

      if (!response.data.books || response.data.books.length === 0) {
        return [];
      }

      return response.data.books.map((book: any) => this.mapToBookResult(book));
    } catch (error) {
      console.error('ISBNdb API error:', error);
      throw error;
    }
  }

  async searchByISBN(isbn: string): Promise<BookSearchResult | null> {
    if (!env.ISBNDB_API_KEY) {
      console.warn('ISBNdb API key not configured, skipping');
      throw new Error('ISBNdb API key not configured');
    }

    try {
      const cleanIsbn = isbn.replace(/[- ]/g, '');
      const response = await axios.get(`${this.baseUrl}/book/${cleanIsbn}`, {
        headers: {
          Authorization: env.ISBNDB_API_KEY,
        },
      });

      if (!response.data.book) {
        return null;
      }

      return this.mapToBookResult(response.data.book);
    } catch (error) {
      console.error('ISBNdb API error:', error);
      throw error;
    }
  }

  private mapToBookResult(book: any): BookSearchResult {
    return {
      title: book.title || '',
      author: book.authors?.join(', ') || '',
      isbn: book.isbn,
      isbn13: book.isbn13,
      publisher: book.publisher,
      publishedDate: book.date_published,
      description: book.synopsis || book.overview,
      pageCount: book.pages,
      language: book.language,
      coverUrl: book.image,
      thumbnail: book.image,
      source: 'isbndb',
      externalId: book.isbn13 || book.isbn || '',
    };
  }
}
