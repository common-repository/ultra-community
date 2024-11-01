<?php
namespace UltraCommunity\Entities;

use UltraCommunity\Controllers\TemplatesController;

class PageActionEntity
{
	CONST TYPE_GROUP_JOIN_FLAT_BUTTON   = 1;
	CONST TYPE_GROUP_LEAVE_FLAT_BUTTON  = 2;
	CONST TYPE_GROUP_MANAGE_FLAT_BUTTON = 3;

	CONST TYPE_GROUP_JOIN_ICON_BUTTON   = -1;
	CONST TYPE_GROUP_LEAVE_ICON_BUTTON  = -2;
	CONST TYPE_GROUP_MANAGE_ICON_BUTTON = -3;

	CONST TYPE_GROUP_BLOCK_USER_FLAT_BUTTON        = 4;
	CONST TYPE_GROUP_UNBLOCK_USER_FLAT_BUTTON      = 5;
	CONST TYPE_GROUP_DELETE_USER_FLAT_BUTTON       = 6;


	CONST TYPE_GROUP_BLOCK_USER_ICON_BUTTON        = -4;
	CONST TYPE_GROUP_UNBLOCK_USER_ICON_BUTTON      = -5;
	CONST TYPE_GROUP_DELETE_USER_ICON_BUTTON       = -6;

	CONST TYPE_GROUP_ACCEPT_USER_JOIN_REQUEST_FLAT_BUTTON  = 7;
	CONST TYPE_GROUP_DECLINE_USER_JOIN_REQUEST_FLAT_BUTTON = 8;

	CONST TYPE_GROUP_ACCEPT_USER_JOIN_REQUEST_ICON_BUTTON  = -7;
	CONST TYPE_GROUP_DECLINE_USER_JOIN_REQUEST_ICON_BUTTON = -8;



	CONST TYPE_USER_ADD_FRIEND_FLAT_BUTTON = 9;
	CONST TYPE_USER_UN_FRIEND_FLAT_BUTTON  = 10;

	CONST TYPE_USER_ADD_FRIEND_ICON_BUTTON = -9;
	CONST TYPE_USER_UN_FRIEND_ICON_BUTTON  = -10;


	CONST TYPE_USER_ACCEPT_FRIENDSHIP_FLAT_BUTTON  = 11;
	CONST TYPE_USER_DECLINE_FRIENDSHIP_FLAT_BUTTON = 12;

	CONST TYPE_USER_ACCEPT_FRIENDSHIP_ICON_BUTTON  = -11;
	CONST TYPE_USER_DECLINE_FRIENDSHIP_ICON_BUTTON = -12;


	CONST TYPE_USER_FOLLOW_FLAT_BUTTON     = 13;
	CONST TYPE_USER_UN_FOLLOW_FLAT_BUTTON  = 14;

	CONST TYPE_USER_FOLLOW_ICON_BUTTON     = -13;
	CONST TYPE_USER_UN_FOLLOW_ICON_BUTTON  = -14;

	public $ActionTargetId     = null;
	public $ActionTargetUrl    = null;
	public $ActionText         = null;
	public $ActionType         = null;

	public $ElementDataAction  = null;

	public $ElementClasses     = array();
	public $ElementIconClasses = array();
	public $ElementSvgIcon     = null;

	public $ToolTipText    = null;
	public $ToolTipFlow    = 'down';

	public function __construct($actionTargetId = null, $actionType = null, $actionTargetUrl = null, $actionText = null)
	{
		$this->ActionTargetId  = $actionTargetId;
		$this->ActionTargetUrl = $actionTargetUrl;
		$this->ActionText      = $actionText;
		$this->ActionType      = $actionType;

	}

