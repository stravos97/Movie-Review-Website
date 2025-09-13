# Movie Review Website — Architecture Overview

This document explains the architecture of the Symfony-based Movie Review Website — the design choices behind controllers, repositories, entities, templating, security, and testing. It focuses on the “why” and the boundaries between layers to keep the codebase maintainable and testable.

---

## 1) Architectural Foundation: Layered MVC with Doctrine

### High‑level flow

```
Browser → Controller → (Form/Validator) → Repository (Doctrine ORM) → Database
                         ↘ Twig View (HTML)
```

Why this matters: each responsibility has a clear home.
- Controllers: HTTP routing, request parsing, delegating to domain operations.
- Forms/Validation: guardrails at the edge for user input.
- Repositories: query logic and data access via Doctrine ORM.
- Entities: domain model mapped to MySQL tables.
- Views: Twig templates render HTML (no business logic).

Golden rule: Keep controllers thin. Query/DB concerns belong in repositories. Views render data that’s prepared by controllers.

---

## 2) Domain Modeling with Doctrine Entities

### Key entities
- User (table: `users`): authentication identity, roles (JSON), profile fields
- Review (table: `reviews`): title, summary, body, rating, viewCount, dates, relations to User
- Comment (table: `comments`): body, timestamps, relations to Review and User

Example (trimmed) — `App\\Entity\\Review`:
```php
/**
 * @ORM\\Entity(repositoryClass=ReviewRepository::class)
 * @ORM\\Table(name="reviews")
 */
class Review
{
    /** @ORM\\Id @ORM\\GeneratedValue @ORM\\Column(type="integer", name="review_id") */
    private $id;

    /** @ORM\\Column(type="integer", nullable=true) */
    private $rating;

    /** @ORM\\Column(type="datetime", name="created_at", nullable=true) */
    private $date;

    /** @ORM\\Column(type="string", length=255) */
    private $summary;

    /** @ORM\\Column(type="text", name="review_body") */
    private $message_body;

    // …movieTitle, director, actors, releaseYear, genre, user, viewCount, updatedAt …
}
```

Why these choices:
- Explicit table/column mapping keeps DB coupling visible and intentional
- Nullable fields reflect real‑world optional data (e.g., director, rating)
- Timestamps on creation/update allow sorting and recency features

---

## 3) Data Access: Focused Repositories

Repositories encapsulate all query logic. Highlights — `App\\Repository\\ReviewRepository`:
- `recent(int $limit)`: latest reviews for homepage
- `search(string $query, int $limit, int $offset)`: partial match search
- `searchFullText(string $query, …)`: MySQL full‑text MATCH … AGAINST when available
- Filtering helpers: byRating, byUser, byGenre, byYear, mostViewed, reported

Example (trimmed):
```php
public function search(string $query, int $limit = 20, int $offset = 0): array
{
    $qb = $this->createQueryBuilder('r');
    $qb->andWhere($qb->expr()->orX(
        'r.movieTitle LIKE :q',
        'r.summary LIKE :q',
        'r.message_body LIKE :q',
        'r.director LIKE :q'
    ))
    ->setParameter('q', '%'.$query.'%')
    ->orderBy('r.date', 'DESC')
    ->setFirstResult($offset)
    ->setMaxResults($limit);

    return $qb->getQuery()->getResult();
}
```

Why this matters:
- Keeps controllers free of SQL/criteria details
- Eases testing by isolating query behavior

---

## 4) Controller Design: Thin, Purpose‑built

Controllers parse input, delegate to repositories/services, and render Twig.

Examples:
- `IndexController::index()` renders homepage with recent reviews
- `SearchController::search()` implements resilient search: tries full‑text, falls back to partial search, and handles empty DB gracefully
- `MyController` pages (`/me/reviews`, `/me/comments`) paginate user activity

