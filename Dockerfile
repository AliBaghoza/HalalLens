FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure PHP settings for AI image uploads & Gemini API calls
RUN echo "max_execution_time = 120" > /usr/local/etc/php/conf.d/halallens.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/halallens.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/halallens.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/halallens.ini

# Support dynamic PORT from Render (defaults to 80 if not set)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

ENV PORT=80

WORKDIR /var/www/html
COPY . /var/www/html/

# Ensure uploads directory exists and is writable by www-data
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
