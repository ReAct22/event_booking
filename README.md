# Event Booking API
Laravel 12 · PostgreSQL · Sanctum

A booking system API designed to handle complex event scenarios including slot management, waitlists, cancellation chains, overlapping conflicts, and busy-user cases.

---

# 1. Tech Choices

## Stack Overview

- Backend: Laravel 12
- Database: PostgreSQL
- Authentication: Laravel Sanctum
- Architecture: RESTful JSON API
- Seeding Strategy: Complex scenario-driven seeders

---

## Why This Stack?

### Laravel 12
- Mature and battle-tested ecosystem
- Excellent ORM (Eloquent) for relational modeling
- Built-in validation, middleware, and authentication
- Clean structure for scalable API design
- High developer productivity

Laravel makes transactional logic and constraint handling easier to manage in complex booking flows.

---

### PostgreSQL
Chosen for:
- Strong ACID compliance
- Strict constraint enforcement (CHECK, FK, Transaction safety)
- Better relational integrity control
- Suitable for concurrency-heavy systems like booking platforms

For booking systems, data correctness is more important than flexibility.

---

### Sanctum (instead of Passport or JWT)

Sanctum was selected because:
- Lightweight
- Simple token-based authentication
- Ideal for SPA / mobile integration
- No OAuth2 complexity overhead

Passport was considered but deemed overkill for this scope.

---

## Alternatives Considered

| Option | Why Not Selected |
|--------|------------------|
| GraphQL | REST is simpler and faster to implement for this test |
| MySQL | Less strict constraint enforcement compared to PostgreSQL |
| Laravel Passport | Too heavy for non-OAuth requirements |
| Microservices | Over-engineering for this scope |

---

# 2. Trade-offs

## What Was Prioritized

- Data integrity
- Constraint enforcement
- Transaction safety
- Real-world edge case simulation
- Clear API structure

---

## What Was Deprioritized

- UI polish (focus is backend robustness)
- Caching layer (no Redis yet)
- Horizontal scaling setup
- Advanced monitoring and observability

---

## What Would Break Under Production Load?

Potential bottlenecks without improvements:

1. Race conditions during slot booking
2. High query cost on event listing
3. Waitlist auto-promote performance degradation
4. No caching → database stress

### Production Fix Strategy

- Use DB transactions + row-level locking (SELECT FOR UPDATE)
- Add Redis caching
- Move waitlist promotion to queue workers
- Add proper indexing
- Add rate limiting

---

# 3. Setup Instructions

## Requirements

- PHP 8.3+
- Composer
- PostgreSQL

---

## Installation

git clone https://github.com/your-repo/event-booking.git
cd event-booking
composer install
cp .env.example .env
php artisan key:generate

---

## Configure Database (.env)

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=event_booking
DB_USERNAME=postgres
DB_PASSWORD=yourpassword

---

## Run Migration & Seeder

php artisan migrate:fresh --seed

Seeder generates:
- 300 users
- 100 events
- 30 past events
- 70 upcoming events
- Multi-slot configurations
- Booking + waitlist scenarios
- Conflict & cancellation chains

---

## Run Application

php artisan serve

API available at:

http://127.0.0.1:8000/api

---

# 4. Test Accounts

Regular User  
Email: admin@example.com  
Password: password123  

Busy User (All Scenario Coverage)  
Email: busy@test.com  
Password: password  

Admin  
Email: admin@test.com  
Password: password  

---

# 5. Complex Scenario Coverage

The system supports:

- Full slot + waitlist overflow
- Multi-slot event with mixed availability
- Cancellation chain + auto promotion
- Busy user (multiple booking states)
- Overlapping time conflict detection
- Past vs Upcoming event filtering

All scenarios are seeded automatically.

---

# 6. What I'd Improve First

If given more time, I would prioritize:

## 1. Concurrency Safety (Critical)

Implement row-level locking and transactional consistency for booking.

Reason:
Booking systems typically fail due to race conditions.

---

## 2. Redis Caching

Cache:
- Event listing
- Slot availability

Reason:
Reduce DB load and improve performance under traffic.

---

## 3. Queue-Based Waitlist Promotion

Move promotion logic to async job queue.

Reason:
Prevents blocking requests and improves scalability.

---

## 4. Automated Testing

Add:
- Feature tests
- Concurrency tests
- API contract tests

Target: 80%+ coverage.

---

## 5. Observability & Monitoring

- Structured logging
- Performance metrics
- Error tracking

---

# 7. Production Readiness Gaps

Recommended next steps:

- Docker setup
- CI/CD pipeline
- Rate limiting
- API versioning
- Load testing
- Read replicas
- Security hardening

---

# 8. Engineering Philosophy

This project emphasizes:

- Correctness over cleverness
- Data integrity over speed
- Real-world edge cases
- Production-aware thinking

The goal is not just feature completion, but demonstrating architectural awareness and scalability mindset.
