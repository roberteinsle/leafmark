import { PrismaClient } from '@prisma/client';
import { hashPassword, comparePassword, validatePassword } from '../../utils/password.js';
import {
  generateAccessToken,
  generateRefreshToken,
  verifyRefreshToken,
} from '../../utils/jwt.js';
import { AppError } from '../../middleware/errorHandler.js';

const prisma = new PrismaClient();

export class AuthService {
  async register(data: {
    email: string;
    password: string;
    name?: string;
    language?: string;
  }) {
    // Validate password strength
    const passwordValidation = validatePassword(data.password);
    if (!passwordValidation.valid) {
      throw new AppError(400, passwordValidation.errors.join(', '));
    }

    // Check if user already exists
    const existingUser = await prisma.user.findUnique({
      where: { email: data.email },
    });

    if (existingUser) {
      throw new AppError(400, 'User already exists');
    }

    // Hash password
    const hashedPassword = await hashPassword(data.password);

    // Create user
    const user = await prisma.user.create({
      data: {
        email: data.email,
        password: hashedPassword,
        name: data.name,
        language: data.language || 'en',
      },
      select: {
        id: true,
        email: true,
        name: true,
        language: true,
        createdAt: true,
      },
    });

    // Create default shelves
    await prisma.shelf.createMany({
      data: [
        {
          userId: user.id,
          name: 'Want to Read',
          description: 'Books I want to read',
          isDefault: true,
          sortOrder: 1,
        },
        {
          userId: user.id,
          name: 'Currently Reading',
          description: 'Books I am currently reading',
          isDefault: true,
          sortOrder: 2,
        },
        {
          userId: user.id,
          name: 'Read',
          description: 'Books I have finished reading',
          isDefault: true,
          sortOrder: 3,
        },
      ],
    });

    // Generate tokens
    const accessToken = generateAccessToken({ userId: user.id, email: user.email });
    const refreshToken = generateRefreshToken({ userId: user.id, email: user.email });

    return {
      user,
      accessToken,
      refreshToken,
    };
  }

  async login(email: string, password: string) {
    // Find user
    const user = await prisma.user.findUnique({
      where: { email },
    });

    if (!user) {
      throw new AppError(401, 'Invalid credentials');
    }

    // Verify password
    const isValid = await comparePassword(password, user.password);
    if (!isValid) {
      throw new AppError(401, 'Invalid credentials');
    }

    // Generate tokens
    const accessToken = generateAccessToken({ userId: user.id, email: user.email });
    const refreshToken = generateRefreshToken({ userId: user.id, email: user.email });

    return {
      user: {
        id: user.id,
        email: user.email,
        name: user.name,
        language: user.language,
      },
      accessToken,
      refreshToken,
    };
  }

  async refresh(refreshToken: string) {
    try {
      const payload = verifyRefreshToken(refreshToken);

      // Verify user still exists
      const user = await prisma.user.findUnique({
        where: { id: payload.userId },
      });

      if (!user) {
        throw new AppError(401, 'User not found');
      }

      // Generate new access token
      const accessToken = generateAccessToken({ userId: user.id, email: user.email });

      return { accessToken };
    } catch (error) {
      throw new AppError(401, 'Invalid refresh token');
    }
  }

  async getMe(userId: string) {
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        email: true,
        name: true,
        language: true,
        createdAt: true,
      },
    });

    if (!user) {
      throw new AppError(404, 'User not found');
    }

    return user;
  }
}
