<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Venues
        $venues = collect(['Main Gym', 'Audio-Visual Hall', 'Open Quadrangle'])
            ->map(fn ($n) => Venue::create(['name' => $n, 'capacity' => 200]));
        $venueIds = $venues->pluck('id')->all();

        // 20,000 events, inserted in chunks of 1,000
        $phrases = [
            'Join us for a lively evening of music and food.',
            'A hands-on workshop for beginners and experts.',
            'Open to all students, faculty, and guests.',
            'Bring your friends and enjoy the program.',
            'Featuring guest speakers and a Q&A session.',
            'A community gathering with games and prizes.',
        ];
        $dressCodes = ['casual', 'formal', 'smart casual', 'uniform', 'business'];
        $tagPool    = ['music', 'sports', 'tech', 'arts', 'food', 'academic'];

        $rows = [];
        for ($i = 1; $i <= 20000; $i++) {
            $isRare = ($i % 250 === 0);   // 80 events = 0.4%

            $description = collect($phrases)->random(3)->implode(' ');
            if ($isRare) {
                $description .= ' Includes a night telescope viewing.';
            }

            $rows[] = [
                'venue_id'    => $venueIds[$i % count($venueIds)],
                'title'       => "Event $i",
                   'starts_at' => $i <= 100
                        ? $now->copy()->addDays(7)->addMinutes($i)
                        : $now->copy()->addDays(14 + $i % 365)->addHours($i % 24),
                'description' => $description,
                'details'     => json_encode([
                    'dress_code' => $isRare ? 'costume' : $dressCodes[$i % count($dressCodes)],
                    'capacity'   => 50 + ($i % 10) * 25,
                    'tags'       => collect($tagPool)->random(2)->values()->all(),
                ]),
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            if (count($rows) === 1000) {
                DB::table('events')->insert($rows);
                $rows = [];
            }
        }

        // 200 participants
        $people = [];
        for ($i = 1; $i <= 200; $i++) {
            $people[] = [
                'name' => "Participant $i",
                'email' => "p$i@example.com",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('participants')->insert($people);

        // Registrations for the first 100 events (5 each, both statuses)
        $participantIds = Participant::pluck('id');
        $regs = [];
        foreach (range(1, 100) as $eventId) {
            foreach ($participantIds->random(5)->values() as $n => $pid) {
                $regs[] = [
                    'event_id' => $eventId,
                    'participant_id' => $pid,
                    'status' => $n % 4 === 0 ? 'waitlisted' : 'registered',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('registrations')->insert($regs);

        // A few notes (kept from Week 10)
        foreach ([1, 2, 3] as $id) {
            Event::find($id)->notes()->create(['body' => "Event note $id"]);
        }
        foreach ($venues as $i => $venue) {
            $venue->notes()->create(['body' => 'Venue note ' . ($i + 1)]);
        }
    }
}