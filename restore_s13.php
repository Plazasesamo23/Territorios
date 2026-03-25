<?php

/**
 * S-13 Territory Assignment Restore Script
 *
 * Reads S-13 data from restore_s13_data.json and creates missing
 * registros in the Laravel database.
 *
 * Usage: php restore_s13.php [--dry-run]
 *
 * --dry-run: Show what would be created without actually creating records
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Parse arguments
$dryRun = in_array('--dry-run', $argv ?? []);

if ($dryRun) {
    echo "=== DRY RUN MODE - No changes will be made ===\n\n";
}

$congregacionId = 1;

// Load S-13 data
$jsonPath = __DIR__ . '/restore_s13_data.json';
if (!file_exists($jsonPath)) {
    die("ERROR: restore_s13_data.json not found at $jsonPath\n");
}

$s13Data = json_decode(file_get_contents($jsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERROR: Invalid JSON in restore_s13_data.json: " . json_last_error_msg() . "\n");
}

echo "Loaded " . count($s13Data) . " territories from S-13 data.\n\n";

// Load all publicadores for name matching
$publicadores = DB::table('publicadores')
    ->where('congregacion_id', $congregacionId)
    ->get();

echo "Found " . $publicadores->count() . " publicadores in congregation $congregacionId.\n\n";

// Build name lookup index (normalized name => publicador)
$nameIndex = [];
foreach ($publicadores as $pub) {
    $fullName = trim($pub->nombre . ' ' . $pub->apellidos);
    $normalized = normalizeNameForMatch($fullName);
    $nameIndex[$normalized] = $pub;

    // Also index by first name only for single-name matches (e.g., "Jhonaiker")
    $firstNormalized = normalizeNameForMatch($pub->nombre);
    if (!isset($nameIndex[$firstNormalized])) {
        $nameIndex[$firstNormalized] = $pub;
    }
}

// Load all territorios
$territorios = DB::table('territorios')
    ->where('congregacion_id', $congregacionId)
    ->get()
    ->keyBy('numero');

echo "Found " . $territorios->count() . " territorios in congregation $congregacionId.\n\n";

// Load existing registros for duplicate detection
$existingRegistros = DB::table('registros')
    ->get();

echo "Found " . $existingRegistros->count() . " existing registros.\n\n";

// Build existing registros lookup: "territorio_id|publicador_id|fecha_salida"
$existingLookup = [];
foreach ($existingRegistros as $reg) {
    $key = $reg->territorio_id . '|' . $reg->publicador_id . '|' . $reg->fecha_salida;
    $existingLookup[$key] = true;
}

// Process S-13 data
$created = 0;
$skippedExisting = 0;
$skippedNoTerritorio = 0;
$skippedNoPublicador = 0;
$errors = [];
$unmatchedNames = [];

foreach ($s13Data as $entry) {
    $territorioNum = $entry['territorio'];

    if (!isset($territorios[$territorioNum])) {
        $skippedNoTerritorio++;
        $errors[] = "Territory #$territorioNum not found in database";
        continue;
    }

    $territorio = $territorios[$territorioNum];

    foreach ($entry['registros'] as $registro) {
        $pubName = $registro['publicador'];
        $fechaSalida = $registro['fecha_salida'];
        $fechaEntrada = $registro['fecha_entrada'];

        // Find publicador by name
        $publicador = findPublicador($pubName, $nameIndex, $publicadores);

        if (!$publicador) {
            $skippedNoPublicador++;
            $unmatchedNames[$pubName] = ($unmatchedNames[$pubName] ?? 0) + 1;
            continue;
        }

        // Check if this registro already exists
        $key = $territorio->id . '|' . $publicador->id . '|' . $fechaSalida;
        if (isset($existingLookup[$key])) {
            $skippedExisting++;
            continue;
        }

        // Create the registro
        if (!$dryRun) {
            $now = now();
            DB::table('registros')->insert([
                'publicador_id' => $publicador->id,
                'territorio_id' => $territorio->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => $fechaEntrada,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $created++;
        $pubFullName = trim($publicador->nombre . ' ' . $publicador->apellidos);
        echo ($dryRun ? "[DRY] " : "") . "CREATED: T$territorioNum - $pubFullName - salida:$fechaSalida" .
             ($fechaEntrada ? " entrada:$fechaEntrada" : "") . "\n";

        // Add to lookup to prevent duplicates within the same run
        $existingLookup[$key] = true;
    }
}

// Summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY" . ($dryRun ? " (DRY RUN)" : "") . "\n";
echo str_repeat('=', 60) . "\n";
echo "Records " . ($dryRun ? "to create" : "created") . ": $created\n";
echo "Skipped (already exist): $skippedExisting\n";
echo "Skipped (territory not found): $skippedNoTerritorio\n";
echo "Skipped (publicador not matched): $skippedNoPublicador\n";

if (!empty($unmatchedNames)) {
    echo "\nUNMATCHED NAMES (name => occurrences):\n";
    arsort($unmatchedNames);
    foreach ($unmatchedNames as $name => $count) {
        echo "  '$name' x $count\n";
    }
}

if (!empty($errors)) {
    echo "\nERRORS:\n";
    foreach (array_unique($errors) as $error) {
        echo "  $error\n";
    }
}

echo "\nDone.\n";

// ---- Helper Functions ----

/**
 * Normalize a name for fuzzy matching
 */
function normalizeNameForMatch(string $name): string
{
    $name = mb_strtolower(trim($name));
    // Remove accents
    $name = strtr($name, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ñ' => 'n', 'ü' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
    ]);
    // Normalize whitespace
    $name = preg_replace('/\s+/', ' ', $name);
    return $name;
}

/**
 * Find a publicador by name with fuzzy matching
 */
function findPublicador(string $name, array &$nameIndex, $publicadores)
{
    // Direct normalized match
    $normalized = normalizeNameForMatch($name);
    if (isset($nameIndex[$normalized])) {
        return $nameIndex[$normalized];
    }

    // Handle "OIeg" -> "Oleg" (capital I vs lowercase L)
    $fixedName = str_replace('OIeg', 'Oleg', $name);
    if ($fixedName !== $name) {
        $normalized2 = normalizeNameForMatch($fixedName);
        if (isset($nameIndex[$normalized2])) {
            $nameIndex[$normalized] = $nameIndex[$normalized2]; // Cache for future lookups
            return $nameIndex[$normalized2];
        }
    }

    // Try case-insensitive partial matching with Levenshtein distance
    $bestMatch = null;
    $bestDistance = PHP_INT_MAX;

    foreach ($nameIndex as $indexedName => $pub) {
        $distance = levenshtein($normalized, $indexedName);
        if ($distance < $bestDistance && $distance <= 3) { // Allow up to 3 edits
            $bestDistance = $distance;
            $bestMatch = $pub;
        }
    }

    if ($bestMatch) {
        $nameIndex[$normalized] = $bestMatch; // Cache for future lookups
        return $bestMatch;
    }

    // Try matching just first name + first word of apellidos
    $parts = explode(' ', $normalized);
    if (count($parts) >= 2) {
        // For "De" names: try "nombre de apellido" patterns
        foreach ($nameIndex as $indexedName => $pub) {
            if (str_starts_with($indexedName, $parts[0] . ' ') ||
                str_ends_with($indexedName, ' ' . end($parts))) {
                $distance = levenshtein($normalized, $indexedName);
                if ($distance <= 4) {
                    $nameIndex[$normalized] = $pub;
                    return $pub;
                }
            }
        }
    }

    return null;
}
