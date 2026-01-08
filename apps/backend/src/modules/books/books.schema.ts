export const createBookSchema = {
  body: {
    type: 'object',
    required: ['title'],
    properties: {
      title: { type: 'string', minLength: 1, maxLength: 500 },
      author: { type: 'string', maxLength: 300 },
      isbn: { type: 'string', pattern: '^[0-9-]{10,17}$' },
      isbn13: { type: 'string', pattern: '^[0-9-]{13,17}$' },
      publisher: { type: 'string', maxLength: 200 },
      publishedDate: { type: 'string' },
      description: { type: 'string' },
      pageCount: { type: 'integer', minimum: 1 },
      language: { type: 'string', maxLength: 10 },
      coverUrl: { type: 'string', format: 'uri' },
      thumbnail: { type: 'string', format: 'uri' },
      currentPage: { type: 'integer', minimum: 0 },
      status: {
        type: 'string',
        enum: ['WANT_TO_READ', 'CURRENTLY_READING', 'READ'],
      },
      apiSource: { type: 'string' },
      externalId: { type: 'string' },
    },
  },
};

export const updateBookSchema = {
  body: {
    type: 'object',
    properties: {
      title: { type: 'string', minLength: 1, maxLength: 500 },
      author: { type: 'string', maxLength: 300 },
      isbn: { type: 'string', pattern: '^[0-9-]{10,17}$' },
      isbn13: { type: 'string', pattern: '^[0-9-]{13,17}$' },
      publisher: { type: 'string', maxLength: 200 },
      publishedDate: { type: 'string' },
      description: { type: 'string' },
      pageCount: { type: 'integer', minimum: 1 },
      language: { type: 'string', maxLength: 10 },
      coverUrl: { type: 'string', format: 'uri' },
      thumbnail: { type: 'string', format: 'uri' },
      currentPage: { type: 'integer', minimum: 0 },
      status: {
        type: 'string',
        enum: ['WANT_TO_READ', 'CURRENTLY_READING', 'READ'],
      },
    },
  },
};

export const updateProgressSchema = {
  body: {
    type: 'object',
    required: ['currentPage'],
    properties: {
      currentPage: { type: 'integer', minimum: 0 },
    },
  },
};

export const updateStatusSchema = {
  body: {
    type: 'object',
    required: ['status'],
    properties: {
      status: {
        type: 'string',
        enum: ['WANT_TO_READ', 'CURRENTLY_READING', 'READ'],
      },
    },
  },
};

export const listBooksQuerySchema = {
  querystring: {
    type: 'object',
    properties: {
      status: {
        type: 'string',
        enum: ['WANT_TO_READ', 'CURRENTLY_READING', 'READ'],
      },
      shelfId: { type: 'string' },
    },
  },
};
