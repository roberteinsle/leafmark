import type { FastifyRequest, FastifyReply } from 'fastify';
import { AuthService } from './auth.service.js';
import type { AuthRequest } from '../../middleware/auth.js';

const authService = new AuthService();

export class AuthController {
  async register(request: FastifyRequest, reply: FastifyReply) {
    const { email, password, name, language } = request.body as {
      email: string;
      password: string;
      name?: string;
      language?: string;
    };

    const result = await authService.register({ email, password, name, language });

    return reply.status(201).send(result);
  }

  async login(request: FastifyRequest, reply: FastifyReply) {
    const { email, password } = request.body as {
      email: string;
      password: string;
    };

    const result = await authService.login(email, password);

    return reply.send(result);
  }

  async refresh(request: FastifyRequest, reply: FastifyReply) {
    const { refreshToken } = request.body as {
      refreshToken: string;
    };

    const result = await authService.refresh(refreshToken);

    return reply.send(result);
  }

  async getMe(request: FastifyRequest, reply: FastifyReply) {
    const { userId } = (request as AuthRequest).user;

    const user = await authService.getMe(userId);

    return reply.send(user);
  }
}
