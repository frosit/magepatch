#!/bin/bash
#
# test_setup.sh - install magento to run tests on a local development box
#
# Borrowed from n89-magerun
set -euo pipefail
IFS=$'\n\t'

export NC="\033[m"               # Color Reset
export Red='\033[0;31m'          # Red
export Green='\033[0;32m'        # Green
export Yellow='\033[0;33m'       # Yellow
export Blue='\033[0;34m'         # Blue

php=$(which php)
magerun=$(which n98-magerun)

buildecho()
{
    echo -en "${Yellow}[TEST-SETUP]${NC} "
    echo "${1}"
}

installed_magerun_cmd() {
    local magerun_cmd=$(which n98-magerun)

    if [ ! -e "${magerun_cmd}" ]; then
        echo >&2 "Error: can not find magerun '${magerun_cmd}', ensure you're running from project root."
        exit 2
    fi;

    echo "${magerun_cmd}"
}

# obtain installed version of the test-setup
installed_version() {
    local magerun_cmd=$(installed_magerun_cmd)
    local directory="${test_setup_directory}"

    local magento_local_xml="${directory}/app/etc/local.xml"

    if [ -e  "${magento_local_xml}" ]; then
        local version="$(php -dmemory_limit=1g -f "${magerun_cmd}" -- --root-dir="${directory}" sys:info -- "version")"
        echo "${version}"
    fi
}

# checks before starting the setup
ensure_environment() {
    local magerun_cmd=$(installed_magerun_cmd)
    local directory="${test_setup_directory}"

    buildecho "magerun command: '${test_setup_magerun_cmd}'"

    if [ ! -d "${directory}" ]; then
        mkdir -p "${directory}"
        # create .gitignore one-up if it does not yet exists to allow having the install within an existing git repo
        if [ ! -e "${directory}/../.gitignore" ]; then
            echo "*" > "${directory}/../.gitignore"
        fi
    fi;
    buildecho "directory: '${directory}'"
}

# create mysql database if it does not yet exists
ensure_mysql_db() {
    local db_host="${test_setup_db_host}"
    local db_port="${test_setup_db_port}"
    local db_user="${test_setup_db_user}"
    local db_pass="${test_setup_db_pass}"
    local db_name="${test_setup_db_name}"

    mysql -u"${db_user}" --password="${db_pass}" -h"${db_host}" -P"${db_port}" -e "CREATE DATABASE IF NOT EXISTS \`${db_name}\`;"

    buildecho "mysql database: '${db_name}' (${db_user}@${db_host})"
}

# install into a directory a Magento version with or w/o sample-data
ensure_magento() {
    local directory="${test_setup_directory}"
    local db_host="${test_setup_db_host}"
    local db_port="${test_setup_db_port}"
    local db_user="${test_setup_db_user}"
    local db_pass="${test_setup_db_pass}"
    local db_name="${test_setup_db_name}"

    local magento_version="${1}"
    local install_sample_data="${2:-no}"

    local magerun_cmd=`${test_setup_magerun_cmd}`
    local version="$(installed_version)"

    if [ "" != "$version" ]; then
        buildecho "version '${version}' already installed, skipping setup"
    else
        $magerun install \
                    --magentoVersionByName="${magento_version}" --installationFolder="${directory}" \
                    --dbHost="${db_host}" --dbPort="${db_port}" --dbUser="${db_user}" --dbPass="${db_pass}" \
                    --dbName="${db_name}" \
                    --installSampleData="${install_sample_data}" --useDefaultConfigParams=yes \
                    --baseUrl="http://dev.magento.local/"
        buildecho "magento version '${magento_version}' installed."
    fi
}

test_setup_basename="n98-magerun"
test_setup_magerun_cmd=$(which n98-magerun)
test_setup_directory="./public"
test_setup_db_host="127.0.0.1"
test_setup_db_port="${test_setup_db_port:-3306}"
test_setup_db_user="root"
test_setup_db_pass="root"
test_setup_db_name="magento_magerun_test"

if [ "" != "$(installed_version)" ]; then
    buildecho "version '$(installed_version)' already installed, skipping setup"
else
    ensure_environment
    ensure_mysql_db
    ensure_magento "magento-mirror-1.9.2.4"
fi

# create stopfile if it does not yet exists
test_stopfile=".magedir"
if [ ! -f "${test_stopfile}" ]; then
    echo "${test_setup_directory}" > "${test_stopfile}"
    buildecho "stopfile ${test_stopfile} created: $(cat "${test_stopfile}")"
else
    buildecho "stopfile ${test_stopfile} exists: $(cat "${test_stopfile}")"
fi

buildecho "export N98_MAGERUN_TEST_MAGENTO_ROOT='${test_setup_directory}'"
