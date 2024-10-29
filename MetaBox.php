<?php
/**
 * This class implements our post editor widget.
 * The widget lets the user toggle automatic heading numbering.
 */
class Anh_MetaBox {
	private $id = 'ws_anh_post_setting';
	private $title = 'Heading Numbering';
	private $postTypes = array('post', 'page');

	public function __construct() {
		add_action('add_meta_boxes', array($this, 'registerPostMetaboxes'));
		add_action('save_post', array($this, 'saveMetaboxData'), 10, 2);
	}

	public function registerPostMetaboxes() {
		foreach($this->postTypes as $postType) {
			add_meta_box(
				$this->id,
				$this->title,
				array($this, 'outputMetabox'),
				$postType
			);
		}
	}

	public function outputMetabox(
		$post,
		/** @noinspection PhpUnusedParameterInspection */
		$box = null
	) {
		$numberingEnabled = get_post_meta($post->ID, '_anh_enable_numbering', true);
		$numberingEnabled = !empty($numberingEnabled);

		wp_nonce_field('anh_update_numbering_meta', 'anh_metabox_nonce');
		printf(
			'<p><label><input name="%1$s" id="%1$s" type="checkbox" %2$s> Enable automatic heading numbering</label></p>',
			'_anh_enable_numbering',
			checked($numberingEnabled, true, false)
		);
	}

	/**
	 * Save settings from our post meta box.
	 * This is a callback for the "save_post" action.
	 *
	 * Note: While WordPress.org docs claim that only post ID is passed to the callback,
	 * in practice a post data object is also passed in as the second argument (at least WP 3.0+).
	 *
	 * @param int $postId
	 * @param StdClass $post
	 */
	public function saveMetaboxData($postId, $post) {
		//If this is an auto-save our form has not been submitted and we don't need to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		//We only care about some post types.
		if ( !in_array($post->post_type, $this->postTypes) ) {
			return;
		}

		//Is the current user allowed to edit this post?
		$postType = get_post_type_object($post->post_type);
		if ( !isset($postType, $postType->cap) || !current_user_can($postType->cap->edit_post, $postId) ) {
			return;
		}

		//Set/clear the "enable numbering" flag.
		if ( isset($_POST['anh_metabox_nonce']) && wp_verify_nonce($_POST['anh_metabox_nonce'], 'anh_update_numbering_meta') ) {
			$numberingEnabled = isset($_POST['_anh_enable_numbering']) && !empty($_POST['_anh_enable_numbering']);
			if ( $numberingEnabled ) {
				update_post_meta($postId, '_anh_enable_numbering', true);
			} else {
				delete_post_meta($postId, '_anh_enable_numbering');
			}
		}
	}
}
