<?php

namespace Database\Seeders;

use App\Models\BiicfBdqfLevel;
use App\Models\BiicfJobLevel;
use App\Models\BiicfProficiencyLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The framework's fixed reference scales.
 *
 * These are the parts of the BIICF that are short enough to
 * transcribe by hand and stable enough to be worth holding
 * outside the edition import: the two proficiency scales, the
 * job levels, and the BDQF ladder from Annex 1.
 *
 * Everything bulkier — competency definitions, level
 * descriptors, job roles — comes from the reference book itself
 * rather than from here.
 *
 * Safe to re-run.
 */
class BiicfReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProficiencyScales();
        $this->seedJobLevels();
        $this->seedBdqfLadder();
    }

    /**
     * Both proficiency scales, worded as the book words them.
     *
     * Technical has five rungs and soft skills four. Keeping the
     * descriptions verbatim matters because they are what a
     * student reads to understand what is being asked of them.
     */
    private function seedProficiencyScales(): void
    {
        $technical = [
            1 => [
                'Follow',
                'Demonstrates introductory understanding and ability and, with guidance, applies the competency in a few simple situations.',
            ],
            2 => [
                'Assist',
                'Demonstrates basic knowledge and ability and, with guidance, can apply the competency in common situations that present limited difficulties.',
            ],
            3 => [
                'Apply',
                'Demonstrates solid knowledge and ability and can apply the competency with minimal or no guidance in the full range of typical situations. Would require guidance to handle novel or more complex situations.',
            ],
            4 => [
                'Ensure',
                'Demonstrates advanced knowledge and ability and can apply the competency in new or complex situations. Guides other professionals.',
            ],
            5 => [
                'Strategise',
                'Demonstrates expert knowledge and ability and can apply the competency in the most complex situations. Develops new approaches, methods, or policies in the area. Is recognised as an expert, internally and/or externally. Leads the guidance of other professionals.',
            ],
        ];

        $soft = [
            1 => [
                'Basic',
                'Foundational understanding of the competency, usage in the business context to achieve the right results.',
            ],
            2 => [
                'Intermediate',
                'Moderate understanding on the competency and have the necessary business acumen to use it in a broader context.',
            ],
            3 => [
                'Advanced',
                'Strong understanding on the competency, knows the context on where and how to use it to achieve the expected results.',
            ],
            4 => [
                'Expert',
                'Have the necessary expertise on the competency, able to use in a strategic context and assist others to use it efficiently.',
            ],
        ];

        foreach ([
            BiicfProficiencyLevel::SCALE_TECHNICAL => $technical,
            BiicfProficiencyLevel::SCALE_SOFT => $soft,
        ] as $scale => $levels) {
            foreach ($levels as $number => [$name, $description]) {
                BiicfProficiencyLevel::updateOrCreate(
                    [
                        'scale' => $scale,
                        'level_number' => $number,
                    ],
                    [
                        'name' => $name,
                        'description' => $description,
                    ]
                );
            }
        }
    }

    /**
     * The job levels used across the role pages.
     *
     * The framework's own list names four. C-Suite is included
     * because role pages use it — Chief Information Officer is
     * marked that way — and omitting it would leave those roles
     * with no level at all.
     *
     * Left without descriptions on purpose: the book does not
     * define these, and inventing wording would misrepresent it.
     */
    private function seedJobLevels(): void
    {
        $levels = [
            'Entrant',
            'Specialist',
            'Expert/Management',
            'Senior Management',
            'C-Suite',
        ];

        foreach ($levels as $index => $name) {
            /*
             * Keyed on the name rather than the slug, so that
             * correcting how a name is slugged updates the level
             * in place instead of leaving the old row behind
             * beside a new one.
             */
            BiicfJobLevel::updateOrCreate(
                ['name' => $name],
                [
                    'slug' => self::slugify($name),
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    /**
     * Slug a framework name that may contain a slash.
     *
     * Str::slug drops the slash rather than treating it as a
     * word break, so "Expert/Management" would otherwise become
     * "expertmanagement". Several names in the book are written
     * this way, including job role titles such as
     * "IT Administrator/Manager".
     */
    private static function slugify(string $name): string
    {
        return Str::slug(str_replace('/', '-', $name));
    }

    /**
     * Annex 1: the Brunei Darussalam Qualifications Framework.
     *
     * Columns follow the Annex's own layout — schools,
     * technical and vocational, higher education — so the page
     * can be checked against the book line by line. A sector the
     * Annex leaves blank at a level stays null.
     */
    private function seedBdqfLadder(): void
    {
        $ladder = [
            8 => [
                'higher_education_sector' => ['Doctoral Degree'],
            ],
            7 => [
                'higher_education_sector' => [
                    "Master's Degree",
                    'Post Graduate Diploma',
                    'Post Graduate Certificate',
                ],
            ],
            6 => [
                'higher_education_sector' => ["Bachelor's Degree"],
            ],
            5 => [
                'tvet_sector' => [
                    'Advanced Diploma',
                    'Higher National Diploma (HND)',
                    'Foundation Degree',
                ],
                'higher_education_sector' => [
                    'Advanced Diploma',
                    'Higher National Diploma (HND)',
                ],
                /*
                 * The Annex prints Advanced Diploma and HND under
                 * both sectors at this level. Worth an eye over
                 * the printed page before this is relied on, as
                 * the column split is the one place the layout is
                 * genuinely ambiguous in text form.
                 */
                'notes' => 'Advanced Diploma and HND appear under both the vocational and higher education sectors in Annex 1.',
            ],
            4 => [
                'schools_sector' => [
                    'GCE "A" Level',
                    'IGCSE "A" Level',
                    'IB Diploma',
                    'STPU',
                ],
                'tvet_sector' => [
                    'Diploma',
                    'Higher National Technical Education Certificate (HNTec)',
                ],
            ],
            3 => [
                'schools_sector' => [
                    'GCE "O" Level (Grades A–C)',
                    'IGCSE and GCSE "O" Level (Grades A*–C)',
                    'SPU (Grades A–C)',
                ],
                'tvet_sector' => [
                    'BTEC Level 2 Diploma',
                    'Skills Certificate 3 (SC3)',
                    'National Technical Education Certificate (NTec)',
                ],
            ],
            2 => [
                'schools_sector' => [
                    'GCE "O" Level (Grades D–E)',
                    'IGCSE "O" Level (Grades D–E)',
                    'SPU (Grade D)',
                ],
                'tvet_sector' => [
                    'BTEC Level 2 Extended Certificate',
                    'Skills Certificate 2 (SC2)',
                    'Industrial Skills Qualifications (ISQ)',
                ],
            ],
            1 => [
                'schools_sector' => ['BTEC Level Introductory Certificate'],
                'tvet_sector' => ['Skills Certificate 1 (SC1)'],
            ],
        ];

        foreach ($ladder as $level => $sectors) {
            BiicfBdqfLevel::updateOrCreate(
                ['level' => $level],
                [
                    'schools_sector' => $sectors['schools_sector'] ?? null,
                    'tvet_sector' => $sectors['tvet_sector'] ?? null,
                    'higher_education_sector' => $sectors['higher_education_sector'] ?? null,
                    'notes' => $sectors['notes'] ?? null,
                ]
            );
        }
    }
}
