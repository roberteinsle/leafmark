import { z } from 'zod';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'production', 'test']).default('development'),
  PORT: z.string().transform(Number).default('3001'),
  DATABASE_URL: z.string().url().optional().default('mysql://localhost:3306/leafmark'),
  JWT_SECRET: z.string().min(32).optional().default('build-time-placeholder-min-32-characters-long'),
  JWT_REFRESH_SECRET: z.string().min(32).optional().default('build-time-placeholder-min-32-characters-long'),
  CORS_ORIGIN: z.string().url().optional().default('http://localhost:5173'),
  GOOGLE_BOOKS_API_KEY: z.string().optional(),
  ISBNDB_API_KEY: z.string().optional(),
});

export type Env = z.infer<typeof envSchema>;

const parseEnv = () => {
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
