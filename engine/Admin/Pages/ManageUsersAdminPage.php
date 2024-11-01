<?php
namespace UltraCommunity\Admin\Pages;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\Entities\UserRoleEntity;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;

class ManageUsersAdminPage extends BaseAdminPage
{
	CONST CLASS_NAME = __CLASS__;

	public function __construct( $pageMenuTitle )
	{
		parent::__construct($pageMenuTitle);

		$this->setPageLayoutColumns(1);
		
		foreach (array(UserMetaEntity::USER_STATUS_AWAITING_REVIEW, UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION) as $userStatus)
		{
			$arrUsers = UserRepository::getUsersByStatus($userStatus, 1, 1);
			$pageBadgeCounter = $this->getPageBadgeCounter() + (  empty($arrUsers[UserRepository::TOTAL_FOUND_ROWS]) ? 0 : $arrUsers[UserRepository::TOTAL_FOUND_ROWS] );
			$this->setPageBadgeCounter($pageBadgeCounter);
		}
		
		
		if(!empty($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], self::CLASS_NAME))
		{
			if ( ! empty( $_GET['action'] ) && $_GET['action'] === 'uc-approve-user' && ! empty( $_GET['userId'] ) && MchValidator::isInteger( $_GET['userId'] ) )
			{

				if(UserRoleController::currentUserCanManageUltraCommunity()){
					UserController::changeUserStatus($_GET['userId'], UserMetaEntity::USER_STATUS_APPROVED);
				}

			}
		}

		if(!empty($_POST['uc-manage-users-nonce']) && wp_verify_nonce($_POST['uc-manage-users-nonce'], self::CLASS_NAME))
		{
			if(!empty($_POST['uc-users']) && !empty($_POST['uc-combo-new-role']) && MchValidator::isInteger($_POST['uc-combo-new-role']))
			{

				if($postTypeUserRole = PostTypeController::getPostTypeInstanceByPostId($_POST['uc-combo-new-role']))
				{
					foreach ((array)$_POST['uc-users'] as $userId)
					{
						if(!MchValidator::isInteger($userId))
							continue;

						if(null === ($wpUser = WpUserRepository::getUserById($userId)))
							continue;

						if(null === ($userPostTypeAdminInstance = PostTypeController::getAssociatedPublicModuleInstance($postTypeUserRole)))
							continue;

						if(!$userPostTypeAdminInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG))
							continue;

						foreach (UserRoleController::getAllRegisteredUserRoles() as $roleSlug => $descr)
						{
							$wpUser->remove_role($roleSlug);
						}

						$wpUser->add_role($userPostTypeAdminInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG));

					}

				}

			}

		}

	}
	
	public function hasRegisteredModules()
	{
		return !!$this->getPageBadgeCounter();
	}
	
	
//	public function getPageMenuTitle()
//	{
//		$awaitingAdminApprovalUsers =  UserRepository::getUsersByStatus(UserMetaEntity::USER_STATUS_AWAITING_REVIEW, 1, 1);
//		$awaitingAdminApprovalUsers = empty($awaitingAdminApprovalUsers[UserRepository::TOTAL_FOUND_ROWS]) ? 0 : $awaitingAdminApprovalUsers[UserRepository::TOTAL_FOUND_ROWS];
//
//		return empty($awaitingAdminApprovalUsers) ? parent::getPageMenuTitle() : parent::getPageMenuTitle() . '<span class="uc-badge-counter"><span>' . $awaitingAdminApprovalUsers .  '</span></span>';
//	}

	public function registerPageMetaBoxes()
	{
		parent::registerPageMetaBoxes();

		add_meta_box(
			$this->getSettingGroupId(0),
			__('Manage Users', 'ultra-community'),
			array( $this, 'renderUsersTable' ),
			$this->getAdminScreenId(),
			'advanced',
			'core',
			0
		);
	}

	public function renderUsersTable()
	{
		if(!MchWpUtils::isAdminLoggedIn())
			return;

		$usersTable = new UltraCommUsersTable();
		$usersTable->PageBaseUrl = $this->getAdminUrl();
		$usersTable->prepare_items();

		echo '<form method="post">';
		echo wp_nonce_field(self::CLASS_NAME, 'uc-manage-users-nonce', false, false);

		$usersTable->display();

		echo '</form>';
	}


	public function getPageHiddenContent()
	{}

}

