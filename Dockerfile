FROM php:8.2-apache

# Habilitar extensiones PDO MySQL y mod_rewrite
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Copiar el código del proyecto al directorio web de Apache
COPY . /var/www/html/

# Ajustar permisos de archivos
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto estándar
EXPOSE 80

CMD ["apache2-foreground"]
