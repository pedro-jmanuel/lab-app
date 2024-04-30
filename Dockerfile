FROM php:8.1-fpm

# Instala as dependências necessárias para o Laravel

RUN apt-get update && \
    apt-get install -y libzip-dev zip unzip && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip pdo_mysql

# Instala a extensão GD

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Download do Composer e o instala localmente para o usuário atual

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia o código-fonte da aplicação para o contêiner

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer update

# Espera 30 segundos antes de executar as migrations
RUN sleep 30

# Executa as migrações do banco de dados Laravel
RUN php artisan migrate --force

# Abre a porta 9000 para conexões
EXPOSE 80

CMD ["php-fpm"]
