#!/bin/bash -e

SITE_PATH="/var/www/html"

while !</dev/tcp/$WORDPRESS_DB_HOST/3306; do sleep 2; done

if ! $(wp core is-installed); then
    wp core install --path="$SITE_PATH" --url="http://localhost:8080" --title="WordPress Development" --admin_user=admin --admin_password=admin --admin_email=admin@local.host --skip-email
fi
