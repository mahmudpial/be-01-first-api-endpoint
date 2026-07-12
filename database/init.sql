-- Initialize schema for first-api-endpoint

-- Posts table for demonstrating persistence
CREATE TABLE IF NOT EXISTS posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(255) NOT NULL DEFAULT 'Anonymous',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster queries on author
CREATE INDEX IF NOT EXISTS idx_posts_author ON posts(author);

-- Create index on created_at for range queries
CREATE INDEX IF NOT EXISTS idx_posts_created_at ON posts(created_at DESC);

-- Seed initial data
INSERT INTO posts (title, content, author) VALUES
    ('Hello World', 'This is the first post demonstrating persistence.', 'Pial Mahmud'),
    ('Docker & Laravel', 'Setting up a containerized Laravel application with Postgres.', 'Pial Mahmud'),
    ('Persistence Test', 'This data will survive container restarts.', 'Backend Intern')
ON CONFLICT DO NOTHING;
