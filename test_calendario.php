<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate logged-in user with session
session(['congregacion_activa_id' => 2]);
$user = App\Models\User::first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/ppoc');
$response = $kernel->handle($request);
echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
$content = $response->getContent();
echo 'Content length: ' . strlen($content) . PHP_EOL;

if (strpos($content, 'mobile-agenda') !== false) echo 'FOUND: mobile-agenda section' . PHP_EOL;
if (strpos($content, 'mini-cal-strip') !== false) echo 'FOUND: mini-cal-strip' . PHP_EOL;
if (strpos($content, 'agenda-day-card') !== false) echo 'FOUND: agenda-day-card' . PHP_EOL;
if (strpos($content, 'calendar-grid') !== false) echo 'FOUND: calendar-grid (desktop)' . PHP_EOL;
if (strpos($content, 'Exception') !== false) echo 'WARNING: Exception found in output!' . PHP_EOL;
if (strpos($content, 'Error') !== false && strpos($content, 'ErrorException') === false) echo 'WARNING: Error in output' . PHP_EOL;

// Count agenda day cards
preg_match_all('/agenda-day-card/', $content, $matches);
echo 'Agenda day cards: ' . count($matches[0]) . PHP_EOL;
