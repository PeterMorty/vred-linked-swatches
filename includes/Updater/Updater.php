<?php

namespace VRED_Linked_Swatches\Updater;

if (! defined('ABSPATH')) {
	exit;
}

/** Private free updater */
final class Updater {
	private const CACHE_KEY = 'vred_linked_swatches_remote_plugin_info';

	public static function boot() : void {
		add_filter('update_plugins_dev.viviendoenred.com', [self::class, 'filter_plugin_update'], 10, 4);
		add_filter('pre_set_site_transient_update_plugins', [self::class, 'filter_update_transient']);
		add_filter('site_transient_update_plugins', [self::class, 'filter_update_transient']);
		add_filter('plugins_api', [self::class, 'filter_plugin_info'], 20, 3);
		add_action('upgrader_process_complete', [self::class, 'after_plugin_update'], 10, 2);
	}

	public static function filter_plugin_update($update, array $plugin_data, string $plugin_file, array $locales) {
		unset($locales);

		if ($plugin_file !== VRED_LINKED_SWATCHES_BASENAME) {
			return $update;
		}

		return self::get_update_payload($plugin_data);
	}

	public static function filter_update_transient($transient) {
		if (! is_object($transient) || empty($transient->checked) || ! is_array($transient->checked)) {
			return $transient;
		}

		if (! isset($transient->checked[VRED_LINKED_SWATCHES_BASENAME])) {
			return $transient;
		}

		$plugin_data = get_plugin_data(VRED_LINKED_SWATCHES_FILE, false, false);
		$payload = self::get_update_payload($plugin_data);

		if (! empty($payload)) {
			if (! isset($transient->response) || ! is_array($transient->response)) {
				$transient->response = [];
			}

			$transient->response[VRED_LINKED_SWATCHES_BASENAME] = (object) $payload;
		} else {
			if (isset($transient->response[VRED_LINKED_SWATCHES_BASENAME])) {
				unset($transient->response[VRED_LINKED_SWATCHES_BASENAME]);
			}

			if (! isset($transient->no_update) || ! is_array($transient->no_update)) {
				$transient->no_update = [];
			}

			$transient->no_update[VRED_LINKED_SWATCHES_BASENAME] = (object) [
				'id' => VRED_LINKED_SWATCHES_UPDATE_URL,
				'slug' => VRED_LINKED_SWATCHES_SLUG,
				'plugin' => VRED_LINKED_SWATCHES_BASENAME,
				'new_version' => VRED_LINKED_SWATCHES_VERSION,
				'url' => 'https://viviendoenred.com',
				'package' => '',
				'icons' => self::get_plugin_icons(),
				'banners' => [],
				'banners_rtl' => [],
				'tested' => '',
				'requires_php' => '',
				'compatibility' => new \stdClass(),
			];
		}

		return $transient;
	}

	public static function filter_plugin_info($result, string $action, $args) {
		if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== VRED_LINKED_SWATCHES_SLUG) {
			return $result;
		}

		$plugin_info = self::get_remote_plugin_info(false);

		if (empty($plugin_info['version'])) {
			return $result;
		}

		$sections = ! empty($plugin_info['sections']) && is_array($plugin_info['sections']) ? $plugin_info['sections'] : [];

