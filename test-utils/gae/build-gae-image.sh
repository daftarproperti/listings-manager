#!/bin/bash

if ! [ -f app.yaml ]; then
    echo "app.yaml does not exist, copy from app.yaml.local and configure first."
    exit 1
fi

if ! command -v yq &> /dev/null
then
    echo "yq could not be found, get it from https://github.com/mikefarah/yq"
    exit 1
fi

if ! command -v pack &> /dev/null
then
    echo "pack could not be found, get Buildpack CLI from https://buildpacks.io/docs/tools/pack/#pack-cli"
    exit 1
fi

TEMP_DIR=$(mktemp -d)
echo Using temp dir $TEMP_DIR

trap "rm -rf $TEMP_DIR" EXIT

# Use fresh checkout to ignore local file modifications
git archive HEAD | tar -x -C $TEMP_DIR
cp app.yaml $TEMP_DIR/

# Change to temp dir to do the buildpack in a clean checkout.
pushd $TEMP_DIR
pack build daftarproperti --builder gcr.io/gae-runtimes/buildpacks/google-gae-22/php/builder \
    --env GOOGLE_COMPOSER_VERSION=2.6.5 \
    --env GOOGLE_RUNTIME_VERSION=8.2 \
    --env GOOGLE_RUNTIME=php \
    --env GAE_APPLICATION_YAML_PATH=./app.yaml

# Back to original dir.
popd

GEN_COMPOSE_FILE=test-utils/gae/docker-compose.yml.generated

cp test-utils/gae/docker-compose.yml.template $GEN_COMPOSE_FILE

# Populate environment variables based on app.yaml
yq e '.env_variables' app.yaml > /tmp/env.yml
yq eval -i '
  .services.default.environment = (
    .services.default.environment // {}
    ) * load("/tmp/env.yml")
' $GEN_COMPOSE_FILE

echo Generated $GEN_COMPOSE_FILE.
echo Use docker-compose to up the service.