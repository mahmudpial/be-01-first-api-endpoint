# First API Endpoint - Postgres Persistence & Repository Pattern

A Laravel 13 API demonstrating **persistent data storage with Postgres**, **repository pattern architecture**, and **containerized deployment**.

## Key Features

- **Postgres Persistence**: Data survives container restarts via named volumes
- **Repository Pattern**: Swappable storage backends (Postgres/In-Memory) - services and routes unchanged
- **Docker Compose**: Full stack (app + Postgres + Redis) starts with `docker compose up`
- **Health Checks**: HTTP health endpoint for the app plus service checks for db and Redis
- **Redis Integration**: Optional caching layer included
- **Index Performance**: Database indexes with EXPLAIN ANALYZE demonstrations
- **API Versioning**: Future-proof versioned endpoints (/api/v1/)

## Architecture

### Repository Pattern (Proof of Architecture)

The service layer depends on an abstraction, not concrete implementations:

```
PostController → PostRepository (interface)
                      ↙         ↘
            PostgresPostRepository  InMemoryPostRepository
```

**This proves the architecture works**: You can swap `PostgresPostRepository` for `InMemoryPostRepository` in `PostRepositoryServiceProvider.php` without changing a single route or service method.

#### Implementation

1. **Interface** (`app/Contracts/PostRepository.php`)
   - Defines contract: `all()`, `getById()`, `create()`, `update()`, `delete()`, `getByAuthor()`, `count()`

2. **Postgres Implementation** (`app/Repositories/PostgresPostRepository.php`)
   - Uses Eloquent ORM against persistent Postgres tables
   - Data survives container restarts via volume `/var/lib/postgresql/data`

3. **In-Memory Implementation** (`app/Repositories/InMemoryPostRepository.php`)
   - Uses PHP arrays — data lost on app restart
   - Useful for testing or demonstration

4. **Service Provider** (`app/Providers/PostRepositoryServiceProvider.php`)
   - Single line to swap backends: change the class binding

### Database

**Postgres 16** runs in a container with:
- Named volume `pgdata` → data persists across restarts
- Init script (`database/init.sql`) creates `posts` table with indices
- Indexes on `author` and `created_at` for query optimization
- Seeded with 3 sample posts

### Redis

**Redis 7** included for caching/sessions:
- Accessible via `redis://redis:6379` in docker-compose
- Health check integrated into app startup

## Quick Start

### Prerequisites

- Docker & Docker Compose installed
- `.env` file with connection strings (see `.env.example`)

### Run the Stack

```bash
# Start all services (app + Postgres + Redis)
docker compose up --build

# Migrations run automatically on startup

# Check health
curl http://localhost:10000/api/v1/health
```

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/posts` | List all posts |
| POST | `/api/v1/posts` | Create a post |
| GET | `/api/v1/posts/{id}` | Get post by ID |
| PUT | `/api/v1/posts/{id}` | Update post |
| DELETE | `/api/v1/posts/{id}` | Delete post |
| GET | `/api/v1/posts/author/{author}` | Get posts by author |
| GET | `/api/v1/health` | Health check (database + Redis) |

### Create a Post

```bash
curl -X POST http://localhost:10000/api/v1/posts \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My First Post",
    "content": "This data persists across container restarts!",
    "author": "Your Name"
  }'
```

### Persistence Test

Run the provided script to prove data survives a container restart:

```bash
./persistence-test.sh
```

**What it does:**
1. Creates a test post
2. Lists posts
3. Stops containers
4. Restarts containers
5. Lists posts again — the test post is still there ✓

This is the exact behavior the assignment asks for: create rows, restart the app and database containers, then confirm the rows are still present.

### Performance Analysis

Show database indexes and EXPLAIN ANALYZE output:

```bash
./explain-analysis.sh
```

**What it shows:**
- 10,000 posts seeded in database
- EXPLAIN ANALYZE output with index `idx_posts_author`
- Index definitions on `posts` table
- Query results filtered by author (benefiting from index)

## Environment Variables

**Required in `.env` (use `.env.example` as template):**

```
DB_CONNECTION=pgsql
DB_HOST=db                  # Docker service name
DB_PORT=5432
DB_DATABASE=be01
DB_USERNAME=postgres
DB_PASSWORD=secret

