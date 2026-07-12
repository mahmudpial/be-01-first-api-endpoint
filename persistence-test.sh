#!/bin/bash

# Persistence test script
# This script demonstrates that data survives container restarts

set -e

echo "=== Post Persistence Test ==="
echo ""

# Check if containers are running
if ! docker compose ps app | grep -q "running"; then
    echo "Error: App container is not running. Run 'docker compose up' first."
    exit 1
fi

echo "1. Creating a test post..."
POST_RESPONSE=$(docker compose exec -T app php artisan tinker --execute "
\$post = App\Models\Post::create([
    'title' => 'Persistence Test Post',
    'content' => 'This post was created at ' . date('Y-m-d H:i:s') . '. It should survive a container restart.',
    'author' => 'Persistence Tester',
]);
echo 'Created post with ID: ' . \$post->id;
" 2>/dev/null || echo "Could not use tinker, using curl instead")

echo "$POST_RESPONSE"
echo ""

# Alternative: use curl
echo "1b. Creating post via API..."
curl -X POST http://localhost:10000/api/v1/posts \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Persistence Test Post",
    "content": "This post was created at '$(date -u +'%Y-%m-%d %H:%M:%S')'. It should survive a container restart.",
    "author": "Persistence Tester"
  }' \
  --silent | jq '.data.post | {id, title, author}'

echo ""
echo "2. Listing posts before restart..."
curl -s http://localhost:10000/api/v1/posts | jq '.data | {total, posts: (.posts | map({id, title, author}))}'

echo ""
echo "3. Stopping containers..."
docker compose stop app db

echo ""
echo "4. Waiting 3 seconds..."
sleep 3

echo ""
echo "5. Restarting containers..."
docker compose up -d app db

echo ""
echo "6. Waiting for services to be healthy..."
sleep 10

echo ""
echo "7. Listing posts after restart..."
echo "If the persistence test post is still here, persistence works!"
echo ""
curl -s http://localhost:10000/api/v1/posts | jq '.data | {total, posts: (.posts | map({id, title, author}))}'

echo ""
echo "8. Filtering by author..."
curl -s http://localhost:10000/api/v1/posts/author/Persistence%20Tester | jq '.data'

echo ""
echo "✓ Persistence test complete!"
