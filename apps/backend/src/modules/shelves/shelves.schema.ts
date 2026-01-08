export const createShelfSchema = {
  body: {
    type: 'object',
    required: ['name'],
    properties: {
      name: { type: 'string', minLength: 1, maxLength: 100 },
      description: { type: 'string', maxLength: 500 },
      sortOrder: { type: 'integer', minimum: 0 },
    },
  },
};

export const updateShelfSchema = {
  body: {
    type: 'object',
    properties: {
      name: { type: 'string', minLength: 1, maxLength: 100 },
      description: { type: 'string', maxLength: 500 },
      sortOrder: { type: 'integer', minimum: 0 },
    },
  },
};

export const addBookToShelfSchema = {
  body: {
    type: 'object',
    required: ['bookId'],
    properties: {
      bookId: { type: 'string' },
    },
  },
};
