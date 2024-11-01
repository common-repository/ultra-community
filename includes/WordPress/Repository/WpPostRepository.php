<?php

namespace UltraCommunity\MchLib\WordPress\Repository;

use WP_Post;

class WpPostRepository
{
	/**
	 * @param int   $pageNumber
	 * @param int   $postsPerPage
	 * @param array $arrAdditionalQueryOptions
	 *
	 * @return \WP_Query
	 */
	public static function getPostsPreparedQuery( $pageNumber = 1, $postsPerPage = 10, $arrAdditionalQueryOptions = array())
	{
		(($pageNumber = (int)$pageNumber) > 0) ?: $pageNumber = 1;

		$arrOptions = array(
			//'author' => (int)$userId,
			'paged'                  => (int)$pageNumber,
			'posts_per_page'         => (int)$postsPerPage,
			'post_status'            => 'publish',
			'post_type'              => 'post',
			'orderby'                => 'date',
			'has_password'           => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			//'no_found_rows' => true,
		);

		empty($arrAdditionalQueryOptions) ?: $arrOptions = \wp_parse_args( $arrAdditionalQueryOptions, $arrOptions );


		return new \WP_Query($arrOptions);
	}

	/**
	 * @param       $userId
	 * @param int   $pageNumber
	 * @param int   $postsPerPage
	 * @param array $arrAdditionalQueryOptions
	 *
	 * @return \WP_Query
	 */
	public static function getUserPostsPreparedQuery($userId, $pageNumber = 1, $postsPerPage = 10, $arrAdditionalQueryOptions = array())
	{
//		$arrOptions = array(
//			'author' => (int)$userId,
//		);

		//empty($arrAdditionalQueryOptions) ?: $arrOptions = wp_parse_args( $arrAdditionalQueryOptions, $arrOptions );

		$arrAdditionalQueryOptions['author'] = (int)$userId;

		return self::getPostsPreparedQuery($pageNumber, $postsPerPage, $arrAdditionalQueryOptions);
	}

	/**
	 * @param       $userId
	 * @param int   $pageNumber
	 * @param int   $postsPerPage
	 * @param array $arrQueryOptions
	 *
	 * @return \WP_Post[]
	 */
	public static function getUserPosts($userId, $pageNumber = 1, $postsPerPage = 10, $arrQueryOptions = array())
	{
		(($pageNumber = (int)$pageNumber) > 0) ?: $pageNumber = 1;

		$arrOptions = array(
			'author' => (int)$userId,
			'paged' => $pageNumber,
			'posts_per_page' => (int)$postsPerPage,
			'post_status' => 'publish',
			'post_type' => 'post',
			'has_password' => false,

			'orderby'          => 'date',

			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,

			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		empty($arrQueryOptions) ?: $arrOptions = wp_parse_args( $arrQueryOptions, $arrOptions );

		return self::find($arrOptions);

	}

	public static function findByPostSlug($postSlug, $postType = 'post')
	{
		$arrPosts = self::find(array('posts_per_page' => 1, 'name' => $postSlug, 'post_type' => $postType));
		return isset($arrPosts[0]) ? $arrPosts[0] : null;
	}

	public static function findByPostType($postType, array $arrAdditionalArgs = null)
	{
		!empty($arrAdditionalArgs) ?: $arrAdditionalArgs = array();

		return self::find(array_merge($arrAdditionalArgs, array('post_type' => $postType)));
	}

	/**
	 * @param $postId
	 *
	 * @return null|WP_Post
	 */
	public static function findByPostId($postId)
	{
		if(empty($postId))
			return null;

		return \get_post((int)$postId); // returns null if not found
	}

	public static function save(array $arrPostInfo)
	{
		foreach($arrPostInfo as $property =>  $propertyValue)
		{
			if(null === $propertyValue){
				unset($arrPostInfo[$property]);
			}
		}

		$postId = (empty($arrPostInfo['ID'])) ?  wp_insert_post($arrPostInfo, false) : wp_update_post($arrPostInfo, false);

		return ($postId instanceof \WP_Error) ? 0 : (int)$postId;
	}


	/**
	 * @param        $userId
	 * @param string $metaKey
	 * @param bool   $single
	 *
	 * @return mixed
	 */
	public static function getPostMeta($postId, $metaKey = '', $single = true)
	{
		$arrPostMeta =  \get_post_meta($postId, $metaKey, $single);

		if(empty($arrPostMeta)){
			return null;
		}

		return $arrPostMeta;

	}

	/**
	 * @param array $queryArgs
	 *
	 * @return \WP_Post[]
	 */
	private static function find(array $queryArgs)
	{

		$queryArgs = \array_merge(

				array(
					'post_status' => 'any',
					'posts_per_page' => -1,
					'orderby' => 'ID',
					'has_password' => false,
			),

			$queryArgs
		);

		return \get_posts($queryArgs);

		/* Default ARGS
		$args = array(
				'posts_per_page'   => 5,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'post',
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'	   => '',
				'author_name'	   => '',
				'post_status'      => 'publish',
				'suppress_filters' => true
		);*/



	}

	public static function delete($postId, $sendToTrash = false)
	{
		return \wp_delete_post($postId, !$sendToTrash);
	}



	private function __construct()
	{}

}