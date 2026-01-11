ARG PHP_VERSION=8.2

FROM php:${PHP_VERSION}-alpine

# Composer uses git to determine the root package version
RUN apk add --no-cache git

#
# Install necessary PHP extensions (Including those for external tools)
#
# Service Manager does not need any additional extensions but tooling does
#
COPY --from=ghcr.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath intl xdebug

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