Trimmed example — `SearchController`:
```php
/** @Route("/search", name="search_reviews", methods={"GET"}) */
public function search(Request $request, ReviewRepository $reviews): Response
{
    $q = trim((string) $request->query->get('q', ''));
    $page = max(1, (int) $request->query->get('page', 1));
    $limit = 20; $offset = ($page - 1) * $limit;
    $results = []; $mode = 'none';

    if ($q !== '') {
        try {
            $results = $reviews->searchFullText($q, $limit, $offset);
            $mode = 'fulltext';
        } catch (\Throwable $e) {
            try {
                $results = $reviews->search($q, $limit, $offset);
                $mode = 'partial';
            } catch (\Throwable $e2) {
                $results = []; $mode = 'none';
            }
        }
    }

    return $this->render('search/results.html.twig', compact('q','results','mode'));
}
```

Best practices applied:
- No DB logic in controllers
- Defensive fallbacks for missing DB/indexes
- Pagination via limit/offset, pass only what view needs

---

## 5) Views and Templating (Twig)

Twig templates render HTML. The project avoids heavy logic in templates and uses safe truncation and conditional displays.
- Example templates: `templates/articles/*.html.twig`, `templates/search/results.html.twig`, `templates/me/*.html.twig`
- The `u.truncate` filter was removed to avoid extra dependency; simple `slice`/`length` is used instead

Why Twig:
- Clear separation from PHP logic
- Secure by default (auto‑escaping)
- Composable partials (e.g., `_user_menu.html.twig`)

---

## 6) Security and Authentication

- Symfony Security Bundle with a Doctrine‑backed user provider (email as identifier)
- Guard authenticator (`App\\Security\\LoginAuthenticator`) handles login form
- CSRF protection on login and form submissions
- Access control rules in `config/packages/security.yaml`:
  - Anonymous access to `/`, `/login`, assets
  - `ROLE_USER` required for creating/editing reviews and `/me/*` routes

Seeded local users (Docker):
- `admin@example.com` / `Password123!` (ROLE_ADMIN, ROLE_USER)
- `alice@example.com` / `Password123!` (ROLE_USER)

---

## 7) Database Design (MySQL)

Tables: `users`, `reviews`, `comments`, `review_likes` with FKs and indexes matching query patterns. Full‑text index on reviews content (when available). Local Docker seeds demo users and sample data.

Why explicit SQL (for seed/schema files):
- Repeatable local setup and demonstration data
- Mirrors production schema used by Doctrine mappings

---

## 8) Testing Strategy

- PHPUnit unit/functional tests (fast):
  - `tests/Repository/ReviewRepositoryTest.php` boots the kernel, uses Doctrine registry, asserts repository contracts
  - `tests/Smoke/RouteSmokeTest.php` hits `/` and `/search` to ensure routes render 2xx
- SQLite used in CI for speed and isolation
- CI “compose smoke” (optional) can launch MySQL + the built image and poll `/health` (disabled by default to keep CI fast)

Why this split:
- Unit/functional tests give quick feedback
- Optional end‑to‑end smoke provides production‑like validation when needed

---

## 9) Production Concerns

- Dockerfile (multi‑stage):
  - Composer install in vendor stage with `--no-scripts` to avoid `bin/console` before sources
  - Optional Xdebug via `WITH_XDEBUG` build arg (off by default)
  - Runtime ensures `var/` is writable and warms up cache (non‑fatal)
  - HEALTHCHECK uses curl
- CI builds/pushes GHCR images with lowercase tags; cache is enabled for speed

Why these choices:
- Fast rebuilds, minimal image size, predictable boots
- Avoids composing the app at build time with environment‑bound scripts

---

## 10) Key Principles Recap

1. Controllers are thin — HTTP only, delegate to repositories/services
2. Repositories own query logic — keep DB details out of controllers
3. Entities model the domain — explicit mappings to tables/columns
4. Templates render views — keep logic minimal in Twig
5. Security at the edge — CSRF and access control rules
6. Tests are fast by default — DB‑less or SQLite; optional full stack when needed
7. Production images are lean — no Xdebug by default, cache warmup and writable dirs ensured

This structure provides a clean foundation that supports maintainability, testability, and pragmatic production deployment.

