<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Images extends Admincontroller {

	public function before()
	{

		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/images';
		xml::to_XML(array('admin_page' => 'Images'), $this->xml_meta);
	}

	public function action_index()
	{
		// List images
		$this->xml_content_images = $this->xml_content->appendChild($this->dom->createElement('images'));
		foreach (Content_Image::get_images() as $image_name => $image_details)
		{
			// Create the image node and set the image data to it
			$image_node = $this->xml_content_images->appendChild($this->dom->createElement('image'));
			$image_node->setAttribute('name', $image_name);
			$image_node->appendChild($this->dom->createElement('name', $image_name));
			$image_node->appendChild($this->dom->createElement('URL', 'user_content/images/'.$image_name));

			foreach ($image_details as $detail_name => $detail_values)
			{
				if (count($detail_values))
				{
					foreach ($detail_values as $detail_value) $image_node->appendChild($this->dom->createElement($detail_name, $detail_value));
				}
				else
				{
					$image_node->appendChild($this->dom->createElement($detail_name));
				}
			}
		}
	}

	public function action_add_image()
	{
		if (count($_FILES))
		{
			$pathinfo = pathinfo($_FILES['file']['name']);
			if (strtolower($pathinfo['extension']) == 'jpg' || strtolower($pathinfo['extension']) == 'png')
			{
				$filename     = URL::title($pathinfo['filename']).'.'.strtolower($pathinfo['extension']);
				$new_filename = $filename;
				$counter      = 1;
				while ( ! Content_Image::image_name_available($new_filename))
				{
					$new_filename = substr($filename, 0, strlen($filename) - 4).'_'.$counter.'.'.strtolower($pathinfo['extension']);
					$counter++;
				}
				if (move_uploaded_file($_FILES['file']['tmp_name'], APPPATH.'/user_content/images/'.$new_filename))
				{

					if (strtolower($pathinfo['extension']) == 'jpg')
						$gd_img_object = ImageCreateFromJpeg(Kohana::$config->load('user_content.dir').'/images/'.$new_filename);
					elseif (strtolower($pathinfo['extension']) == 'png')
						$gd_img_object = ImageCreateFromPng(Kohana::$config->load('user_content.dir').'/images/'.$new_filename);

					$details = array(
					             'width'  => array(imagesx($gd_img_object)),
					             'height' => array(imagesy($gd_img_object))
					           );

					foreach ($_POST['tag'] as $nr => $tag_name)
					{
						if ($tag_name != '')
						{
							if ( ! isset($details[$tag_name])) $details[$tag_name] = array();
							$details[$tag_name][] = $_POST['tag_value'][$nr];
						}
					}

					Content_Image::new_image($new_filename, $details);

					$this->add_message('Image "'.$new_filename.'" added');
				}
				else $this->add_error('Unknown error uploading image');
			}
			else
			{
				$this->add_error('Image must be jpg or png');
			}
		}
	}

	public function action_edit_image()
	{
		$name = $this->request->param('options');

		if ($content_image = new Content_Image($name))
		{
			$this->xml_content_image = $this->xml_content->appendChild($this->dom->createElement('image'));
			$this->xml_content_image->setAttribute('name', $name);

			$this->xml_content_image->appendChild($this->dom->createElement('URL', 'user_content/images/'.$name));
			$tags_node = $this->xml_content_image->appendChild($this->dom->createElement('tags'));

			if (count($_POST))
			{
				list($filename, $extension) = explode('.', $_POST['name']);
				$extension = strtolower($extension);

				$_POST['name'] = URL::title($filename, '-', TRUE).'.'.$extension;
				$post = new Validation($_POST);
				$post->filter('trim');
				$post->rule('Valid::not_empty', 'name');

				$form_data = $post->as_array();
				$old_name  = $content_image->get_name();
				if ($form_data['name'] != $old_name)
				{
					$post->rule('Content_Image::image_name_available', 'name');
				}

				// Check for form errors
				if ($post->validate())
				{
					// No form errors, edit image

					$new_image_data = array();
					foreach ($form_data['tag'] as $nr => $tag_name)
					{
						if ( ! isset($new_image_data[$tag_name])) $new_image_data[$tag_name] = array();
						$new_image_data[$tag_name][] = $form_data['tag_value'][$nr];
					}
					$content_image->set_data(array_merge($new_image_data, array('name' => $form_data['name'])));

					if ($form_data['name'] != $old_name)
					{
						// If the image name have changed, we need to change the URL also

						// Save the message for the new URL
						$_SESSION['content']['image']['message'] = 'Image data saved';

						// Redirect to the new name
						$this->redirect('/admin/images/edit_image/'.$form_data['name']);
					}

					$this->add_message('Image data saved');

					$this->set_formdata(array('name'=>$form_data['name']));

					$image_data = $content_image->get_data();
					foreach ($image_data as $tag_name => $tag_values)
					{
						foreach ($tag_values as $tag_value)
						{
							$tag_node = $tags_node->appendChild($this->dom->createElement('tag', $tag_value));
							$tag_node->setAttribute('name', $tag_name);
						}
						if ( ! count($tag_values))
						{
							$tag_node = $tags_node->appendChild($this->dom->createElement('tag'));
							$tag_node->setAttribute('name', $tag_name);
						}
					}
				}
				else
				{
					// Something is wrong. Fill form with unsaved data and push error

					$this->set_formdata(array('name'=>$form_data['name']));
					foreach ($form_data['tag'] as $nr => $tag_name)
					{
						if ($tag_name != '')
						{
							$tag_node = $tags_node->appendChild($this->dom->createElement('tag', $form_data['tag_value'][$nr]));
							$tag_node->setAttribute('name', $tag_name);
						}
					}
					$this->add_form_errors($post->errors());
				}

			}
			else
			{
				$this->set_formdata(array('name'=>$name));

				$image_data = $content_image->get_data();
				foreach ($image_data as $tag_name => $tag_values)
				{
					foreach ($tag_values as $tag_value)
					{
						$tag_node = $tags_node->appendChild($this->dom->createElement('tag', $tag_value));
						$tag_node->setAttribute('name', $tag_name);
					}
					if ( ! count($tag_values))
					{
						$tag_node = $tags_node->appendChild($this->dom->createElement('tag'));
						$tag_node->setAttribute('name', $tag_name);
					}
				}
			}

			if ( isset($_SESSION['content']['image']['message']))
			{
				$this->add_message($_SESSION['content']['image']['message']);

				unset($_SESSION['content']['image']['message']);
			}

		}
		else $this->redirect();
	}

	public function action_rm_image()
	{
		$content_image = new Content_Image($this->request->param('options'));
		$content_image->rm_image();

		$this->redirect();
	}

}
