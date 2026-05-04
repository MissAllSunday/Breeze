FROM node:22-slim

RUN apt-get update && apt-get install -y --no-install-recommends curl && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .

# Remove the local .env file so that Docker Compose environment variables
# take full precedence. Vite's loadEnv() always reads .env files and ignores
# runtime env vars; without this, the baked-in dev URL would override the
# container's configured API endpoint.
RUN rm -f .env

ENV BROWSER=none

EXPOSE 3000

CMD ["npx", "vite", "--no-open"]

