<?php
namespace UltraCommunity\PostsType;
use UltraCommunity\MchLib\WordPress\CustomPostType;

class CustomTabPostType extends CustomPostType
{
	public $PostUrl   = null;
	public $IconClass = null;

	public function __construct($postType, \WP_Post $wpPost = null)
	{
		parent::__construct($postType, $wpPost);
	}

	public function getAttributes()
	{
		return array(
			'labels'              => array(),
			'public'              => false,
			'exclude_from_search' => true,
			'show_ui'             => false,
			'show_in_admin_bar'   => false,
			'rewrite'             => false,
			'query_var'           => false,
			'revisions'           => false,
			'supports'            => array( 'title' ),
			);
	}


	public function toPublishArray()
	{
		return parent::toPublishArray();
	}

}