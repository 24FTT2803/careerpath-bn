<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * What the CPBN Bot knows about CareerPath BN itself.
 *
 * The bot guides students around the website: what each page is
 * for, how to do things on it, and the rules of the account. It
 * is not the Career Adviser and does not give career advice.
 *
 * Each page has a key. The bot may only link to pages by these
 * keys, so it can never send a student to an address that does
 * not exist.
 */
class CpbnBotKnowledge
{
    /**
     * @return array<string, array{name: string, route: string, also: array<int, string>, purpose: string, how_to: array<int, string>}>
     */
    public static function pages(): array
    {
        return [
            'dashboard' => [
                'name' => 'Dashboard',
                'route' => 'student.dashboard',
                'also' => [],
                'purpose' => 'The home page after signing in: profile completion, the next step to take, top career matches, milestones and notifications.',
                'how_to' => [
                    'Follow the next step card to see what to do next.',
                    'Open the Innovation Lab from its button to read developer notes.',
                ],
            ],
            'profile' => [
                'name' => 'My Profile',
                'route' => 'student.profile',
                'also' => [],
                'purpose' => 'A read-only view of everything on the student\'s profile.',
                'how_to' => [
                    'Use Edit Profile to change anything shown here.',
                    'Export the profile as a PDF from this page.',
                ],
            ],
            'profile_edit' => [
                'name' => 'Edit Profile',
                'route' => 'student.profile.edit',
                'also' => [],
                'purpose' => 'Where the profile is filled in, in seven sections: Personal Information, Qualifications, Skills & Competencies, Interests & Preferences, Projects & Experience, Certifications and Career Aspirations.',
                'how_to' => [
                    'Jump between sections from the Sections list beside the form.',
                    'Click the ? beside a section heading for help with that section.',
                    'Qualifications: choose the BDQF level of each award from its BDQF Level list. "What are BDQF levels?", just under the Qualifications heading, opens the full ladder of levels and the awards at each, so a student can find their award and read off its level. Tick "Still studying this" for an award not finished yet.',
                    'Skills & Competencies: pick each BIICF competency the student has and the proficiency level that matches what they can actually do. Technical competencies and soft skills have separate lists and separate scales.',
                    'Save the form to keep changes; the progress bar shows how complete the profile is.',
                    'Profile completion counts personal details, academic information, competencies, interests and aspirations. Projects and certifications are optional.',
                ],
            ],
            'recommendations' => [
                'name' => 'Career Recommendations',
                'route' => 'student.recommendations.index',
                'also' => ['student.recommendations.*'],
                'purpose' => 'Generate AI career recommendations from the profile, matched to BIICF job roles, and see each match.',
                'how_to' => [
                    'Press Generate Recommendations. A fuller profile gives better matches.',
                    'Generations are limited per plan; the page shows how many are left.',
                    'After changing the profile, generate again so the matches reflect it.',
                    'Open a role to see its Career Analysis: overview, entry requirements, matching competencies, readiness, skill gaps, training, certifications and a development plan.',
                ],
            ],
            'career_adviser' => [
                'name' => 'Career Adviser',
                'route' => 'student.career-adviser',
                'also' => [],
                'purpose' => 'A separate AI chat for career advice: which roles suit the student, how to close skill gaps, what qualifications open. Career questions belong here, not with the CPBN Bot.',
                'how_to' => [
                    'Ask questions about careers, competencies, BDQF levels or BIICF roles.',
                    'It uses the student\'s profile, recommendations and milestones to personalise its answers.',
                ],
            ],
            'milestones' => [
                'name' => 'Milestones',
                'route' => 'student.milestones',
                'also' => ['student.milestones.*'],
                'purpose' => 'Goals the student sets themselves, with target dates, to track learning and career progress.',
                'how_to' => [
                    'Add a milestone with a title, description and target date.',
                    'Upload proof when it is done: PDF, JPG, PNG, DOC or DOCX, up to 10 MB.',
                ],
            ],
            'history' => [
                'name' => 'History',
                'route' => 'student.history',
                'also' => [],
                'purpose' => 'Past recommendation generations and Career Adviser history, where the plan includes them.',
                'how_to' => [
                    'Compare how earlier sets of recommendations looked.',
                ],
            ],
            'biicf_explorer' => [
                'name' => 'BIICF Explorer',
                'route' => 'student.biicf-explorer.index',
                'also' => ['student.biicf-explorer.*'],
                'purpose' => 'Browse the Brunei ICT Industry Competency Framework. A side menu on the left switches between its tabs: under Explore, ICT Sub-sectors and Job Roles; under About the role, Career Paths, Competencies, Entry Requirements and Training & Certifications; under Reference, Proficiency Levels. A "Selected role" box above the menu shows the role being looked at.',
                'how_to' => [
                    'ICT Sub-sectors: cards for the six sub-sectors. Clicking one opens Job Roles filtered to it.',
                    'Job Roles: every role, ordered by career level. Its search box, "Search job roles by title...", matches role titles and sub-sector names. A sub-sector filter shows as a tag with an × to clear it.',
                    'Clicking a role selects it and opens Career Paths: what the role involves and where it leads. Career Paths, Entry Requirements and Training & Certifications need a role selected first.',
                    'Competencies with no role selected: the whole glossary, with its own search box, "Search competencies by name...", and All / Technical / Soft Skill filters.',
                    'Competencies with a role selected: only that role\'s competencies, each with the level it is required at. Where the role has several tiers, "Required at" buttons switch between them. "Compare to my profile" checks each against the levels on the student\'s profile. To see the competencies of one area of work, select a role in that sub-sector and open Competencies.',
                    'Clicking a competency opens it under Proficiency Levels, showing what it asks at each level, with the level the role requires marked.',
                    'Entry Requirements: the qualifications and experience the selected role expects, per tier where it has several, written in BDQF levels.',
                    'Proficiency Levels with nothing opened: both scales, technical (five levels) and soft skills (four).',
                ],
            ],
            'innovation_lab' => [
                'name' => 'Innovation Lab',
                'route' => 'student.innovation-lab',
                'also' => [],
                'purpose' => 'Notes from the CareerPath BN developers about what is being built.',
                'how_to' => [
                    'React to a note, or say you are interested where a note invites it.',
                ],
            ],
            'notifications' => [
                'name' => 'Notifications',
                'route' => 'student.notifications',
                'also' => [],
                'purpose' => 'Every notification sent to the student, such as Premium changes and new Innovation Lab notes.',
                'how_to' => [
                    'Mark notifications as read one at a time or all at once.',
                ],
            ],
            'settings' => [
                'name' => 'Settings',
                'route' => 'student.settings',
                'also' => [],
                'purpose' => 'Account settings: plan and subscription, advertising, password, and deleting the account.',
                'how_to' => [
                    'Upgrade to Premium, or cancel a subscription, from Your Plan.',
                    'Change the password from Change Password.',
                    'Delete the account from Delete Account. This removes all data permanently.',
                ],
            ],
        ];
    }

