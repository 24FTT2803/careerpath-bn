<?php

use App\Support\AdviserText;

test('numbered points become a list', function () {
    $html = AdviserText::toHtml(
        "1. First thing\n2. Second thing"
    );

    expect($html)
        ->toContain('<ol')
        ->toContain('<li>First thing</li>')
        ->not->toContain('1.');
});

test('bold markers become emphasis rather than asterisks', function () {
    $html = AdviserText::toHtml('You need **Analytical Thinking**.');

    /*
     * These were printed literally before, so students read
     * asterisks in the middle of sentences.
     */
    expect($html)
        ->toContain('<strong>Analytical Thinking</strong>')
        ->not->toContain('**');
});

test('a short label on its own becomes a heading', function () {
    $html = AdviserText::toHtml(
        "**Practical next steps**\n\nDo the thing."
    );

    expect($html)->toContain('adviser-heading');
});

test('markup in the reply is escaped', function () {
    $html = AdviserText::toHtml(
        'Try <script>alert(1)</script> now.'
    );

    /*
     * The text comes from a language model, so it is escaped
     * first and only a few patterns are allowed back.
     */
    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

test('empty input produces nothing', function () {
    expect(AdviserText::toHtml(null))->toBe('');
    expect(AdviserText::toHtml('   '))->toBe('');
});

test('numbered points on consecutive lines each get their own item', function () {
    /*
     * The model often writes a run of points with no blank
     * lines between them. Requiring a whole block to match
     * collapsed the lot into one paragraph.
     */
    $html = AdviserText::toHtml(
        "1. Yes, workshops help.\n"
        ."2. You have 12 gaps.\n"
        .'3. CareerPath lists no providers.'
    );

    expect(substr_count($html, '<li>'))->toBe(3);
});

test('a paragraph followed by bullets keeps them apart', function () {
    $html = AdviserText::toHtml(
        "Workshops would help.\n"
        ."- Data Analytics\n"
        .'- Database Management'
    );

    expect($html)
        ->toContain('<p>Workshops would help.</p>')
        ->toContain('<ul')
        ->and(substr_count($html, '<li>'))
        ->toBe(2);
});

test('bullets buried inside a line get their own line', function () {
    /*
     * The model writes several points on one line separated
     * only by a bullet, which used to read as one paragraph
     * with bullets stranded in the middle of it.
     */
    $html = AdviserText::toHtml(
        'Practical next steps:  • Ask your college. '
        .'• Look for workshops. • Speak to your adviser.'
    );

    expect(substr_count($html, '<li>'))->toBe(3);
});
