<?php

namespace App\Services\Biicf\Pdf;

use RuntimeException;

/**
 * Reads the reference book's pages into the shape the edition
 * comparison works against.
 *
 * Deliberately free of the framework and of whatever library
 * turned the PDF into positioned text. Everything hard about
 * reading this book is geometry, and geometry is testable
 * without either: give it positions, check what comes out. The
 * library-shaped part is a few dozen lines elsewhere.
 *
 * What the book actually tells us, in order of how much it can
 * be trusted:
 *
 *   - The bullet glyph is written down, so bullets are read, not
 *     guessed.
 *   - Section headings are a closed set of five phrases.
 *   - Columns are clusters of starting positions, found per page
 *     because they move between pages.
 *   - A line continuing a bullet starts where the bullet's text
 *     starts; a heading starts where the bullet glyph does.
 *
 * Nothing here reads the colour or the size of anything, because
 * a position-only reader has neither. An earlier reading of this
 * book leaned on both.
 *
 * Where it cannot read something it says so, on that role, and
 * leaves the field out rather than proposing a guess. A field
 * left out raises no difference, so the framework keeps what it
 * has; a guess would be put in front of an administrator as
 * though the book had said it.
 */
class BookReader
{
    /**
     * The section headings, as the only phrases that open a
     * section.
     *
     * Matched with the spaces taken out: the book's kerning
     * loses the space in "Entry Requirements" on four pages, and
     * a reader that insists on it silently drops those roles'
     * entry requirements.
     */
    private const SECTIONS = [
        'jobdescription' => 'description',
        'criticalworkfunctions' => 'work_functions',
        'skills&competencies' => 'competencies',
        'entryrequirements' => 'entry_requirements',
        'recommendedtechnicaltrainingcourses' => 'trainings',
        'recommendedsoftskillstrainingcourses' => 'trainings',
        'recommendedtrainingcourses' => 'trainings',
    ];

    /**
     * The labels in the block at the top of a role.
     *
     * Each sits in its own column with its value to the right,
     * and the value may run to more lines than the label, so
     * they are paired by which label's band the value falls in
     * rather than by sharing a line.
     */
    private const LABELS = [
        'alternatejobtitles' => 'alternate_titles',
        'sub-sector' => 'sub_sector',
        'functionalgroup' => 'functional_group',
        'jobfamily' => 'job_family',
        'joblevel' => 'job_level',
    ];

    /** How far a line must sit in from the bullet to be its text rather than a heading. */
    private const INDENT_STEP = 8.0;

    /**
     * The two scales a proficiency can be written on.
     *
     * These are the whole vocabulary of the competency tables'
     * value columns, and they are what makes those tables
     * findable at all: a column in which every entry is one of
     * these is a column of proficiencies, and nothing else in
     * the book looks like that.
     *
     * Which scale a value is on also settles what kind of
     * competency it belongs to. Technical competencies are rated
     * one to five, soft skills on four named rungs, so the type
     * is read from the book rather than guessed — the guess, in
     * an earlier reading, put the extractor's own word for the
     * scale into the data and left four hundred rows pointing at
     * nothing.
     */
    private const SCALES = Proficiency::SCALES;

    /**
     * How far apart two proficiencies can be and still be in
     * the same column.
     *
     * The figures are centred, so they wander by under a point
     * down a column; the columns themselves are thirty-five
     * points apart and more.
     */
    private const COLUMN_JITTER = Proficiency::JITTER;

    /**
     * The most text a page can carry and still be a divider.
     *
     * The book separates its parts with a page holding nothing
     * but a title in large type — between sub-sectors, and again
     * where the roles end and the glossaries begin. A divider
     * closes the role before it. Without that the last role in
     * the book runs on through every annex, and its recommended
     * courses come out as a single string thirty thousand
     * characters long.
     *
     * Measured: the dividers carry at most four pieces of text,
     * and the lightest page of role content carries eleven.
     */
    private const DIVIDER_AT_MOST = 6;

    /** @var array<int, string> */
    private array $problems = [];

