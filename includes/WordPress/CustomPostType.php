<?php

namespace UltraCommunity\MchLib\WordPress;

abstract class CustomPostType
{
	public $PostId    = null;
	public $UserId    = null;
	public $PostType  = null;
	public $PostSlug  = null;
	public $PostTitle = null;
	public $PostContent = null;

	public abstract function getAttributes();

	public function __construct($postType, \WP_Post $wpPost = null)
	{
		$this->PostType = $postType;

		if(empty($wpPost->ID))
			return;

		$this->PostId      = $wpPost->ID;
		$this->PostSlug    = $wpPost->post_name;
		$this->PostTitle   = $wpPost->post_title;
		$this->UserId      = $wpPost->post_author;
		$this->PostContent = $wpPost->post_content;

	}


	public function toPublishArray()
	{
		$arrAttributes = array(

				'post_author'    => empty($this->UserId) ? get_current_user_id() : $this->UserId,
				'post_content'   => isset($this->PostContent) ? $this->PostContent : '',
				'post_title'     => isset($this->PostTitle) ? $this->PostTitle : '',
				'post_status'    => 'private',
				'post_type'      => $this->PostType,
				'comment_status' => 'closed',
				'ping_status'    => 'closed'

		);

		empty($this->PostId)   ?: $arrAttributes['ID'] = (int)$this->PostId;

		$this->PostSlug = trim($this->PostSlug);
		empty($this->PostSlug) ?: $arrAttributes['post_name'] = $this->PostSlug;

		return $arrAttributes;
	}

}