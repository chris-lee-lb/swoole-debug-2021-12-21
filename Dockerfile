FROM asia-east1-docker.pkg.dev/sevensenses-nm-beta/utils/metasens-passport-app:dev AS build
WORKDIR /getoken_code
COPY ./composer.json ./composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-scripts

FROM asia-east1-docker.pkg.dev/sevensenses-nm-beta/utils/metasens-passport-app:dev
COPY --chown=www-data:www-data --from=build /getoken_code .
COPY --chown=www-data:www-data . .
RUN composer run post-autoload-dump && find ./ -type d -exec chmod 755 {} + && chown -R www-data.www-data /getoken_code
