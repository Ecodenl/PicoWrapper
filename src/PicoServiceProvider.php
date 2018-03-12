<?php

namespace Ecodenl\PicoWrapper;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class PicoServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		// Publish config files
		$this->publishes( [
			__DIR__ . '/../config/config.php' => config_path( 'pico.php' ),
		],
			'config' );
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->app->bind('pico', function($app){
			$client = new Client(
				array_merge_recursive($app->config['pico'], ['defaults' => ['allow_redirects' => true, 'cookies' => true]])
			);
			return new PicoClient($client);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['pico'];
	}
}
