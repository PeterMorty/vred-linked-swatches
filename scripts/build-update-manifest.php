@'
<?php

if (PHP_SAPI !== 'cli') {
	exit(1);
}

$root = dirname(__DIR__);
$plugin_file = $root . '/vred-linked-swatches.php';

if (! is_file($plugin_file)) {
	fwrite(STDERR, "Plugin file not found.\n");
	exit(1);
}

$plugin_source = file_get_contents($plugin_file);

if (! is_string($plugin_source) || $plugin_source === '') {
	fwrite(STDERR, "Unable to read plugin file.\n");
	exit(1);
}

if (! preg_match('/^ \* Version:\s*(.+)$/m', $plugin_source, $header_match)) {
	fwrite(STDERR, "Plugin header version not found.\n");
	exit(1);
}

if (! preg_match("/define\('VRED_LINKED_SWATCHES_VERSION',\s*'([^']+)'\);/", $plugin_source, $constant_match)) {
	fwrite(STDERR, "VRED_LINKED_SWATCHES_VERSION constant not found.\n");
	exit(1);
}

$header_version = trim($header_match[1]);
$constant_version = trim($constant_match[1]);

if ($header_version !== $constant_version) {
	fwrite(STDERR, "Plugin header version and constant version do not match.\n");
	exit(1);
}

$output_path = getenv('VRED_LINKED_SWATCHES_MANIFEST_OUTPUT');
$base_url = rtrim((string) getenv('VRED_LINKED_SWATCHES_UPDATES_BASE_URL'), '/');
$homepage = (string) getenv('VRED_LINKED_SWATCHES_PLUGIN_HOMEPAGE');
$requires = (string) getenv('VRED_LINKED_SWATCHES_REQUIRES_WP');
$tested = (string) getenv('VRED_LINKED_SWATCHES_TESTED_WP');
$requires_php = (string) getenv('VRED_LINKED_SWATCHES_REQUIRES_PHP');
$changelog = trim((string) getenv('VRED_LINKED_SWATCHES_CHANGELOG'));

if ($output_path === '') {
	fwrite(STDERR, "VRED_LINKED_SWATCHES_MANIFEST_OUTPUT is required.\n");
	exit(1);
}

if ($base_url === '') {
	fwrite(STDERR, "VRED_LINKED_SWATCHES_UPDATES_BASE_URL is required.\n");
	exit(1);
}

if ($homepage === '') {
	$homepage = 'https://viviendoenred.com';
}

if ($requires === '') {
	$requires = '6.5';
}

if ($tested === '') {
	$tested = '7.0';
}

if ($requires_php === '') {
	$requires_php = '7.4';
}

$manifest = [
	'name' => 'VRED Linked Swatches',
	'version' => $header_version,
	'download_url' => $base_url . '/vred-linked-swatches-v' . rawurlencode($header_version) . '.zip',
	'homepage' => $homepage,
	'requires' => $requires,
	'tested' => $tested,
	'requires_php' => $requires_php,
	'last_updated' => gmdate('Y-m-d H:i:s'),
	'icons' => [
		'1x' => $base_url . '/icon-128x128.png',
		'2x' => $base_url . '/icon-256x256.png',
	],
	'sections' => [
		'description' => 'Connect independent WooCommerce products as visual linked swatches for Elementor product templates.',
		'installation' => 'Upload the plugin ZIP, activate WooCommerce and Elementor, activate VRED Linked Swatches, configure linked products in the product data panel, then add the Elementor widgets to your product template.',
		'changelog' => $changelog,
	],
];

$json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (! is_string($json)) {
	fwrite(STDERR, "Unable to encode manifest JSON.\n");
	exit(1);
}

$json .= "\n";
$output_dir = dirname($output_path);

if (! is_dir($output_dir) && ! mkdir($output_dir, 0777, true) && ! is_dir($output_dir)) {
	fwrite(STDERR, "Unable to create output directory.\n");
	exit(1);
}

if (file_put_contents($output_path, $json) === false) {
	fwrite(STDERR, "Unable to write manifest file.\n");
	exit(1);
}

fwrite(STDOUT, 'Generated manifest for version ' . $header_version . "\n");
'@ | Set-Content -Path scripts/build-update-manifest.php -Encoding UTF8