REDIS_HOST=redis            # Docker service name
REDIS_PORT=6379

APP_DEBUG=true
APP_ENV=local
```

**Important**: `.env` is gitignored. Commit `.env.example` instead.
The app container reads `.env` through `env_file` in `docker-compose.yml`.

## Swapping Repositories (Architecture Proof)

To use the **in-memory repository** instead of Postgres:

**File**: `app/Providers/PostRepositoryServiceProvider.php`

```php
public function register(): void
{
    // Change this line:
    // $this->app->singleton(PostRepository::class, PostgresPostRepository::class);
    
    // To this:
    $this->app->singleton(PostRepository::class, InMemoryPostRepository::class);
}
```

**Then restart:**
```bash
docker compose restart app
```

**Result**: All routes and services work identically. Only the backend changes. This proves the architecture.

## Submission Notes

- The API keeps the same routes and service layer while swapping only the repository implementation.
- Persistence is demonstrated with a container restart test, not just a database schema.
- Docker Compose starts the app, database, and Redis together with one command.

## Testing

```bash
# Run tests
docker compose exec app php artisan test

# Run specific test
docker compose exec app php artisan test tests/Feature/PostApiTest.php
```

## Database Migrations

Migrations run automatically on app startup via:

```bash
php artisan migrate --force
```

## Volumes

Data persists in:
- **`pgdata`**: Postgres database files (`/var/lib/postgresql/data`)
- **`redis_data`**: Redis data (`/data`)

Clear persistent data:
```bash
docker compose down -v
```

## Files

- **Controllers**: `app/Http/Controllers/Api/V1/PostController.php`
- **Models**: `app/Models/Post.php`
- **Repositories**: `app/Repositories/{Postgres,InMemory}PostRepository.php`
- **Interface**: `app/Contracts/PostRepository.php`
- **Routes**: `routes/api.php`
- **Tests**: `tests/Feature/PostApiTest.php`
- **Init Script**: `database/init.sql`
- **Compose File**: `docker-compose.yml`

## Docker Compose Breakdown

```yaml
services:
  app:
    # Laravel app on port 10000
    # Uses a vendor volume so the bind mount does not hide Composer dependencies
    depends_on:
      - db (healthcheck)
      - redis (healthcheck)
  
  db:
    # Postgres 16, volume pgdata for persistence
    healthcheck: pg_isready

  redis:
    # Redis 7-alpine, volume redis_data for persistence
    healthcheck: redis-cli ping

volumes:
  pgdata:      # Persists /var/lib/postgresql/data
  redis_data:  # Persists /data
  vendor:      # Keeps installed app dependencies available in the container
```

## Stretch Goals

✓ **Redis Added**: Redis service is included in Compose and checked by the stack
✓ **Indexes + EXPLAIN**: `idx_posts_author` and `idx_posts_created_at` with analysis script  
✓ **Init Script**: `database/init.sql` creates schema + seeds data  
✓ **Persistence Proven**: `persistence-test.sh` demonstrates data survives restarts  

## How Persistence Works

1. **Volume Binding**: `pgdata:/var/lib/postgresql/data` in `docker-compose.yml`
2. **Postgres Writes**: All table data written to `/var/lib/postgresql/data` inside container
3. **Host Storage**: Docker mounts this to the host filesystem (location varies by OS)
4. **Restart Survival**: Even if the container dies, the volume persists
5. **Reattachment**: New container attaches to the same volume, data is intact

**Test it:**
```bash
# Create posts
# Stop containers
docker compose stop

# Wait...

# Start containers
docker compose up

# Posts still exist!
curl http://localhost:10000/api/v1/posts
```

## Troubleshooting

**Container won't start?**
```bash
docker compose logs app
docker compose logs db
```

**Migrations failed?**
```bash
docker compose exec app php artisan migrate --fresh --force
```

**Data lost after restart?**
- Check that `pgdata` volume exists: `docker volume ls`
- Ensure `docker-compose.yml` has the volume mount

**Health check failing?**
```bash
docker compose exec db pg_isready -U postgres
docker compose exec redis redis-cli ping
```

## License

MIT
