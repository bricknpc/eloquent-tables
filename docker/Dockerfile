FROM php:8.4-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# Install Composer 2.8
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.8.6 --install-dir=/usr/local/bin --filename=composer

# Create a user with your host UID/GID
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m appuser

# Create app directory
WORKDIR /app

# Switch to non-root user
USER appuser