import type { FastifyInstance } from 'fastify';
import { ShelvesController } from './shelves.controller.js';
import { createShelfSchema, updateShelfSchema, addBookToShelfSchema } from './shelves.schema.js';
import { authMiddleware } from '../../middleware/auth.js';

const shelvesController = new ShelvesController();

export async function shelvesRoutes(fastify: FastifyInstance) {
  // All routes require authentication
  fastify.addHook('preHandler', authMiddleware);

  // List shelves
  fastify.get('/', shelvesController.getAll);

  // Get single shelf
  fastify.get('/:id', shelvesController.getById);

  // Create shelf
  fastify.post(
    '/',
    {
      schema: createShelfSchema,
    },
    shelvesController.create
  );

  // Update shelf
  fastify.put(
    '/:id',
    {
      schema: updateShelfSchema,
    },
    shelvesController.update
  );

  // Delete shelf
  fastify.delete('/:id', shelvesController.delete);

  // Add book to shelf
  fastify.post(
    '/:id/books',
    {
      schema: addBookToShelfSchema,
    },
    shelvesController.addBook
  );

  // Remove book from shelf
  fastify.delete('/:id/books/:bookId', shelvesController.removeBook);
}
