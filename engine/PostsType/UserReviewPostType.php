<?php


namespace UltraCommunity\PostsType;


use UltraCommunity\MchLib\WordPress\CustomPostType;

class UserReviewPostType extends CustomPostType
{
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
//'hierarchical'        => TRUE,
			'supports'            => array( 'title', 'excerpt', 'comments'),
		);
	}
	
	
	public function toPublishArray()
	{
		$arrAttributes = parent::toPublishArray();
		$arrAttributes['post_status'] = 'private';
		$arrAttributes['comment_status'] = 'open';
		
		!empty($arrAttributes['post_title']) ?: $arrAttributes['post_title'] = 'uc-user-review';
		
		return $arrAttributes;
	}
	
}