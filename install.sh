#!/bin/bash

# -------------------------------------------
# NOTE: order of operations matters
# -------------------------------------------

# Global Network - Gateway
docker network create --driver=bridge --attachable --internal=false gateway

# Build CC-Plus Image
docker build -t dzoladz/ccplus:latest -f docker/Dockerfile --no-cache .

# Run App Container
docker compose -f ./docker/docker-compose.yml up -d
