# Architecture Diagrams

## Repository Pattern - Single Line Swap

```
                    ┌─────────────────────────────────┐
                    │   PostRepositoryServiceProvider  │
                    │    (app/Providers/)              │
                    └──────────────┬────────────────────┘
                                   │
                    Binding (ONE LINE TO SWAP)
                                   │
              ┌────────────────────┴────────────────────┐
              │                                         │
    ┌─────────▼──────────────┐          ┌──────────────▼──────┐
    │  PostgresPostRepository│          │InMemoryPostRepository│
    │ (app/Repositories/)    │          │ (app/Repositories/) │
    │                        │          │                     │
    │ Uses Eloquent ORM      │          │ Uses PHP arrays     │
    │ Queries Postgres       │          │ No persistence      │
    │ Data survives restart  │          │ For testing only    │
    └─────────┬──────────────┘          └─────────────────────┘
              │
              │ implements
              │
    ┌─────────▼────────────────────┐
    │  PostRepository (interface)   │
    │  (app/Contracts/)             │
    │                               │
    │  all()                        │
    │  getById(id)                  │
    │  create(data)                 │
    │  update(id, data)             │
    │  delete(id)                   │
    │  getByAuthor(author)          │
    │  count()                      │
    └─────────▲────────────────────┘
              │
              │ injected into
              │
    ┌─────────┴────────────────────┐
    │   PostController             │
    │  (app/Http/Controllers/)      │
    │                              │
    │  index()   ├─ GET /posts     │
    │  store()   ├─ POST /posts    │
    │  show()    ├─ GET /posts/{id}│
    │  update()  ├─ PUT /posts/{id}│
    │  destroy() ├─ DELETE /posts  │
    │  getByAuthor() ├─ GET /posts │
    └────────────────────────────────┘
              │
              │ handles
              │
         API Routes
         (routes/api.php)
```

**Key:** Change ONE line in PostRepositoryServiceProvider to swap from Postgres → In-Memory (or vice versa). No route changes. No controller changes. Architecture proven.

---

## Data Persistence - Volume Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      Host Filesystem                             │
│  (Mac: ~/Library/Docker/volumes)                                │
│  (Linux: /var/lib/docker/volumes)                               │
│  (Windows: %LOCALAPPDATA%\Docker\volumes)                       │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  pgdata  (Named Volume)                                  │  │
│  │  - Created: docker compose up                            │  │
│  │  - Survives: container stop, restart, rebuild            │  │
│  │  - Location: /var/lib/docker/volumes/pgdata/_data/       │  │
│  └──────────────┬───────────────────────────────────────────┘  │
│                 │                                                │
│                 │ Docker mount                                  │
│                 │ pgdata:/var/lib/postgresql/data               │
│                 │                                                │
└─────────────────┼────────────────────────────────────────────────┘
                  │
                  ▼
     ┌────────────────────────────────┐
     │  Docker Container: db          │
     │  Image: postgres:16            │
     │                                │
     │  /var/lib/postgresql/data ◄────┘
     │  ├── base/                 (base tables)
     │  ├── posts/                (posts table data)
     │  ├── pg_log/               (logs)
     │  └── ...                   (other postgres files)
     │                                │
     │  Application writes:            │
     │  INSERT INTO posts ...          │
     │       ▼                         │
     │  Postgres WAL (write-ahead log) │
     │       ▼                         │
     │  Data flushed to disk           │
     │       ▼                         │
     │  Persisted in /var/lib/... ────┘
     │                                │
     └────────────────────────────────┘

               Scenario 1: Restart
               
       Container stopped       Container restarted
            ▼                         ▼
       Process killed         New container created
       Memory flushed         Attaches to pgdata volume
       Volume intact          Reads data from volume
                              Old data intact! ✓

               Scenario 2: Rebuild
               
       Image rebuilt          New container from new image
            ▼                         ▼
       Old container killed   Attaches to pgdata volume
       Process killed         Same data returned
       New binary loaded      Backward compatible ✓
