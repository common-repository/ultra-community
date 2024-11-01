<?php

namespace UltraCommunity\MchLib\WordPress;

\class_exists('\Walker_Nav_Menu_Edit') || require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';

class NavigationMenuEditWalker extends \Walker_Nav_Menu_Edit
{

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu_Edit::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item.
	 * @param array $args
	 * @param int $id
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		parent::start_el( $output, $item, $depth, $args, $id );

		$output = preg_replace( '(<p[^>]+class="[^"]*field-description(.|\n)*?<\/p>)', "$1 \n" . $this->get_custom_fields( $item, $depth, $args ), $output, 1 );
	}


	/**
	 * Get custom fields
	 *
	 * @uses do_action() Calls 'menu_item_custom_fields' hook
	 *
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param array $args Menu item args.
	 *
	 * @return string Additional fields or html for the nav menu editor.
	 */
	protected function get_custom_fields( $item, $depth, $args = array() ) {
		ob_start();
		$item_id = intval( $item->ID );


		do_action( 'mch_wp_nav_menu_item_custom_meta', $item_id, $item, $depth, $args );

		/**
		 * Get menu item custom fields from plugins/themes
		 *
		 * @param int $item_id post ID of menu
		 * @param object $item Menu item data object.
		 * @param int $depth Depth of menu item. Used for padding.
		 * @param array $args Menu item args.
		 *
		 * @return string Custom fields
		 */
		do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args );

		return ob_get_clean();
	}
}