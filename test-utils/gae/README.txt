This directory contains utility to simulate Google App Engine deployment locally.

How to:
1. Run `test-utils/gae/build-gae-image.sh` from the root directory of this repo. A docker image
   called `daftarproperti` and a docker-compose.yml.generated file will be generated.
2. Run the docker-compose: `docker-compose -f test-utils/gae/docker-compose.yml.generated up`
