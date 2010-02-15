<?php defined('SYSPATH') or die('No direct access allowed.');
/* $Id: user_guide.php 3209 2008-07-27 08:55:43Z Geert $ */

class User_Guide_Controller extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public function __construct()
	{
		parent::__construct();

		$this->lang = array
		(
			'benchmark' => array
			(
				'total_execution_time' => 'Total execution time of the application, starting at the earliest possible point',
				'base_classes_loading' => 'Time to load the core classes that are required for Kohana to run'
			),
			'event' => array
			(
				'system.ready'    => 'Basic system is prepared, but no routing has been performed',
				'system.shutdown' => 'Last event called before Kohana stops processing the current request'
			)
		);

		if ($this->uri->segment(2) == FALSE)
		{
			url::redirect('user_guide/'.current(explode('_', Kohana::config('locale.language'))).'/kohana/about');
		}
	}

	public function _remap()
	{
		$language = strtolower($this->uri->segment(2));
		$category = strtolower($this->uri->segment(3));
		$section  = strtolower($this->uri->segment(4));

		// Media resource loading
		if ($language === 'js' OR $language === 'css')
		{
			return $this->$language($category);
		}
		elseif ($section == FALSE)
		{
			url::redirect('user_guide/'.$language.'/kohana/about');
		}

		// Reset the language to non-localized language
		$language = current(explode('_', $language));

		// Set the view that will be loaded
		$category = ($category == FALSE)  ? 'kohana' : $category;
		$content  = 'user_guide/'.$language.'/'.$category.'/'.$section;
		$content  = rtrim($content, '/');

		try
		{
			// Display output
			$this->load->view('user_guide/template', array
			(
				'category' => $category,
				'section'  => $section,
				'language' => $language,
				'content'  => $this->load->view($content)->render(FALSE, array($this, '_tags'))
			))->render(TRUE);
		}
		catch (Kohana_Exception $exception)
		{
			trigger_error('This Kohana User Guide page has not been translated into the requested language.', E_USER_ERROR);
		}
	}

	public function _tags($output)
	{
		// Load markdown
		require Kohana::find_file('vendor', 'Markdown');

		$output = Markdown($output);

		$output = str_replace(array
		(
			'<pre><code>', '</code></pre>',
			'<code>', '</code>'
		), array
		(
			'<pre class="prettyprint">', '</pre>',
			'<code class="prettyprint">', '</code>'
		),
		$output);

		$output = preg_replace_callback('!<(benchmark|config|definition|event|file)>.+?</[^>]+>!', array($this, '_tag_update'), $output);

		return $output;
	}

	public function _tag_update($match)
	{
		preg_match('!^<([^>]+)>(.+?)</[^>]+>$!', $match[0], $tag);

		$type = $tag[2];
		$tag  = $tag[1];

		switch ($tag)
		{
			case 'benchmark':
				return isset($this->lang['benchmark'][$type]) ? '<abbr title="Benchmark: '.$this->lang['benchmark'][$type].'">'.$type.'</abbr>' : $type;
			case 'config':
				return '<tt class="config">'.$type.'</tt>';
			case 'definition':
				return html::anchor('user_guide/general/definitions?search='.$type, $type);
			case 'event':
				return isset($this->lang['event'][$type]) ? '<abbr title="Event: '.$this->lang['event'][$type].'">'.$type.'</abbr>' : $type;
			case 'file':
				return '<tt class="filename">'.$type.EXT.'</tt>';
		}
	}

	public function js($filename)
	{
		header('Content-Type: text/javascript');

		$this->_media('js', $filename);
	}

	public function css($filename)
	{
		header('Content-Type: text/css');

		$this->_media('css', $filename);
	}

	private function _media($type, $filename)
	{
		/**
		 * @todo Enable Caching
		 */
		try
		{
			$this->load->view('user_guide/media/'.$type.'/'.$filename)->render(TRUE);
		}
		catch (Kohana_Exception $exception)
		{
			print '/* script not found */';
		}
	}

} // End User_guide Controller