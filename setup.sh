# Install tool for creating certificates (MacOS)
brew install mkcert
mkcert -install
# Create wildcard HTTPS certificate
mkcert -cert-file ./.docker/caddy/certs/wildcard.local.pem -key-file ./.docker/caddy/certs/wildcard.key.pem orders.dev "*.orders.dev"

cp .env.example .env
cp ./docker-compose.override.example.yml ./docker-compose.override.yml
cp ./.docker/php/conf.d/app.dev.example.ini ./.docker/php/conf.d/app.dev.ini
cp ./adminero-backend/core/.env.local.example ./adminero-backend/core/.env.local

chmod +x ./.docker/database/init-cms-db.sh
