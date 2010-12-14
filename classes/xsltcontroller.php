<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Kohana Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * Downloaded from http://kohana.lillem4n.se
 */
abstract class Xsltcontroller
{

	/**
	 * If set to TRUE, render() will automaticly be ran
	 * when the controller is done.
	 */
	var $auto_render = TRUE;

	/**
	 * Decides where the transformation of XSLT->HTML
	 * should be done
	 * ATTENTION! This setting is configurable in xslt.php
	 *
	 * options:
	 * 'auto' = Normally sends XML+XSLT, but sometimes HTML,
	 *          depending on the HTTP_USER_AGENT
	 * TRUE   = Always send HTML
	 * FALSE  = Always send XML+XSLT
	 *
	 */
	var $transform;

	/**
	 * Where to look for the XSLT stylesheets
	 */
	var $xslt_path;

	/**
	 * The filename of the XSLT stylesheet, excluding .xsl
	 */
	var $xslt_stylesheet = 'default';

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		// Set transformation
		if (isset($_GET['transform']))
		{
			if     ($_GET['transform'] == 'TRUE')  $this->transform = TRUE;
			elseif ($_GET['transform'] == 'FALSE') $this->transform = FALSE;
			else                                   $this->transform = 'auto';
		}
		else
		{
			$this->transform = Kohana::config('xslt.transform');
		}

		// Set XSLT path
		$this->xslt_path = Kohana::$base_url.'xsl/';

		// Create the XML DOM
		$this->dom = new DomDocument('1.0', 'UTF-8');
		$this->dom->formatOutput = TRUE;

		// Create the XML root
		$this->xml = $this->dom->appendChild($this->dom->createElement('root'));

		// Create the meta node
		$this->xml_meta = $this->xml->appendChild($this->dom->createElement('meta'));
		xml::to_XML(
			array(
				'protocol'      => (isset($_SERVER['HTTPS'])) ? 'https' : 'http',
				'domain'        => $_SERVER['HTTP_HOST'],
				'base'					=> URL::base(),
				'path'          => Request::instance()->uri,
				'action'        => request::instance()->action,
				'controller'    => request::instance()->controller,
			),
			$this->xml_meta
		);

		// Create the content node
		$this->xml_content = $this->xml->appendChild($this->dom->createElement('content'));

	}

	public function before()
	{
	}

	/**
	 * Render the page - this is ran automaticly
	 *
	 * @return Boolean
	 */
	public function render()
	{
		$this->dom->insertBefore($this->dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $this->xslt_path . $this->xslt_stylesheet . '.xsl"'), $this->xml);

		$user_agent_trigger = FALSE;
		foreach (Kohana::config('xslt.user_agents') as $user_agent)
		{
			if (strpos($_SERVER['HTTP_USER_AGENT'], $user_agent)) $user_agent_trigger = TRUE;
		}

		if ($this->transform === TRUE || ($this->transform == 'auto' && $user_agent_trigger == TRUE))
		{
			$xslt = new DOMDocument;
			if (file_exists(getenv('DOCUMENT_ROOT').$this->xslt_path.$this->xslt_stylesheet.'.xsl'))
			{
				// If the stylesheet exists in the specified path, load it directly
				$xslt->load(getenv('DOCUMENT_ROOT').$this->xslt_path.$this->xslt_stylesheet.'.xsl');
			}
			else
			{
				// Else make a search for it

				// We need to load all theme modules
				foreach (scandir(MODPATH) as $modulePath)
				{
					if (substr($modulePath, 0, 5) == 'theme')
					{
						Kohana::modules(array($modulePath => MODPATH.$modulePath) + Kohana::modules());
					}
				}

				$xslt->load(Kohana::find_file(
					rtrim(preg_replace('/^'.str_replace('/', '\\/', Kohana::$base_url).'/', '', $this->xslt_path), '/'),
					$this->xslt_stylesheet,
					'xsl'
				));
			}

			$proc = new xsltprocessor();
			$proc->importStyleSheet($xslt);

			$html = $proc->transformToDoc($this->dom);
			echo $html->saveXML();
		}
		else
		{
			Request::instance()->headers['Content-Type'] = 'application/xml; encoding='.Kohana::$charset.';';
			echo $this->dom->saveXML();
		}

		return TRUE;
	}

	public function after()
	{
		if (Kohana::$profiling === TRUE)
		{
			xml::to_XML(
				array('benchmark' => Profiler::application()),
				$this->xml_meta
			);
		}

		if ($this->auto_render == TRUE)
		{
			// Render the template immediately after the controller method
			$this->render();
		}
	}

	/**
	 * Redirect to another URI. All further execution is terminated
	 *
	 * @param str $uri - If left out, redirects to previous uri.
	 */
	public function redirect($uri = FALSE)
	{
		if ($uri == FALSE)
		{
			if (isset($_SERVER['HTTP_REFERER']))
			{
				Request::instance()->redirect($_SERVER['HTTP_REFERER']);
			}
			else
			{
				Request::instance()->redirect(Kohana::$base_url);
			}
		}

		Request::instance()->redirect($uri);
	}

}
