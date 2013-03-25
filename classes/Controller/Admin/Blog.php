<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Blog extends Admincontroller {

	public function action_index()
	{
		$tags           = array('blogpost');
		$order_by       = array('datetime' => 'DESC');
		$posts_per_page = 10000;

		if (isset($_GET['page']))   $offset = ($_GET['page'] - 1) * $posts_per_page;

		if (isset($_GET['limit']))  $limit  = $_GET['limit'];
		else                        $limit  = $posts_per_page;

		if (isset($_GET['offset'])) $offset = $_GET['offset'];
		else                        $offset = 0;

		xml::to_XML(Content::get_contents_for_xml($tags, $order_by, $limit, $offset), array('contents' => $this->xml_content));
	}

	public function action_blogpost()
	{
		if ( ! isset($_GET['id'])) $_GET['id'] = FALSE;
		$content = new Content($_GET['id']);

		if ( ! empty($_POST))
		{
			$preview = '';
			$post = new Validation($_POST);
			$post->filter('trim');
			$post->rule('Valid::not_empty', 'title');
			$post->rule('Valid::not_empty', 'content');
			if ($post->validate())
			{
				$post_data = $post->as_array();
				$tags      = array('title' => $post_data['title'], 'blogpost' => NULL);
				if (isset($post_data['datetime']) && $post_data['datetime'] != '')
				{
					$tags['datetime'] = date('Y-m-d H:i:s', strtotime($post_data['datetime']));
					$tags['slug']     = date('Y/m/d/', strtotime($post_data['datetime'])).URL::title($tags['title']);
				}

				if ( ! $_GET['id'])
				{
					$content_id = Content::new_content($post_data['content'], $tags);
					$this->add_message('Added blogpost');
					$this->redirect('/admin/blog/blogpost?id='.$content_id);
				}
				else
				{
					$content->update_content($post_data['content'], $tags);
					$this->add_message('Updated blogpost');
				}

				$this->set_formdata(array(
					'title'    => $content->get_tag('title'),
					'datetime' => $content->get_tag('datetime'),
					'content'  => $content->get_content(),
				));
			}
			else
			{
				$this->set_formdata(array(
					'title'    => $post_data['title'],
					'datetime' => $post_data['datetime'],
					'content'  => $post_data['content'],
				));
				$this->add_error('Fix errors and try again');
			}
		}
		else
		{
			$this->set_formdata(array(
				'title'    => $content->get_tag('title'),
				'datetime' => $content->get_tag('datetime'),
				'content'  => $content->get_content(),
			));
		}


	}

}
