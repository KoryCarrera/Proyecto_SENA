#DockerFile para el proyecto SENA, usaremos la imagen preconstruida de apache y php de dockerhub
FROM php:8.2-apache

#Procedemos a instalar dependencias para el proyecto (La imagen es en base Debian por lo cual se utilizan sus comandos de bash)
RUN apt-get update && apt-get install -y \
libpng-dev \
libjpeg-dev \
libfreetype6-dev \
libzip-dev \
zip \
unzip \
git \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip

#Habilitamos Mod_Rewrite (Necesario para que apache maneje correctamente rutas)
RUN a2enmod rewrite

#Configuramos la carpeta raiz del proyecto (Public) en apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/Public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

#configuramos los permisos para la subida de archivos
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 15M" >> /usr/local/etc/php/conf.d/uploads.ini

#Se define el directorio de trabajo del contenedor
WORKDIR /var/www/html

#Copiamos el contenido de la carpeta donde ejecutamos el dockerfile al contenedor
COPY . .

#Instalamos composer para gestionar librerias
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install  --no-interaction --optimize-autoloader

#Ajustamos permisos para apache (leer y escribir archivos)
RUN chown -R www-data:www-data /var/www/html