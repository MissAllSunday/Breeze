FROM php:8.3-cli

RUN docker-php-ext-install pdo_mysql mysqli

WORKDIR /app

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "index.php"]
