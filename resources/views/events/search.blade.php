<div>
<!DOCTYPE html>
<html>
<head><title>Search Events</title></head>
<body>
<h1>Search events (full-text)</h1>

<form method="GET" action="/events/search">
    <input type="text" name="q" value="{{ $term }}" placeholder="Search descriptions">
    <button type="submit">Search</button>
</form>

<h3>SQL produced by Laravel</h3>
<pre>{{ $sql }}</pre>

<h3>Results ({{ $events->count() }}, max 50)</h3>
<table border="1" cellpadding="6">
    <tr><th>Title</th><th>Venue</th><th>Description</th></tr>
    @foreach ($events as $event)
        <tr>
            <td>{{ $event->title }}</td>
            <td>{{ $event->venue->name }}</td>
            <td>{{ $event->description }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
</div>
