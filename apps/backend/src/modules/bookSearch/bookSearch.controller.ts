import type { FastifyRequest, FastifyReply } from 'fastify';
import { BookSearchService } from './bookSearch.service.js';

const bookSearchService = new BookSearchService();

export class BookSearchController {
  async searchBooks(request: FastifyRequest, reply: FastifyReply) {
    const { q } = request.query as { q: string };

    if (!q) {
      return reply.status(400).send({ error: 'Query parameter "q" is required' });
    }

    const results = await bookSearchService.searchBooks(q);

    return reply.send(results);
  }

  async searchByISBN(request: FastifyRequest, reply: FastifyReply) {
    const { isbn } = request.params as { isbn: string };

    const result = await bookSearchService.searchByISBN(isbn);

    if (!result) {
      return reply.status(404).send({ error: 'Book not found' });
    }

    return reply.send(result);
  }
}
