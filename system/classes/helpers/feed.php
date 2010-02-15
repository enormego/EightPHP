<?php
/**
 * Feed helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class feed_Core {

	/**
	 * Parses a remote feed into an array.
	 *
	 * @param   string   remote feed URL
	 * @param   integer  item limit to fetch
	 * @return  array
	 */
	public static function parse($feed, $limit = 0) {
		// Make limit an integer
		$limit = (int) $limit;

		// Disable error reporting while opening the feed
		$ER = error_reporting(0);

		// Allow loading by filename/url or raw XML string
		if(valid::url($feed)) {
			$feed = remote::get($feed, 45);
			$feed = $feed['content'];
		} elseif(is_file($feed)) {
			$feed = file_get_contents($feed);
		}

		// Double check we have something to work with
		if(empty($feed)) {
			return FALSE;
		}
		
		// Load the feed
		$feed = simplexml_load_string($feed, 'SimpleXMLElement', LIBXML_NOCDATA);

		// Restore error reporting
		error_reporting($ER);

		// Feed could not be loaded
		if($feed === NO)
			return array();

		// Detect the feed type. RSS 1.0/2.0 and Atom 1.0 are supported.
		$feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;

		$i = 0;
		$items = array();

		foreach($feed as $item) {
			if($limit > 0 and $i++ === $limit)
				break;

			$items[] = (array) $item;
		}

		return $items;
	}

	/**
	 * Creates a feed from the given parameters.
	 *
	 * @param   array   feed information
	 * @param   array   items to add to the feed
	 * @return  string
	 */
	public static function create($info, $items, $format = 'rss2') {
		$info += array('title' => 'Generated Feed', 'link' => '', 'generator' => 'EightPHP');

		$feed = '<?xml version="1.0"?><rss version="2.0"><channel></channel></rss>';
		$feed = simplexml_load_string($feed);

		foreach($info as $name => $value) {
			if(($name === 'pubDate' OR $name === 'lastBuildDate') and (is_int($value) OR ctype_digit($value))) {
				// Convert timestamps to RFC 822 formatted dates
				$value = date(DATE_RFC822, $value);
			} elseif(($name === 'link' OR $name === 'docs') and strpos($value, '://') === NO) {
				// Convert URIs to URLs
				$value = url::site($value, 'http');
			}

			// Add the info to the channel
			$feed->channel->addChild($name, $value);
		}

		foreach($items as $item) {
			// Add the item to the channel
			$row = $feed->channel->addChild('item');

			foreach($item as $name => $value) {
				if($name === 'pubDate' and (is_int($value) OR ctype_digit($value))) {
					// Convert timestamps to RFC 822 formatted dates
					$value = date(DATE_RFC822, $value);
				} elseif(($name === 'link' OR $name === 'guid') and strpos($value, '://') === NO) {
					// Convert URIs to URLs
					$value = url::site($value, 'http');
				}

				// Add the info to the row
				$row->addChild($name, $value);
			}
		}

		return $feed->asXML();
	}

} // End feed