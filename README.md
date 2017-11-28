Stash Package
==============

Provides basic stash bindings and helpers into Laravel.

# Installation

Install the base package with composer.

~~~ bash
$ composer require zingle-com/stash-package
~~~

Add service provider to your providers after the Illuminate providers, 
but before your project service providers.

~~~ php
// config.php
// ...
	'providers' => [
		// ...
		Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,

        /**
         * Vendors
         */
    	// ...
    	ZingleCom\Stash\StashServiceProvider::class,

    	// ...
    	/**
    	 * Project providers
    	 */
	],
~~~

Finally install the vendor assets:

~~~ bash
$ php artisan vendor:publish --provider="ZingleCom\Stash\StashServiceProvider::class"
~~~