	public function getOutputContent()
	{

		$clonedPageAction = clone $this;

		empty($clonedPageAction->ActionText)        ?: $clonedPageAction->ActionText        = esc_html($clonedPageAction->ActionText);
		empty($clonedPageAction->ActionTargetId)    ?: $clonedPageAction->ActionTargetId    = esc_html($clonedPageAction->ActionTargetId);
		empty($clonedPageAction->ActionTargetUrl)   ?: $clonedPageAction->ActionTargetUrl   = esc_url($clonedPageAction->ActionTargetUrl);
		empty($clonedPageAction->ElementDataAction) ?: $clonedPageAction->ElementDataAction = sanitize_html_class($clonedPageAction->ElementDataAction);

		$templateFileName = ($clonedPageAction->ActionType < 0 ? 'icon-button-action' : 'flat-button-action');

		$arrActionElementClasses = array('uc-button', 'uc-button-primary---', 'uc-button-action', 'uc-button-action-transparent',  'uc-button-icon-action', 'uc-button-action-gray');

		if($clonedPageAction->ActionType < 0)
		{
			$arrActionElementClasses = array_diff($arrActionElementClasses, array('uc-button-action', 'uc-button-action-transparent', 'uc-button-action-gray'));

			if(in_array($clonedPageAction->ActionType, array(
				self::TYPE_GROUP_LEAVE_ICON_BUTTON,
				self::TYPE_USER_UN_FRIEND_ICON_BUTTON,
				self::TYPE_USER_UN_FOLLOW_ICON_BUTTON,
				self::TYPE_USER_DECLINE_FRIENDSHIP_ICON_BUTTON,
				//self::TYPE_GROUP_LEAVE_ICON_BUTTON
			))){
				$arrActionElementClasses[] = 'uc-button-action-gray';
			}

			//$arrActionElementClasses = array_diff($arrActionElementClasses, array('uc-button-action', 'uc-button-action-transparent'));

		}

		switch ($clonedPageAction->ActionType)
		{
			case self::TYPE_GROUP_JOIN_FLAT_BUTTON :
			case self::TYPE_GROUP_MANAGE_FLAT_BUTTON:
			case self::TYPE_USER_ADD_FRIEND_FLAT_BUTTON:
			case self::TYPE_USER_FOLLOW_FLAT_BUTTON:
			case self::TYPE_USER_ACCEPT_FRIENDSHIP_FLAT_BUTTON:

			case self::TYPE_GROUP_ACCEPT_USER_JOIN_REQUEST_FLAT_BUTTON:

			//$clonedPageAction->ElementClasses = array_merge(array('uc-button', 'uc-button-primary', 'uc-button-action', 'uc-button-action-transparent'), $clonedPageAction->ElementClasses);

			$arrActionElementClasses = array_diff($arrActionElementClasses, array('uc-button-icon-action', 'uc-button-action-gray'));

						break;

			case self::TYPE_USER_UN_FRIEND_FLAT_BUTTON:
			case self::TYPE_USER_UN_FOLLOW_FLAT_BUTTON:
			case self::TYPE_GROUP_LEAVE_FLAT_BUTTON:
			case self::TYPE_GROUP_DECLINE_USER_JOIN_REQUEST_FLAT_BUTTON:
			case self::TYPE_USER_DECLINE_FRIENDSHIP_FLAT_BUTTON:
				//$clonedPageAction->ElementClasses = array_merge(array('uc-button', 'uc-button-primary', 'uc-button-action', 'uc-button-action-gray'), $clonedPageAction->ElementClasses);
			$arrActionElementClasses = array_diff($arrActionElementClasses, array('uc-button-action-transparent',  'uc-button-icon-action', 'uc-button-primary'));



					break;


			case self::TYPE_GROUP_BLOCK_USER_ICON_BUTTON:
			case self::TYPE_GROUP_UNBLOCK_USER_ICON_BUTTON:
			case self::TYPE_GROUP_DELETE_USER_ICON_BUTTON:

				//$clonedPageAction->ElementClasses = array_merge(array('uc-button', 'uc-button-primary', 'uc-button-danger',  'uc-button-icon-action',), $clonedPageAction->ElementClasses);
			$arrActionElementClasses[] =  'uc-button-danger';
					break;
		}


		$clonedPageAction->ElementClasses = array_merge($arrActionElementClasses, $clonedPageAction->ElementClasses);

		switch ($clonedPageAction->ActionType)
		{
			case self::TYPE_GROUP_JOIN_FLAT_BUTTON :
			case self::TYPE_GROUP_JOIN_ICON_BUTTON :
				$clonedPageAction->ElementDataAction  = 'userJoinGroup';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-sign-in');

				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Join Group', 'ultra-community');

				break;

			case self::TYPE_GROUP_LEAVE_FLAT_BUTTON:
			case self::TYPE_GROUP_LEAVE_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userLeaveGroup';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-sign-out');

				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Leave Group', 'ultra-community');

				break;

			case self::TYPE_GROUP_MANAGE_FLAT_BUTTON:
			case self::TYPE_GROUP_MANAGE_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userManageGroup';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-cog');

				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Manage Group', 'ultra-community');

				break;

			case self::TYPE_USER_ADD_FRIEND_FLAT_BUTTON:
			case self::TYPE_USER_ADD_FRIEND_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userFriendRequest';
				$clonedPageAction->ElementSvgIcon = $this->getSvgIcon('user-fa-add-friend-solid');

				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Add Friend', 'ultra-community');

				break;

			case self::TYPE_USER_UN_FRIEND_FLAT_BUTTON:
			case self::TYPE_USER_UN_FRIEND_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userUnFriend';
				$clonedPageAction->ElementSvgIcon = $this->getSvgIcon('user-fa-remove-friend-solid');

				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Unfriend', 'ultra-community');

				break;

			case self::TYPE_USER_FOLLOW_FLAT_BUTTON:
			case self::TYPE_USER_FOLLOW_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userFollow';
				$clonedPageAction->ElementSvgIcon = $this->getSvgIcon('user-follow-solid');

				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Follow', 'ultra-community');

				break;

			case self::TYPE_USER_UN_FOLLOW_FLAT_BUTTON:
			case self::TYPE_USER_UN_FOLLOW_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userUnFollow';
				$clonedPageAction->ElementSvgIcon = $this->getSvgIcon('user-unfollow-solid');
				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Unfollow', 'ultra-community');

				break;

