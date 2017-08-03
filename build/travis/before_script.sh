# only install magento if MAGENTO_VERSION has been set
# Borrowed from n89-magerun

grep -v 'remove uninstall test' phpunit.xml.dist > phpunit.xml

if [ ! -z ${MAGENTO_VERSION+x} ]; then


    if [ ! -f bin/n98-magerun.phar  ]; then
        echo -e "Downloading magerun"
        wget --no-check-certificate https://files.magerun.net/n98-magerun.phar
        chmod +x n98-magerun.phar
        mv n98-magerun.phar bin/
    fi

    echo "ensuring magento ${MAGENTO_VERSION} is installed"

    db_user="${SETUP_DB_USER:-root}"
    db_pass=""

    if [ "" == "${db_pass}" ]; then
        mysql -u"${db_user}" -e 'CREATE DATABASE IF NOT EXISTS `magento_travis`;'
    else
        mysql -u"${db_user}" -p"${db_pass}" -e 'CREATE DATABASE IF NOT EXISTS `magento_travis`;'
    fi;

    # target_directory="${SETUP_DIR:-./}${MAGENTO_VERSION}"
    target_directory="${SETUP_DIR:-./}public"
    echo $(readlink -f "${target_directory}") > .magedir

    export N98_MAGERUN_TEST_MAGENTO_ROOT="${target_directory}"

    if [ ! -f "${target_directory}/app/etc/config.xml" ]; then
        php -dmemory_limit=1g -f ./bin/n98-magerun.phar -- install \
                    --magentoVersionByName="${MAGENTO_VERSION}" --installationFolder="${target_directory}" \
                    --dbHost=127.0.0.1 --dbUser="${db_user}" --dbPass="${db_pass}" --dbName="magento_travis" \
                    --installSampleData=${INSTALL_SAMPLE_DATA} --useDefaultConfigParams=yes \
                    --baseUrl="${base_url:-http://travis.magento.local/}"
    fi;

    if [ ! -f ".magedir" ]; then
        echo -e "No magedir file found"
        echo -e "${target_directory}"
        echo ${target_directory} > .magedir
    fi

else

    echo "no magento version to install"

fi

