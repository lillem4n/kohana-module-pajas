<?php defined('SYSPATH') or die('No direct script access.');

/*
 * This config file is used to configure the
 * media controller
 *
 */

return array(
	// Name of route and action to use => URL as a route, use multiple by splitting with |
	// Name (array key) will also be the action name in the media controller
	'css'   => 'css/<path>.css',
	'fonts' => 'fonts/<path>',
	'img'   => 'img/<path>',
	'js'    => 'js/<path>.js',
	'xsl'   => 'xsl/<path>.xsl',
);
