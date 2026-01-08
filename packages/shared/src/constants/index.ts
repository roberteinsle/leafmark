export const BOOK_STATUSES = {
  WANT_TO_READ: 'WANT_TO_READ',
  CURRENTLY_READING: 'CURRENTLY_READING',
  READ: 'READ',
} as const;

export const DEFAULT_SHELVES = ['Want to Read', 'Currently Reading', 'Read'] as const;

export const SUPPORTED_LANGUAGES = ['en', 'de'] as const;
