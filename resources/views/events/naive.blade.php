<div>
<!DOCTYPE html>
<html>
<head><title>Events (naive)</title></head>
<body>
<h1>Events: naive version (lazy loading)</h1>
<p><strong>Total queries: __QUERY_COUNT__</strong></p>
<table border="1" cellpadding="6">
    <tr><th>Title</th><th>Venue</th><th>Participants</th></tr>
    @foreach ($events as $event)
        <tr>
            <td>{{ $event->title }}</td>
            <td>{{ $event->venue->name }}</td>
            <td>{{ $event->participants->count() }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
</div>
