#!/bin/bash
set -e

docker login -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io
docker tag keboola/provisioning-client quay.io/keboola/provisioning-client:${TRAVIS_TAG}
docker tag keboola/provisioning-client quay.io/keboola/provisioning-client:latest
docker images
docker push quay.io/keboola/provisioning-client:${TRAVIS_TAG}
docker push quay.io/keboola/provisioning-client:latest
