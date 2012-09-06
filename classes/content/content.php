<?php defined('SYSPATH') OR die('No direct access allowed.');

class Content_Content extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	/**
	 * Content
	 *
	 * @var str
	 */
	private $content;

	/**
	 * Content id
	 *
	 * @var int
	 */
	private $content_id;

	/**
	 * Tag IDs connected to this content
	 *
	 * @var array - tag name as key, tag values as array value
	 *              example:
	 *              array(
	 *                'location' => array('stockholm', 'tokyo'),
	 *                'blogpost' => array(NULL),
	 *              )
	 */
	private $tags;

	/**
	 * Constructor
	 *
	 * @param int $id - Content id
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($id)
		{
			$this->content_id = $id;
			if ( ! $this->load_content())
			{
				// This content id does not exist, unset the page id again
				$this->content_id = NULL;
			}
		}
	}

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	/**
	 * Get content
	 *
	 * @return str
	 */
	public function get_content()
	{
		return $this->content;
	}

	/**
	 * Get current content id
	 *
	 * @return int
	 */
	public function get_content_id()
	{
		return $this->content_id;
	}

	/**
	 * Get content id by tags
	 *
	 * Will return first matching id for these tags or FALSE if none is found
	 *
	 * @param $tags - tag name as key, tag values as values
	 * @return int or FALSE
	 */
	public static function get_content_id_by_tags($tags)
	{
		$contents = self::driver()->get_contents_by_tags($tags);
		if (count($contents))
		{
			list($content_id) = array_keys($contents);
			return $content_id;
		}

		return FALSE;
	}

	/**
	 * Get contents
	 *
	 * @return array - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                                     array(
	 *                                       id   => 3,
	 *                                       name => blog post,
	 *                                     )
	 *                                   )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        tags    => array(
	 *                                     array(
	 *                                       id   => 4,
	 *                                       name => News,
	 *                                     )
	 *                                     array(
	 *                                       id   => 5,
	 *                                       name => RSS post,
	 *                                     )
	 *                                   )
	 *                        content => Lots of content
	 *                      ),
	 *                    )
	 */
	public static function get_contents()
	{
		return self::driver()->get_contents();
	}

	/**
	 * Get contents by tags
	 *
	 * @param $tags - tag name as key, tag values as values
	 * @param $order_by - 'content', 'id' or array of tag names (tag name as key, order (ASC/DESC) as value)
	 * @param $limit - integer
	 * @param $offset - integer (defaults to 0)
	 * @return array of content ids - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                          date     => array('2011-05-30'),
	 *                          blogpost => array(NULL)
	 *                          location => array('stockholm', 'uppsala')
	 *                        )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                          date     => array('2011-05-30'),
	 *                          blogpost => array(NULL)
	 *                          location => array('stockholm', 'uppsala')
	 *                        )
	 *                      ),
	 *                    )
	 */
	public static function get_contents_by_tags($tags = FALSE, $order_by = FALSE, $limit = FALSE, $offset = 0)
	{
		return self::driver()->get_contents_by_tags($tags, $order_by, $limit, $offset);
	}

	/**
	 * Get contents by tag id
	 *
	 * @param int $tag_id
	 * @return array of content ids - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                          date     => array('2011-05-30'),
	 *                          blogpost => array(NULL)
	 *                          location => array('stockholm', 'uppsala')
	 *                        )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                          date     => array('2011-05-30'),
	 *                          blogpost => array(NULL)
	 *                          location => array('stockholm', 'uppsala')
	 *                        )
	 *                      ),
	 *                    )
	 */
	public static function get_contents_by_tag_id($tag_id)
	{
		return self::driver()->get_contents_by_tag_id($tag_id);
	}

	public static function get_contents_by_tag_value($tag_value)
	{
		return self::driver()->get_contents_by_tag_value($tag_value);
	}

	/**
	 * Get contents by tags
	 *
	 * @param $tags - tag name as key, tag values as values
	 * @param $limit - integer
	 * @param $offset - integer (defaults to 0)
	 * @return int amount of contents found
	 */
	public static function get_contents_count_by_tags($tags = FALSE, $limit = FALSE, $offset = 0)
	{
		return self::driver()->get_contents_count_by_tags($tags, $limit, $offset);
	}

	public static function get_contents_for_xml($tags = FALSE, $order_by = FALSE, $limit = FALSE, $offset = 0)
	{
		$contents = self::driver()->get_contents_by_tags($tags, $order_by, $limit, $offset);

		foreach ($contents as $nr => $content)
		{
			$counter = 0;
			foreach ($content['tags'] as $tag_name => $tag_values)
			{
				foreach ($tag_values as $tag_value)
					$content['tags'][$counter.$tag_name] = array(
						'@id'    => Tags::get_id_by_name($tag_name),
						'$value' => $tag_value
					);

				unset($content['tags'][$tag_name]);
				if (count($tag_values) == 0) $content['tags'][$tag_name] = array('@id' => Tags::get_id_by_name($tag_name));
				$counter++;
			}

			$content['@id']     = $content['id'];
			$content['content'] = xml::to_array(Markdown::transform($content['content']));
			unset($content['id']);

			$contents[$nr.'content'] = $content;
			unset($contents[$nr]);
		}

		return $contents;
	}

	public function get_tag($tag_name)
	{
		if ($tags = $this->get_tags())
		{
			foreach ($tags as $tag_id => $tag_data)
			{
				if ($tag_data['name'] == $tag_name)
				{
					if (count($tag_data['values']) == 0)     return TRUE;
					elseif (count($tag_data['values']) == 1) return reset($tag_data['values']);
					else                                     return $tag_data['values'];
				}
			}
		}

		return FALSE;
	}

	public function get_tags()
	{
		return $this->tags;
	}

	public function load_content()
	{
		$this->tags     = self::driver()->get_tags_by_content_id($this->get_content_id());
		$this->content  = self::driver()->get_content($this->get_content_id());
		return TRUE;
	}

	public static function new_content($content, $tags = FALSE)
	{
		return self::driver()->new_content($content, $tags);
	}

	public function rm_content()
	{
		if (self::driver()->rm_content($this->get_content_id()))
		{
			unset($this);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Content_'.ucfirst(Kohana::$config->load('content.driver'));
		return (self::$driver = new $driver_name);
	}

	public function update_content($content, $tags = FALSE)
	{
		if (self::driver()->update_content($this->get_content_id(), $content, $tags))
		{
			// We must update the local class content also
			$this->load_content();
			return TRUE;
		}

		return FALSE;
	}

	public static function update_content_by_tags($content_string, $tags, $create_if_not_exists = TRUE)
	{
		$content_id = self::get_content_id_by_tags($tags);
		if ($content_id)
		{
			$content = new self($content_id);
			$content->update_content($content_string);
		}
		elseif ($create_if_not_exists)
			$content_id = self::new_content($content_string, $tags);

		return $content_id;
	}

}