```

---

## Docker Compose Stack

```
┌──────────────────────────────────────────────────────────────────────┐
│                    docker-compose.yml Stack                          │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌───────────────────────┐   ┌───────────────────┐  ┌─────────────┐│
│  │  app Service          │   │  db Service       │  │  redis      ││
│  │                       │   │                   │  │  Service    ││
│  │  Port 10000           │   │  Port 5432        │  │  Port 6379  ││
│  │  ├─ PHP-FPM           │   │  ├─ Postgres 16   │  │  ├─ Redis   ││
│  │  ├─ Nginx             │   │  └─ pgdata volume │  │  └─ redis_  ││
│  │  └─ Supervisor        │   │     (persistence) │  │     data    ││
│  │                       │   │                   │  │ (persis)    ││
│  │  ENV:                 │   │  ENV:             │  │             ││
│  │  - DB_HOST=db         │   │  - POSTGRES_DB    │  │  ENV:       ││
│  │  - REDIS_HOST=redis   │   │  - POSTGRES_USER  │  │  (defaults) ││
│  │  - .env file mounted  │   │  - POSTGRES_PASS  │  │             ││
│  │                       │   │                   │  │  Health:    ││
│  │  Healthcheck:         │   │  Health:          │  │  redis-cli  ││
│  │  wget /api/v1/health  │   │  pg_isready       │  │  ping       ││
│  │  (10s interval)       │   │  (10s interval)   │  │  (10s)      ││
│  │                       │   │                   │  │             ││
│  │  depends_on:          │   │                   │  │             ││
│  │  - db (healthy)       │   │                   │  │             ││
│  │  - redis (healthy)    │   │                   │  │             ││
│  │                       │   │                   │  │             ││
│  │  Volumes:             │   │                   │  │             ││
│  │  - .:/app             │   │                   │  │             ││
│  │    (bind mount,       │   │                   │  │             ││
│  │     live code)        │   │                   │  │             ││
│  │                       │   │                   │  │             ││
│  └───────────────────────┘   └───────────────────┘  └─────────────┘
│       ▲                             ▲                     ▲          │
│       │                             │                     │          │
│       └─────────────┬───────────────┴─────────────────────┘          │
│                     │                                                │
│             Network: bridge (default)                                │
│             Services can resolve by name:                            │
│             - app talks to db:5432                                   │
│             - app talks to redis:6379                                │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘

Named Volumes (Host Persistence)
├─ pgdata ───────────► /var/lib/postgresql/data (Postgres data)
└─ redis_data ──────► /data (Redis persistence)
```

---

## Request Flow: Create Post

```
1. HTTP Request
   POST /api/v1/posts
   {
     "title": "Test",
     "content": "...",
     "author": "Me"
   }

2. Router (routes/api.php)
   Route::apiResource('posts', PostController::class)
   ──► Matches POST /api/v1/posts
   ──► Routes to PostController::store()

