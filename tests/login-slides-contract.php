<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
declare(strict_types=1);

require dirname(__DIR__) . '/Domain/LoginSlides.php';

use Modules\Theme\Domain\LoginSlides;

function check(bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
}
function rejects(callable $test, string $message): void
{
    try {
        $test();
    } catch (InvalidArgumentException) {
        return;
    }
    throw new RuntimeException($message);
}

$existing = [
    ['title' => 'Finance', 'caption' => 'First', 'image' => 'already-saved/one.png'],
    ['title' => 'Bank', 'caption' => 'Second', 'image' => 'already-saved/two.png'],
    ['title' => 'Cooperation', 'caption' => 'Third', 'image' => 'already-saved/three.png'],
];

check(LoginSlides::reconcile($existing, [], [], [], []) === $existing,
    'Partial form must preserve all slides without an explicit deletion.');
$edited = LoginSlides::reconcile($existing, [1 => 'Bank Updated'], [1 => 'Audit trail'], [], []);
check(count($edited) === 3 && $edited[1]['title'] === 'Bank Updated'
    && $edited[1]['image'] === 'already-saved/two.png', 'Editing a title must preserve the image.');

$deleted = LoginSlides::reconcile($existing, [], [], [1 => '1'], []);
check(count($deleted) === 2 && $deleted[1]['image'] === 'already-saved/three.png',
    'Deleting the middle slide must leave and reindex the others.');

$added = LoginSlides::reconcile($existing, [3 => 'New insight'], [3 => 'New text'], [], [3 => new stdClass()]);
check(count($added) === 4 && $added[3]['image'] === ''
    && $added[3]['upload_index'] === 3, 'A new slide requires a validated upload.');
$replaced = LoginSlides::reconcile($existing, [], [], [], [0 => new stdClass()]);
check($replaced[0]['image'] === 'already-saved/one.png'
    && $replaced[0]['upload_index'] === 0, 'An upload must replace only its own image.');

$removedNew = LoginSlides::reconcile($existing, [3 => 'New'], [], [3 => '1'], [3 => new stdClass()]);
check($removedNew === $existing, 'Removing a new slide must not affect saved slides.');

check(LoginSlides::reconcile($existing, [], [], [0 => 1, 1 => 1, 2 => 1], []) === [],
    'Deleting every slide should produce an empty carousel.');

$tooMany = $existing;
for ($i = 3; $i < 7; $i++) {
    $tooMany[] = ['title' => 'Additional', 'caption' => '', 'image' => 'file.png'];
}
rejects(static fn() => LoginSlides::reconcile($tooMany, [], [], [], []),
    'More than six slides was accepted.');
rejects(static fn() => LoginSlides::reconcile($existing, [3 => 'No image'], [], [], []),
    'New slide missing a file was accepted.');
rejects(static fn() => LoginSlides::reconcile($existing, [0 => ''], [], [], []),
    'Clearing a saved title silently erased a slide.');
rejects(static fn() => LoginSlides::reconcile($existing, ['outside' => 'Unexpected'], [], [], []),
    'An arbitrary index was accepted.');
rejects(static fn() => LoginSlides::reconcile($existing, [100 => 'Excessive'], [], [], []),
    'Oversized index was accepted.');

echo "PASS: global login slides retain, update, add, delete, reindex and reject invalid inputs\n";
