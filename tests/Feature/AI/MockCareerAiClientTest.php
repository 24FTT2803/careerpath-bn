<?php

use App\Services\AI\MockCareerAiClient;

test('mock career AI returns software development for programming profile', function () {
    $client = new MockCareerAiClient();

    $response = $client->recommend([
        'student_profile' => [
            'programme' => 'Application Development',
            'competencies' => ['Java'],
            'interests' => ['Programming'],
        ],
    ]);

    expect($response['status'])
        ->toBe('completed')
        ->and($response['recommendations'][0]['biicf_career_id'])
        ->toBe(1);
});

test('mock career AI returns data analyst for data profile', function () {
    $client = new MockCareerAiClient();

    $response = $client->recommend([
        'student_profile' => [
            'competencies' => ['SQL', 'Python'],
            'interests' => [
                'Data Analysis',
                'Analytical Thinking',
            ],
        ],
    ]);

    expect(
        $response['recommendations'][0]['biicf_career_id']
    )->toBe(3);
});

test('mock career AI returns network engineer for networking profile', function () {
    $client = new MockCareerAiClient();

    $response = $client->recommend([
        'student_profile' => [
            'competencies' => ['Linux'],
            'interests' => ['Networking'],
        ],
    ]);

    expect(
        $response['recommendations'][0]['biicf_career_id']
    )->toBe(2);
});

test('mock career AI returns cybersecurity specialist for cybersecurity profile', function () {
    $client = new MockCareerAiClient();

    $response = $client->recommend([
        'student_profile' => [
            'competencies' => ['Linux'],
            'interests' => [
                'Cybersecurity',
                'Networking',
            ],
        ],
    ]);

    expect(
        $response['recommendations'][0]['biicf_career_id']
    )->toBe(4);
});

test('mock career AI returns cloud engineer for cloud profile', function () {
    $client = new MockCareerAiClient();

    $response = $client->recommend([
        'student_profile' => [
            'competencies' => [
                'AWS',
                'Docker',
                'Linux',
            ],
            'interests' => ['Cloud Computing'],
        ],
    ]);

    expect(
        $response['recommendations'][0]['biicf_career_id']
    )->toBe(5);
});