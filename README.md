## Daftar Properti

This is the backend system of Daftar Properti:

* Telegram webhook
* Web API
* Public web pages

### Development

Read [Laravel 10.x](https://laravel.com/docs/10.x).

One-time set up:

* Install composer for your OS
* Run `composer install`
* Set up local `.env` file (copy and edit from .env.example)
* Run `php artisan key:generate`

Running development server:

* Run `php artisan serve`
* development server listens at http://localhost:8000

Running static analyzer:

* Run `vendor/bin/phpstan analyse`

Running tests:

* Prepare test environment (see test-utils/)
* Run `php artisan test`

### Setting up a Telegram Bot
Daftar Properti uses Telegram Bot to receive new listings. Here is a guide how to set up your own development bot:

* Chat with `BotFather` and type `/newbot`. Full reference: https://core.telegram.org/bots/features#botfather
* Follow the prompts, e.g. set the bot name and id. At the end, BotFather will give you a bot token. Keep this token
  safely and securely.
* Use the bot token in your env var called `TELEGRAM_BOT_TOKEN`. The system uses this token to verify user
  authentication, send reply messages, etc.
* You can use the helper script to further set up your bot: `php artisan app:setup-telegram-bot {base-url} {ui-url}`,
  with `base-url` being your publicly accessible address of the backend and `ui-url` is the front-end.
* Your bot is ready to use for development. Chat with it by searching for its bot id and you should be able to forward
  listings to the bot. If your setup is correct, the webhook handler will handle incoming messages and reply.

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
