export interface BookSearchResult {
  title: string;
  author: string;
  isbn?: string;
  isbn13?: string;
  publisher?: string;
  publishedDate?: string;
  description?: string;
  pageCount?: number;
  language?: string;
  coverUrl?: string;
  thumbnail?: string;
  source: 'google' | 'openlibrary' | 'isbndb';
  externalId: string;
}

export interface BookProvider {
  name: string;
  search(query: string): Promise<BookSearchResult[]>;
  searchByISBN(isbn: string): Promise<BookSearchResult | null>;
}
