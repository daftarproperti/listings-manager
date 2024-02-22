## MLS Indonesia (TODO: brand)

This is the backend system of MLS Indonesia:
* Telegram webhook
* Web API

### Development

Read [Laravel 10.x](https://laravel.com/docs/10.x).

One-time set up:
* Install composer for your OS
* Run `composer install`
* Set up local `.env` file (copy and edit from .env.example)

Running development server:
* Run `php artisan serve`
* development server listens at http://localhost:8000

Running static analyzer:
* Run `vendor/bin/phpstan analyse`

Running tests:
* Prepare test environment (see test-utils/)
* Run `php artisan test`

### Running tests in a container
To make it easy to run tests in an isolated environment, e.g. in a CI, a
docker-compose is provided in test-env/.

1. Create necessary docker external resources:
```
$ docker network create global_network
```

2. Build image and run the container:
```
$ docker-compose -f test-env/docker-compose.yml build
$ docker-compose -f test-env/docker-compose.yml run test-app
```

### Viewing Logs
Log destination can be configured by setting LOG_CHANNEL environment variable.

For local development, it's simplest to use LOG_CHANNEL=single or leave it unset. This will write logs to
`storage/logs/laravel.log`.

For GCP deployment, set LOG_CHANNEL=gcp to utilize Google Cloud Logging as the log output. To view the logs in GCP
console, go to Logs Explorer (https://console.cloud.google.com/logs), and filter the logs by the log name:
```
logName="projects/{project-name}/logs/app"
```

### Production Deployment
TODO

### Database set up
TODO
