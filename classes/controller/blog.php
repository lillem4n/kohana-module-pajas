<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Blog extends Xsltcontroller
{

	public function action_index()
	{
		$tags           = array('blogpost');//, 'published');
		$order_by       = array('datetime' => 'DESC');
		$posts_per_page = 10;

		if (isset($_GET['page']))   $offset = ($_GET['page'] - 1) * $posts_per_page;

		if (isset($_GET['limit']))  $limit  = $_GET['limit'];
		else                        $limit  = $posts_per_page;

		if (isset($_GET['offset'])) $offset = $_GET['offset'];
		else                        $offset = 0;

		xml::to_XML(Content::get_contents_for_xml($tags, $order_by, $limit, $offset), array('contents' => $this->xml_content));
	}

}
