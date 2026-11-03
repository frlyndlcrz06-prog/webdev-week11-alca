<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{

    // Naive version: lazy loading (causes the N+1 problem)
    public function naive()
    {
        DB::enableQueryLog();

        $events = Event::upcoming()->soonest()->limit(50)->get();

        return $this->renderWithQueryCount('events.naive', ['events' => $events]);
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = Event::query()->with('venue');

        if ($term !== '') {
            $query->whereFullText('description', $term);
        }

        $query->limit(50);
        $sql = $query->toSql();

        $events = $term !== '' ? $query->get() : collect();

        return view('events.search', compact('events', 'term', 'sql'));
    }

    // Fixed version: eager loading + withCount
    public function index(Request $request)
    {
        DB::enableQueryLog();

        $events = Event::upcoming()
            ->soonest()
            ->when($request->venue, fn ($q, $venueId) => $q->atVenue((int) $venueId))
            ->when($request->query('dress_code'), function ($q, $dress) use ($request) {
                return $request->query('mode') === 'contains'
                    ? $q->whereJsonContains('details', ['dress_code' => $dress])   // containment form
                    : $q->where('details->dress_code', $dress);                    // arrow form
            })
            ->with('venue')
            ->withCount('participants')
            ->limit(50)
            ->get();

        return $this->renderWithQueryCount('events.index', ['events' => $events]);
    }

    // Render first, then count, because lazy loading happens while the view renders
    private function renderWithQueryCount(string $view, array $data)
    {
        $html = view($view, $data)->render();
        $count = count(DB::getQueryLog());

        return str_replace('__QUERY_COUNT__', $count, $html);
    }
}