			case self::TYPE_USER_ACCEPT_FRIENDSHIP_FLAT_BUTTON:
			case self::TYPE_USER_ACCEPT_FRIENDSHIP_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userAcceptFriendship';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-thumbs-o-up');
				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Accept', 'ultra-community');

				break;

			case self::TYPE_USER_DECLINE_FRIENDSHIP_FLAT_BUTTON:
			case self::TYPE_USER_DECLINE_FRIENDSHIP_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'userDeclineFriendship';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-thumbs-o-down');
				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Decline', 'ultra-community');

				break;


			case self::TYPE_GROUP_ACCEPT_USER_JOIN_REQUEST_FLAT_BUTTON:
			case self::TYPE_GROUP_ACCEPT_USER_JOIN_REQUEST_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'groupAcceptUserJoinRequest';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-thumbs-o-up');
				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Accept', 'ultra-community');

				break;

			case self::TYPE_GROUP_DECLINE_USER_JOIN_REQUEST_FLAT_BUTTON:
			case self::TYPE_GROUP_DECLINE_USER_JOIN_REQUEST_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'groupDeclineUserJoinRequest';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-thumbs-o-down');
				!empty($clonedPageAction->ActionText) ?: $clonedPageAction->ActionText = esc_html__('Decline', 'ultra-community');

				break;


			case self::TYPE_GROUP_BLOCK_USER_ICON_BUTTON:
			case self::TYPE_GROUP_BLOCK_USER_FLAT_BUTTON:
				$clonedPageAction->ElementDataAction  = 'groupBlockUser';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-ban');

				break;

			case self::TYPE_GROUP_UNBLOCK_USER_FLAT_BUTTON:
			case self::TYPE_GROUP_UNBLOCK_USER_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'groupUnBlockUser';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-unlock');

				break;

			case self::TYPE_GROUP_DELETE_USER_FLAT_BUTTON:
			case self::TYPE_GROUP_DELETE_USER_ICON_BUTTON:
				$clonedPageAction->ElementDataAction  = 'groupDeleteUser';
				$clonedPageAction->ElementIconClasses = array('fa', 'fa-trash');

				break;


		}


		$clonedPageAction->ElementClasses     =  array_map('sanitize_html_class', (array)$clonedPageAction->ElementClasses);
		$clonedPageAction->ElementIconClasses =  array_map('sanitize_html_class', (array)$clonedPageAction->ElementIconClasses);

		$clonedPageAction->ElementClasses     = implode(' ', $clonedPageAction->ElementClasses);
		$clonedPageAction->ElementIconClasses = implode(' ', $clonedPageAction->ElementIconClasses);


		$clonedPageAction->ElementDataAction  = 'data-uc-trigger-login = "' . $clonedPageAction->ElementDataAction . '"';

		empty($clonedPageAction->ActionTargetId)  ?: $clonedPageAction->ElementDataAction .= ' data-action-target-id  = "' . $clonedPageAction->ActionTargetId  . '"';
		empty($clonedPageAction->ActionTargetUrl) ?: $clonedPageAction->ElementDataAction .= ' data-action-target-url = "' . $clonedPageAction->ActionTargetUrl . '"';
