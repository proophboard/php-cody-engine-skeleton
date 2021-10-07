# PHP Cody + Event Engine Skeleton

This is a project skeleton for event engine 
The following tutorial shows how to connect and use the *Cody* bot with the [Event Engine Skeleton](https://github.com/event-engine/php-engine-skeleton "Event Engine Skeleton on GitHub")
to generate PHP code from [prooph board](https://free.prooph-board.com/ "Free version of prooph board") event map.

## Installation
Please make sure you have installed [Docker](https://docs.docker.com/install/ "Install Docker") 
and [Docker Compose](https://docs.docker.com/compose/install/ "Install Docker Compose").

Install the *PHP Cody + Event Engine Skeleton*:

```
docker run --rm -it \
    -v $(pwd):/app \
    prooph/composer:8.0 create-project -v \
        --stability dev \
        --remove-vcs \
        proophboard/php-cody-engine-skeleton \
        php-cody-engine-tutorial-demo
```

## Running

To start this application, you can use the `dev.sh` bash script in the root directory. 
It does some checks and setups the application. After that you can use Docker Compose as usual.

```bash
$ ./dev.sh
```

Prepare the database:

```
$ docker-compose run --rm pb-ces-worker bin/console database:prepare
```

## Services

This skeleton comes with some preconfigured services for development.

- [Backend: Message Box](http://localhost/api/v1/messagebox): `http://localhost/api/v1/messagebox`
- [Backend: Message Box Schema](http://localhost/api/v1/messagebox-schema): `http://localhost/api/v1/messagebox-schema`
- [Event Engine Cockpit](https://localhost:4444/api/cockpit): `https://localhost:4444/api/cockpit`
  - ensure that you configure the right schema url `https://localhost:4444/api/cockpit` and message box url `https://localhost:4444/api/messagebox`

### Event Engine Cockpit
[Cockpit](https://github.com/event-engine/cockpit) is an admin UI for Event Engine. You can access it on port `4444`: [https://localhost:4444](https://localhost:4444).
The skeleton is preconfigured with the [cockpit-php-backend handler](https://github.com/event-engine/cockpit-php-backend).

*Note: To avoid CORS issues the Nginx configuration of the Cockpit server is modified to also act as a reverse proxy for requests from Cockpit to the backend.*

You can execute the built-in `HealthCheck` query to very that Cockpit can access the Event Engine backend.

Ensure that you configure the right schema url `https://localhost:4444/api/cockpit` and message box url `https://localhost:4444/api/messagebox` via settings.

![HealthCheck](https://github.com/event-engine/php-engine-skeleton/blob/master/docs/assets/cockpit_health_check.png?raw=true)

### Cody

**prooph board is a modeling tool specifically designed for remote Event Storming. It ships with realtime collaboration
features for teams (only available in paid version). The free version is a standalone variant without any backend
connection. Your work is stored in local storage and can be exported. It is hosted on Github Pages and has the same
code generation capabilities as the SaaS version.**

You can use [prooph board free version](https://free.prooph-board.com/ "Free version of prooph board") and model
the [building tutorial](https://event-engine.io/tutorial/intro.html#2-1 "Event Engine Building Tutorial") on the event
map (no login required).

Create a new board called "Cody Tutorial". You'll be redirected to the fresh board. Choose "Cody" from top menu to
open the **Cody Console**. Just hit ENTER in the console to connect to the default Cody server that we've setup and started
in the previous step.

Finally type "**/help**" in the console to let Cody explain the basic functionality.

Cody will generate the following boilerplate for you:
- Event Engine API description for commands, aggregates, domain events and queries
- Command, aggregate, domain event and query classes with corresponding value objects based on [metadata](https://wiki.prooph-board.com/Card-Metadata "prooph board card metadata") (JSON schema)
- Glue code between command, corresponding aggregate and corresponding domain events and also queries

## Troubleshooting

With the command `docker-compose ps` you can list the running containers. This should look like the following list:

```bash
            Name                           Command               State                                      Ports                                   
----------------------------------------------------------------------------------------------------------------------------------------------------
pb-ces_nginx_1                  /docker-entrypoint.sh ngin ...   Up       0.0.0.0:443->443/tcp,:::443->443/tcp, 0.0.0.0:8080->80/tcp,:::8080->80/tcp
pb-ces_pb-ces-cockpit_1         nginx -g daemon off;             Up       0.0.0.0:4444->443/tcp,:::4444->443/tcp, 80/tcp                            
pb-ces_pb-ces-cody_1            docker-php-entrypoint vend ...   Up       0.0.0.0:3311->8080/tcp,:::3311->8080/tcp                                  
pb-ces_pb-ces-composer-cody_1   composer --ansi install          Exit 0                                                                             
pb-ces_pb-ces-composer_1        composer --ansi install          Exit 0                                                                             
pb-ces_pb-ces-test_1            docker-php-entrypoint php -a     Exit 0                                                                             
pb-ces_pb-ces-worker_1          docker-php-entrypoint php -a     Exit 0                                                                             
pb-ces_php_1                    docker-php-entrypoint php-fpm    Up       9000/tcp                                                                  
pb-ces_postgres_1               docker-entrypoint.sh -c ma ...   Up       0.0.0.0:5432->5432/tcp,:::5432->5432/tcp
```

Make sure that all required ports are available on your machine. If not you can modify port mapping in the `docker-compose.yml`.
