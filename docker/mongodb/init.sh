#!/bin/bash
mongo --eval "db.auth('$MONGO_INITDB_ROOT_USERNAME', '$MONGO_INITDB_ROOT_PASSWORD'); db = db.getSiblingDB('$MONGO_DBNAME'); db.createUser({ user: '$MONGO_USER', pwd: '$MONGO_PASSWORD', roles: [{ role: 'dbOwner', db: '$MONGO_DBNAME' }] }); db.bieristo.insert({});"
