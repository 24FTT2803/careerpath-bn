<?php

use App\Rules\NoProfanity;
use Illuminate\Support\Facades\Validator;

/**
 * Run a single string through the NoProfanity rule and return
 * whether validation passed.
 */
function passesNoProfanity(string $value): bool
{
    return Validator::make(
        ['field' => $value],
        ['field' => [new NoProfanity]]
    )->passes();
}

test('clean names pass the profanity rule', function () {
    expect(passesNoProfanity('Ahmad'))->toBeTrue();
    expect(passesNoProfanity('Abdullah'))->toBeTrue();
    expect(passesNoProfanity('Justine Chai'))->toBeTrue();
    expect(passesNoProfanity('Nurul Aisyah binti Hassan'))->toBeTrue();
});

test('obvious profanity is rejected', function () {
    expect(passesNoProfanity('fuck'))->toBeFalse();
    expect(passesNoProfanity('fucker'))->toBeFalse();
    expect(passesNoProfanity('bullshit'))->toBeFalse();
    expect(passesNoProfanity('asshole'))->toBeFalse();
});

test('leet speak and repeat-character evasions are caught', function () {
    expect(passesNoProfanity('f_u_c_k'))->toBeFalse();
    expect(passesNoProfanity('f.u.c.k'))->toBeFalse();
    expect(passesNoProfanity('f-u-c-k'))->toBeFalse();
    expect(passesNoProfanity('f u c k'))->toBeFalse();
    expect(passesNoProfanity('fuuuuuck'))->toBeFalse();
    expect(passesNoProfanity('fuuck'))->toBeFalse();
    expect(passesNoProfanity('fuuuucking'))->toBeFalse();
});

test('legitimate names that contain blocked substrings still pass', function () {
    expect(passesNoProfanity('Dickinson'))->toBeTrue();
    expect(passesNoProfanity('Michelle'))->toBeTrue();
    expect(passesNoProfanity('Hellenic'))->toBeTrue();
    expect(passesNoProfanity('Assam'))->toBeTrue();
    expect(passesNoProfanity('Cockerill'))->toBeTrue();
    expect(passesNoProfanity('Hassan'))->toBeTrue();
});

test('empty and whitespace-only values pass through', function () {
    expect(passesNoProfanity(''))->toBeTrue();
    expect(passesNoProfanity('   '))->toBeTrue();
    expect(passesNoProfanity("\t\n"))->toBeTrue();
});

test('academic and career vocabulary is not blocked', function () {
    /*
     * These are the false-positive cases the narrower scope
     * exists to avoid. None should trigger the filter.
     */
    expect(passesNoProfanity('offensive security'))->toBeTrue();
    expect(passesNoProfanity('penetration testing'))->toBeTrue();
    expect(passesNoProfanity('vulnerability analysis'))->toBeTrue();
    expect(passesNoProfanity('red team exercise'))->toBeTrue();
});