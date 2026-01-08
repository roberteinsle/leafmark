import { buildServer } from './server.js';
import { env } from './config/env.js';
import { logger } from './utils/logger.js';

async function start() {
  try {
    const server = await buildServer();

    await server.listen({
      port: env.PORT,
      host: '0.0.0.0',
    });

    logger.info(`🚀 Server running on http://localhost:${env.PORT}`);
    logger.info(`📚 Environment: ${env.NODE_ENV}`);
  } catch (error) {
    logger.error(error);
    process.exit(1);
  }
}

start();