//		empty($clonedPageAction->ToolTipText)     ?: $clonedPageAction->ElementDataAction .= ' uc-tooltip = "' . $clonedPageAction->ToolTipText . '"  uc-tooltip-flow = "' . $clonedPageAction->ToolTipFlow . '"';


		if($clonedPageAction->ActionType < 0)
		{
			!empty($clonedPageAction->ToolTipText) ?: $clonedPageAction->ToolTipText = $clonedPageAction->ActionText;
			if(!empty($clonedPageAction->ToolTipText))
			{
				$clonedPageAction->ElementDataAction .= ' uc-tooltip = "' . $clonedPageAction->ToolTipText . '"  uc-tooltip-flow = "' . $clonedPageAction->ToolTipFlow . '"';
				$clonedPageAction->ActionText = null;
			}

		}


		return TemplatesController::getTemplateOutputContent($templateFileName, array('pageAction' => $clonedPageAction));

	}


	public static function getSvgIcon($iconKey)
	{

		switch($iconKey)
		{

			case 'add-user-solid' : return '<svg class="uc-svg add-user-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.985 15.986v-2.746h1.268v2.746h2.747v1.268h-2.747v2.746h-1.268v-2.746h-2.747v-1.268h2.747zM17.032 12.006c-0.681-0.34-1.45-0.532-2.263-0.532-2.8 0-5.071 2.27-5.071 5.070 0 0.88 0.224 1.707 0.618 2.428-0.26 0.027-0.525 0.041-0.792 0.041-4.156 0-7.525-3.369-7.525-7.524s3.369 0 7.525 0c4.156 0 7.525-4.155 7.525 0 0 0.173-0.006 0.345-0.017 0.516zM9.598 9.643c-2.663 0-4.822-2.159-4.822-4.821s2.159-4.821 4.822-4.821c2.663 0 4.822 2.159 4.822 4.821s-2.159 4.821-4.822 4.821z"></path></svg>';
			case 'mixed-users-solid' : return '<svg class="uc-svg mixed-users-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.079 12.278c-0.697-0.348-1.482-0.544-2.314-0.544-2.864 0-5.186 2.322-5.186 5.185 0 0.9 0.229 1.746 0.632 2.483-0.266 0.028-0.537 0.042-0.81 0.042-4.25 0-7.696-3.445-7.696-7.695s3.446 0 7.696 0c4.25 0 7.696-4.25 7.696 0 0 0.177-0.006 0.353-0.018 0.528zM9.476 9.862c-2.723 0-4.931-2.208-4.931-4.931s2.208-4.931 4.931-4.931c2.723 0 4.931 2.208 4.931 4.931s-2.208 4.931-4.931 4.931zM16.704 17.954v-1.136l1.591 1.591-1.591 1.591v-1.136h-0.909c-0.12 0-0.236-0.048-0.321-0.133l-1.269-1.269-1.269 1.269c-0.085 0.085-0.201 0.133-0.321 0.133h-1.364v-0.909h1.175l1.136-1.136-1.136-1.136h-1.175v-0.909h1.364c0.12 0 0.236 0.048 0.321 0.133l1.269 1.269 1.269-1.269c0.085-0.085 0.201-0.133 0.321-0.133h0.909v-1.136l1.591 1.591-1.591 1.591v-1.136h-0.721l-1.136 1.136 1.136 1.136h0.721z"></path></svg>';
			case 'user-check-solid' : return '<svg class="uc-svg user-check-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.4 18.165l3.773-3.773 0.939 0.939-3.773 3.773 0.012 0.012-0.884 0.884-2.816-2.816 0.884-0.884 1.865 1.865zM16.727 11.953c-0.672-0.339-1.431-0.529-2.234-0.529-2.765 0-5.006 2.26-5.006 5.048 0 0.876 0.221 1.7 0.61 2.418-0.257 0.027-0.518 0.041-0.782 0.041-4.103 0-7.428-3.354-7.428-7.491s3.326 0 7.428 0c4.103 0 7.428-4.137 7.428 0 0 0.173-0.006 0.344-0.017 0.514zM9.388 9.6c-2.629 0-4.76-2.149-4.76-4.8s2.131-4.8 4.76-4.8c2.629 0 4.76 2.149 4.76 4.8s-2.131 4.8-4.76 4.8z"></path></svg>';
			case 'user-edit-solid' : return '<svg class="uc-svg user-edit-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M16.749 11.991c0.011-0.167 0.017-0.336 0.017-0.506 0-4.076-3.305 0-7.382 0s-7.382-4.076-7.382 0c0 4.076 3.305 7.381 7.382 7.381 0.262 0 0.522-0.014 0.777-0.040-0.387-0.707-0.606-1.519-0.606-2.382 0-2.747 2.227-4.974 4.974-4.974 0.798 0 1.552 0.188 2.22 0.521zM9.456 9.673c-2.612 0-4.73-2.118-4.73-4.73s2.118-4.73 4.73-4.73c2.612 0 4.73 2.118 4.73 4.73s-2.118 4.73-4.73 4.73zM16.976 13.294c0.564 0 1.022 0.457 1.022 1.022 0 0.23-0.076 0.442-0.204 0.613l-0.409 0.409-1.431-1.431 0.409-0.409c0.171-0.128 0.383-0.204 0.613-0.204zM12.003 17.946l-0.409 1.839 1.839-0.409 3.781-3.781-1.431-1.431-3.781 3.781zM16.115 15.608l-2.861 2.861-0.352-0.352 2.861-2.861 0.352 0.352z"></path></svg>';
			case 'user-fa-add-friend-solid' : return '<svg class="uc-svg user-fa-add-friend-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M19.5 8.5h-2v-2c0-0.275-0.225-0.5-0.5-0.5h-1c-0.275 0-0.5 0.225-0.5 0.5v2h-2c-0.275 0-0.5 0.225-0.5 0.5v1c0 0.275 0.225 0.5 0.5 0.5h2v2c0 0.275 0.225 0.5 0.5 0.5h1c0.275 0 0.5-0.225 0.5-0.5v-2h2c0.275 0 0.5-0.225 0.5-0.5v-1c0-0.275-0.225-0.5-0.5-0.5zM7 10c2.209 0 4-1.791 4-4s-1.791-4-4-4-4 1.791-4 4 1.791 4 4 4zM9.8 11h-0.522c-0.694 0.319-1.466 0.5-2.278 0.5s-1.581-0.181-2.278-0.5h-0.522c-2.319 0-4.2 1.881-4.2 4.2v1.3c0 0.828 0.672 1.5 1.5 1.5h11c0.828 0 1.5-0.672 1.5-1.5v-1.3c0-2.319-1.881-4.2-4.2-4.2z"></path></svg>';
			case 'user-fa-friend-check-solid' : return '<svg class="uc-svg user-fa-friend-check-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M7 10c2.209 0 4-1.791 4-4s-1.791-4-4-4-4 1.791-4 4 1.791 4 4 4zM9.8 11h-0.522c-0.694 0.319-1.466 0.5-2.278 0.5s-1.581-0.181-2.278-0.5h-0.522c-2.319 0-4.2 1.881-4.2 4.2v1.3c0 0.828 0.672 1.5 1.5 1.5h11c0.828 0 1.5-0.672 1.5-1.5v-1.3c0-2.319-1.881-4.2-4.2-4.2zM19.894 6.988l-0.869-0.878c-0.144-0.147-0.378-0.147-0.525-0.003l-3.275 3.25-1.422-1.431c-0.144-0.147-0.378-0.147-0.525-0.003l-0.878 0.872c-0.147 0.144-0.147 0.378-0.003 0.525l2.553 2.572c0.144 0.147 0.378 0.147 0.525 0.003l4.416-4.381c0.144-0.147 0.147-0.381 0.003-0.525z"></path></svg>';
			case 'user-fa-friends-solid' : return '<svg class="uc-svg user-fa-friends-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M6 10c1.934 0 3.5-1.566 3.5-3.5s-1.566-3.5-3.5-3.5-3.5 1.566-3.5 3.5 1.566 3.5 3.5 3.5zM8.4 11h-0.259c-0.65 0.313-1.372 0.5-2.141 0.5s-1.487-0.188-2.141-0.5h-0.259c-1.987 0-3.6 1.612-3.6 3.6v0.9c0 0.828 0.672 1.5 1.5 1.5h9c0.828 0 1.5-0.672 1.5-1.5v-0.9c0-1.987-1.612-3.6-3.6-3.6zM15 10c1.656 0 3-1.344 3-3s-1.344-3-3-3-3 1.344-3 3 1.344 3 3 3zM16.5 11h-0.119c-0.434 0.15-0.894 0.25-1.381 0.25s-0.947-0.1-1.381-0.25h-0.119c-0.637 0-1.225 0.184-1.741 0.481 0.762 0.822 1.241 1.912 1.241 3.119v1.2c0 0.069-0.016 0.134-0.019 0.2h5.519c0.828 0 1.5-0.672 1.5-1.5 0-1.934-1.566-3.5-3.5-3.5z"></path></svg>';
			case 'user-fa-remove-friend-solid' : return '<svg class="uc-svg user-fa-remove-friend-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M19.5 8.5h-6c-0.275 0-0.5 0.225-0.5 0.5v1c0 0.275 0.225 0.5 0.5 0.5h6c0.275 0 0.5-0.225 0.5-0.5v-1c0-0.275-0.225-0.5-0.5-0.5zM7 10c2.209 0 4-1.791 4-4s-1.791-4-4-4-4 1.791-4 4 1.791 4 4 4zM9.8 11h-0.522c-0.694 0.319-1.466 0.5-2.278 0.5s-1.581-0.181-2.278-0.5h-0.522c-2.319 0-4.2 1.881-4.2 4.2v1.3c0 0.828 0.672 1.5 1.5 1.5h11c0.828 0 1.5-0.672 1.5-1.5v-1.3c0-2.319-1.881-4.2-4.2-4.2z"></path></svg>';
			case 'user-follow-solid' : return '<svg class="uc-svg user-follow-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.331 12.141c-0.689-0.344-1.466-0.538-2.288-0.538-2.832 0-5.128 2.296-5.128 5.127 0 0.89 0.227 1.726 0.625 2.455-0.263 0.028-0.531 0.042-0.801 0.042-4.203 0-7.609-3.406-7.609-7.609s3.407 0 7.609 0c4.203 0 7.609-4.202 7.609 0 0 0.175-0.006 0.349-0.018 0.522zM9.813 9.751c-2.693 0-4.876-2.183-4.876-4.875s2.183-4.875 4.876-4.875c2.693 0 4.876 2.183 4.876 4.875s-2.183 4.875-4.876 4.875zM15.366 14.893v-1.635l2.504 2.528-2.504 2.528v-1.671c-2.913-0.069-2.788 2-2.047 3.356-1.83-1.998-1.442-5.198 2.047-5.106z"></path></svg>';
			case 'user-location-solid' : return '<svg class="uc-svg user-location-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.425 11.874c0.011-0.169 0.017-0.339 0.017-0.51 0-4.11-3.332 0-7.442 0s-7.442-4.11-7.442 0c0 4.11 3.332 7.441 7.442 7.441 0.265 0 0.526-0.014 0.783-0.041-0.39-0.713-0.611-1.531-0.611-2.402 0-2.769 2.245-5.015 5.015-5.015 0.804 0 1.564 0.189 2.238 0.526zM10.072 9.537c-2.634 0-4.769-2.135-4.769-4.768s2.135-4.768 4.769-4.768c2.634 0 4.769 2.135 4.769 4.768s-2.135 4.768-4.769 4.768zM15.195 13.187c-1.153 0-2.088 0.953-2.088 2.129 0 2.129 2.088 4.684 2.088 4.684s2.088-2.555 2.088-4.684c0-1.176-0.935-2.129-2.088-2.129zM15.195 16.593c-0.692 0-1.253-0.572-1.253-1.277s0.561-1.277 1.253-1.277c0.692 0 1.253 0.572 1.253 1.277s-0.561 1.277-1.253 1.277z"></path></svg>';
			case 'user-love-solid' : return '<svg class="uc-svg user-love-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M16.994 12.258c-0.679-0.339-1.446-0.53-2.257-0.53-2.793 0-5.057 2.264-5.057 5.057 0 0.877 0.223 1.702 0.617 2.422-0.26 0.027-0.523 0.041-0.79 0.041-4.145 0-7.505-3.36-7.505-7.504s3.36 0 7.505 0c4.145 0 7.505-4.144 7.505 0 0 0.173-0.006 0.345-0.017 0.515zM9.58 9.902c-2.656 0-4.809-2.153-4.809-4.808s2.153-4.808 4.809-4.808c2.656 0 4.809 2.153 4.809 4.808s-2.153 4.808-4.809 4.808zM16.3 13.804h0c0.937 0 1.697 0.75 1.697 1.675 0 1.821-1.994 2.386-3.233 4.235-1.31-1.86-3.233-2.354-3.233-4.235 0-0.925 0.761-1.675 1.697-1.675 0.68 0 1.264 0.546 1.536 1.116 0.271-0.57 0.856-1.116 1.535-1.116z"></path></svg>';
			case 'user-remove-solid' : return '<svg class="uc-svg user-remove-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.031 12.498c-0.681-0.34-1.449-0.531-2.262-0.531-2.8 0-5.069 2.269-5.069 5.069 0 0.879 0.224 1.707 0.618 2.428-0.26 0.027-0.524 0.041-0.792 0.041-4.155 0-7.523-3.368-7.523-7.522s3.368 0 7.523 0c4.155 0 7.523-4.154 7.523 0 0 0.173-0.006 0.345-0.017 0.516zM9.598 10.136c-2.662 0-4.821-2.158-4.821-4.82s2.158-4.82 4.821-4.82c2.662 0 4.821 2.158 4.821 4.82s-2.158 4.82-4.821 4.82zM11.238 16.477h6.759v1.267h-6.759v-1.267z"></path></svg>';
			case 'user-reset-solid' : return '<svg class="uc-svg user-reset-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.031 12.019c0.012-0.17 0.017-0.342 0.017-0.516 0-4.154-3.368 0-7.523 0s-7.523-4.154-7.523 0c0 4.154 3.368 7.522 7.523 7.522 0.267 0 0.532-0.014 0.792-0.041-0.394-0.721-0.618-1.548-0.618-2.428 0-2.799 2.27-5.069 5.069-5.069 0.813 0 1.581 0.191 2.262 0.531zM9.598 9.657c-2.662 0-4.82-2.158-4.82-4.82s2.158-4.82 4.82-4.82c2.662 0 4.82 2.158 4.82 4.82s-2.158 4.82-4.82 4.82zM12.166 15.429h4.165v1.25l1.666-1.666-1.666-1.666v1.25h-4.998v2.499h0.833v-1.666zM17.165 17.9h-4.165v-1.25l-1.666 1.666 1.666 1.666v-1.25h4.998v-2.499h-0.833v1.666z"></path></svg>';
			case 'user-rotate-left-solid' : return '<svg class="uc-svg user-rotate-left-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.331 12.141c-0.689-0.344-1.466-0.538-2.288-0.538-2.832 0-5.128 2.296-5.128 5.127 0 0.89 0.227 1.726 0.625 2.455-0.263 0.028-0.531 0.042-0.801 0.042-4.203 0-7.609-3.406-7.609-7.609s3.407 0 7.609 0c4.203 0 7.609-4.202 7.609 0 0 0.175-0.006 0.349-0.018 0.522zM9.813 9.751c-2.693 0-4.876-2.183-4.876-4.875s2.183-4.875 4.876-4.875c2.693 0 4.876 2.183 4.876 4.875s-2.183 4.875-4.876 4.875zM14.746 18.364v1.635l-2.504-2.528 2.504-2.528v1.671c2.913 0.069 2.788-2 2.047-3.356 1.83 1.998 1.442 5.198-2.047 5.106z"></path></svg>';
			case 'user-rotate-right-solid' : return '<svg class="uc-svg user-rotate-right-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.331 12.141c-0.689-0.344-1.466-0.538-2.288-0.538-2.832 0-5.128 2.296-5.128 5.127 0 0.89 0.227 1.726 0.625 2.455-0.263 0.028-0.531 0.042-0.801 0.042-4.203 0-7.609-3.406-7.609-7.609s3.407 0 7.609 0c4.203 0 7.609-4.202 7.609 0 0 0.175-0.006 0.349-0.018 0.522zM9.813 9.751c-2.693 0-4.876-2.183-4.876-4.875s2.183-4.875 4.876-4.875c2.693 0 4.876 2.183 4.876 4.875s-2.183 4.875-4.876 4.875zM15.366 18.364c-3.488 0.092-3.877-3.109-2.047-5.106-0.742 1.356-0.866 3.425 2.047 3.356v-1.671l2.504 2.528-2.504 2.528v-1.635z"></path></svg>';
			case 'user-search-solid' : return '<svg class="uc-svg user-search-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.031 12.201c0.012-0.17 0.017-0.342 0.017-0.516 0-4.154-3.368 0-7.523 0s-7.523-4.154-7.523 0c0 4.154 3.368 7.522 7.523 7.522 0.267 0 0.532-0.014 0.792-0.041-0.394-0.721-0.618-1.548-0.618-2.428 0-2.8 2.27-5.069 5.069-5.069 0.813 0 1.581 0.191 2.262 0.531zM9.599 9.839c-2.662 0-4.821-2.158-4.821-4.82s2.158-4.82 4.821-4.82c2.662 0 4.821 2.158 4.821 4.82s-2.158 4.82-4.821 4.82zM17.805 18.866l-1.473-1.264c-0.152-0.138-0.315-0.202-0.447-0.196 0.348-0.411 0.558-0.944 0.558-1.526 0-1.299-1.044-2.352-2.333-2.352s-2.333 1.053-2.333 2.352c0 1.299 1.044 2.352 2.333 2.352 0.578 0 1.106-0.212 1.514-0.563-0.006 0.133 0.057 0.297 0.194 0.45l1.253 1.486c0.215 0.24 0.565 0.261 0.779 0.045s0.194-0.569-0.045-0.785l0-0zM14.11 17.449c-0.859 0-1.555-0.702-1.555-1.568s0.696-1.568 1.555-1.568c0.859 0 1.555 0.702 1.555 1.568s-0.696 1.568-1.555 1.568z"></path></svg>';
			case 'user-settings-solid' : return '<svg class="uc-svg user-settings-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M16.825 11.868c0.011-0.168 0.017-0.338 0.017-0.509 0-4.097-3.322 0-7.42 0s-7.42-4.097-7.42 0c0 4.097 3.322 7.419 7.42 7.419 0.264 0 0.524-0.014 0.781-0.041-0.389-0.711-0.61-1.527-0.61-2.394 0-2.761 2.239-5 5-5 0.802 0 1.56 0.189 2.231 0.524zM9.494 9.538c-2.626 0-4.754-2.128-4.754-4.754s2.129-4.754 4.754-4.754c2.626 0 4.754 2.128 4.754 4.754s-2.129 4.754-4.754 4.754zM17.518 17.225c-0.357-0.617-0.143-1.408 0.48-1.768l-0.669-1.156c-0.191 0.112-0.414 0.176-0.651 0.176-0.715 0-1.295-0.582-1.295-1.3h-1.339c0.002 0.222-0.054 0.446-0.173 0.652-0.357 0.617-1.152 0.827-1.776 0.47l-0.669 1.156c0.193 0.109 0.36 0.269 0.478 0.474 0.357 0.616 0.143 1.406-0.477 1.766l0.669 1.156c0.191-0.111 0.412-0.174 0.648-0.174 0.713 0 1.292 0.578 1.295 1.293h1.339c-0-0.219 0.055-0.442 0.173-0.645 0.357-0.616 1.15-0.827 1.773-0.471l0.669-1.156c-0.192-0.109-0.357-0.269-0.475-0.473zM14.711 17.949c-0.762 0-1.38-0.616-1.38-1.375s0.617-1.375 1.38-1.375c0.762 0 1.38 0.616 1.38 1.375s-0.617 1.375-1.38 1.375z"></path></svg>';
			case 'user-share-solid' : return '<svg class="uc-svg user-share-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.143 12.006c-0.681-0.34-1.45-0.532-2.263-0.532-2.8 0-5.071 2.27-5.071 5.070 0 0.88 0.224 1.707 0.618 2.428-0.26 0.027-0.525 0.041-0.792 0.041-4.156 0-7.525-3.369-7.525-7.524s3.369 0 7.525 0c4.156 0 7.525-4.155 7.525 0 0 0.173-0.006 0.345-0.017 0.516zM9.709 9.643c-2.663 0-4.822-2.159-4.822-4.821s2.159-4.821 4.822-4.821c2.663 0 4.822 2.159 4.822 4.821s-2.159 4.821-4.822 4.821zM16.847 17.916c0.575 0 1.042 0.466 1.042 1.042s-0.466 1.042-1.042 1.042c-0.575 0-1.042-0.466-1.042-1.042 0-0.056 0.004-0.111 0.013-0.165l-2.806-1.403c-0.189 0.196-0.455 0.318-0.749 0.318-0.575 0-1.042-0.466-1.042-1.042s0.466-1.042 1.042-1.042c0.294 0 0.559 0.122 0.749 0.318l2.806-1.403c-0.008-0.054-0.013-0.109-0.013-0.165 0-0.575 0.466-1.042 1.042-1.042s1.042 0.466 1.042 1.042c0 0.575-0.466 1.042-1.042 1.042-0.294 0-0.559-0.122-0.749-0.318l-2.806 1.403c0.009 0.054 0.013 0.109 0.013 0.165s-0.005 0.111-0.013 0.165l2.806 1.403c0.19-0.196 0.455-0.318 0.749-0.318z"></path></svg>';
			case 'user-solid' : return '<svg class="uc-svg user-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10.077 10.143c2.801 0 5.071-2.271 5.071-5.071s-2.271-5.071-5.071-5.071c-2.801 0-5.071 2.271-5.071 5.071s2.271 5.071 5.071 5.071zM10 20c4.371 0 7.914-3.543 7.914-7.914s-3.543 0-7.914 0c-4.371 0-7.914-4.371-7.914 0s3.543 7.914 7.914 7.914z"></path></svg>';
			case 'user-star-solid' : return '<svg class="uc-svg user-star-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M16.715 12.072c-0.667-0.333-1.419-0.52-2.215-0.52-2.741 0-4.963 2.222-4.963 4.962 0 0.861 0.219 1.671 0.605 2.377-0.255 0.027-0.513 0.040-0.775 0.040-4.067 0-7.365-3.297-7.365-7.364s3.297 0 7.365 0c4.067 0 7.365-4.067 7.365 0 0 0.17-0.006 0.338-0.017 0.505zM9.438 9.759c-2.606 0-4.719-2.113-4.719-4.719s2.113-4.719 4.719-4.719c2.606 0 4.719 2.113 4.719 4.719s-2.113 4.719-4.719 4.719zM17.998 15.646v0l-1.715 1.672 0.405 2.361-2.12-1.114-2.12 1.114 0.405-2.361-1.715-1.672 2.37-0.344 1.060-2.148 1.060 2.148 2.37 0.344z"></path></svg>';
			case 'user-unfollow-solid' : return '<svg class="uc-svg user-unfollow-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.331 12.141c-0.689-0.344-1.466-0.538-2.288-0.538-2.832 0-5.128 2.296-5.128 5.127 0 0.89 0.227 1.726 0.625 2.455-0.263 0.028-0.531 0.042-0.801 0.042-4.203 0-7.609-3.406-7.609-7.609s3.407 0 7.609 0c4.203 0 7.609-4.202 7.609 0 0 0.175-0.006 0.349-0.018 0.522zM9.813 9.751c-2.693 0-4.876-2.183-4.876-4.875s2.183-4.875 4.876-4.875c2.693 0 4.876 2.183 4.876 4.875s-2.183 4.875-4.876 4.875zM14.746 14.893c3.488-0.092 3.877 3.109 2.047 5.106 0.742-1.356 0.866-3.425-2.047-3.356v1.671l-2.504-2.528 2.504-2.528v1.635z"></path></svg>';
			case 'user-warning-solid' : return '<svg class="uc-svg user-warning-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M16.429 11.712c-0.654-0.326-1.391-0.51-2.172-0.51-2.688 0-4.867 2.179-4.867 4.866 0 0.844 0.215 1.638 0.593 2.33-0.25 0.026-0.503 0.040-0.76 0.040-3.989 0-7.222-3.233-7.222-7.221s3.233 0 7.222 0c3.989 0 7.222-3.988 7.222 0 0 0.166-0.006 0.332-0.017 0.495zM9.294 9.444c-2.556 0-4.628-2.072-4.628-4.627s2.072-4.627 4.628-4.627c2.556 0 4.628 2.072 4.628 4.627s-2.072 4.627-4.628 4.627zM14.265 19.811c-2.061 0-3.732-1.671-3.732-3.732s1.671-3.732 3.732-3.732c2.061 0 3.732 1.671 3.732 3.732s-1.671 3.732-3.732 3.732zM14.265 14.073c0.246 0 0.446 0.2 0.446 0.446v1.783c0 0.246-0.2 0.446-0.446 0.446s-0.446-0.2-0.446-0.446v-1.783c0-0.246 0.2-0.446 0.446-0.446zM14.265 18.084c-0.246 0-0.446-0.2-0.446-0.446s0.2-0.446 0.446-0.446c0.246 0 0.446 0.2 0.446 0.446s-0.2 0.446-0.446 0.446zM14.265 19.031c1.631 0 2.952-1.322 2.952-2.952s-1.322-2.952-2.952-2.952c-1.631 0-2.952 1.322-2.952 2.952s1.322 2.952 2.952 2.952z"></path></svg>';
			case 'users-solid' : return '<svg class="uc-svg users-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M4.876 7.168c-1.007 0-1.824-0.818-1.824-1.828s0.817-1.828 1.824-1.828c1.007 0 1.824 0.818 1.824 1.828s-0.817 1.828-1.824 1.828zM4.849 10.721c-1.572 0-2.847-1.277-2.847-2.852s1.274 0 2.847 0c1.572 0 2.847-1.575 2.847 0s-1.274 2.852-2.847 2.852zM12.924 10.093c-1.813 0-3.283-1.473-3.283-3.29s1.47-3.29 3.283-3.29c1.813 0 3.283 1.473 3.283 3.29s-1.47 3.29-3.283 3.29zM12.874 16.487c-2.83 0-5.124-2.299-5.124-5.134s2.294 0 5.124 0c2.83 0 5.124-2.836 5.124 0s-2.294 5.134-5.124 5.134z"></path></svg>';

			case 'group-users-solid' : return '<svg class="uc-svg users-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 11.304c1.201 0 2.288-0.487 3.075-1.274s1.273-1.873 1.273-3.074-0.487-2.288-1.273-3.074c-0.787-0.787-1.874-1.274-3.075-1.274s-2.288 0.487-3.075 1.274c-0.786 0.786-1.273 1.873-1.273 3.074s0.487 2.288 1.273 3.074c0.787 0.787 1.874 1.274 3.075 1.274z"></path>
												<path d="M16.956 12.174c0.6 0 1.143-0.243 1.537-0.636s0.637-0.937 0.637-1.538c0-0.6-0.243-1.143-0.637-1.537s-0.937-0.637-1.537-0.637c-0.601 0-1.144 0.243-1.538 0.637s-0.636 0.937-0.636 1.537c0 0.601 0.243 1.144 0.636 1.538s0.937 0.636 1.538 0.636z"></path>
												<path d="M16.956 12.687c-1.157 0-2.028 0.353-2.536 0.842-0.97-0.797-2.503-1.355-4.42-1.355-1.97 0-3.474 0.563-4.428 1.36-0.518-0.491-1.398-0.847-2.529-0.847-1.903 0-3.043 0.948-3.043 1.897 0 0.474 1.141 0.95 3.043 0.95 0.525 0 0.997-0.044 1.411-0.116-0.009 0.079-0.035 0.157-0.035 0.235 0 0.87 2.092 1.739 5.58 1.739 3.271 0 5.58-0.87 5.58-1.739 0-0.074-0.010-0.148-0.017-0.222 0.403 0.063 0.865 0.103 1.394 0.103 1.783 0 3.043-0.476 3.043-0.95 0-0.95-1.194-1.897-3.043-1.897z"></path>
												<path d="M3.044 12.174c0.6 0 1.143-0.243 1.537-0.637s0.637-0.937 0.637-1.537c0-0.599-0.243-1.143-0.637-1.537-0.394-0.393-0.937-0.637-1.537-0.637s-1.144 0.243-1.538 0.637c-0.393 0.395-0.636 0.938-0.636 1.537s0.243 1.143 0.636 1.537c0.394 0.394 0.937 0.637 1.538 0.637z"></path>
												</svg>';


			case 'forums-solid' : return '<svg class="uc-svg forums-solid" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M17.912 14.968c1.378-1.169 2.088-2.672 2.088-4.3 0-1.503-0.668-2.964-1.879-4.092 0.125 0.543 0.209 1.127 0.209 1.712 0 4.3-4.133 7.849-9.227 8.016 1.002 0.376 2.129 0.585 3.257 0.585 0.752 0 1.461-0.084 2.171-0.251l3.925 2.213c0.084 0.042 0.125 0.042 0.209 0.042s0.209-0.042 0.292-0.084c0.125-0.125 0.167-0.292 0.125-0.459l-1.169-3.382z"></path><path d="M17.536 8.288c0-3.966-3.925-7.181-8.768-7.181s-8.768 3.215-8.768 7.181c0 1.879 0.877 3.632 2.463 4.968l-1.461 3.966c-0.042 0.167 0 0.334 0.125 0.459 0.084 0.084 0.167 0.084 0.251 0.084s0.125 0 0.209-0.042l4.593-2.589c0.835 0.209 1.67 0.292 2.547 0.292 4.843 0.042 8.81-3.173 8.81-7.14zM5.679 4.196h6.138c0.251 0 0.418 0.167 0.418 0.418s-0.167 0.418-0.418 0.418h-6.138c-0.251 0-0.418-0.167-0.418-0.418s0.167-0.418 0.418-0.418zM11.816 12.38h-6.138c-0.251 0-0.418-0.167-0.418-0.418s0.167-0.418 0.418-0.418h6.138c0.251 0 0.418 0.167 0.418 0.418s-0.209 0.418-0.418 0.418zM13.194 8.706h-8.893c-0.251 0-0.418-0.167-0.418-0.418s0.167-0.418 0.418-0.418h8.935c0.251 0 0.418 0.167 0.418 0.418s-0.209 0.418-0.459 0.418z"></path></svg>';

		}


		return null;
	}



}