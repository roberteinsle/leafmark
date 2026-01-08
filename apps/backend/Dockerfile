# Development stage
FROM node:20-alpine AS development

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY apps/backend/package*.json ./apps/backend/
COPY packages/shared/package*.json ./packages/shared/

# Install all dependencies (including dev)
RUN npm ci

# Copy source code
COPY apps/backend ./apps/backend
COPY packages/shared ./packages/shared
COPY tsconfig.json ./

# Generate Prisma client
RUN npm run prisma:generate --workspace=apps/backend

EXPOSE 3001

CMD ["npm", "run", "dev", "--workspace=apps/backend"]

# Builder stage
FROM node:20-alpine AS builder

WORKDIR /app

# Copy root package files for workspace setup
COPY package*.json ./

# Create workspace directories structure
RUN mkdir -p apps/backend apps/frontend packages/shared

# Copy workspace package files
COPY apps/backend/package*.json ./apps/backend/
COPY packages/shared/package*.json ./packages/shared/

# Install ALL dependencies including devDependencies (needed for build)
RUN npm install && \
    echo "✓ npm install completed" && \
    ls -la node_modules/.bin | head -20

# Copy source code
COPY apps/backend ./apps/backend
COPY packages/shared ./packages/shared
COPY tsconfig.json ./

# Generate Prisma client
RUN npm run prisma:generate --workspace=apps/backend && \
    echo "✓ Prisma client generated"

# Build application with NODE_ENV=build to skip env validation
ENV NODE_ENV=build
RUN echo "Starting TypeScript build..." && \
    cd apps/backend && \
    npx tsc && \
    echo "✓ TypeScript build completed" && \
    ls -la dist/
ENV NODE_ENV=production

# Production stage
FROM node:20-alpine AS production

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY apps/backend/package*.json ./apps/backend/
COPY packages/shared/package*.json ./packages/shared/

# Copy node_modules from builder (includes all dependencies)
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/apps/backend/node_modules ./apps/backend/node_modules

# Copy built application and prisma
COPY --from=builder /app/apps/backend/dist ./apps/backend/dist
COPY apps/backend/prisma ./apps/backend/prisma

# Create non-root user
RUN addgroup -g 1001 -S nodejs && adduser -S nodejs -u 1001
USER nodejs

EXPOSE 3001

# Run migrations and start server
CMD ["sh", "-c", "npm run prisma:migrate:deploy --workspace=apps/backend && node apps/backend/dist/index.js"]
