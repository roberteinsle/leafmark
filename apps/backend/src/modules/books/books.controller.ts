import type { FastifyRequest, FastifyReply } from 'fastify';
import { BooksService } from './books.service.js';
import type { AuthRequest } from '../../middleware/auth.js';
import { BookStatus } from '@prisma/client';

const booksService = new BooksService();

export class BooksController {
  async getAll(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { status, shelfId } = request.query as { status?: BookStatus; shelfId?: string };

    const books = await booksService.getAll(userId, { status, shelfId });

    return reply.send(books);
  }

  async getById(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };

    const book = await booksService.getById(id, userId);

    return reply.send(book);
  }

  async create(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const data = request.body;

    const book = await booksService.create(userId, data);

    return reply.status(201).send(book);
  }

  async update(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };
    const data = request.body;

    const book = await booksService.update(id, userId, data);

    return reply.send(book);
  }

  async delete(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };

    const result = await booksService.delete(id, userId);

    return reply.send(result);
  }

  async updateProgress(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };
    const { currentPage } = request.body as { currentPage: number };

    const book = await booksService.updateProgress(id, userId, currentPage);

    return reply.send(book);
  }

  async updateStatus(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;
    const { id } = request.params as { id: string };
    const { status } = request.body as { status: BookStatus };

    const book = await booksService.updateStatus(id, userId, status);

    return reply.send(book);
  }
}
