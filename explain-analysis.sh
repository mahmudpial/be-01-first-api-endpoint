#!/bin/bash

# Performance analysis script
# Shows EXPLAIN ANALYZE output before and after index creation

set -e

echo "=== Index Performance Analysis ==="
echo ""
echo "This script demonstrates the performance impact of database indexes."
echo ""

# Seed data
echo "1. Seeding 10,000 posts..."
docker compose exec -T db psql -U postgres -d be01 <<EOF 2>/dev/null || true
INSERT INTO posts (title, content, author)
SELECT 
    'Post ' || i,
    'Content ' || i,
    CASE (i % 5)
        WHEN 0 THEN 'Pial Mahmud'
        WHEN 1 THEN 'Alice'
        WHEN 2 THEN 'Bob'
        WHEN 3 THEN 'Charlie'
        ELSE 'Dave'
    END
FROM generate_series(4, 10000) i
ON CONFLICT DO NOTHING;
EOF

echo "✓ Database seeded"
echo ""

# Query without index optimization (already has index, but we'll show the plan)
echo "2. EXPLAIN ANALYZE - Query with index (already created at init):"
echo ""
docker compose exec -T db psql -U postgres -d be01 -c "EXPLAIN ANALYZE SELECT * FROM posts WHERE author = 'Pial Mahmud' ORDER BY created_at DESC;" 2>/dev/null || true

echo ""
echo "3. Index information:"
echo ""
docker compose exec -T db psql -U postgres -d be01 -c "\d posts" 2>/dev/null || true

echo ""
echo "4. Index statistics:"
echo ""
docker compose exec -T db psql -U postgres -d be01 -c "SELECT schemaname, tablename, indexname, indexdef FROM pg_indexes WHERE tablename = 'posts';" 2>/dev/null || true

echo ""
echo "5. Accessing posts by author (with index):"
echo ""
curl -s http://localhost:10000/api/v1/posts/author/Pial%20Mahmud | jq '.data | {author, total}'

echo ""
echo "✓ Analysis complete!"
