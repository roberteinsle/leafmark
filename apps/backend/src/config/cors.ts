import type { FastifyCorsOptions } from '@fastify/cors';
import { env } from './env.js';

export const corsOptions: FastifyCorsOptions = {
  origin:
    env.NODE_ENV === 'production'
      ? env.CORS_ORIGIN
      : ['http://localhost:5173', 'http://localhost:3000'],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
  allowedHeaders: ['Content-Type', 'Authorization'],
};