    /**
     * @param  array<int, PageLayout>  $pages
     * @return array<string, mixed>
     */
    public function read(array $pages): array
    {
        $roles = [];

        foreach ($this->split($pages) as $group) {
            $role = $this->role($group);

            if ($role !== null) {
                $roles[] = $role;
            }
        }

        if ($roles === []) {
            throw new RuntimeException(
                'No job roles were found in that file. A reader expects the '
                .'framework\'s role pages, each opening with a Sub-Sector and '
                .'Job Level.'
            );
        }

        return [
            'job_roles' => $roles,
            'competencies' => [],
            'problems' => $this->problems,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function problems(): array
    {
        return $this->problems;
    }

    /**
     * Group the book's pages into roles.
     *
     * A role opens where the label block does. Everything up to
     * the next one belongs to it, which is what carries a bullet
     * broken over a page break into the right role — the failure
     * that once filed a marketing role's training courses under
     * a manager's duties.
     *
     * @param  array<int, PageLayout>  $pages
     * @return array<int, array<int, PageLayout>>
     */
    private function split(array $pages): array
    {
        $groups = [];
        $current = null;

        foreach ($pages as $page) {
            if ($this->opensRole($page)) {
                if ($current !== null) {
                    $groups[] = $current;
                }

                $current = [$page];

                continue;
            }

            if ($current === null) {
                continue;
            }

            if (count($page->runs()) <= self::DIVIDER_AT_MOST) {
                $groups[] = $current;
                $current = null;

                continue;
            }

            $current[] = $page;
        }

        if ($current !== null) {
            $groups[] = $current;
        }

        return $groups;
    }

    /**
     * Whether a page starts a role.
     *
     * Three things have to be true together, and each rules out
     * a page the others let through. A divider page carries a
     * sub-sector's name in large type but no labels. The page
     * explaining how the framework is put together lists every
     * label as a numbered table — "3 Sub-Sector", "6 Job Level"
     * — and would otherwise open a role whose title was the
     * whole explanation. What that page does not have, and every
     * real role page does, is a section heading under the
     * labels.
     */
    private function opensRole(PageLayout $page): bool
    {
        $found = [];

        foreach ($page->runs() as $run) {
            $key = $this->normalise($run->text);

            if (isset(self::LABELS[$key])) {
                $found[self::LABELS[$key]] = true;
            }
        }

        if (! isset($found['sub_sector'], $found['job_level'])) {
            return false;
        }

        foreach ($page->lines() as $line) {
            if ($this->sectionOf($line) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, PageLayout>  $pages  one role's pages, the first opening it
     * @return array<string, mixed>|null
     */
    private function role(array $pages): ?array
    {
        $first = $pages[0];

        $opensAt = $this->firstHeadingY($first);

        $block = $this->labelBlock($first, $opensAt);

        $title = $this->title($first, $opensAt);

        if ($title === '') {
            $this->problems[] = 'A role page carries no title, and it has been left out.';

            return null;
        }

        if (($block['sub_sector'] ?? '') === '') {
            $this->problems[] = "{$title}: no sub-sector could be read, so the role was left out.";

            return null;
        }

        $tiers = $this->tiers($block['job_level'] ?? '');

        if ($tiers === []) {
            $this->problems[] = "{$title}: no job level could be read, so the role was left out.";

            return null;
        }

        $tables = $this->tables($pages, $tiers, $title);

        $tiers = $tables['tiers'];

        $sections = $this->sections($pages, $opensAt, $tables['used']);

        $role = [
            'title' => $title,
            'sub_sector' => $block['sub_sector'],
            'tiers' => $tiers,
        ];

        $role['alternate_titles'] = $this->commaList($block['alternate_titles'] ?? '');

        foreach (['functional_group', 'job_family'] as $extra) {
            if (($block[$extra] ?? '') !== '') {
                $role[$extra] = $block[$extra];
            }
        }

        if (isset($sections['description'])) {
            $role['description'] = $this->bullets($sections['description']);
        }

        if (isset($sections['trainings'])) {
            $role['trainings'] = $this->trainings($sections['trainings'], $title);
        }

        if (isset($sections['work_functions'])) {
            $role['critical_work_functions'] = $this->workFunctions($sections['work_functions']);
        }

        if (isset($sections['entry_requirements'])) {
            $role['entry_requirements'] = $this->entryRequirements($sections['entry_requirements'], $tiers, $title);
        }

        if ($tables['rows'] !== []) {
            $role['competencies'] = $tables['rows'];
        }

        return $role;
    }

    /**
     * Read the competency tables, and say which runs they used.
     *
     * These are found by position rather than by following the
     * text, because they do not follow it. A role's soft skills
     * are set as a table whose value column sits at the far
     * right of the page, well past the column holding the entry
     * requirements and the training courses; read in reading
     * order it arrives last, and its proficiencies come out as
     * recommended courses — "Python programming language
     * Advanced Advanced".
     *
     * A value column is one whose entries are all proficiencies.
     * Nothing else in the book is a column of "4"s, or of
     * "Advanced"s, so this needs no header to find and works on
     * the pages where a table continues from the one before and
     * the header is on the previous page.
     *
     * The competency's name is the text to the left of the value
     * on the same line. Its type is whichever scale the value is
     * written on.
     *
     * @param  array<int, PageLayout>  $pages
     * @param  array<int, string>  $tiers
     * @return array{rows: array<int, array<string, mixed>>, used: array<string, true>}
     */
    private function tables(array $pages, array $tiers, string $title): array
    {
        $rows = [];
        $used = [];
        $headers = [];

        foreach ($pages as $at => $page) {
            $runs = $page->runs();

            foreach ($this->valueColumns($runs) as $found) {
                [$scale, $x, $column] = $found;

                $header = $this->headerOver($column, $runs);

                if ($header !== null) {
                    $headers[$scale][$at][(string) $x] = $this->tidy($header->text);
                    $used[$this->keyOf($header)] = true;
                }

                $pairs = [];

                foreach ($column as $value) {
                    $name = $this->toTheLeftOf($value, $runs);

                    if ($name !== null) {
                        $pairs[] = [$value, $name];
                    }
                }

                $pairs = $this->inOneColumn($pairs);

                if (count($pairs) < 2) {
                    continue;
                }

                foreach ($pairs as [$value, $name]) {

                    $rows[] = [
                        'name' => $this->tidy($name->text),
                        'type' => $scale,
                        'page' => $at,
                        'at' => $value->x,
                        'of' => $this->keyOf($name),
                        'level' => trim($value->text),
                    ];

                    $used[$this->keyOf($value)] = true;
                    $used[$this->keyOf($name)] = true;
                }
            }
        }

        if ($rows === []) {
            return ['rows' => [], 'used' => [], 'tiers' => $tiers];
        }

        $rows = $this->rank($rows);

        $wide = $this->widthOf($rows, $title);

        if ($wide === null) {
            return ['rows' => [], 'used' => $used, 'tiers' => $tiers];
        }

        /*
         * A role entered at more than one level has those levels
         * written over the table's columns — "Level 1", "Level
         * 2", "Level 3" — and that row is what the levels really
         * are. The Job Level field above says only "Entrant",
         * and a role whose table has three columns is not
         * entered at one level.
         *
         * Where the table has a single column the book prints no
         * such row. Then the Job Level field is all there is, and
         * it is one level however it is worded: "Entrant or
         * Specialist" over a single column is the name of that
         * one level, not two levels sharing a column.
         */
        $named = $this->namedTiers($headers, $wide);

        if ($named !== null) {
            $tiers = $named;
        } elseif ($wide === 1) {
            $tiers = [$this->tidy(implode(' or ', $tiers))];
        }

        return [
            'rows' => $this->byTier($rows, $tiers, $wide, $title),
            'used' => $used,
            'tiers' => $tiers,
        ];
    }

    /**
     * Number each value by which column of its table it is in.
     *
     * Position cannot say this on its own. A table runs over
     * several pages and its columns shift between them, or move
     * across the page entirely — a role's soft skills sit at the
     * far right on one page and in the middle on the next.
     *
     * What does say it is the competency the value is read
     * against. A role entered at three levels prints three
     * figures on one line, all belonging to the same competency,
     * and their order left to right is the order of the levels.
     * A role entered at one level may still print two tables
     * side by side to save space, and those are two competencies
     * on a line rather than two levels of one.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function rank(array $rows): array
    {
        $lines = [];

        foreach ($rows as $index => $row) {
            $lines[$row['type'].'|'.$row['page'].'|'.$row['of']][$index] = $row['at'];
        }

        foreach ($lines as $line) {
            asort($line);

            $rank = 0;

            foreach (array_keys($line) as $index) {
                $rows[$index]['rank'] = $rank++;
            }
        }

        return $rows;
    }

    /**
     * How many levels the tables are written for: how many
     * proficiencies one competency carries on one line.
     *
     * Taken as the commonest count rather than the largest, so
     * that one misread line cannot widen the whole table.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function widthOf(array $rows, string $title): ?int
    {
        $lines = [];

        foreach ($rows as $row) {
            $key = $row['type'].'|'.$row['page'].'|'.$row['of'];
            $lines[$key] = ($lines[$key] ?? 0) + 1;
        }

        $counts = array_count_values($lines);
        arsort($counts);

        $found = (int) array_key_first($counts);

        if ($found < 1) {
            $this->problems[] = "{$title}: no proficiency columns could be read. "
                .'Its competencies were left out.';

            return null;
        }

        return $found;
    }

    /**
     * The levels as the table names them, if it names them.
     *
     * @param  array<string, array<int, array<string, string>>>  $headers
     * @return array<int, string>|null
     */
    private function namedTiers(array $headers, int $wide): ?array
    {
        if ($wide < 2) {
            return null;
        }

        foreach ($headers as $pages) {
            foreach ($pages as $row) {
                if (count($row) !== $wide) {
                    continue;
                }

                uksort($row, fn (string $a, string $b) => (float) $a <=> (float) $b);

                return array_values($row);
            }
        }

        return null;
    }

    /**
     * The level written over a column of proficiencies, if there
     * is one.
     *
     * It sits just above the column's first value and is set
     * slightly to its left, the values being centred and the
     * heading not. It is never itself a proficiency, which is
     * what tells it from the column it heads.
     *
     * @param  array<int, TextRun>  $column
     * @param  array<int, TextRun>  $runs
     */
    private function headerOver(array $column, array $runs): ?TextRun
    {
        $top = null;

        foreach ($column as $run) {
            if ($top === null || $run->y < $top->y) {
                $top = $run;
            }
        }

        if ($top === null) {
            return null;
        }

        $best = null;

        foreach ($runs as $run) {
            if ($run->y >= $top->y - 3.0 || $run->y < $top->y - 25.0) {
                continue;
            }

            if (abs($run->x - $top->x) > 25.0 || mb_strlen($run->text) > 30) {
                continue;
            }

            if ($this->isProficiency($run)) {
                continue;
            }

            if ($best === null || abs($run->x - $top->x) < abs($best->x - $top->x)) {
                $best = $run;
            }
        }

        return $best;
    }

    /**
     * The columns of proficiencies on one page.
     *
     * Found from the values themselves rather than by grouping
     * runs that start at the same point: the book's figures are
     * centred in their column and land a fraction of a point
     * apart down the page — 438.2 on one row, 439.0 on the next
     * — so counting exact positions splits one column into
     * several and the role is refused for having more columns
     * than it has levels.
     *
     * A lone figure somewhere in the prose is not a column, so
     * two are required before it counts as one.
     *
     * @param  array<int, TextRun>  $runs
     * @return array<int, array{0: string, 1: float, 2: array<int, TextRun>}>
     */
    private function valueColumns(array $runs): array
    {
        $columns = [];

        foreach (self::SCALES as $scale => $values) {
            $found = array_values(array_filter(
                $runs,
                fn (TextRun $run) => in_array(trim($run->text), $values, true)
            ));

            usort($found, fn (TextRun $a, TextRun $b) => $a->x <=> $b->x);

            $group = [];

            foreach ($found as $run) {
                if ($group !== [] && $run->x - end($group)->x > self::COLUMN_JITTER) {
                    $columns[] = [$scale, $group[0]->x, $group];
                    $group = [];
                }

                $group[] = $run;
            }

            if ($group !== []) {
                $columns[] = [$scale, $group[0]->x, $group];
            }
        }

        return array_values(array_filter(
            $columns,
            fn (array $column) => count($column[2]) >= 2
        ));
    }

    /**
     * The nearest text to the left of a value, on its line.
     *
     * @param  array<int, TextRun>  $runs
     */
    private function toTheLeftOf(TextRun $value, array $runs): ?TextRun
    {
        $best = null;

        foreach ($runs as $run) {
            if ($run->x >= $value->x || ! $run->sharesLineWith($value)) {
                continue;
            }

            /*
             * Never another proficiency. Where a role is entered
             * at three levels the line reads "Application
             * Development 1 2 2", and the nearest thing to the
             * left of the second figure is the first — which
             * would file three competencies named "1", "2" and
             * "2", each at one level, instead of one competency
             * at three.
             */
            if ($this->isProficiency($run)) {
                continue;
            }

            if ($best === null || $run->x > $best->x) {
                $best = $run;
            }
        }

        return $best;
    }

    /**
     * Turn the paired-up values into one row per competency,
     * with a proficiency for each of the role's levels.
     *
     * A table has one value column per level, in the order the
     * levels are listed. When the counts disagree the table has
     * not been read the way it is printed, and the role's
     * competencies are left out rather than attributed to the
     * wrong level — a proficiency filed against the wrong level
     * is what a student is shown as the gap between them and the
     * job.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $tiers
     * @return array<int, array<string, mixed>>
     */
    private function byTier(array $rows, array $tiers, int $wide, string $title): array
    {
        if (count($tiers) !== $wide) {
            $this->problems[] = "{$title}: the competency tables have {$wide} proficiency column"
                .($wide === 1 ? '' : 's').' but the role is entered at '
                .count($tiers).' level'.(count($tiers) === 1 ? '' : 's')
                .'. Its competencies were left out.';

            return [];
        }

        $competencies = [];

        foreach ($rows as $row) {
            $tier = $tiers[$row['rank']] ?? null;

            if ($tier === null) {
                continue;
            }

            $key = $row['name'].'|'.$row['type'];

            $competencies[$key] ??= [
                'name' => $row['name'],
                'type' => $row['type'],
                'levels' => [],
            ];

            /*
             * Both scales are stored as a number: the technical
             * one is already written as a figure, and a soft
             * skill's rung is its place on its own four-step
             * scale. They are not the same scale and must not be
             * compared as though they were — Advanced is three of
             * four, not three of five.
             */
            $competencies[$key]['levels'][$tier] = $row['type'] === 'technical'
                ? (int) $row['level']
                : (int) array_search($row['level'], self::SCALES['soft_skill'], true) + 1;
        }

        return array_values($competencies);
    }

    /**
     * Keep only the values read against the column of names.
     *
     * A page of duties contains figures, and two of them down
     * the page look exactly like a column of proficiencies. A
     * table's competency names line up under one another; a
     * sentence that happens to contain a figure lines up with
     * nothing.
     *
     * The same test fixes a subtler mistake. Where the book
     * leaves a row of the table blank, the nearest text to the
     * left of the figure is not its competency but whatever is
     * printed in the column beyond — a line of somebody's
     * duties, filed as a competency called "Assist in creating
     * and implementing a".
     *
     * @param  array<int, array{0: TextRun, 1: TextRun}>  $pairs
     * @return array<int, array{0: TextRun, 1: TextRun}>
     */
    private function inOneColumn(array $pairs): array
    {
        if (count($pairs) < 2) {
            return [];
        }

        $buckets = array_count_values(array_map(
            fn (array $pair) => (string) round($pair[1]->x / self::COLUMN_JITTER),
            $pairs
        ));

        arsort($buckets);

        $column = (string) array_key_first($buckets);

        return array_values(array_filter(
            $pairs,
            fn (array $pair) => (string) round($pair[1]->x / self::COLUMN_JITTER) === $column
        ));
    }

    private function isProficiency(TextRun $run): bool
    {
        foreach (self::SCALES as $values) {
            if (in_array(trim($run->text), $values, true)) {
                return true;
            }
        }

        return false;
    }

    private function keyOf(TextRun $run): string
    {
        return round($run->x, 1).':'.round($run->y, 1);
    }

    /**
     * Whether a line was consumed by a competency table.
     *
     * @param  array<string, true>  $used
     */
    private function taken(PageLine $line, array $used): bool
    {
        if ($used === []) {
            return false;
        }

        foreach ($line->runs as $run) {
            if (! isset($used[$this->keyOf($run)])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Where the body of a role's first page begins.
     *
     * Above it sit the title and the label block, which are read
     * by position; below it the sections, which are read in
     * reading order. Mixing the two puts the label block's
     * values into the job description.
     */
    private function firstHeadingY(PageLayout $page): float
    {
        foreach ($page->lines() as $line) {
            if ($this->sectionOf($line) !== null) {
                return $line->y;
            }
        }

        return INF;
    }

    /**
     * The role's title: the text at the top of the leftmost
     * column, which the book sets over as many lines as it needs.
     */
    private function title(PageLayout $page, float $opensAt): string
    {
        $columns = $page->columns();

        if ($columns === []) {
            return '';
        }

        $title = null;

        foreach ($columns[0] as $line) {
            if ($line->y >= $opensAt) {
                break;
            }

            $title = $title === null ? $line->text : $this->join($title, $line->text);
        }

        return $this->tidy($title ?? '');
    }

    /**
     * Read the label-and-value block at the top of a role.
     *
     * A value is set to the right of its label and centred
     * against it, so a value running to several lines starts
     * above its own label and can finish below the next one.
     * Data Scientist's alternate titles run to four lines and
     * end past the halfway point to Sub-Sector, so splitting the
     * difference between labels puts "Director of Global
     * Intelligence" into the sub-sector.
     *
     * What holds everywhere is narrower and firmer: a value of
     * one line sits on exactly its label's line. Those pin the
     * column down, and the blocks between them fall to the
     * labels in order.
     *
     * @return array<string, string>
     */
    private function labelBlock(PageLayout $page, float $opensAt): array
    {
        $labels = [];
        $rest = [];

        foreach ($page->runs() as $run) {
            if ($run->y >= $opensAt) {
                continue;
            }

            $key = $this->normalise($run->text);

            if (isset(self::LABELS[$key])) {
                $labels[] = ['field' => self::LABELS[$key], 'x' => $run->x, 'y' => $run->y];

                continue;
            }

            $rest[] = $run;
        }

        if ($labels === []) {
            return [];
        }

        usort($labels, fn (array $a, array $b) => $a['y'] <=> $b['y']);

        $leftmost = min(array_map(fn (array $label) => $label['x'], $labels));

        $values = array_values(array_filter(
            $rest,
            fn (TextRun $run) => $run->x > $leftmost + self::INDENT_STEP
        ));

        usort($values, fn (TextRun $a, TextRun $b) => [$a->y, $a->x] <=> [$b->y, $b->x]);

        /*
         * Each label's block ends where the next label's own line
         * begins — when that next label has a value on its line to
         * mark it. Where it has none, the halfway point is all
         * there is to go on, and a value of one line never
         * reaches it.
         */
        $block = [];
        $taken = 0;

        /*
         * The block is set on a regular grid, so the last label's
         * row is as tall as the others. Without that bound it runs
         * to the foot of the page: Data Architect's Job Family is
         * printed with no value at all, and the row below it
         * swallowed the first lines of two body columns, which
         * begin higher up the page than the Job Description
         * heading does.
         */
        $rows = [];

        for ($index = 1; $index < count($labels); $index++) {
            $rows[] = $labels[$index]['y'] - $labels[$index - 1]['y'];
        }

        $rowHeight = $rows === [] ? 20.0 : array_sum($rows) / count($rows);

        foreach ($labels as $index => $label) {
            $next = $labels[$index + 1] ?? null;

            $ends = $label['y'] + $rowHeight;

            if ($next !== null) {
                $pinned = null;

                foreach ($values as $run) {
                    if (abs($run->y - $next['y']) <= 2.0) {
                        $pinned = $run->y;

                        break;
                    }
                }

                $ends = $pinned ?? ($label['y'] + $next['y']) / 2;
            }

            $mine = [];

            while ($taken < count($values) && $values[$taken]->y < $ends) {
                $mine[] = $values[$taken]->text;
                $taken++;
            }

            $block[$label['field']] = $this->tidy(implode(' ', $mine));
        }

        return $block;
    }

    /**
     * Sort a role's lines into its sections.
     *
     * Reading order is each column in full, then the next: the
     * book flows its three columns that way, so a section
     * starting halfway down the first column runs to the foot of
     * it and picks up at the head of the second. A heading
     * claims everything until the next heading.
     *
     * Runs already taken by a competency table are skipped: a
     * table is laid out across the page rather than down it, so
     * left in the flow its values arrive in whatever section
     * happens to be open.
     *
     * @param  array<int, PageLayout>  $pages
     * @param  array<string, true>  $used
     * @return array<string, array<int, PageLine>>
     */
    private function sections(array $pages, float $opensAt, array $used = []): array
    {
        $sections = [];
        $open = null;

        foreach ($pages as $index => $page) {
            foreach ($page->columns() as $column) {
                $step = $this->textIndent($column);

                foreach ($column as $line) {
                    if ($index === 0 && $line->y < $opensAt) {
                        continue;
                    }

                    if ($this->taken($line, $used)) {
                        continue;
                    }

                    $heading = $this->sectionOf($line);

                    if ($heading !== null) {
                        $open = $heading;
                        $sections[$open] ??= [];

                        continue;
                    }

                    if ($open === null) {
                        continue;
                    }

                    $sections[$open][] = $this->placed($line, $step);
                }
            }
        }

        return $sections;
    }

    /**
     * Where a column's bullet text begins.
     *
     * Taken from the bullet glyph rather than from the leftmost
     * line, so that a column holding nothing but bullets is not
     * read as a column of headings.
     *
     * @param  array<int, PageLine>  $column
     */
    private function textIndent(array $column): ?float
    {
        foreach ($column as $line) {
            if ($line->opensBullet) {
                return $line->indent;
            }
        }

        return null;
    }

    /**
     * Mark a line as a heading or as text, by where it starts.
     *
     * With no bullet anywhere in the column there is nothing to
     * measure against, so everything is text — a column of
     * wrapped prose, which is what those columns hold.
     */
    private function placed(PageLine $line, ?float $step): PageLine
    {
        if ($step === null) {
            return $line;
        }

        $isHeading = ! $line->opensBullet && $line->indent < $step - self::INDENT_STEP;

        return new PageLine(
            text: $line->text,
            indent: $isHeading ? -1.0 : $line->indent,
            y: $line->y,
            opensBullet: $line->opensBullet,
            runs: $line->runs,
        );
    }

    /**
     * Fold a run of lines into bullets.
     *
     * A line opening a bullet starts one; every line after it
     * that is not a heading and does not open its own belongs to
     * it. Indentation decides, never wording — deciding it by
     * whether a line looked finished is what once stored
     * thirty-four wrapped course names as courses of their own.
     *
     * @param  array<int, PageLine>  $lines
     * @return array<int, string>
     */
    private function bullets(array $lines): array
    {
        $bullets = [];
        $current = null;

        foreach ($lines as $line) {
            if ($line->indent < 0) {
                continue;
            }

            if ($line->opensBullet) {
                if ($current !== null) {
                    $bullets[] = $current;
                }

                $current = $line->text;

                continue;
            }

            if ($current === null) {
                continue;
            }

            $current = $this->join($current, $line->text);
        }

        if ($current !== null) {
            $bullets[] = $current;
        }

        return array_values(array_filter(
            array_map(fn (string $text) => $this->tidy($text), $bullets)
        ));
    }

    /**
     * The duty groups, each with the duties under it.
     *
     * @param  array<int, PageLine>  $lines
     * @return array<int, array<string, mixed>>
     */
    private function workFunctions(array $lines): array
    {
        $groups = [];
        $group = null;
        $pending = [];

        $flush = function () use (&$groups, &$group, &$pending) {
            if ($group === null) {
                return;
            }

            $groups[] = ['name' => $group, 'duties' => $this->bullets($pending)];
            $pending = [];
        };

        foreach ($lines as $line) {
            if ($line->indent < 0) {
                /*
                 * A group heading can itself wrap: "Infrastructure
                 * Governance and Compliance" then "Management" on
                 * the line under it. Two headings in a row with no
                 * bullet between them are one heading.
                 */
                if ($group !== null && $pending === []) {
                    $group = $this->join($group, $line->text);

                    continue;
                }

                $flush();
                $group = $this->tidy($line->text);

                continue;
            }

            $pending[] = $line;
        }

        $flush();

        return $groups;
    }

    /**
     * The recommended courses.
     *
     * Checked for length because the column this reads from once
     * held whole sentences of duties, and a course name of three
     * hundred characters is the shape that mistake takes. It is
     * refused rather than stored.
     *
     * @param  array<int, PageLine>  $lines
     * @return array<int, string>
     */
    private function trainings(array $lines, string $title): array
    {
        $courses = [];

        foreach ($this->bullets($lines) as $course) {
            if (mb_strlen($course) > 255) {
                $this->problems[] = "{$title}: a recommended course ran to "
                    .mb_strlen($course).' characters, which is a sentence rather '
                    .'than a course name. It was left out.';

                continue;
            }

            $courses[] = $course;
        }

        return $courses;
    }

    /**
     * The ways in to a role, one per alternative.
     *
     * The book writes them as prose ending in "OR", which is the
     * only reliable divider: the qualification, the field and
     * the years are phrased differently from role to role.
     *
     * @param  array<int, PageLine>  $lines
     * @param  array<int, string>  $tiers
     * @return array<string, array<int, array<string, string>>>
     */
    private function entryRequirements(array $lines, array $tiers, string $title): array
    {
        /*
         * Every line, flush left or not. The ways in are set as
         * prose rather than as bullets, and they share a column
         * with the training courses, which are bulleted. Measured
         * against those bullets the prose sits where a heading
         * would, and dropping it on that basis leaves the role
         * with no way in at all.
         */
        $open = null;
        $prose = [];
        $shared = [];

        foreach ($lines as $line) {
            /*
             * A line naming one of the role's levels opens that
             * level's block. Some roles are entered the same way
             * at every level and the book says so once; others
             * print each level's requirements under its own
             * heading, and giving every level all of them — as
             * this used to — tells a student at Specialist that
             * they need the three years Expert asks for.
             *
             * The book writes the heading as the level alone
             * ("Specialist") or as the role and the level
             * together ("IT Technician Level 1").
             */
            $named = $this->tierNamed($line->text, $tiers, $title);

            if ($named !== null) {
                $open = $named;
                $prose[$open] ??= [];

                continue;
            }

            if ($open === null) {
                $shared[] = $line->text;

                continue;
            }

            $prose[$open][] = $line->text;
        }

        if ($prose === []) {
            /*
             * Nothing named a level, so the one statement is the
             * way in at every level.
             */
            $ways = $this->waysIn(implode(' ', $shared));

            return $ways === [] ? [] : array_fill_keys($tiers, $ways);
        }

        $requirements = [];

        foreach ($tiers as $tier) {
            /*
             * Anything printed before the first heading applies
             * to all of them — it is said once, above the lot.
             */
            $ways = $this->waysIn(implode(' ', array_merge($shared, $prose[$tier] ?? [])));

            if ($ways !== []) {
                $requirements[$tier] = $ways;
            }
        }

        return $requirements;
    }

    /**
     * Which of the role's levels a line names, if it names one.
     */
    private function tierNamed(string $text, array $tiers, string $title): ?string
    {
        $line = $this->normalise($text);

        foreach ($tiers as $tier) {
            $named = $this->normalise($tier);

            if ($line === $named || $line === $this->normalise($title).$named) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * The separate ways in that one statement describes.
     *
     * Always a capital OR. The book uses it as the divider
     * between alternatives throughout, and lower-case "or"
     * appears inside them constantly — "Computer Science or any
     * related field" is one way in, not two.
     *
     * @return array<int, array<string, string>>
     */
    private function waysIn(string $whole): array
    {
        $whole = $this->tidy($whole);

        if ($whole === '') {
            return [];
        }

        $ways = [];

        foreach (preg_split('/\s+OR\s+/u', $whole) ?: [$whole] as $alternative) {
            $alternative = $this->tidy(rtrim(trim($alternative), ','));

            if ($alternative !== '') {
                $ways[] = ['raw_text' => $alternative];
            }
        }

        return $ways;
    }

    /**
     * The levels a role is entered at.
     *
     * The book writes several levels as "Entrant or Expert/
     * Management", and the slash is part of a level's name
     * rather than a separator between two — "Expert/Management"
     * and "Specialist/Manager" are each one level the framework
     * already knows by that name. Splitting on it invents levels
     * that do not exist and loses the ones that do.
     *
     * @return array<int, string>
     */
    private function tiers(string $level): array
    {
        $tiers = [];

        foreach (preg_split('/\s*,\s*|\s+or\s+/ui', $level) ?: [] as $one) {
            $one = $this->tidy($one);

            if ($one !== '') {
                $tiers[] = $one;
            }
        }

        return $tiers;
    }

    /**
     * @return array<int, string>
     */
    private function commaList(string $text): array
    {
        $items = [];

        foreach (explode(',', $text) as $item) {
            $item = $this->tidy($item);

            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function sectionOf(PageLine $line): ?string
    {
        return self::SECTIONS[$this->normalise($line->text)] ?? null;
    }

    /**
     * A phrase with its spacing and case taken out, for matching.
     */
    private function normalise(string $text): string
    {
        return strtolower(preg_replace('/\s+/u', '', trim($text)) ?? '');
    }

    /**
     * Join a line to the one it continues.
     *
     * A line ending in a hyphen or a slash is broken at that
     * mark, and the mark belongs to the text: the book breaks
     * only at hyphens that are already in the word. All
     * thirty-one of them are compounds — "cost-efficiency",
     * "day-to-day", "proof-of-concept", "non-compliance" — and
     * not one is a word split across lines for justification. So
     * the hyphen stays and the space does not: dropping the
     * hyphen gives "mediumrange", and keeping the space gives
     * "medium- range".
     */
    private function join(string $text, string $next): string
    {
        $text = rtrim($text);

        if (str_ends_with($text, '-') || str_ends_with($text, '/')) {
            return $text.ltrim($next);
        }

        return $text.' '.ltrim($next);
    }

    private function tidy(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }
}
