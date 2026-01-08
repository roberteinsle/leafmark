import type { FastifyError, FastifyRequest, FastifyReply } from 'fastify';
import { env } from '../config/env.js';

export class AppError extends Error {
  constructor(
    public statusCode: number,
    public message: string,
    public isOperational = true
  ) {
    super(message);
    Object.setPrototypeOf(this, AppError.prototype);
  }
}

export async function errorHandler(
  error: FastifyError,
  request: FastifyRequest,
  reply: FastifyReply
) {
  // Log error
  request.log.error(error);

  // Prisma errors
  if (error.code?.startsWith('P')) {
    return reply.status(400).send({
      error: 'Database error',
      message: 'An error occurred while processing your request',
    });
  }

  // Validation errors
  if (error.validation) {
    return reply.status(400).send({
      error: 'Validation error',
      details: error.validation,
    });
  }

  // Custom app errors
  if (error instanceof AppError) {
    return reply.status(error.statusCode).send({
      error: error.message,
    });
  }

  // Default error
  return reply.status(error.statusCode || 500).send({
    error: 'Internal server error',
    message: env.NODE_ENV === 'development' ? error.message : undefined,
  });
}
