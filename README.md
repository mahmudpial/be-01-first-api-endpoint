# BE-01 — First API Endpoint

> **FlyRank AI Internship** — Backend AI Engineering Track
> Assignment `BE-01` · Week 1 · Setup Phase

A minimal Laravel REST API demonstrating two JSON endpoints, built and deployed as an introduction to the request → response lifecycle and the publish-to-GitHub workflow used throughout this program.

**Live API:** https://be-01-first-api-endpoint.onrender.com/api/v1/

---

## Overview

This project is intentionally small: two `GET` endpoints that return JSON, callable from `curl`, a browser, or Postman. The goal wasn't complexity — it was to make the request/response loop tangible and to establish the publish-to-GitHub habit from day one.

On top of the core requirement, the API is organized using standard Laravel/REST conventions (versioned routes, a dedicated controller, a consistent response envelope) rather than inline route closures — a small step toward how production APIs are typically structured.

## Endpoints

| Method | Endpoint        | Description                                          |
| ------ | --------------- | ---------------------------------------------------- |
| `GET`  | `/api/v1/`      | Confirms the API is online                           |
| `GET`  | `/api/v1/greet` | Returns developer info and a categorized skill stack |

### Example: `GET /api/v1/`

```json
{
    "status": "success",
    "data": {
        "message": "Welcome to my first API endpoint",
        "status": "online"
    },
    "meta": {
        "timestamp": "2026-07-10T05:35:00+00:00"
    }
}
```

### Example: `GET /api/v1/greet`

```json
{
    "status": "success",
    "data": {
        "name": "Pial Mahmud",
        "role": "Backend Intern",
        "bio": "Full-stack engineer focused on Laravel backends and Vue.js frontends.",
        "skills": {
            "languages": ["JavaScript", "PHP"],
            "frameworks": ["Laravel", "Vue.js"],
            "backend": [
                "REST API design",
                "JWT authentication",
                "Authentication systems",
                "ORM (Eloquent)",
                "Repository pattern",
                "OOP",
                "Data Structures & Algorithms"
            ],
            "database": ["MySQL", "Database design"],
            "integration": ["Third-party API integration", "AI integration"],
            "tools": ["Git & GitHub", "Postman", "Docker"],
            "deployment": ["Vercel", "Render"]
        },
        "currently_learning": "Backend AI Engineering"
    },
    "meta": {
        "timestamp": "2026-07-10T05:35:00+00:00"
    }
}
```

## Tech Stack

- **Framework:** Laravel 13 (PHP 8.4)
- **Deployment:** Docker → Render (Web Service)
- **Version control:** Git & GitHub
- **Testing tools:** curl, browser, Postman

## Project Structure (relevant files)

```
app/Http/Controllers/Api/V1/WelcomeController.php   # Endpoint logic
routes/api.php                                       # Versioned route definitions
Dockerfile                                            # Container build for deployment
render.yaml                                           # Render service definition
```

## Local Setup

```bash
git clone https://github.com/<your-username>/be-01-first-api-endpoint.git
cd be-01-first-api-endpoint

composer install
cp .env.example .env
php artisan key:generate

php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api/v1/`.

## Testing

**curl:**

```bash
curl http://127.0.0.1:8000/api/v1/
curl http://127.0.0.1:8000/api/v1/greet
```

**Browser:**
Open `http://127.0.0.1:8000/api/v1/greet` directly.

**Postman:**
Import the two `GET` requests above into a collection to inspect status codes, headers, and response time.

## Deployment

Deployed on [Render](https://render.com) as a Dockerized web service:

- Base image: `php:8.4-cli` (required by Laravel 13 / Symfony 8 dependencies)
- Build: `composer install --no-dev --optimize-autoloader`
- Start: `php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=$PORT`

Any push to `main` triggers an automatic redeploy.

## Assignment Context

| Field    | Value                  |
| -------- | ---------------------- |
| Type     | Assignment             |
| Code     | BE-01                  |
| Track    | Backend AI Engineering |
| Week     | 1                      |
| Workload | ~3h                    |
| Phase    | Setup                  |

**Goal:** Build the smallest possible backend — a server with two JSON endpoints — call it from curl and a browser, and publish it to a public GitHub repository.

## 👤 Author

**Pial Mahmud**
Full-Stack Web Developer · Backend AI Engineering Intern, FlyRank AI

[GitHub](https://github.com/mahmudpial) | [LinkedIn](https://www.linkedin.com/in/pial-mahmud/)
