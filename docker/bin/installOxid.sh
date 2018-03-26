#!/usr/bin/env bash

ln -fs ${DOCKER_DOCUMENT_ROOT}/oxrun/bin/oxrun /usr/local/bin

if [ ! -f "${DOCKER_DOCUMENT_ROOT}/config.inc.php" ]; then

    echo "Install Shop ${OXID_SHOP_VERSION}";

    oxrun install:shop \
        --oxidVersion="${OXID_SHOP_VERSION}" \
        --dbHost="${MYSQL_HOST}" \
        --dbUser="${MYSQL_USER}" \
        --dbPwd="${MYSQL_PASSWORD}" \
        --dbName="${MYSQL_DATABASE}" \
        --installationFolder="${DOCKER_DOCUMENT_ROOT}/" \
        --shopURL="${OXID_SHOP_URL}" \
        --adminUser="${OXID_ADMIN_PASSWORD}" \
        --adminPassword="${OXID_ADMIN_USERNAME}"

    chown -R www-data: "${DOCKER_DOCUMENT_ROOT}/"
fi

echo ""
echo "WebSeite: ${OXID_SHOP_URL}";
echo ""

/usr/sbin/apache2ctl -D FOREGROUND