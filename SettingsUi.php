<?php
/**
 * Plugin settings page handler.
 *
 * Created by RevZAP.
 * User: RevZAP
 * Date: 10.15.13
 * Time: 16:34
 */
class Anh_SettingsUi {
	private $settings;

	private $pageSlug = 'numbered-headings';
	private $pageTitle = 'Numbered Headings';

	private $group = 'anh_default_settings_group';
	private $optionName;

	public function __construct($settings, $optionName, $pageSlug = 'numbered-settings') {
		$this->pageSlug = $pageSlug;
		$this->optionName = $optionName;
		$this->settings = $settings;

		add_action('admin_init', array($this, 'initSettingsApi'));
		add_action('admin_menu', array($this, 'addSettingsPage'));
		add_filter('plugin_action_links_' . plugin_basename(WSANH_PLUGIN_FILE), array($this, 'addSettingsLink'));
	}

	public function initSettingsApi() {
		register_setting($this->group, $this->optionName, array($this, 'validateSettings'));

		$sectionId = 'anh_basic_settings';
		add_settings_section(
			$sectionId,
			'',
			'__return_null',
			$this->pageSlug
		);
		add_settings_field(
			'css',
			'Custom CSS',
			array($this, 'outputCssField'),
			$this->pageSlug,
			$sectionId
		);
	}

	public function addSettingsPage() {
		add_options_page(
			$this->pageTitle,
			$this->pageTitle,
			'manage_options',
	        $this->pageSlug,
			array($this, 'displaySettingsPage')
		);
	}

	public function displaySettingsPage() {
		//Pretty standard settings page code.
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo $this->pageTitle; ?></h2>
	        <form action="<?php echo esc_attr(admin_url('options.php')); ?>" method="post">
				<?php
		        settings_fields($this->group);
		        do_settings_sections($this->pageSlug);
		        submit_button();
		        ?>
			</form>
		</div>
		<?php
	}

	public function outputCssField() {
		printf(
			'<textarea name="%1$s[%2$s]" id="%2$s" cols="80" rows="10">%3$s</textarea>',
			esc_attr($this->optionName),
			'css',
			!empty($this->settings['css']) ? htmlentities($this->settings['css']) : ''
		);
	}

	public function validateSettings($input) {
		$input['css'] = trim(strval($input['css']));
		return array_merge($this->settings, $input);
	}

	/**
	 * Add "Settings" to the plugin's action links on the "Plugins" page.
	 *
	 * @param array $actionLinks List of existing action links.
	 * @return array Modified list of action links.
	 */
	public function addSettingsLink($actionLinks) {
		$actionLinks['settings'] = sprintf(
			'<a href="%s">Settings</a>',
			esc_attr($this->getSettingsPageUrl())
		);
		return $actionLinks;
	}

	public function getSettingsPageUrl() {
		return add_query_arg('page', $this->pageSlug, admin_url('options-general.php'));
	}
}
