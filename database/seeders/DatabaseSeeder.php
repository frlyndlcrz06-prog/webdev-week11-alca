<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $venues = collect(['Main Gym', 'Audio-Visual Hall', 'Open Quadrangle'])
            ->map(fn ($n) => Venue::create(['name' => $n, 'capacity' => 200]));

        $events = collect(range(1, 6))->map(fn ($i) =>
            $venues[$i % 3]->events()->create([
                'title' => "Event $i",
                'starts_at' => now()->addDays($i),
            ]));

        $people = collect(range(1, 8))->map(fn ($i) =>
            Participant::create(['name' => "Participant $i", 'email' => "p$i@example.com"]));

        $pairs = [
            [0,0,'registered'],[0,1,'registered'],[1,1,'waitlisted'],[1,2,'registered'],
            [2,2,'registered'],[2,3,'waitlisted'],[3,3,'registered'],[3,4,'registered'],
            [4,4,'waitlisted'],[4,5,'registered'],[5,6,'registered'],[5,7,'waitlisted'],
        ];
        foreach ($pairs as [$e, $p, $status]) {
            $events[$e]->participants()->attach($people[$p]->id, ['status' => $status]);
        }

        foreach ([0, 1, 2] as $i) { $events[$i]->notes()->create(['body' => "Event note $i"]); }
        foreach ([0, 1, 2] as $i) { $venues[$i]->notes()->create(['body' => "Venue note $i"]); }
    }

}
