<?php

use App\Services\Biicf\ValueDiff;

/*
 * Marking what differs between the framework and an edition.
 *
 * Over a hundred differences, most of them nearly identical on
 * both sides — a hyphen, a full stop, one course in twenty.
 * Found by eye that is a careful minute each, which is how a
 * review stops being read and starts being clicked through.
 */

test('a difference of one word is marked on both sides', function () {
    $panes = ValueDiff::panes(
        'BDQF Level 5 in Information Systems or related field',
        'BDQF Level 5 in Information Systems or related field.'
    );

    expect($panes['current'])->toContain('<mark class="diff-gone">field</mark>')
        ->and($panes['proposed'])->toContain('<mark class="diff-came">field.</mark>')
        ->and($panes['current'])->toContain('BDQF Level 5');
});

test('a line only one side has is marked whole', function () {
    $panes = ValueDiff::panes(
        ['ITIL Foundation', 'Node.js'],
        ['ITIL Foundation', 'Node.js', 'Scrum Developer']
    );

    expect($panes['proposed'])->toContain('<mark class="diff-came">Scrum Developer</mark>')
        ->and($panes['current'])->not->toContain('<mark');
});

test('a reworded line is marked by its words, not thrown away whole', function () {
    /*
     * "Renamed" and "replaced" are different things, and only
     * the words say which.
     */
    $panes = ValueDiff::panes(
        ['Google Cloud Certified Professional Cloud Architect'],
        ['Google Cloud Certified - Professional Cloud Architect']
    );

    expect($panes['proposed'])->toContain('Google Cloud Certified')
        ->and($panes['proposed'])->toContain('<mark class="diff-came">-</mark>')
        ->and($panes['current'])->not->toContain('<mark');
});

test('two unrelated lines are a removal and an addition', function () {
    $panes = ValueDiff::panes(['Node.js'], ['Oracle Database Administrator (DBA)']);

    expect($panes['current'])->toContain('<mark class="diff-gone">Node.js</mark>')
        ->and($panes['proposed'])
        ->toContain('<mark class="diff-came">Oracle Database Administrator (DBA)</mark>');
});

test('values that match carry no marks at all', function () {
    $panes = ValueDiff::panes(['ITIL Foundation'], ['ITIL Foundation']);

    expect($panes['current'])->not->toContain('<mark')
        ->and($panes['proposed'])->not->toContain('<mark');
});

test('a proficiency changing is marked on both sides', function () {
    $panes = ValueDiff::panes(3, 4);

    expect($panes['current'])->toBe('<mark class="diff-gone">3</mark>')
        ->and($panes['proposed'])->toBe('<mark class="diff-came">4</mark>');
});

test('a missing value reads as nothing rather than as empty marks', function () {
    $panes = ValueDiff::panes(null, ['Something new']);

    expect($panes['current'])->toBe('—')
        ->and($panes['proposed'])->toContain('Something new');
});

test('what it marks is escaped, because it is written into the page as HTML', function () {
    /*
     * The panes are printed unescaped so the marks survive, so
     * everything inside them has to arrive already safe.
     */
    $panes = ValueDiff::panes(
        ['<script>alert(1)</script>'],
        ['<b>bold</b>']
    );

    expect($panes['current'])->toContain('&lt;script&gt;')
        ->and($panes['current'])->not->toContain('<script>')
        ->and($panes['proposed'])->toContain('&lt;b&gt;')
        ->and($panes['proposed'])->not->toContain('<b>');
});
