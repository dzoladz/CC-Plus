
network:
	@docker network create --driver=bridge --attachable --internal=false gateway

image:
	@docker build -t dzoladz/ccplus:latest -f docker/Dockerfile --no-cache .

build:
	@docker compose -f docker-compose.yml up -d
