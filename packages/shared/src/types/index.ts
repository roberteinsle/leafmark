export type BookStatus = 'WANT_TO_READ' | 'CURRENTLY_READING' | 'READ';

export interface User {
  id: string;
  email: string;
  name: string | null;
  language: string;
  createdAt: Date;
}

export interface Book {
  id: string;
  userId: string;
  title: string;
  author: string | null;
  isbn: string | null;
  isbn13: string | null;
  publisher: string | null;
  publishedDate: string | null;
  description: string | null;
  pageCount: number | null;
  language: string | null;
  coverUrl: string | null;
  thumbnail: string | null;
  currentPage: number | null;
  status: BookStatus;
  addedAt: Date;
  startedAt: Date | null;
  finishedAt: Date | null;
  updatedAt: Date;
  apiSource: string | null;
  externalId: string | null;
}

export interface Shelf {
  id: string;
  userId: string;
  name: string;
  description: string | null;
  isDefault: boolean;
  sortOrder: number;
  createdAt: Date;
  updatedAt: Date;
}

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
