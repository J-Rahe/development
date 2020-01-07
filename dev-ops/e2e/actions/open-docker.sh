#!/usr/bin/env bash

./psh.phar e2e:prepare-container;

vendor/shopware/platform/src/__CYPRESS_ENV__/Resources/app/__CYPRESS_FOLDER__/test/e2e/node_modules/@shopware-ag/e2e-testsuite-platform/node_modules/.bin/cypress open --project ./vendor/shopware/platform/src/__CYPRESS_ENV__/Resources/app/__CYPRESS_FOLDER__/test/e2e --config baseUrl=__APP_URL__ --env localUsage=__CYPRESS_LOCAL__,projectRoot=__PROJECT_ROOT__;
