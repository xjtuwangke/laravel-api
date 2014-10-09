<?php namespace Xjtuwangke\LaravelApi;

use Illuminate\Support\ServiceProvider;

class LaravelApiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{
		if( file_exists( app_path( 'routes/api.routes.php') ) ){
			include( app_path( 'routes/api.routes.php') );
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
