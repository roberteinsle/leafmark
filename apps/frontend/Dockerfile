# Development stage
FROM node:20-alpine AS development

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY apps/frontend/package*.json ./apps/frontend/
COPY packages/shared/package*.json ./packages/shared/

# Install all dependencies
RUN npm ci

# Copy source code
COPY apps/frontend ./apps/frontend
COPY packages/shared ./packages/shared
COPY tsconfig.json ./

EXPOSE 5173

CMD ["npm", "run", "dev", "--workspace=apps/frontend"]

# Builder stage
FROM node:20-alpine AS builder

WORKDIR /app

# Copy all package files
COPY package*.json ./
COPY apps/frontend/package*.json ./apps/frontend/
COPY packages/shared/package*.json ./packages/shared/

# Install ALL dependencies (using install for better compatibility)
RUN npm install --ignore-scripts

# Copy source code
COPY apps/frontend ./apps/frontend
COPY packages/shared ./packages/shared
COPY tsconfig.json ./

# Build for production
ARG VITE_API_URL
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build --workspace=apps/frontend

# Production stage - nginx
FROM nginx:alpine AS production

# Copy built files
COPY --from=builder /app/apps/frontend/dist /usr/share/nginx/html

# Copy nginx configuration
COPY apps/frontend/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
