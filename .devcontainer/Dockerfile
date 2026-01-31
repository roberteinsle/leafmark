FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

# Remove outdated Yarn repository to avoid GPG key errors
RUN rm -f /etc/apt/sources.list.d/yarn.list

# Install MariaDB client and other dependencies
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
    mariadb-client \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*