3. Controller (app/Http/Controllers/Api/V1/PostController.php)
   public function store(Request $request): JsonResponse
   {
     $validated = $request->validate([...])
     
     ──► Resolves dependency:
         PostRepository $postRepository
         (Laravel service container)

4. Service Provider (app/Providers/PostRepositoryServiceProvider.php)
   $this->app->singleton(
     PostRepository::class,
     PostgresPostRepository::class  ◄── THE ONE LINE TO SWAP
   )
   
   Returns PostgresPostRepository instance

5. PostController calls:
   $post = $this->postRepository->create($validated)

6. PostgresPostRepository::create() (app/Repositories/PostgresPostRepository.php)
   public function create(array $data): array
   {
     $post = Post::create([
       'title' => $data['title'],
       'content' => $data['content'],
       'author' => $data['author'] ?? 'Anonymous',
     ]);
     return $this->formatPost($post);
   }

7. Eloquent Model (app/Models/Post.php)
   Post::create($data)
   ──► Eloquent handles fillable attributes
   ──► Generates INSERT SQL
   ──► Sends to Postgres connection

8. Database Driver (Laravel/Illuminate)
   ──► Postgres PDO connection
   ──► Executes: INSERT INTO posts (title, content, author) VALUES (...)
   ──► Returns inserted row with ID

9. Postgres Server (Docker container)
   ──► Receives INSERT
   ──► Creates new row
   ──► Assigns auto-increment ID
   ──► Writes to WAL (Write-Ahead Log)
   ──► Commits transaction
   ──► Writes data to disk
   ──► Writes to pgdata volume

10. Docker Volume (Host filesystem)
    ──► pgdata volume receives data
    ──► Data persists on host

11. Response Back to Controller
    ──► Eloquent returns Model instance
    ──► PostgresPostRepository formats to array
    ──► PostController wraps in response JSON

12. HTTP Response
    200 OK
    {
      "status": "success",
      "data": {
        "post": {
          "id": 4,
          "title": "Test",
          "content": "...",
          "author": "Me",
          "created_at": "2024-...",
          "updated_at": "2024-..."
        }
      },
      "meta": { "timestamp": "...", "version": "v1" }
    }

13. Browser/API Client
    ──► Displays success
    ──► Data persisted!
```

---

## Test Persistence Lifecycle

```
                    Timeline
┌──────────────────────────────────────────────────────────┐

1. Start (0s)
   docker compose up
   ├─ Postgres starts
   ├─ App starts
   ├─ Migrations run
   ├─ init.sql runs
   └─ 3 seed posts created
   
   State: 3 posts in pgdata volume

2. Create (5s)
   POST /api/v1/posts (Test Post)
   ├─ Data inserted to Postgres
   ├─ Written to pgdata volume
   └─ Response: id=4
   
   State: 4 posts in pgdata volume

3. Verify Pre-Restart (10s)
   GET /api/v1/posts
   ├─ Queries Postgres
   ├─ Reads from pgdata volume
   └─ Returns 4 posts ✓
   
   State: 4 posts confirmed

4. Stop (15s)
   docker compose stop
   ├─ App container killed (process + memory flushed)
   ├─ Postgres container killed (process flushed)
   └─ pgdata volume REMAINS on host filesystem
   
   State: 4 posts in pgdata (host storage), 0 posts in memory

5. Restart (25s)
   docker compose up
   ├─ New app container created
   ├─ New Postgres container created
   ├─ Postgres attaches to pgdata volume
   ├─ Reads data from host filesystem
   ├─ 4 posts loaded into Postgres memory
   └─ App healthy
   
   State: 4 posts in pgdata + Postgres memory

6. Verify Post-Restart (30s)
   GET /api/v1/posts
   ├─ Queries Postgres
   ├─ Reads from memory + pgdata
   └─ Returns 4 posts ✓✓✓ PERSISTENCE WORKS!
   
   State: 4 posts (including Test Post from before restart)

Conclusion:
Data persists because:
- Docker volume pgdata survived container kill
- New container reattached to same volume
- Postgres loaded old data on startup
- Test Post (and all others) intact

This proves:
✓ Persistence across app restart
✓ Persistence across database restart
✓ Persistence across full stack restart
```

---

## Index Performance - Before vs After

```
                Query: SELECT * FROM posts WHERE author = 'Pial Mahmud'

WITHOUT Index (Sequential Scan)
─────────────────────────────────────────
Seq Scan on posts  (cost=0.00..1000.00 rows=10 width=200)
  Filter: (author = 'Pial Mahmud')

Execution:
1. Start at first row
2. Check every row: is author == 'Pial Mahmud'?
3. If match, include in result
4. Continue until end of table
5. Return all matches

Cost: O(n) = 1000 (full table scan)
Time: ~100ms for 10,000 rows


WITH Index (Index Scan)
──────────────────────────────────────────
Index Scan using idx_posts_author on posts  
(cost=0.29..100.00 rows=10 width=200)
  Index Cond: (author = 'Pial Mahmud')

Index Structure (B-tree):
                    'Alice'
                   /        \
            'Bob'          'Charlie'
           /     \         /       \
       'Alice'  'Bob'  'Charlie'  'Dave'
       [rows]  [rows]   [rows]   [rows]
       Pial    Pial      Bob     Charlie
       Pial    Alice     Alice    Dave

Execution:
1. Jump to index
2. Binary search for 'Pial Mahmud'
3. Follow leaf node pointer
4. Retrieve row IDs directly
5. Fetch rows from heap table
6. Return results

Cost: O(log n) = 100 (index lookup + table access)
Time: ~1ms for 10,000 rows


Performance Gain: 10x FASTER!
─────────────────────────────
Sequential Scan: 1000 cost units, ~100ms
Index Scan:     100 cost units,  ~1ms

Scaling:
- 100 rows:    1ms → 0.1ms (10x)
- 1M rows:     100ms → 10ms (10x)
- 1B rows:     ~1s → ~100ms (10x)
```
