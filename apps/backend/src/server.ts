import Fastify from 'fastify';
import cors from '@fastify/cors';
import helmet from '@fastify/helmet';
import rateLimit from '@fastify/rate-limit';
import { logger } from './utils/logger.js';
import { corsOptions } from './config/cors.js';
import { errorHandler } from './middleware/errorHandler.js';
import { authRoutes } from './modules/auth/auth.routes.js';
import { booksRoutes } from './modules/books/books.routes.js';
import { shelvesRoutes } from './modules/shelves/shelves.routes.js';
import { bookSearchRoutes } from './modules/bookSearch/bookSearch.routes.js';

export async function buildServer() {
  const fastify = Fastify({
    logger,
  });

  // Register plugins
  await fastify.register(cors, corsOptions);
  await fastify.register(helmet, {
    contentSecurityPolicy: false, // Disable for development, configure for production
  });
  await fastify.register(rateLimit, {
    max: 100,
    timeWindow: '15 minutes',
  });

  // Error handler
  fastify.setErrorHandler(errorHandler);

  // Health check
  fastify.get('/health', async () => {
    return { status: 'ok', timestamp: new Date().toISOString() };
  });

  // Register routes
  await fastify.register(authRoutes, { prefix: '/api/auth' });
  await fastify.register(booksRoutes, { prefix: '/api/books' });
  await fastify.register(shelvesRoutes, { prefix: '/api/shelves' });
  await fastify.register(bookSearchRoutes, { prefix: '/api/search' });

  return fastify;
}
