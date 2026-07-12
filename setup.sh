#!/bin/bash

# Complete setup and verification script

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  First API Endpoint - Postgres Persistence Setup              ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# 1. Check prerequisites
echo "1️⃣  Checking prerequisites..."
command -v docker >/dev/null 2>&1 || { echo "❌ Docker not found"; exit 1; }
command -v docker-compose >/dev/null 2>&1 || { echo "❌ Docker Compose not found"; exit 1; }
echo "✓ Docker and Docker Compose installed"
echo ""

# 2. Check .env file
echo "2️⃣  Checking .env configuration..."
if [ ! -f .env ]; then
    echo "⚠️  .env not found, creating from .env.example..."
    cp .env.example .env
    echo "✓ Created .env"
else
    echo "✓ .env exists"
fi
echo ""

# 3. Stop existing containers
echo "3️⃣  Cleaning up existing containers..."
docker compose down -v 2>/dev/null || true
echo "✓ Cleaned up"
echo ""

# 4. Build and start
echo "4️⃣  Building and starting services..."
docker compose up -d --build
echo "✓ Services starting..."
echo ""

# 5. Wait for services
echo "5️⃣  Waiting for services to be healthy..."
echo "   - Checking database..."
for i in {1..30}; do
    if docker compose exec -T db pg_isready -U postgres >/dev/null 2>&1; then
        echo "   ✓ Database ready"
        break
    fi
    echo -n "."
    sleep 1
done
echo ""

echo "   - Checking Redis..."
for i in {1..30}; do
    if docker compose exec -T redis redis-cli ping >/dev/null 2>&1; then
        echo "   ✓ Redis ready"
        break
    fi
    echo -n "."
    sleep 1
done
echo ""

echo "   - Checking app..."
for i in {1..30}; do
    if curl -s http://localhost:10000/api/v1/health >/dev/null 2>&1; then
        echo "   ✓ App ready"
        break
    fi
    echo -n "."
    sleep 1
done
echo ""

# 6. Check database
echo "6️⃣  Verifying database..."
POSTS_COUNT=$(docker compose exec -T db psql -U postgres -d be01 -c "SELECT COUNT(*) FROM posts;" 2>/dev/null | grep -oE '[0-9]+' | tail -1)
echo "✓ Database has $POSTS_COUNT posts"
echo ""

# 7. Test API
echo "7️⃣  Testing API endpoints..."
echo ""
echo "   a) Health check:"
curl -s http://localhost:10000/api/v1/health | jq '.services'
echo ""

echo "   b) List posts:"
curl -s http://localhost:10000/api/v1/posts | jq '.data | {total, first_post: .posts[0]?}'
echo ""

echo "   c) Create post:"
NEW_POST=$(curl -s -X POST http://localhost:10000/api/v1/posts \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Setup Test Post",
    "content": "Created during setup verification",
    "author": "Setup Script"
  }')
echo "$NEW_POST" | jq '.data.post | {id, title, author}'
echo ""

# 8. Run tests
echo "8️⃣  Running test suite..."
docker compose exec -T app php artisan test --no-progress 2>/dev/null | tail -5
echo ""

# 9. Show next steps
echo "9️⃣  Setup complete! 🎉"
echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  Quick Start                                                   ║"
echo "╠════════════════════════════════════════════════════════════════╣"
echo "║                                                                ║"
echo "║  View all posts:                                              ║"
echo "║  curl http://localhost:10000/api/v1/posts                     ║"
echo "║                                                                ║"
echo "║  Create a post:                                               ║"
echo "║  curl -X POST http://localhost:10000/api/v1/posts \\          ║"
echo "║    -H \"Content-Type: application/json\" \\                     ║"
echo "║    -d '{                                                      ║"
echo "║      \"title\": \"My Post\",                                    ║"
echo "║      \"content\": \"Content here\",                             ║"
echo "║      \"author\": \"Your Name\"                                  ║"
echo "║    }'                                                          ║"
echo "║                                                                ║"
echo "║  Persistence test (restart proof):                            ║"
echo "║  ./persistence-test.sh                                        ║"
echo "║                                                                ║"
echo "║  Database analysis:                                           ║"
echo "║  ./explain-analysis.sh                                        ║"
echo "║                                                                ║"
echo "║  View logs:                                                   ║"
echo "║  docker compose logs -f app                                   ║"
echo "║                                                                ║"
echo "║  Stop everything:                                             ║"
echo "║  docker compose down                                          ║"
echo "║                                                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "📚 Full documentation in README.md"
echo ""
