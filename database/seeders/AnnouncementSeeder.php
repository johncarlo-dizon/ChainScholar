<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Try to pick an admin author; fall back to any user if needed.
            $admin = User::where('role', 'ADMIN')->first();
            if (!$admin) {
                $admin = User::first(); // fallback (shouldn't happen with DemoUsersSeeder)
            }

            // Optionally grab an adviser for author variety (not required).
            $adviser = User::where('role', 'ADVISER')->first() ?: $admin;

            // Wipe existing announcements for deterministic seeding (optional)
            // Comment out if you prefer to keep existing data when re-seeding.
            Announcement::query()->delete();

            $now = Carbon::now();

            $rows = [
                [
                    'title'      => 'System Downtime (Tonight)',
                    'body'       => 'Heads up! Scheduled maintenance from 10:00 PM to 12:00 AM. Services may be temporarily unavailable.',
                    'event_date' => $now->copy()->addDay()->toDateString(),
                    'tier'       => Announcement::TIER_URGENT,
                    'audience'   => Announcement::AUD_ALL,
                    'user_id'    => $admin->id,
                ],
                [
                    'title'      => 'Capstone Final Defense – Batch A',
                    'body'       => 'Final defense schedule is posted on the portal. Please check your assigned panel and room.',
                    'event_date' => $now->copy()->addDays(7)->toDateString(),
                    'tier'       => Announcement::TIER_IMPORTANT,
                    'audience'   => Announcement::AUD_STUDENT,
                    'user_id'    => $admin->id,
                ],
                [
                    'title'      => 'Advisers’ Coordination Meeting',
                    'body'       => 'Short sync meeting for advisers to align on rubrics and timelines. See calendar invite.',
                    'event_date' => $now->copy()->addDays(3)->toDateString(),
                    'tier'       => Announcement::TIER_IMPORTANT,
                    'audience'   => Announcement::AUD_ADVISER,
                    'user_id'    => $adviser->id,
                ],
                [
                    'title'      => 'Admin: Grade Portal Window',
                    'body'       => 'Please review the grade submission portal settings for Q1. Open window is next week.',
                    'event_date' => $now->copy()->addDays(5)->toDateString(),
                    'tier'       => Announcement::TIER_GENERAL,
                    'audience'   => Announcement::AUD_ADMIN,
                    'user_id'    => $admin->id,
                ],
                [
                    'title'      => 'Library Orientation',
                    'body'       => 'All students are encouraged to attend the digital library orientation this Friday.',
                    'event_date' => $now->copy()->addDays(2)->toDateString(),
                    'tier'       => Announcement::TIER_GENERAL,
                    'audience'   => Announcement::AUD_STUDENT,
                    'user_id'    => $admin->id,
                ],
                [
                    'title'      => 'Update: Research Template v2.1',
                    'body'       => 'New CKEditor template with updated formatting has been uploaded. Please use v2.1 moving forward.',
                    'event_date' => null,
                    'tier'       => Announcement::TIER_GENERAL,
                    'audience'   => Announcement::AUD_ALL,
                    'user_id'    => $admin->id,
                ],
            ];

            foreach ($rows as $r) {
                Announcement::create($r);
            }
        });
    }
}
