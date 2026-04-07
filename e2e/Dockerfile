FROM mcr.microsoft.com/playwright:v1.52.0-noble

WORKDIR /e2e

RUN npm init -y && npm install @playwright/test@1.52.0

COPY playwright.config.ts .
COPY tests/ ./tests/

CMD ["npx", "playwright", "test", "--config=playwright.config.ts"]
