#!/bin/bash
echo Deploying Symfony website

# ----------------------
# KUDU Deployment Script
# Version: 0.1.10
# ----------------------

# Helpers
# -------

exitWithMessageOnError () {
  if [ ! $? -eq 0 ]; then
    echo "An error has occurred during web site deployment."
    echo $1
    exit 1
  fi
}

echo Validating prerequisites
# Prerequisites
# -------------

# Verify node.js installed
hash node 2>/dev/null
exitWithMessageOnError "Missing node.js executable, please install node.js, if already installed make sure it can be reached from current environment."

# Setup
# -----
echo Setup env variables

SCRIPT_DIR="${BASH_SOURCE[0]%\\*}"
SCRIPT_DIR="${SCRIPT_DIR%/*}"
ARTIFACTS=$SCRIPT_DIR/../artifacts
KUDU_SYNC_CMD=${KUDU_SYNC_CMD//\"}

if [[ ! -n "$DEPLOYMENT_SOURCE" ]]; then
  DEPLOYMENT_SOURCE=$SCRIPT_DIR
fi

if [[ ! -n "$NEXT_MANIFEST_PATH" ]]; then
  NEXT_MANIFEST_PATH=$ARTIFACTS/manifest

  if [[ ! -n "$PREVIOUS_MANIFEST_PATH" ]]; then
    PREVIOUS_MANIFEST_PATH=$NEXT_MANIFEST_PATH
  fi
fi

if [[ ! -n "$DEPLOYMENT_TARGET" ]]; then
  DEPLOYMENT_TARGET=$ARTIFACTS/wwwroot
else
  KUDU_SERVICE=true
fi

echo Checking KUDU is there
if [[ ! -n "$KUDU_SYNC_CMD" ]]; then
  # Install kudu sync
  echo Installing Kudu Sync
  npm install kudusync -g --silent
  exitWithMessageOnError "npm failed"

  if [[ ! -n "$KUDU_SERVICE" ]]; then
    # In case we are running locally this is the correct location of kuduSync
    KUDU_SYNC_CMD=kuduSync
  else
    # In case we are running on kudu service this is the correct location of kuduSync
    KUDU_SYNC_CMD=$APPDATA/npm/node_modules/kuduSync/bin/kuduSync
  fi
fi

##################################################################################################################################
# Deployment
# ----------
echo Initializing PHP

export PHP_INI_SCAN_DIR=$DEPLOYMENT_SOURCE/app/websites/php
export PHP_BASE_CUSTOM_EXTENSIONS_DIR=$DEPLOYMENT_SOURCE/app/websites/php/ext

echo Initializing Symfony project
export SYMFONY_ENV=azure

cd "$DEPLOYMENT_SOURCE"
# Run composer install only if there is a difference between the new composer.lock and the previous one
diff composer.lock "$DEPLOYMENT_TARGET/composer.lock"

if [ ! $? -eq 0 ]; then
  # Check composer is there
  if [ ! -f composer.phar ];
  then
    echo "Downloading Composer"
    curl -sS https://getcomposer.org/installer | php -- --quiet
    exitWithMessageOnError "Composer download failed"
  else
    echo "Updating Composer"
    php composer.phar self-update
    exitWithMessageOnError "Composer self-update failed"
  fi

  php composer.phar install --prefer-dist -v --no-interaction --optimize-autoloader
  exitWithMessageOnError "Composer install failed"
else
  echo No need to run composer install
  php app/console cache:clear

  php app/console assets:install web
fi

php app/console assetic:dump

# 1. KuduSync
if [[ "$IN_PLACE_DEPLOYMENT" -ne "1" ]]; then
  echo Handling Basic Web Site deployment.
  echo Kudu Sync from "$DEPLOYMENT_SOURCE" to "$DEPLOYMENT_TARGET"
  "$KUDU_SYNC_CMD" -v 50 -f "$DEPLOYMENT_SOURCE" -t "$DEPLOYMENT_TARGET" -n "$NEXT_MANIFEST_PATH" -p "$PREVIOUS_MANIFEST_PATH" -i ".git;.hg;.deployment;deploy.sh"
  exitWithMessageOnError "Kudu Sync failed"
fi

##################################################################################################################################

# Post deployment stub
if [[ -n "$POST_DEPLOYMENT_ACTION" ]]; then
  POST_DEPLOYMENT_ACTION=${POST_DEPLOYMENT_ACTION//\"}
  cd "${POST_DEPLOYMENT_ACTION_DIR%\\*}"
  "$POST_DEPLOYMENT_ACTION"
  exitWithMessageOnError "post deployment action failed"
fi

echo "Clearing Production cache by deleting $TEMP/cache/azure"
rm -Rf "$TEMP/cache/azure"

echo "Finished successfully."