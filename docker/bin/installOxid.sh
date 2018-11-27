cd #!/usr/bin/env bash

ln -fs ${DOCKER_DOCUMENT_ROOT}/oxrun/bin/oxrun /usr/local/bin

if [ ! -f "${DOCKER_DOCUMENT_ROOT}/oxrun/vendor" ]; then
    pushd ${DOCKER_DOCUMENT_ROOT}/oxrun/ && \
    composer install --no-interaction && \
    popd;
fi

if [ ! -f "${DOCKER_DOCUMENT_ROOT}/config.inc.php" ]; then

    echo "Install Shop";

    install_dir=$(dirname ${DOCKER_DOCUMENT_ROOT})

    echo "Download 'oxid-esales/oxideshop-project:${OXID_SHOP_VERSION}'";

    composer create-project --no-dev --keep-vcs --working-dir=/ \
        oxid-esales/oxideshop-project /tmp/preinstall \
        ${OXID_SHOP_VERSION}

    chown -R www-data: "/tmp/preinstall" && \
    rsync -ap /tmp/preinstall/ ${install_dir} && \
    rm -rf /tmp/preinstall

    echo "Configure OXID eShop ...";
    sed -i "s/<dbHost>/${MYSQL_HOST}/" ${DOCKER_DOCUMENT_ROOT}/config.inc.php && \
    sed -i "s/<dbName>/${MYSQL_DATABASE}/" ${DOCKER_DOCUMENT_ROOT}/config.inc.php && \
    sed -i "s/<dbUser>/${MYSQL_USER}/" ${DOCKER_DOCUMENT_ROOT}/config.inc.php && \
    sed -i "s/<dbPwd>/${MYSQL_PASSWORD}/" ${DOCKER_DOCUMENT_ROOT}/config.inc.php && \
    sed -i "s|<sShopURL>|${OXID_SHOP_URL}|" ${DOCKER_DOCUMENT_ROOT}/config.inc.php && \
    sed -i "s/'<sShopDir>'/__DIR__ . '\/'/" ${DOCKER_DOCUMENT_ROOT}/config.inc.php && \
    sed -i "s/'<sCompileDir>'/__DIR__ . '\/tmp'/" ${DOCKER_DOCUMENT_ROOT}/config.inc.php

    echo "Create mysql database schema ...";
    mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < ${DOCKER_DOCUMENT_ROOT}/Setup/Sql/database_schema.sql && \
    mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < ${install_dir}/vendor/oxid-esales/oxideshop-demodata-ce/src/demodata.sql && \
    rm -Rf ${DOCKER_DOCUMENT_ROOT}/Setup

    echo "Copy demo asset ...";
    ${install_dir}/vendor/bin/oe-eshop-demodata_install

    echo "Create OXID views ...";
    oxrun view:update --shopDir ${DOCKER_DOCUMENT_ROOT}

#   oxrun user:password --shopDir ${DOCKER_DOCUMENT_ROOT} -a ${OXID_ADMIN_PASSWORD} ${OXID_ADMIN_USERNAME}

#    oxrun install:shop \
#        --oxidVersion="${OXID_SHOP_VERSION}" \
#        --dbHost="${MYSQL_HOST}" \
#        --dbUser="${MYSQL_USER}" \
#        --dbPwd="${MYSQL_PASSWORD}" \
#        --dbName=" " \
#        --installationFolder="${DOCKER_DOCUMENT_ROOT}/" \
#        --shopURL="${OXID_SHOP_URL}" \
#        --adminUser="${OXID_ADMIN_PASSWORD}" \
#        --adminPassword="${OXID_ADMIN_USERNAME}"

fi

echo ""
echo "WebSeite: ${OXID_SHOP_URL}";
echo ""

/usr/sbin/apache2ctl -D FOREGROUND