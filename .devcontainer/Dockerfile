FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

# Install MariaDB client and other dependencies
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
    mariadb-client \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*
