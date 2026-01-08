import { z } from 'zod';
import dotenv from 'dotenv';

// Load environment variables from .env file if present
dotenv.config();

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'production', 'test', 'build']).default('development'),
  PORT: z.string().transform(Number).default('3001'),
  DATABASE_URL: z.string().default('mysql://localhost:3306/leafmark'),
  JWT_SECRET: z.string().default('build-time-placeholder-min-32-characters-long'),
  JWT_REFRESH_SECRET: z.string().default('build-time-placeholder-min-32-characters-long'),
  CORS_ORIGIN: z.string().default('http://localhost:5173'),
  GOOGLE_BOOKS_API_KEY: z.string().optional(),
  ISBNDB_API_KEY: z.string().optional(),
});

export type Env = z.infer<typeof envSchema>;

const parseEnv = () => {
  // During build time, return a safe default configuration
  // This allows TypeScript compilation without runtime env variables
  const isBuildTime = process.env.NODE_ENV === 'build' || !process.env.DATABASE_URL;

  if (isBuildTime) {
    return {
      NODE_ENV: 'build' as const,
      PORT: 3001,
      DATABASE_URL: 'mysql://localhost:3306/leafmark',
      JWT_SECRET: 'build-time-placeholder-min-32-characters-long',
      JWT_REFRESH_SECRET: 'build-time-placeholder-min-32-characters-long',
      CORS_ORIGIN: 'http://localhost:5173',
      GOOGLE_BOOKS_API_KEY: undefined,
      ISBNDB_API_KEY: undefined,
    };
  }

  // Runtime validation with proper error handling
  try {
    return envSchema.parse(process.env);
  } catch (error) {
    if (error instanceof z.ZodError) {
      console.error('❌ Environment validation failed:');
      error.errors.forEach((err) => {
        console.error(`  - ${err.path.join('.')}: ${err.message}`);
      });
      process.exit(1);
    }
    throw error;
  }
};

export const env = parseEnv();
