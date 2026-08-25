<?php
/**
 * Reads a Clover XML coverage report and prints coverage percentage(s).
 *
 * Usage:
 *   php coverage-percentage.php path/to/clover.xml
 *   php coverage-percentage.php path/to/clover.xml --metric=statements   (default)
 *   php coverage-percentage.php path/to/clover.xml --metric=elements     (Clover's TPC)
 *   php coverage-percentage.php path/to/clover.xml --metric=methods
 *   php coverage-percentage.php path/to/clover.xml --metric=conditionals
 *   php coverage-percentage.php path/to/clover.xml --metric=all
 *
 * Exits non-zero on any parse/argument error so it's safe to use in CI.
 */

$args = $argv;
array_shift($args); // drop script name

if (count($args) < 1) {
    fail("missing path to clover.xml\nUsage: php coverage-percentage.php path/to/clover.xml [--metric=statements|elements|methods|conditionals|all]");
}

[$path, $metric] = parseArgs($args);

if (!is_file($path)) {
    fail("file not found: {$path}");
}

$xmlContents = file_get_contents($path);
if ($xmlContents === false) {
    fail("could not read file: {$path}");
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($xmlContents);
if ($xml === false) {
    $errors = array_map(fn($e) => trim($e->message), libxml_get_errors());
    fail("invalid XML in {$path}: " . implode('; ', $errors));
}

// Project-level <metrics> is the pre-aggregated total across all files.
// Path: <coverage><project><metrics .../></project></coverage>
$projectMetrics = $xml->project->metrics ?? null;
if ($projectMetrics === null) {
    fail("no <project><metrics> node found — is this a valid Clover report?");
}

$attrs = $projectMetrics->attributes();

$results = [
    'statements'   => ratio($attrs->coveredstatements, $attrs->statements),
    'methods'      => ratio($attrs->coveredmethods, $attrs->methods),
    'conditionals' => ratio($attrs->coveredconditionals, $attrs->conditionals),
    'elements'     => ratio($attrs->coveredelements, $attrs->elements), // Clover's TPC
];

if ($metric === 'all') {
    handleMetricAll($results);
    exit(0);
}

if (!array_key_exists($metric, $results)) {
    fail("unknown metric '{$metric}'. Valid options: " . implode(', ', array_keys($results)) . ", all");
}

if ($results[$metric] === null) {
    fail("metric '{$metric}' has zero total elements in this report — nothing to divide by");
}

// Single number, nothing else — safe to pipe into other tooling.
echo $results[$metric] . "\n";

// -----------------------------------------------------------------------
// Functions
// -----------------------------------------------------------------------

function fail(string $message): never
{
    fwrite(STDERR, "Error: {$message}\n");
    exit(1);
}

/**
 * Splits argv into [path, metric], accepting the path and an optional
 * --metric=X flag in either order. Rejects a second positional argument
 * and a duplicated --metric= flag rather than silently overwriting.
 *
 * @return array{0: string, 1: string}
 */
function parseArgs(array $args): array
{
    $path = null;
    $metric = null;

    foreach ($args as $arg) {
        if (str_starts_with($arg, '--metric=')) {
            if ($metric !== null) {
                fail('--metric was specified more than once');
            }
            $metric = substr($arg, strlen('--metric='));
        } elseif ($path === null) {
            $path = $arg;
        } else {
            fail("unexpected argument: {$arg}");
        }
    }

    if ($path === null) {
        fail('no clover.xml path provided');
    }

    return [$path, $metric ?? 'statements'];
}

function ratio($covered, $total): ?float
{
    $covered = (int) $covered;
    $total = (int) $total;
    if ($total === 0) {
        return null; // avoid div-by-zero; e.g. no methods in the codebase
    }
    return round(($covered / $total) * 100, 2);
}

function handleMetricAll(array $results): void
{
    foreach ($results as $name => $value) {
        printf("%-12s %s\n", $name . ':', $value === null ? 'n/a' : $value . '%');
    }
}
