# Campus Events: Week 10 Relational Data Model

A small Laravel and PostgreSQL data layer for a Campus Events system. Venues host events, participants register for events (with a registered or waitlisted status), and notes can be attached to either an event or a venue. The project demonstrates one-to-many, many-to-many, and polymorphic relationships, a report page that queries across them with Eloquent, and which rules PostgreSQL enforces versus which only the application code can enforce.

## Tech Stack

- Laravel (PHP)
- PostgreSQL 17
- Blade templates

## Setup

1. Clone the repository and install dependencies:
   ```bash
   composer install
   ```
2. Copy `.env.example` to `.env`, then set the database values:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=campus_events
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```
3. Create the database in psql:
   ```sql
   CREATE DATABASE campus_events;
   ```
4. Generate the app key, then build and seed the tables:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
5. Start the server and open the report page at `http://127.0.0.1:8000/report`:
   ```bash
   php artisan serve
   ```

The report page shows the first record of each type by default. To choose others, use query parameters, for example `/report?venue=2&event=3&participant=4&note=5`.

## Database Tables

| Table | Purpose |
|---|---|
| `venues` | Places that host events |
| `events` | Events, each at one venue (`venue_id`) |
| `participants` | People who register for events |
| `registrations` | Pivot table linking events and participants, with a `status` column. Composite primary key (`event_id`, `participant_id`) |
| `notes` | Notes for either an event or a venue (`noteable_type`, `noteable_id`) |

## Relationships

| Link | Type | Model/table holding the foreign key | Relationship methods | ON DELETE | Reason |
|---|---|---|---|---|---|
| Venue and Event | One-to-many | `events.venue_id` | `Venue::events()`, `Event::venue()` | RESTRICT | A venue that still has events must not be deletable. |
| Event and Participant | Many-to-many (through `registrations`) | `registrations.event_id` and `registrations.participant_id` | `Event::participants()`, `Participant::events()` | CASCADE | A registration has no meaning once its event or participant is deleted. |
| Note and Event/Venue | Polymorphic one-to-many | `notes.noteable_type` + `notes.noteable_id` (no real FK) | `Note::noteable()`, `Event::notes()`, `Venue::notes()` | None (app-level) | PostgreSQL cannot enforce a foreign key that points to two different tables. |

**Note on many-to-many:** in the database this is two one-to-many links (event to registrations, participant to registrations). Laravel presents it as a single `belongsToMany` relationship on each side. The composite primary key on `registrations` stops the same participant from registering for the same event twice.

## What PostgreSQL Enforces and What It Does Not

**Enforced by PostgreSQL:**
- An event cannot reference a venue that does not exist (foreign key).
- A venue with events cannot be deleted (RESTRICT).
- The same participant cannot register for the same event twice (composite primary key).
- Registrations are removed when their event or participant is deleted (CASCADE).
- `status` can only be `registered` or `waitlisted` (check constraint).

**Not enforced by PostgreSQL:**
- A note can point to an event or venue that does not exist. A foreign key can only reference one specific table, but `noteable_id` refers to different tables depending on `noteable_type`, so only Laravel code can keep this link valid.

## Report Page

The route `/report` is handled by `ReportController@index` and rendered by `resources/views/report.blade.php`. It shows seven labeled results, all built with Eloquent relationships (`withCount`, `whereHas`, relationship methods) and no hand-written JOINs:

1. Every venue with the number of events it hosts
2. All events at one chosen venue
3. All participants of one chosen event, with registration status
4. All events one chosen participant has joined
5. All events that have at least one waitlisted participant
6. All notes of one chosen event and of one chosen venue
7. For one chosen note, the name and type of the thing it belongs to