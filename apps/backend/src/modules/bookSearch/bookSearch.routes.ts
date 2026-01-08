import type { FastifyInstance } from 'fastify';
import { BookSearchController } from './bookSearch.controller.js';
import { authMiddleware } from '../../middleware/auth.js';

const bookSearchController = new BookSearchController();

export async function bookSearchRoutes(fastify: FastifyInstance) {
  // All routes require authentication
  fastify.addHook('preHandler', authMiddleware);

  // Search books by query
  fastify.get('/books', bookSearchController.searchBooks);

  // Search book by ISBN
  fastify.get('/isbn/:isbn', bookSearchController.searchByISBN);
}
