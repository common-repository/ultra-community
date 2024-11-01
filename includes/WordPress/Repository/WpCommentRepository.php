<?php
namespace UltraCommunity\MchLib\WordPress\Repository;


use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use WP_Comment;

class WpCommentRepository
{
	/**
	 * @param $commentId
	 *
	 * @return null|WP_Comment
	 */
	public static function getCommentById($commentId)
	{
		if(!MchValidator::isPositiveInteger($commentId))
			return null;

		return MchWpUtils::isWPComment($wpCommentObject = \get_comment($commentId)) ?  $wpCommentObject : null;

	}


}