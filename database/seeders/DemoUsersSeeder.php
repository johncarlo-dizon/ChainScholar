<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AdviserProfile;
use App\Models\ResearchInterest;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1) Seed users (by email, capture actual models)
            $rows = [
                ['name'=>'Juan',   'email'=>'student2@gmail.com', 'role'=>'STUDENT', 'password'=>'$2y$12$S6V.NX6GBEce0ENaqPQrNuopV/chV.IafZqEn7XlTUMRQMziPRwp6'],
                ['name'=>'Pedro',  'email'=>'student1@gmail.com', 'role'=>'STUDENT', 'password'=>'$2y$12$S6V.NX6GBEce0ENaqPQrNuopV/chV.IafZqEn7XlTUMRQMziPRwp6'],
                ['name'=>'Jc',     'email'=>'admin@gmail.com',    'role'=>'ADMIN',   'password'=>'$2y$12$S6V.NX6GBEce0ENaqPQrNuopV/chV.IafZqEn7XlTUMRQMziPRwp6'],
                ['name'=>'Aron',   'email'=>'adviser1@gmail.com', 'role'=>'ADVISER', 'password'=>'$2y$12$TngIlt/AXWDNiDjNa7W5ceP2fexcpspOQfVwVwjifdoOZP82HQPG.'],
                ['name'=>'Joel',   'email'=>'adviser2@gmail.com', 'role'=>'ADVISER', 'password'=>'$2y$12$TngIlt/AXWDNiDjNa7W5ceP2fexcpspOQfVwVwjifdoOZP82HQPG.'],
                ['name'=>'Atasha', 'email'=>'adviser3@gmail.com', 'role'=>'ADVISER', 'password'=>'$2y$12$TngIlt/AXWDNiDjNa7W5ceP2fexcpspOQfVwVwjifdoOZP82HQPG.'],
                ['name'=>'Jay',    'email'=>'admin1@gmail.com',   'role'=>'ADMIN',   'password'=>'$2y$12$RIBWwKRNfDimZe2oGmijbuYsGYE0WrzeHuKZphqYqaNMM2ILOH/CO'],
            ];

            $users = [];
            foreach ($rows as $r) {
                $u = User::updateOrCreate(
                    ['email' => $r['email']],
                    [
                        'name'     => $r['name'],
                        'role'     => $r['role'],
                        'password' => $r['password'], // already bcrypt-hashed
                    ]
                );
                $users[$r['email']] = $u; // capture actual model (with real id)
            }

            // 2) Adviser profiles keyed by email (no hardcoded IDs!)
            $adviserProfiles = [
                'adviser1@gmail.com' => [
                    'department'          => 'Computer Science',
                    'field_of_expertise'  => 'Artificial Intelligence',
                    'highest_degree'      => 'PhD',
                    'degree_school'       => 'UP Diliman',
                    'degree_year'         => 2015,
                    'advisory_years'      => 5,
                    'projects_handled'    => 12,
                    'notes'               => 'Specializes in AI-driven research.',
                    'interests'           => ['Machine Learning','Data Mining','NLP'],
                ],
                'adviser2@gmail.com' => [
                    'department'          => 'Information Technology',
                    'field_of_expertise'  => 'Cybersecurity',
                    'highest_degree'      => 'MS',
                    'degree_school'       => 'De La Salle University',
                    'degree_year'         => 2012,
                    'advisory_years'      => 8,
                    'projects_handled'    => 20,
                    'notes'               => 'Focus on network security and forensics.',
                    'interests'           => ['Cybersecurity','Network Forensics','Blockchain'],
                ],
                'adviser3@gmail.com' => [
                    'department'          => 'Engineering',
                    'field_of_expertise'  => 'Robotics',
                    'highest_degree'      => 'PhD',
                    'degree_school'       => 'Mapua University',
                    'degree_year'         => 2018,
                    'advisory_years'      => 3,
                    'projects_handled'    => 7,
                    'notes'               => 'Researcher in automation and robotics.',
                    'interests'           => ['Robotics','Computer Vision','IoT'],
                ],
            ];

            foreach ($adviserProfiles as $email => $data) {
                if (!isset($users[$email])) {
                    // skip if adviser user wasn't created for some reason
                    continue;
                }

                $userId = $users[$email]->id;

                // Upsert adviser profile
                $profile = AdviserProfile::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'department'         => $data['department'] ?? null,
                        'field_of_expertise' => $data['field_of_expertise'] ?? null,
                        'highest_degree'     => $data['highest_degree'] ?? null,
                        'degree_school'      => $data['degree_school'] ?? null,
                        'degree_year'        => $data['degree_year'] ?? null,
                        'advisory_years'     => $data['advisory_years'] ?? null,
                        'projects_handled'   => $data['projects_handled'] ?? null,
                        'notes'              => $data['notes'] ?? null,
                    ]
                );

                // Sample achievements (wipe & recreate to keep deterministic)
                $profile->achievements()->delete();
                $profile->achievements()->createMany([
                    ['title'=>'Best Research Mentor',      'issuer'=>'School of IT', 'year'=>2021, 'description'=>'Recognized for outstanding mentorship'],
                    ['title'=>'Outstanding Adviser Award', 'issuer'=>'CHED',         'year'=>2022, 'description'=>'For dedication in guiding thesis students'],
                ]);

                // Research interests: ensure exist and attach
                $interestIds = [];
                foreach ($data['interests'] as $name) {
                    $slug = Str::slug($name);
                    $interestIds[] = ResearchInterest::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
                }
                $profile->researchInterests()->sync($interestIds);
            }
        });
    }
}
