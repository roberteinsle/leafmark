import type { FastifyRequest, FastifyReply } from 'fastify';
import { ShelvesService } from './shelves.service.js';
import type { AuthRequest } from '../../middleware/auth.js';

const shelvesService = new ShelvesService();

export class ShelvesController {
  async getAll(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;

    const shelves = await shelvesService.getAll(userId);

    return reply.send(shelves);
  }

  async getById(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };

    const shelf = await shelvesService.getById(id, userId);

    return reply.send(shelf);
  }

  async create(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const data = request.body as { name: string; description?: string; sortOrder?: number };

    const shelf = await shelvesService.create(userId, data);

    return reply.status(201).send(shelf);
  }

  async update(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };
    const data = request.body as { name?: string; description?: string; sortOrder?: number };

    const shelf = await shelvesService.update(id, userId, data);

    return reply.send(shelf);
  }

  async delete(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };

    const result = await shelvesService.delete(id, userId);

    return reply.send(result);
  }

  async addBook(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };
    const { bookId } = request.body as { bookId: string };

    const result = await shelvesService.addBook(id, userId, bookId);

    return reply.status(201).send(result);
  }

  async removeBook(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id, bookId } = request.params as { id: string; bookId: string };

    const result = await shelvesService.removeBook(id, userId, bookId);

    return reply.send(result);
  }
}
