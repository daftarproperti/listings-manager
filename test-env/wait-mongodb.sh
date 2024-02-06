#!/bin/bash

MONGO_HOST="test-mongodb"
MONGO_PORT=27017

# Timeout in seconds
TIMEOUT=60
START_TIME=$(date +%s)

echo "Waiting for MongoDB to start..."

# Function to check if MongoDB is up
check_mongo() {
    if nc -z $MONGO_HOST $MONGO_PORT; then
        echo "MongoDB is up!"
        exit 0
    else
        echo "Waiting for MongoDB..."
    fi
}

# Loop until MongoDB is up or timeout is reached
until [ $(($(date +%s) - $START_TIME)) -ge $TIMEOUT ]; do
    check_mongo
    sleep 1
done

echo "Timeout reached. MongoDB did not become available."
exit 1