		return (object) [
			'name' => ! empty($plugin_info['name']) ? $plugin_info['name'] : 'VRED Linked Swatches',
			'slug' => VRED_LINKED_SWATCHES_SLUG,
			'version' => $plugin_info['version'],
			'author' => '<a href="https://viviendoenred.com">VRED</a>',
			'homepage' => ! empty($plugin_info['homepage']) ? $plugin_info['homepage'] : 'https://viviendoenred.com',
			'requires' => ! empty($plugin_info['requires']) ? $plugin_info['requires'] : '',
			'tested' => ! empty($plugin_info['tested']) ? $plugin_info['tested'] : '',
			'requires_php' => ! empty($plugin_info['requires_php']) ? $plugin_info['requires_php'] : '',
			'last_updated' => ! empty($plugin_info['last_updated']) ? $plugin_info['last_updated'] : '',
			'download_link' => ! empty($plugin_info['download_url']) ? $plugin_info['download_url'] : '',
			'sections' => [
				'description' => ! empty($sections['description']) ? $sections['description'] : '',
				'installation' => ! empty($sections['installation']) ? $sections['installation'] : '',
			],
			'banners' => ! empty($plugin_info['banners']) && is_array($plugin_info['banners']) ? $plugin_info['banners'] : [],
			'icons' => self::get_plugin_icons($plugin_info),
		];
	}

	public static function after_plugin_update($upgrader_object, array $options) : void {
		unset($upgrader_object);

		if (empty($options['action']) || $options['action'] !== 'update' || empty($options['type']) || $options['type'] !== 'plugin') {
			return;
		}

		delete_site_transient(self::CACHE_KEY);
		delete_site_transient('update_plugins');
	}

	private static function get_update_payload(array $plugin_data) : array {
		$plugin_info = self::get_remote_plugin_info(false);
		$installed_version = ! empty($plugin_data['Version']) ? (string) $plugin_data['Version'] : VRED_LINKED_SWATCHES_VERSION;

		if (empty($plugin_info['version']) || version_compare($installed_version, $plugin_info['version'], '>=')) {
			return [];
		}

		$package_url = ! empty($plugin_info['download_url']) ? (string) $plugin_info['download_url'] : '';

		if ($package_url === '') {
			return [];
		}

		return [
			'id' => ! empty($plugin_data['UpdateURI']) ? $plugin_data['UpdateURI'] : VRED_LINKED_SWATCHES_UPDATE_URL,
			'slug' => VRED_LINKED_SWATCHES_SLUG,
			'plugin' => VRED_LINKED_SWATCHES_BASENAME,
			'new_version' => $plugin_info['version'],
			'url' => ! empty($plugin_info['homepage']) ? $plugin_info['homepage'] : 'https://viviendoenred.com',
			'package' => $package_url,
			'tested' => ! empty($plugin_info['tested']) ? $plugin_info['tested'] : '',
			'requires' => ! empty($plugin_info['requires']) ? $plugin_info['requires'] : '',
			'requires_php' => ! empty($plugin_info['requires_php']) ? $plugin_info['requires_php'] : '',
			'autoupdate' => false,
			'icons' => self::get_plugin_icons($plugin_info),
			'banners' => ! empty($plugin_info['banners']) && is_array($plugin_info['banners']) ? $plugin_info['banners'] : [],
			'banners_rtl' => ! empty($plugin_info['banners_rtl']) && is_array($plugin_info['banners_rtl']) ? $plugin_info['banners_rtl'] : [],
			'translations' => [],
			'compatibility' => new \stdClass(),
		];
	}

	private static function get_remote_plugin_info(bool $force = false) : array {
		static $plugin_info = null;

		if ($plugin_info !== null && ! $force) {
			return $plugin_info;
		}

		if (! $force) {
			$cached = get_site_transient(self::CACHE_KEY);

			if (is_array($cached)) {
				$cached = self::sanitize_remote_plugin_info($cached);
			}

			if (! empty($cached['version']) && ! empty($cached['download_url'])) {
				$plugin_info = $cached;
				return $plugin_info;
			}
		}

		$update_url = self::validate_remote_url(VRED_LINKED_SWATCHES_UPDATE_URL);

		if ($update_url === '') {
			$plugin_info = [];
			return $plugin_info;
		}

		$response = wp_remote_get(
			$update_url,
			[
				'timeout' => 10,
				'redirection' => 0,
				'headers' => [
					'Accept' => 'application/json',
					'Cache-Control' => 'no-cache',
				],
			]
		);

		if (is_wp_error($response)) {
			$plugin_info = [];
			return $plugin_info;
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if ($code !== 200 || ! is_array($data)) {
			$plugin_info = [];
			return $plugin_info;
		}

		$plugin_info = self::sanitize_remote_plugin_info($data);

		if (empty($plugin_info['version']) || empty($plugin_info['download_url'])) {
			$plugin_info = [];
			return $plugin_info;
		}

		set_site_transient(self::CACHE_KEY, $plugin_info, HOUR_IN_SECONDS);

		return $plugin_info;
	}

	private static function sanitize_remote_plugin_info(array $data) : array {
		$version = ! empty($data['version']) ? sanitize_text_field((string) $data['version']) : '';

		if ($version === '' || strlen($version) > 64 || ! preg_match('/^[0-9]+(?:\.[0-9]+)*(?:[-+][0-9A-Za-z.-]+)?$/', $version)) {
			return [];
		}

		$sections = ! empty($data['sections']) && is_array($data['sections']) ? $data['sections'] : [];
		$package_url = ! empty($data['download_url']) ? (string) $data['download_url'] : (! empty($data['package']) ? (string) $data['package'] : '');

		return [
			'name' => ! empty($data['name']) ? sanitize_text_field((string) $data['name']) : '',
			'version' => $version,
			'homepage' => ! empty($data['homepage']) ? self::validate_remote_url((string) $data['homepage']) : '',
			'requires' => ! empty($data['requires']) ? sanitize_text_field((string) $data['requires']) : '',
			'tested' => ! empty($data['tested']) ? sanitize_text_field((string) $data['tested']) : '',
			'requires_php' => ! empty($data['requires_php']) ? sanitize_text_field((string) $data['requires_php']) : '',
			'last_updated' => ! empty($data['last_updated']) ? sanitize_text_field((string) $data['last_updated']) : '',
			'sections' => [
				'description' => ! empty($sections['description']) ? wp_kses_post((string) $sections['description']) : '',
				'installation' => ! empty($sections['installation']) ? wp_kses_post((string) $sections['installation']) : '',
			],
			'icons' => self::sanitize_remote_assets($data['icons'] ?? [], ['1x', '2x', 'svg', 'default']),
			'banners' => self::sanitize_remote_assets($data['banners'] ?? [], ['low', 'high']),
			'banners_rtl' => self::sanitize_remote_assets($data['banners_rtl'] ?? [], ['low', 'high']),
			'download_url' => self::validate_remote_url($package_url),
		];
	}

	private static function sanitize_remote_assets($assets, array $allowed_keys) : array {
		if (! is_array($assets)) {
			return [];
		}

		$sanitized = [];

		foreach ($allowed_keys as $key) {
			if (empty($assets[$key])) {
				continue;
			}

			$url = self::validate_remote_url((string) $assets[$key]);

			if ($url !== '') {
				$sanitized[$key] = $url;
			}
		}

		return $sanitized;
	}

	private static function get_plugin_icons(array $plugin_info = []) : array {
		$icons = ! empty($plugin_info['icons']) && is_array($plugin_info['icons']) ? $plugin_info['icons'] : [];

		if (empty($icons['1x'])) {
			$icons['1x'] = 'https://dev.viviendoenred.com/wordpress/plugins/vred-linked-swatches/updates/icon-128x128.png';
		}

		if (empty($icons['2x'])) {
			$icons['2x'] = 'https://dev.viviendoenred.com/wordpress/plugins/vred-linked-swatches/updates/icon-256x256.png';
		}

		return $icons;
	}

	private static function validate_remote_url(string $url) : string {
		$url = trim($url);

		if ($url === '' || ! wp_http_validate_url($url)) {
			return '';
		}

		$parts = wp_parse_url($url);

		if (
			! is_array($parts)
			|| empty($parts['scheme'])
			|| strtolower((string) $parts['scheme']) !== 'https'
			|| empty($parts['host'])
			|| isset($parts['user'])
			|| isset($parts['pass'])
			|| (isset($parts['port']) && (int) $parts['port'] !== 443)
		) {
			return '';
		}

		$host = strtolower(rtrim((string) $parts['host'], '.'));
		$allowed_hosts = apply_filters('vred_linked_swatches_allowed_remote_hosts', [
			'dev.viviendoenred.com',
			'viviendoenred.com',
			'www.viviendoenred.com',
		]);

		if (! is_array($allowed_hosts)) {
			return '';
		}

		$allowed_hosts = array_values(array_unique(array_filter(array_map(static function ($allowed_host) : string {
			$allowed_host = strtolower(rtrim(trim((string) $allowed_host), '.'));

			return preg_match('/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $allowed_host) ? $allowed_host : '';
		}, $allowed_hosts))));

		return in_array($host, $allowed_hosts, true) ? esc_url_raw($url, ['https']) : '';
	}
}
