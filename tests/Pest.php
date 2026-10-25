<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Saying what a page is missing, without printing the page
|--------------------------------------------------------------------------
|
| assertSee and assertDontSee hand the whole response to PHPUnit as the
| value being compared, so one failed assertion against a page like the
| profile form prints eight thousand lines of markup and CSS — and the
| needle, the only part anybody reads, ends up at the bottom.
|
| assertShows and assertHides check the same thing and fail with one line.
| They also say why, because the reason an assertion like this fails on
| markup that looks right is nearly always one of three things: the page
| rendered something else entirely, the text is there but broken across
| lines by the template's indentation, or the casing differs.
|
*/

if (! function_exists('cpbn_page_summary')) {
    /**
     * Which page this was, in a few words.
     */
    function cpbn_page_summary(TestResponse $response): string
    {
        $html = (string) $response->getContent();

        preg_match('/<title>(.*?)<\/title>/is', $html, $title);

        return sprintf(
            '%d response, %s characters, title "%s"',
            $response->getStatusCode(),
            number_format(mb_strlen($html)),
            trim(preg_replace('/\s+/', ' ', $title[1] ?? '(none)'))
        );
    }
}

if (! function_exists('cpbn_near_miss')) {
    /**
     * Why a needle that ought to be there is not.
     */
    function cpbn_near_miss(string $html, string $needle): string
    {
        $flat = preg_replace('/\s+/', ' ', $html);
        $flatNeedle = preg_replace('/\s+/', ' ', $needle);

        if (str_contains($flat, $flatNeedle)) {
            return ' It is on the page but broken across lines, so only the '
                .'whitespace differs.';
        }

        if (stripos($html, $needle) !== false) {
            return ' It is on the page in different casing.';
        }

        /*
         * The longest leading run of the needle that does appear,
         * which is usually where the wording was changed.
         */
        for ($length = mb_strlen($needle) - 1; $length >= 8; $length--) {
            $start = mb_substr($needle, 0, $length);

            if (str_contains($html, $start)) {
                return sprintf(' The page has "%s" but nothing after it.', $start);
            }
        }

        return ' No part of it appears on the page.';
    }
}

TestResponse::macro('assertShows', function (string ...$needles) {
    $html = (string) $this->getContent();

    foreach ($needles as $needle) {
        if (! str_contains($html, $needle)) {
            Assert::fail(sprintf(
                'The page does not show "%s".%s (%s)',
                $needle,
                cpbn_near_miss($html, $needle),
                cpbn_page_summary($this)
            ));
        }
    }

    Assert::assertTrue(true);

    return $this;
});

TestResponse::macro('assertHides', function (string ...$needles) {
    $html = (string) $this->getContent();

    foreach ($needles as $needle) {
        if (str_contains($html, $needle)) {
            $at = mb_strpos($html, $needle);

            Assert::fail(sprintf(
                'The page shows "%s", and should not. Around it: "...%s..." (%s)',
                $needle,
                trim(preg_replace(
                    '/\s+/',
                    ' ',
                    mb_substr($html, max(0, $at - 70), mb_strlen($needle) + 140)
                )),
                cpbn_page_summary($this)
            ));
        }
    }

    Assert::assertTrue(true);

    return $this;
});
