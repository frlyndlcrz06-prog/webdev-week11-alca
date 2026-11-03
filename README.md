# Campus Events at Scale (Week 11)

A Laravel + PostgreSQL project that grows the Week 10 Campus Events data to 20,000 events and measures query performance: an N+1 fix, query scopes, JSONB filters, full-text search, and `EXPLAIN (ANALYZE)` plans.

## Setup

```bash
composer install
cp .env.example .env        # set the PostgreSQL DB_* values and DB_DATABASE=campus_events_week11
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

`migrate:fresh --seed` builds everything, including all indexes.

## Pages

| Route | What it shows |
|---|---|
| `/events` | First 50 upcoming events (fixed version: eager loading + count) |
| `/events/naive` | Same list using lazy loading, for the N+1 demo |
| `/events?dress_code=costume` | JSONB filter, arrow form |
| `/events?dress_code=costume&mode=contains` | JSONB filter, containment form |
| `/events/search?q=telescope` | Full-text search (max 50 results, shows the SQL) |

## Seeded data

- 20,000 events across 3 venues, inserted in chunks of 1,000
- 200 participants
- 500 registrations for the first 100 events (5 each, both statuses)
- The first 100 events are scheduled to start soonest, so the 50 events on `/events` show real participant counts
- Rare search word: `telescope` appears in 80 events (0.4%)
- Rare JSONB value: `details.dress_code = 'costume'` appears in 80 events (0.4%)

## Local scopes (`Event` model)

| Scope | What it does |
|---|---|
| `upcoming()` | Keeps only events whose `starts_at` is now or later |
| `soonest()` | Orders events by `starts_at`, earliest first |
| `atVenue($venueId)` | Keeps only events at the given venue (takes a parameter) |

They are chained in `EventController`, for example `Event::upcoming()->soonest()->atVenue($id)`.

## Query counts (Step 4)

| Version | Route | How it loads data | Queries sent |
|---|---|---|---|
| Naive | `/events/naive` | Lazy loading inside the loop | 101 |
| Fixed | `/events` | `with('venue')` + `withCount('participants')` | 2 |

**Why the difference:** The naive page sends one query for the 50 events and then, for each row, one more query to load its venue and another to load its participants, which adds up to 101 queries. The fixed page uses `with('venue')` to load all the venues in a single extra query and `withCount('participants')` to compute the counts inside the main query, so it needs only 2.

`Model::preventLazyLoading()` is enabled outside production, so the naive page throws a `LazyLoadingViolationException`, which is how the N+1 problem was caught.

## Query plans (Step 7)

Each query was run with `EXPLAIN (ANALYZE)` using the rare value so the result is selective (80 of 20,000 rows).

| # | Query | Index that exists | Plan type | Execution time |
|---|---|---|---|---|
| 1 | Arrow form: `details->>'dress_code' = 'costume'` | none | Seq Scan | 12.046 ms |
| 2 | Containment: `details @> '{"dress_code":"costume"}'` | none | Seq Scan | 11.449 ms |
| 3 | Full-text: `to_tsvector('english', description) @@ plainto_tsquery('english', 'telescope')` | none | Seq Scan | 1034.299 ms |
| 4 | Arrow form (same as 1) | `events_details_dress_code_idx` (btree on `details->>'dress_code'`) | Bitmap Index Scan + Bitmap Heap Scan | 0.241 ms |
| 5 | Containment (same as 2) | `events_details_gin_idx` (GIN, `jsonb_path_ops`) | Bitmap Index Scan + Bitmap Heap Scan | 0.314 ms |
| 6 | Full-text (same as 3) | `events_description_fulltext` (GIN on `to_tsvector`) | Bitmap Index Scan + Bitmap Heap Scan | 0.352 ms |
| 7 | Arrow form with a common value: `details->>'dress_code' = 'casual'` (3,920 rows) | `events_details_dress_code_idx` | Bitmap Index Scan + Bitmap Heap Scan | 3.050 ms |
| 8 | Arrow form on another key: `details->>'capacity' = '100'` | indexes exist on `events`, but none matches this query | Seq Scan (index not used) | 12.333 ms |

**Why no index was used in row 8:** The btree index is built only on the expression `details->>'dress_code'`, and the GIN index on `details` only supports containment (`@>`), not `->>` equality, so neither index matches a filter on `details->>'capacity'` and PostgreSQL had to scan the whole table.

## Why the indexes live in migrations (Step 8)

Laravel's schema builder cannot create an index on an expression such as `(details->>'dress_code')` or a GIN index with the `jsonb_path_ops` option, so those indexes are created with `DB::statement()` and raw SQL inside a migration. This is necessary because `migrate:fresh` drops every table and rebuilds the database only from the migration files, so any index created by hand in psql would disappear.

## JSONB vs. normal column (Step 9)

- **Stored in JSONB:** `dress_code` (inside `details`). Not every kind of event needs a dress code, and other optional details such as `capacity` and `tags` can differ from event to event, so JSONB lets each event carry only the extra attributes it needs without adding a new column for each one.
- **Kept in a normal column:** `starts_at`. Every event has a start time, it is used for sorting and filtering on the list pages (`upcoming()` and `soonest()`), and a real `timestamp` column gives it a fixed type and a simple btree index.