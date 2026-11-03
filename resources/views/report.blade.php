<div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Events Report</title>
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5c0000;
            --gold: #f5c518;
            --cream: #fdf8ec;
            --ink: #2b2b2b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: var(--cream);
            color: var(--ink);
        }
        header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: #fff;
            padding: 28px 40px;
            border-bottom: 6px solid var(--gold);
        }
        header h1 { margin: 0; font-size: 28px; letter-spacing: 0.5px; }
        header p  { margin: 6px 0 0; color: var(--gold); font-size: 14px; }
        .brand { display: flex; align-items: center; gap: 16px; }
        .brand img { height: 60px; width: auto; }
        main {
            max-width: 960px;
            margin: 28px auto;
            padding: 0 20px;
            display: grid;
            gap: 20px;
        }
        section {
            background: #fff;
            border-radius: 10px;
            border-left: 6px solid var(--maroon);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 18px 22px;
        }
        h2 {
            margin: 0 0 12px;
            font-size: 18px;
            color: var(--maroon);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .num {
            background: var(--gold);
            color: var(--maroon-dark);
            font-weight: 700;
            border-radius: 50%;
            min-width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: var(--maroon);
            color: #fff;
            text-align: left;
            padding: 8px 12px;
            font-size: 14px;
        }
        td { padding: 8px 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        tr:nth-child(even) td { background: #fffaf0; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .registered { background: #e3f4e5; color: #1b6b2a; }
        .waitlisted { background: #fff0c2; color: #8a6200; }
        .empty { color: #888; font-style: italic; margin: 0; }
        .pill {
            background: var(--cream);
            border: 1px solid var(--gold);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 15px;
        }
        footer { text-align: center; color: #777; font-size: 13px; padding: 20px; }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <img src="{{ asset('pup-logo.png') }}" alt="PUP logo" height="60">
        <div>
            <h1>Campus Events Report</h1>
            <p>Venues, events, participants, and notes</p>
        </div>
    </div>
</header>

<main>

    <section>
        <h2><span class="num">1</span> Every venue with the number of events it hosts</h2>
        <table>
            <tr><th>Venue</th><th>Events hosted</th></tr>
            @foreach ($venuesWithCounts as $v)
                <tr><td>{{ $v->name }}</td><td>{{ $v->events_count }}</td></tr>
            @endforeach
        </table>
    </section>

    <section>
        <h2><span class="num">2</span> All events at venue: {{ $venue->name }}</h2>
        @forelse ($venueEvents as $e)
            @if ($loop->first)<table><tr><th>Event</th><th>Starts</th></tr>@endif
            <tr><td>{{ $e->title }}</td><td>{{ $e->starts_at }}</td></tr>
            @if ($loop->last)</table>@endif
        @empty
            <p class="empty">No events at this venue.</p>
        @endforelse
    </section>

    <section>
        <h2><span class="num">3</span> Participants of event "{{ $event->title }}" with status</h2>
        @forelse ($eventParticipants as $p)
            @if ($loop->first)<table><tr><th>Participant</th><th>Status</th></tr>@endif
            <tr>
                <td>{{ $p->name }}</td>
                <td><span class="badge {{ $p->pivot->status }}">{{ $p->pivot->status }}</span></td>
            </tr>
            @if ($loop->last)</table>@endif
        @empty
            <p class="empty">No participants for this event.</p>
        @endforelse
    </section>

    <section>
        <h2><span class="num">4</span> Events joined by {{ $participant->name }}</h2>
        @forelse ($joinedEvents as $e)
            @if ($loop->first)<table><tr><th>Event</th><th>Status</th></tr>@endif
            <tr>
                <td>{{ $e->title }}</td>
                <td><span class="badge {{ $e->pivot->status }}">{{ $e->pivot->status }}</span></td>
            </tr>
            @if ($loop->last)</table>@endif
        @empty
            <p class="empty">This participant has not joined any events.</p>
        @endforelse
    </section>

    <section>
        <h2><span class="num">5</span> Events with at least one waitlisted participant</h2>
        @forelse ($waitlistedEvents as $e)
            @if ($loop->first)<table><tr><th>Event</th></tr>@endif
            <tr><td>{{ $e->title }}</td></tr>
            @if ($loop->last)</table>@endif
        @empty
            <p class="empty">No events have waitlisted participants.</p>
        @endforelse
    </section>

    <section>
        <h2><span class="num">6a</span> Notes of event "{{ $event->title }}"</h2>
        @forelse ($eventNotes as $n)
            @if ($loop->first)<table><tr><th>Note</th></tr>@endif
            <tr><td>{{ $n->body }}</td></tr>
            @if ($loop->last)</table>@endif
        @empty
            <p class="empty">No notes on this event.</p>
        @endforelse
    </section>

    <section>
        <h2><span class="num">6b</span> Notes of venue "{{ $venue->name }}"</h2>
        @forelse ($venueNotes as $n)
            @if ($loop->first)<table><tr><th>Note</th></tr>@endif
            <tr><td>{{ $n->body }}</td></tr>
            @if ($loop->last)</table>@endif
        @empty
            <p class="empty">No notes on this venue.</p>
        @endforelse
    </section>

    <section>
        <h2><span class="num">7</span> Owner of note #{{ $note->id }}</h2>
        <div class="pill">
            Note: <strong>{{ $note->body }}</strong><br>
            Belongs to: <strong>{{ $ownerName }}</strong> (type: <strong>{{ $ownerKind }}</strong>)
        </div>
    </section>

</main>

<footer>Week 10: Relational Data Model and Queries</footer>

</body>
</html>
</div>