class_exists( '\WP_List_Table' ) ?: require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class UltraCommUsersTable extends \WP_List_Table
{
	public $PageBaseUrl = null;
	private $totalItems = 0;
	public function __construct()
	{
		// Set parent defaults
		parent::__construct( array(
			'singular' => 'user',
			'plural'   => 'users',
			'ajax'     => false,
		) );

	}

	public function display()
	{

		parent::display();

	}

	public function prepare_items()
	{
		$columns = $this->get_columns();
		$hidden = array();//$this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = array();

		!empty($_GET['uc-users-filter']) ?:  $_GET['uc-users-filter'] = UserMetaEntity::USER_STATUS_AWAITING_REVIEW;

		if(!empty($_GET['uc-users-filter']) && MchValidator::isInteger($_GET['uc-users-filter']))
		{
			$data = UserRepository::getUsersByStatus($_GET['uc-users-filter'], $this->get_pagenum(), 25);
		}
		else
		{
			$data = UserRepository::getAllUsers( $this->get_pagenum(), 25 );
		}

		$this->set_pagination_args( array(
			'total_items' => empty($data[UserRepository::TOTAL_FOUND_ROWS]) ? 0 : $data[UserRepository::TOTAL_FOUND_ROWS],
			'per_page'    => 25
		) );

		unset($data[UserRepository::TOTAL_FOUND_ROWS]);

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->items = $data;
	}

	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			//'id'       => __( 'User ID', 'ultra-community' ),
			'username'       => __( 'Username', 'ultra-community' ),
			'email'    => __( 'Email Address', 'ultra-community' ),
			'wp-role'  => __( 'User Roles', 'ultra-community' ),
			//'community-role' => __( 'Community Role', 'ultra-community' ),
			'status'   => __( 'Status', 'ultra-community' ),
		);

		return $columns;
	}


	function column_cb($userEntity)
	{
		return sprintf(
			'<input type="checkbox" name="uc-users[]" value="%s" />', $userEntity->Id
		);
	}

	/**
	 * @param UserEntity $userEntity
	 * @param string $columnName
	 *
	 * @return null|string
	 */
	public function column_default( $userEntity, $columnName )
	{

		switch ($columnName)
		{
			case 'email' : return $userEntity->Email;
			case 'wp-role' :

				$arrUserRoles = WpUserRepository::getUserRoles($userEntity->Id);

				//return esc_html(trim(implode(', ', array_values($arrUserRoles))));

//				foreach (UserRoleController::getAllRegisteredUserRoles(true) as $roleSlug => $roleDescription)
//				{
//					unset($arrUserRoles[$roleSlug]);
//				}


//				if(empty($arrUserRoles) && !MchUtils::isNullOrEmpty(WpUserRepository::getUserRoles($userEntity->Id))){
//					$wpUser = WpUserRepository::getUserById($userEntity->Id); $wpUser->roles = (array)$wpUser->roles;
//					if(!in_array(get_option('default_role', 'subscriber'), $wpUser->roles)){
//						$wpUser->add_role(get_option('default_role', 'subscriber'));
//					}
//				}

				return esc_html(trim(implode(', ', array_values($arrUserRoles))));

//			case 'community-role' :
//
//				$roleModuleInstance = UltraCommHelper::getUserRolePublicInstanceByUserInfo($userEntity);
//
//				if(null === $roleModuleInstance){
//					return __('None', 'ultra-community');
//				}
//
//				return $roleModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
//
//			break;


			case 'status':

				if(empty($userEntity->UserMetaEntity->UserStatus))
				{
					$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;
					UserController::saveUserInfo($userEntity);
				}


				switch ($userEntity->UserMetaEntity->UserStatus)
				{
					case UserMetaEntity::USER_STATUS_APPROVED :
						return __('Active', 'ultra-community');
					case UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION :
						return __('Awaiting Approval', 'ultra-community');
					case UserMetaEntity::USER_STATUS_AWAITING_REVIEW :
						return __('Awaiting Review', 'ultra-community');

				}

			break;
		}



		if('username' === $columnName)
		{
			$currentBlogId = MchWpUtils::getCurrentBlogId();
			$url = 'users.php?';

			if ( MchWpUtils::isAdminInNetworkDashboard() )
				$url = "site-users.php?id=$currentBlogId&amp;";

			$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $userEntity->Id ) ) );

			$actions = array(
				'edit' => '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>',
			);

			if ( !is_multisite() && get_current_user_id() != $userEntity->Id && current_user_can( 'delete_user', $userEntity->Id ) )
				$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=delete&amp;user=$userEntity->Id", 'bulk-users' ) . "'>" . __( 'Delete' ) . "</a>";

			if ( is_multisite() && get_current_user_id() != $userEntity->Id && current_user_can( 'remove_user', $userEntity->Id ) )
				$actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url( $url."action=remove&amp;user=$userEntity->Id", 'bulk-users' ) . "'>" . __( 'Remove' ) . "</a>";


			$url = wp_nonce_url( $this->PageBaseUrl ."&amp;action=uc-approve-user&amp;userId=$userEntity->Id", ManageUsersAdminPage::CLASS_NAME );

			if(in_array($userEntity->UserMetaEntity->UserStatus, array(UserMetaEntity::USER_STATUS_AWAITING_REVIEW, UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION)))
			{
				$actions['uc-approve-user'] = '<a href="'.$url.'" class="uc-approve-user" >' . __( 'Approve' ) . "</a>";
			}
			
