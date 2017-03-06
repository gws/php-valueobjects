FROM php:7.1-cli

RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-install -j$(nproc) bcmath intl \
    && rm -rf /var/lib/apt/lists/*
