<?php
/*
Plugin Name: Numbered Headings
Plugin URI: http://www.reviewzap.com/
Description: Automatically add numbers to H3 headings. Just add h3 tags to titles, activate this plugin and enable it from individual post to automatically add numbers. The settings can be easily changed from h3 to any other tag.
Version: 1.0.1
Author: Manish M
Author URI: http://profiles.wordpress.org/revzap
*/

define('WSANH_PLUGIN_FILE', dirname(__FILE__));

require dirname(__FILE__) . '/MetaBox.php';
require dirname(__FILE__) . '/SettingsUi.php';

class Anh_Plugin {
	private $metabox;
	private $currentNumber = 0;

	private $optionName = 'anh_numbering_settings';
	private $settings = array();
	private $settingsUi;

	public function __construct() {
		$this->settings = get_option($this->optionName, array('css' => ''));

		$this->metabox = new Anh_MetaBox();
		$this->settingsUi = new Anh_SettingsUi($this->settings, $this->optionName);

		add_filter('the_content', array($this, 'addNumbersToHeadings'));
		add_action('wp_print_scripts', array($this, 'outputCustomCss'));
	}

	/**
	 * Automatically number all H3 headings in a post. Numbering is off by default
	 * and users must manually enable it for each individual post.
	 *
	 * @param string $content Post content.
	 * @return string Modified post content.
	 */
	public function addNumbersToHeadings($content) {
		$post = get_post();
		//Check if the plugin is enabled for this post.
		if ( isset($post, $post->ID) && get_post_meta($post->ID, '_anh_enable_numbering', true) ) {
			$this->currentNumber = 0;
			$content = preg_replace_callback(
				'@(?P<open><h3[^>]*?>)(?P<content>.+?)(?P<close></h3>)@is',
				array($this, 'processHeading'),
				$content
			);
		}
		return $content;
	}

	/**
	 * Callback for the regex-replace in addNumbersToHeadings(). Actually adds the number.
	 *
	 * @param array $match Regex match elements.
	 * @return string Modified heading HTML.
	 */
	protected function processHeading($match) {
		//Ignore headings that already start with a number.
		$plainContent = trim(strip_tags($match['content']));
		if ( preg_match('@^\d+\.@', $plainContent) ) {
			return $match[0];
		}

		$this->currentNumber++;
		$heading = $match['open'] . $this->currentNumber . '. ' . $match['content'] . $match['close'];
		return $heading;
	}

	/**
	 * Output user-defined heading styles, if any.
	 */
	public function outputCustomCss() {
		$css = $this->settings['css'];
		if ( !empty($css) ) {
			printf('<style type="text/css">%s</style>', $css);
		}
	}
}

$ws_anh_plugin = new Anh_Plugin();