# Docker
## Build
This project is built on Docker to allow it to be ran on any machine without any other prior setup. 
To get started, install Docker and run the following from the project root.
```
docker-compose up
```
This might take a few minutes as it will:
 - Download and run a Composer image to install dependencies
 - Download and run a PHP-FPM image and add PHP extensions
 - Copy files onto the PHP server
 - Download and run MongoDB and Mongo Express images
 - Download and run an NGINX proxy image.
 
 The server will now be running on port 8080, you can test this by navigating to https://localhost:8080/hello.
 
 Mongo Express is a Mongo UI, it can be accessed at https://localhost:8081.
 ## Develop
For development, you will want to install Composer and PHP locally aswell as a PHP IDE such as PHPStorm.

To exec into the PHP web container:
``` 
docker-compose exec php sh
```

## Testing
```
bin/docker-test
```
