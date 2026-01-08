import type { FastifyInstance } from 'fastify';
import { AuthController } from './auth.controller.js';
import { registerSchema, loginSchema, refreshSchema } from './auth.schema.js';
import { authMiddleware } from '../../middleware/auth.js';

const authController = new AuthController();

export async function authRoutes(fastify: FastifyInstance) {
  // Register
  fastify.post(
    '/register',
    {
      schema: registerSchema,
    },
    authController.register
  );

  // Login
  fastify.post(
    '/login',
    {
      schema: loginSchema,
    },
    authController.login
  );

  // Refresh token
  fastify.post(
    '/refresh',
    {
      schema: refreshSchema,
    },
    authController.refresh
  );

  // Get current user (protected)
  fastify.get('/me', { preHandler: [authMiddleware] }, authController.getMe);
}
