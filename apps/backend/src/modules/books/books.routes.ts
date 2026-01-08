import type { FastifyInstance } from 'fastify';
import { BooksController } from './books.controller.js';
import {
  createBookSchema,
  updateBookSchema,
  updateProgressSchema,
  updateStatusSchema,
  listBooksQuerySchema,
} from './books.schema.js';
import { authMiddleware } from '../../middleware/auth.js';

const booksController = new BooksController();

export async function booksRoutes(fastify: FastifyInstance) {
  // All routes require authentication
  fastify.addHook('preHandler', authMiddleware);

  // List books
  fastify.get(
    '/',
    {
      schema: listBooksQuerySchema,
    },
    booksController.getAll
  );

  // Get single book
  fastify.get('/:id', booksController.getById);

  // Create book
  fastify.post(
    '/',
    {
      schema: createBookSchema,
    },
    booksController.create
  );

  // Update book
  fastify.put(
    '/:id',
    {
      schema: updateBookSchema,
    },
    booksController.update
  );

  // Delete book
  fastify.delete('/:id', booksController.delete);

  // Update reading progress
  fastify.patch(
    '/:id/progress',
    {
      schema: updateProgressSchema,
    },
    booksController.updateProgress
  );

  // Update reading status
  fastify.patch(
    '/:id/status',
    {
      schema: updateStatusSchema,
    },
    booksController.updateStatus
  );
}
