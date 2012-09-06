<?php defined('SYSPATH') or die('No direct script access.');

// Check and set up user content directory

if ( ! is_writable(Kohana::$config->load('user_content.dir')))
{
	throw new Kohana_Exception('Directory :dir must be writable',
		array(':dir' => Debug::path(Kohana::$config->load('user_content.dir'))));
}
if (Kohana::$environment === Kohana::DEVELOPMENT && ! is_dir(Kohana::$config->load('user_content.dir').'/images'))
{
	if ( ! mkdir(Kohana::$config->load('user_content.dir').'/images'))
	{
		throw new Kohana_Exception('Failed to create :dir',
			array(':dir' => Debug::path(Kohana::$config->load('user_content.dir').'/images')));
	}
}

Route::set('admin', 'admin/<controller>(/<action>(/<options>))',
	array(
		'action' => '[a-zA-Z0-9_-]+',
		'options' => '.*',
  ))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'admin',
		'action'     => 'index',
	));

foreach (Kohana::$config->load('media') as $name => $URL)
{
	Route::set($name, $URL,
		array(
	    'path' => '[a-zA-Z0-9_/\.-]+',
	  ))
		->defaults(array(
			'controller' => 'media',
			'action'     => $name,
		));
}

// User content images
Route::set('user_content/images', 'user_content/images/<file>',
	array(
    'file' => '[a-zA-Z0-9_/\.-]+',
  ))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'user_content_image',
	));

// Set dynamic routes from the pages model
$URIs = array();
foreach (Content_Page::get_pages() as $page) $URIs[] = $page['URI'];

if (count($URIs))
{
	Route::set('generic', '<page>', array('page' => implode('|', $URIs)))
			->defaults(array(
				'controller' => 'generic',
				'action'     => 'index',
			));
}

// Set blog routes
Route::set('blogpost', 'blog/<slug>', array('slug' => '.*'))
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'blogpost',
		));
