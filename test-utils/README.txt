This directory contains a convenient way to set up a local mongodb instance
that is set up to work with feature tests.

Just run:
$ docker-compose up test-mongodb

The database is not persistent so we can start with a clean state just by restarting the container.
