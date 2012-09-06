<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Blog extends Xsltcontroller
{

	public function action_index()
	{
		$tags           = array('blogpost', 'datetime');
		$order_by       = array('datetime' => 'DESC');
		$posts_per_page = 10;
		$limit          = $posts_per_page;
		$offset         = 0;

		if (isset($_GET['page']))   $offset       = ($_GET['page'] - 1) * $posts_per_page;
		else                        $_GET['page'] = 1;
		if (isset($_GET['limit']))  $limit        = $_GET['limit'];
		if (isset($_GET['offset'])) $offset       = $_GET['offset'];

		$pages = array(
			'current' => $_GET['page'],
			'last'    => ceil(Content::get_contents_count_by_tags($tags) / $posts_per_page),
		);

		xml::to_XML($pages,                                                           array('pages'     => $this->xml_content));
		xml::to_XML(Content::get_contents_for_xml($tags, $order_by, $limit, $offset), array('blogposts' => $this->xml_content));
	}

}
