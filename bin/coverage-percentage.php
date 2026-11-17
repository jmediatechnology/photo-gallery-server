<?php
/**
 * Reads a Clover XML coverage report and prints coverage percentage(s),
 * optionally enforcing a minimum threshold.
 *
 * Usage:
 *   php coverage-percentage.php path/to/clover.xml
 *   php coverage-percentage.php path/to/clover.xml --metric=statements   (default)
 *   php coverage-percentage.php path/to/clover.xml --metric=elements     (Clover's TPC)
 *   php coverage-percentage.php path/to/clover.xml --metric=methods
 *   php coverage-percentage.php path/to/clover.xml --metric=conditionals
 *   php coverage-percentage.php path/to/clover.xml --metric=all
 *   php coverage-percentage.php path/to/clover.xml --min=100             (fail if below)
 *
 * Exits non-zero on any parse/argument error, or when --min is not met,
 * so it's safe to use in CI.
 */

$args = $argv;
array_shift($args); // drop script name

if (count($args) < 1) {
    fail("missing path to clover.xml\nUsage: php coverage-percentage.php path/to/clover.xml [--metric=statements|elements|methods|conditionals|all] [--min=0-100]");
}

[$path, $metric, $min] = parseArgs($args);

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

// Raw [covered, total] counts — kept so the threshold check never
// compares against a rounded percentage.
$counts = [
    'statements'   => [(int) $attrs->coveredstatements, (int) $attrs->statements],
    'methods'      => [(int) $attrs->coveredmethods, (int) $attrs->methods],
    'conditionals' => [(int) $attrs->coveredconditionals, (int) $attrs->conditionals],
    'elements'     => [(int) $attrs->coveredelements, (int) $attrs->elements], // Clover's TPC
];

$results = array_map(fn(array $c) => ratio(...$c), $counts);

if ($metric === 'all') {
    if ($min !== null) {
        fail('--min cannot be combined with --metric=all');
    }
    handleMetricAll($results);
    exit(0);
}

if (!array_key_exists($metric, $results)) {
    fail("unknown metric '{$metric}'. Valid options: " . implode(', ', array_keys($results)) . ", all");
}

if ($results[$metric] === null) {
    fail("metric '{$metric}' has zero total elements in this report — nothing to divide by");
}

if ($min !== null) {
    enforceThreshold($metric, $counts[$metric], $results[$metric], $min);
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
 * Splits argv into [path, metric, min], accepting the path and optional
 * --metric=X / --min=N flags in any order. Rejects a second positional
 * argument and duplicated flags rather than silently overwriting.
 *
 * @return array{0: string, 1: string, 2: ?float}
 */
function parseArgs(array $args): array
{
    $path = null;
    $metric = null;
    $min = null;

    foreach ($args as $arg) {
        if (str_starts_with($arg, '--metric=')) {
            if ($metric !== null) {
                fail('--metric was specified more than once');
            }
            $metric = substr($arg, strlen('--metric='));
        } elseif (str_starts_with($arg, '--min=')) {
            if ($min !== null) {
                fail('--min was specified more than once');
            }
            $value = substr($arg, strlen('--min='));
            if (!is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                fail("--min must be a number between 0 and 100, got '{$value}'");
            }
            $min = (float) $value;
        } elseif ($path === null) {
            $path = $arg;
        } else {
            fail("unexpected argument: {$arg}");
        }
    }

    if ($path === null) {
        fail('no clover.xml path provided');
    }

    return [$path, $metric ?? 'statements', $min];
}

function ratio(int $covered, int $total): ?float
{
    if ($total === 0) {
        return null; // avoid div-by-zero; e.g. no methods in the codebase
    }
    return round(($covered / $total) * 100, 2);
}

/**
 * Compares raw counts (covered * 100 < min * total) instead of the rounded
 * percentage, so 99.996% can never pass a 100% gate.
 *
 * @param array{0: int, 1: int} $counts
 */
function enforceThreshold(string $metric, array $counts, float $percentage, float $min): never
{
    [$covered, $total] = $counts;

    printf("Coverage (%s): %s%% (%d/%d)\n", $metric, $percentage, $covered, $total);

    if ($covered * 100 < $min * $total) {
        printf("::error::Coverage %s%% is below the required %s%%.\n", $percentage, $min);
        exit(1);
    }

    printf("Coverage meets the %s%% threshold.\n", $min);
    exit(0);
}

function handleMetricAll(array $results): void
{
    foreach ($results as $name => $value) {
        printf("%-12s %s\n", $name . ':', $value === null ? 'n/a' : $value . '%');
    }
}
