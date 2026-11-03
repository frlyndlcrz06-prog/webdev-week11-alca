<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Note;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $venue       = Venue::find($request->venue) ?? Venue::first();
        $event       = Event::find($request->event) ?? Event::first();
        $participant = Participant::find($request->participant) ?? Participant::first();
        $note        = Note::find($request->note) ?? Note::first();
        $owner       = $note->noteable;

        return view('report', [
            'venuesWithCounts'  => Venue::withCount('events')->get(),
            'venue'             => $venue,
            'venueEvents'       => $venue->events,
            'event'             => $event,
            'eventParticipants' => $event->participants,
            'participant'       => $participant,
            'joinedEvents'      => $participant->events,
            'waitlistedEvents'  => Event::whereHas('participants',
                                    fn ($q) => $q->where('registrations.status', 'waitlisted'))
                                    ->orderBy('id')->get(),
            'eventNotes'        => $event->notes,
            'venueNotes'        => $venue->notes,
            'note'              => $note,
            'ownerName'         => $owner->name ?? $owner->title,
            'ownerKind'         => $note->noteable_type,
        ]);
    }
}