//			if($userEntity->UserMetaEntity->UserStatus == UserMetaEntity::USER_STATUS_AWAITING_REVIEW){
//				$actions['uc-approve-user'] = '<a href="'.$url.'" class="uc-approve-user" >' . __( 'Approve' ) . "</a>";
//			}

			$actions['uc-view-profile'] = '<a target="_blank" href="'.UltraCommHelper::getUserProfileUrl($userEntity).'" class="" >' . __( 'View Profile' ) . "</a>";


			!MchWpUtils::isAdminUser($userEntity->Id) ?: $actions = array();

			$userAvatarUrl = UltraCommHelper::getUserAvatarUrl($userEntity);

			$output = empty($userAvatarUrl) ? '' : '<img src="'.$userAvatarUrl.'" class="avatar avatar-32 photo" height="32" width="32">';

			return $output .  "<strong>$userEntity->UserName</strong>" . $this->row_actions( $actions, isset($actions['uc-approve-user']) );

		}

		return isset($item[$columnName]) ?  $item[ $columnName ] : null;
	}

	public function get_sortable_columns() {
		return array(
//			'id'     => array( 'id', true ),
//			'email' => array( 'email', false ),
		);
	}

	protected function get_primary_column_name() {
		return 'username';
	}


	public function extra_tablenav($which)
	{

		if('top' !== $which)
			return;

		$outputHtml =  '<div class="alignleft actions" style="margin-left: -8px;">';

		$outputHtml .= '<select name = "uc-combo-new-role" style="width: 220px; max-width:220px">';
		$outputHtml .= '<option value="">' .  __( 'Add Community Role&hellip;' ) .'</option>';

		foreach((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole)
		{
			$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
			if( ! $adminModule instanceof UserRoleAdminModule)
				continue;
			$customPostUserRole->PostId = esc_attr($customPostUserRole->PostId);

			$outputHtml .= "<option value=\"$customPostUserRole->PostId\">" . esc_html($adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE)) .'</option>';

		}


		$outputHtml .= '</select>';

		$outputHtml .= '<button class = "uc-button uc-button-primary">' . __('Assign Role') . '</button>';

		$outputHtml .= '</div>';



		$outputHtml = '';



//		$awaitingEmailConfirmationUsers = count(UserRepository::getUsersByStatus(UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION));
//		$awaitingAdminApprovalUsers     = count(UserRepository::getUsersByStatus(UserMetaEntity::USER_STATUS_AWAITING_REVIEW));

		$awaitingEmailConfirmationUsers = UserRepository::getUsersByStatus(UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION, 1, 1);
		$awaitingEmailConfirmationUsers = empty($awaitingEmailConfirmationUsers[UserRepository::TOTAL_FOUND_ROWS]) ? 0 : $awaitingEmailConfirmationUsers[UserRepository::TOTAL_FOUND_ROWS];


		$awaitingAdminApprovalUsers =  UserRepository::getUsersByStatus(UserMetaEntity::USER_STATUS_AWAITING_REVIEW, 1, 1);
		$awaitingAdminApprovalUsers = empty($awaitingAdminApprovalUsers[UserRepository::TOTAL_FOUND_ROWS]) ? 0 : $awaitingAdminApprovalUsers[UserRepository::TOTAL_FOUND_ROWS];

		$outputHtml  .= '<ul class= "subsubsub" style="font-size: 14px; margin:0 0 0 0">';


		$filterStyle = empty($_GET['uc-users-filter']) ? 'text-decoration: underline;' : '';

//		$outputHtml .= "<li>";
//		$outputHtml .= '<a style="'.$filterStyle.'" href="' . $this->PageBaseUrl . '">' . __('All Users', 'ultra-community') . '</a>';
//		$outputHtml .= "</li>";

		{
			$filterStyle  = empty($awaitingAdminApprovalUsers) ? '' : 'color:#CA3C3C;';
			$filterStyle .= !empty($_GET['uc-users-filter']) && $_GET['uc-users-filter'] ==  UserMetaEntity::USER_STATUS_AWAITING_REVIEW ? 'text-decoration: underline;' : '';

			$outputHtml .= "<li>";
			$outputHtml .= '<a style = "'.$filterStyle.'" href="' . add_query_arg(array('uc-users-filter' => UserMetaEntity::USER_STATUS_AWAITING_REVIEW ), $this->PageBaseUrl) . '">' . sprintf(__('Awaiting Admin Review(%s)', 'ultra-community'), $awaitingAdminApprovalUsers) . '</a>';
			$outputHtml .= "</li>";

		}

		{
			$filterStyle  = empty($awaitingEmailConfirmationUsers) ? '' : 'color:#CA3C3C;';
			$filterStyle .= !empty($_GET['uc-users-filter']) && $_GET['uc-users-filter'] ==  UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION ? 'text-decoration: underline;' : '';

			$outputHtml .= " | <li>";
			$outputHtml .= '<a style = "'.$filterStyle.'" href="' . add_query_arg(array('uc-users-filter' => UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION ), $this->PageBaseUrl) . '">' . sprintf(__('Awaiting Email Confirmation(%d)', 'ultra-community'), $awaitingEmailConfirmationUsers) . '</a>';
			$outputHtml .= "</li>";

		}

		$outputHtml .= "</ul>";


		echo $outputHtml;

	}

}
