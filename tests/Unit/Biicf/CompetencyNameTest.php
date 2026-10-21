<?php

use App\Services\Biicf\CompetencyName;

/*
 * When two spellings are the same competency.
 *
 * The book is not consistent with itself, and the framework
 * holds names from before it was read faithfully. Read
 * literally, each difference is a competency added and another
 * removed — two hundred of them across the book, over
 * ampersands and plurals.
 */

test('an ampersand and the word are the same', function () {
    expect(CompetencyName::same('Creativity & Innovation', 'Creativity and Innovation'))->toBeTrue()
        ->and(CompetencyName::same('Media & Platform Management', 'Media and Platform Management'))->toBeTrue();
});

test('a plural and its singular are the same', function () {
    expect(CompetencyName::same('Systems Integration', 'System Integration'))->toBeTrue()
        ->and(CompetencyName::same('Applications Development', 'Application Development'))->toBeTrue()
        ->and(CompetencyName::same('Solutions Architecture', 'Solution Architecture'))->toBeTrue();
});

test('an abbreviation in brackets is a gloss, not the name', function () {
    expect(CompetencyName::same('Enterprise Architecture', 'Enterprise Architecture (EA)'))->toBeTrue()
        ->and(CompetencyName::same('Disaster Recovery Management', 'Disaster Recovery Management (DRM)'))->toBeTrue()
        ->and(CompetencyName::same('Customer Experience Management', 'Customer Experience Management (CXM)'))->toBeTrue();
});

test('capitals and spacing do not make a competency', function () {
    expect(CompetencyName::same('Decision-making', 'Decision-Making'))->toBeTrue()
        ->and(CompetencyName::same('Data Modelling and Design', 'Data Modelling And Design'))->toBeTrue();
});

test('the pairs no rule would find are written down', function () {
    expect(CompetencyName::same('Results/Achievement Orientation', 'Results Orientation'))->toBeTrue()
        ->and(CompetencyName::same('Decision-making/ Judgement', 'Decision-Making'))->toBeTrue()
        ->and(CompetencyName::same('Decision-making / Judgement', 'Decision-Making'))->toBeTrue()
        ->and(CompetencyName::same('Data Analysis', 'Data Analytics'))->toBeTrue();
});

test('names that merely look alike stay apart', function () {
    /*
     * The point of the whole thing. A framework that quietly
     * merged two competencies would tell a student the wrong
     * thing about both, so similarity is never enough — only a
     * rule or a decision written down.
     */
    expect(CompetencyName::same('Data Analytics', 'Digital Analytics'))->toBeFalse()
        ->and(CompetencyName::same('Results Orientation', 'Service Orientation'))->toBeFalse()
        ->and(CompetencyName::same('Infrastructure Strategy', 'Infrastructure Support'))->toBeFalse();
});

test('a competency the book may have added is not read as a rename', function () {
    /*
     * The framework holds "Data Governance" and the book prints
     * "Data Ethics and Governance". Close enough to look like a
     * rewording — but ethics is a subject of its own, and
     * treating it as one would hide something the book may have
     * added. It is raised for a decision instead.
     */
    expect(CompetencyName::same('Data Ethics and Governance', 'Data Governance'))->toBeFalse();
});

test('every competency in the library stays distinct from the others', function () {
    /*
     * The relaxing above is only safe while it never collapses
     * two entries the framework really does hold apart.
     */
    /*
     * Read by path rather than through database_path(): a unit
     * test here runs without the application booted, so the
     * helpers are not there to call.
     */
    $names = collect(json_decode(
        (string) file_get_contents(
            dirname(__DIR__, 3).'/database/data/biicf-competencies.json'
        ),
        true
    ))->map(fn (array $entry) => [
        'key' => CompetencyName::key($entry['name']).'|'.$entry['type'],
        'name' => $entry['name'],
    ]);

    $collisions = $names
        ->groupBy('key')
        ->filter(fn ($group) => $group->pluck('name')->unique()->count() > 1);

    expect($collisions)->toBeEmpty();
});