    /**
     * Rules of the account and the plans, as plain statements.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'registration' => [
                'Only Politeknik Brunei email addresses can sign up: @student.pb.edu.bn or @pb.edu.bn.',
                'No account exists until the student opens the email sent to that address and chooses Authenticate. "This isn\'t me" in the same email discards the sign-up. The links expire after an hour.',
            ],
            'student_id' => [
                'A @student.pb.edu.bn address is the Student ID, for example 24FTT2803@student.pb.edu.bn, and that ID is verified automatically.',
                'Anyone else enters their Student ID on Edit Profile and a lecturer or administrator verifies it.',
            ],
            'premium' => [
                'Premium is upgraded to from Settings. No payment is taken; it demonstrates a subscription.',
                'A subscription lasts one month and is not renewed automatically. The student is notified when it ends and can subscribe again.',
                'Cancelling ends Premium immediately, not at the end of the month.',
                'Premium given by an administrator, a sponsor or a trial cannot be cancelled by the student.',
                'Premium lets the student switch advertising off and gives a higher limit on generating recommendations, with history and report downloads.',
            ],
            'advertising' => [
                'Advertising is shown on the free plan and pays for free access, so it cannot be switched off there.',
                'On Premium, advertising is off by default and can be switched on from Settings.',
            ],
            'framework' => [
                'BIICF is the Brunei ICT Industry Competency Framework. It describes ICT job roles in sub-sectors, the tiers of each role, the competencies each needs and at what proficiency level, and the qualifications to enter it. CareerPath BN matches students to its roles. The full details are in the BIICF Explorer, and biicf in the context lists its sub-sectors, roles, competencies and scales.',
                'BDQF is the Brunei Darussalam Qualifications Framework: a ladder of levels that places every award, so awards from schools, technical and vocational education and higher education can be compared. BIICF entry requirements are written in BDQF levels. biicf.bdqf_levels lists every level and the awards at it.',
                'Proficiency levels say how well someone can apply a competency. Technical competencies use a five-level scale and soft skills a four-level scale; biicf.proficiency_scales has both.',
                'On a career recommendation, the match score is the AI\'s estimate of how well a role suits the student; career readiness is calculated by CareerPath from the student\'s recorded competency levels against the role\'s requirements. They mean different things.',
            ],
            'cpbn_bot' => [
                'The CPBN Bot answers questions about using CareerPath BN. Its ring shows how much of the plan\'s token allowance has been used.',
                'Plans with CPBN Bot History can reopen past conversations.',
            ],
        ];
    }

    /**
     * The BIICF Explorer's tabs, by the key its links use.
     *
     * @return array<string, string>
     */
    public static function explorerSections(): array
    {
        return [
            'sub-sectors' => 'ICT Sub-sectors',
            'job-roles' => 'Job Roles',
            'career-paths' => 'Career Paths',
            'competencies' => 'Competencies',
            'entry-requirements' => 'Entry Requirements',
            'training' => 'Training & Certifications',
            'proficiency-levels' => 'Proficiency Levels',
        ];
    }

    /**
     * Explorer tabs that show nothing until a role is selected.
     *
     * @return array<int, string>
     */
    public static function roleSections(): array
    {
        return ['career-paths', 'entry-requirements', 'training'];
    }

    /**
     * The page a route belongs to, so the bot knows where the
     * student is asking from.
     */
    public static function pageForRoute(?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        foreach (self::pages() as $key => $page) {
            if ($routeName === $page['route'] || Str::is($page['also'], $routeName)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public static function pageKeys(): array
    {
        return array_keys(self::pages());
    }
}
