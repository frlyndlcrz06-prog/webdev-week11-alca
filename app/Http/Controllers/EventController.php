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

    // Fixed version: eager loading + withCount
    public function index(Request $request)
    {
        DB::enableQueryLog();

        $events = Event::upcoming()
            ->soonest()
            ->when($request->venue, fn ($q, $venueId) => $q->atVenue((int) $venueId))
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