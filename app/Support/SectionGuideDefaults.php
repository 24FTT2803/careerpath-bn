<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * The help behind each '?' icon, as first written.
 *
 * Administrators can rewrite any of it, and add a video, from the
 * Section Guides page. This is what a guide starts as, and what
 * "Restore default" puts back.
 */
class SectionGuideDefaults
{
    public const PAGE_PROFILE = 'profile';

    public const PAGE_ANALYSIS = 'analysis';

    /**
     * @return array<string, array{page: string, title: string, body: string}>
     */
    public static function all(): array
    {
        return [
            'profile.personal' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Personal Information',
                'body' => "Your basic details, as they appear on your IC.\n\n"
                    ."Your Student ID is taken from your student.pb.edu.bn email and verified automatically. If you signed up with another address, enter your ID and a lecturer or administrator will verify it.\n\n"
                    .'A profile picture is optional.',
            ],
            'profile.qualifications' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Qualifications',
                'body' => "List the awards you hold or are studying for. Your Politeknik programme is already filled in and cannot be removed.\n\n"
                    ."Choose the BDQF level for each award. BIICF job roles state their entry requirements as BDQF levels, so this is how CareerPath works out which roles your qualifications open. Not sure of the level? Open \"What are BDQF levels?\" and find your award.\n\n"
                    .'Tick "Still studying this" for an award you have not finished yet. It is treated as what you are working towards, not as an award you already hold.',
            ],
            'profile.skills' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Skills & Competencies',
                'body' => "Choose the BIICF competencies you have and the proficiency level that matches what you can actually do. Technical competencies and soft skills are rated on separate scales.\n\n"
                    ."Be honest rather than generous. Your career readiness and skill gaps are calculated by comparing these levels with what each role requires, so an inflated level hides the gaps you most need to know about.\n\n"
                    .'Skills that are not in the BIICF can be added as additional skills. They help describe you, but do not count towards readiness.',
            ],
            'profile.interests' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Interests & Preferences',
                'body' => "Pick the areas of ICT you enjoy or want to explore, and add your own if they are not listed.\n\n"
                    .'Interests steer your career recommendations towards work you would like doing, not only work you are already able to do.',
            ],
            'profile.projects' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Projects & Experience',
                'body' => "Add coursework, personal projects, internships and work experience.\n\n"
                    .'Say what your role was, the technologies you used and what you achieved. Specific detail gives your recommendations evidence of what you can do. A link to the project or its code is useful if you have one.',
            ],
            'profile.certifications' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Certifications',
                'body' => "Add certificates from courses, training and professional bodies, with the issuing organisation and date.\n\n"
                    .'You can attach the certificate file as proof. Certifications show learning beyond your programme and are taken into account in your recommendations.',
            ],
            'profile.aspirations' => [
                'page' => self::PAGE_PROFILE,
                'title' => 'Career Aspirations',
                'body' => "Tell us where you want to go: your dream career, the vision you have for your working life, and your long-term goals.\n\n"
                    .'There are no wrong answers. Aspirations help shape your recommendations and give the Career Adviser something to plan towards with you.',
            ],
            'analysis.overview' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Career Overview',
                'body' => "What this BIICF job role involves, as described in the Brunei ICT Industry Competency Framework.\n\n"
                    .'Read it to check the day-to-day work is something you would want to do.',
            ],
            'analysis.entry' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Entry Requirements',
                'body' => "The ways into this role. Each line is an alternative, and meeting any one of them is enough.\n\n"
                    .'Requirements are written as BDQF levels, sometimes with a field of study, years of experience or a certification. Compare them with the BDQF levels on your qualifications.',
            ],
            'analysis.match' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Why This Career Matches You',
                'body' => "Why this role was recommended, based on your profile: your competencies, interests, qualifications, projects and aspirations.\n\n"
                    .'The match score is an AI estimate of how well the role suits you overall. It is not the same as being ready for it; see Career Readiness for that.',
            ],
            'analysis.competencies' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Matching Competencies',
                'body' => 'The competencies on your profile that this role also asks for. They are the strengths you already bring to it.',
            ],
            'analysis.readiness' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Career Readiness',
                'body' => "Calculated by CareerPath, not by the AI. It compares the proficiency level you recorded for each competency with the level this role requires.\n\n"
                    .'It rises as you close your skill gaps and update your profile. It is a guide to your progress, not a guarantee of being hired.',
            ],
            'analysis.gaps' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Skill Gaps',
                'body' => "Each bar compares your current level in a competency with the level this role requires. The difference is the gap to close.\n\n"
                    .'Start with the largest gaps, or those you can work on through your course. Once you improve, update the level on your profile and your readiness will change.',
            ],
            'analysis.training' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Recommended Training',
                'body' => 'Training listed for this role. Use it to plan how to close your skill gaps. Where none is listed, ask your lecturer or the Career Adviser about suitable courses and projects.',
            ],
            'analysis.certifications' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Recommended Certifications',
                'body' => 'Certifications relevant to this role. They can strengthen your application. Check entry requirements, cost and how the certification is assessed before you commit to one.',
            ],
            'analysis.plan' => [
                'page' => self::PAGE_ANALYSIS,
                'title' => 'Development Plan',
                'body' => "Practical next steps towards this role, suggested when this recommendation was generated from your profile and skill gaps.\n\n"
                    .'Turn the steps you want to take into milestones so you can track them and set yourself dates.',
            ],
        ];
    }

    /**
     * Add any guide that does not exist yet, leaving the ones an
     * administrator has already edited alone.
     */
    public static function insertMissing(): void
    {
        $now = now();

        DB::table('section_guides')->insertOrIgnore(
            collect(self::all())
                ->map(fn (array $guide, string $key) => [
                    'key' => $key,
                    'page' => $guide['page'],
                    'title' => $guide['title'],
                    'body' => $guide['body'],
                    'video_url' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->values()
                ->all()
        );
    }
}
