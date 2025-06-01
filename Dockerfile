# Use an official PHP image with PostgreSQL support
FROM php:8.2-cli

# Install PostgreSQL PDO driver
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Set working directory
WORKDIR /var/www/html

# Copy all files into the container
COPY . .

# Expose port 8080 for Render
EXPOSE 8080

# Run the PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080"]
