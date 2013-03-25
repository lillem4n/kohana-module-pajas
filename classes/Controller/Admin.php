<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin extends Admincontroller {

	public function action_index()
	{
		if ( ! User::instance()->logged_in()) $this->redirect('admin/login');
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/home';
	}

}
