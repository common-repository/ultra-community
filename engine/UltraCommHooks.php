<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;


final class UltraCommHooks
{
	CONST FILTER_FRONT_END_CAN_USE_GLOBAL_TEMPLATE = 'uc_filter_front_end_can_use_global_template';
	CONST FILTER_FRONT_END_REGISTERED_SCRIPTS      = 'uc_filter_front_end_registered_scripts';
	CONST FILTER_FRONT_END_REGISTERED_STYLES       = 'uc_filter_front_end_registered_styles';
	CONST FILTER_FRONT_END_ADDITIONAL_INLINE_CSS   = 'uc_filter_front_end_additional_inline_css';

	CONST FILTER_FRONT_END_TEMPLATES_DIR_PATH      = 'uc_filter_front_end_templates_dir_path';
	CONST FILTER_FRONT_END_TEMPLATE_FILE_PATH      = 'uc_filter_front_end_template_file_path';
	CONST FILTER_FRONT_END_TEMPLATE_ARGUMENTS      = 'uc_filter_front_end_template_arguments';
	CONST FILTER_FRONT_END_LOAD_TEMPLATE_KEY       = 'uc_filter_front_end_load_template_key';


	CONST FILTER_PAGE_HOLDER_HTML_CLASSES  = 'uc_filter_page_holder_classes';
	CONST FILTER_PAGE_HEADER_HTML_CLASSES  = 'uc_filter_page_header_classes';
	CONST FILTER_PAGE_NAVBAR_HTML_CLASSES  = 'uc_filter_page_navbar_classes';
	CONST FILTER_PAGE_SIDEBAR_HTML_CLASSES = 'uc_filter_page_sidebar_classes';
	CONST FILTER_PAGE_CONTENT_HTML_CLASSES = 'uc_filter_page_content_classes';

	CONST ACTION_BEFORE_START_RENDERING_PAGE   = 'uc_action_before_start_rendering_page';
	CONST ACTION_BEFORE_RENDER_PAGE_HEADER     = 'uc_action_before_render_page_header';
	CONST ACTION_BEFORE_RENDER_PAGE_NAV_BAR    = 'uc_action_before_render_page_nav_bar';
	CONST ACTION_BEFORE_RENDER_PAGE_SIDE_BAR   = 'uc_action_before_render_page_side_bar';
	CONST ACTION_BEFORE_RENDER_PAGE_CONTENT    = 'uc_action_before_render_page_content';


	CONST ACTION_BEFORE_RENDER_PAGE_NAV_BAR_MAIN_MENU  = 'uc_action_before_render_page_nav_bar_main_menu';
	CONST ACTION_AFTER_PAGE_NAV_BAR_MAIN_MENU_RENDERED = 'uc_action_after_page_nav_bar_main_menu_rendered';


	CONST ACTION_AFTER_PAGE_HEADER_RENDERED   = 'uc_action_after_page_header_rendered';
	CONST ACTION_AFTER_PAGE_NAV_BAR_RENDERED  = 'uc_action_after_page_nav_bar_rendered';
	CONST ACTION_AFTER_PAGE_SIDE_BAR_RENDERED = 'uc_action_after_page_side_bar_rendered';
	CONST ACTION_AFTER_PAGE_CONTENT_RENDERED  = 'uc_action_after_page_content_rendered';


	CONST FILTER_PAGE_HEADER_TAG_LINE_ARGUMENTS   = 'uc_filter_page_header_tag_line_arguments';
	CONST FILTER_PAGE_SUB_MENU_TEMPLATE_ARGUMENTS = 'uc_filter_page_sub_menu_template_arguments';

	CONST FILTER_PAGE_HEADER_OUTPUT_HTML     = 'uc_filter_page_header_output_html';
	CONST FILTER_PAGE_NAVBAR_OUTPUT_HTML     = 'uc_filter_page_navbar_output_html';
	CONST FILTER_PAGE_SUB_MENU_OUTPUT_HTML   = 'uc_filter_page_sub_menu_output_html';
	CONST FILTER_PAGE_SIDEBAR_OUTPUT_HTML    = 'uc_filter_page_sidebar_output_html';
	CONST FILTER_PAGE_CONTENT_OUTPUT_HTML    = 'uc_filter_page_content_output_html';

	CONST FILTER_PAGE_FOOTER_HIDDEN_CONTENT  = 'uc_filter_page_footer_hidden_content';

#User Front End Pages

	//CONST FILTER_FRONT_PAGES_GLOBAL_TEMPLATE_FILE_PATH = 'uc_filter_front_pages_global_template_file_path';
	CONST FILTER_USER_PROFILE_PAGE_CSS                = 'uc_filter_user_profile_page_css';
	CONST FILTER_USER_PROFILE_PAGE_HEADER_STYLE       = 'uc_filter_user_profile_page_header_style';
	CONST FILTER_USER_PROFILE_PAGE_SIDEBAR_POSITION   = 'uc_filter_user_profile_page_sidebar_position';
	CONST FILTER_USER_PROFILE_PAGE_ACTIVE_SECTIONS    = 'uc_filter_user_profile_page_active_sections';
	CONST FILTER_USER_PROFILE_PAGE_SECTION_ICON       = 'uc_filter_user_profile_page_section_icon';
	CONST FILTER_USER_PROFILE_PAGE_SECTION_NAME       = 'uc_filter_user_profile_page_section_name';


	CONST FILTER_POST_DEFAULT_THUMB_URL      = 'uc_filter_post_default_thumb_url';

#Form Fields
	CONST FILTER_FORM_FIELD_LANGUAGE_OPTIONS = 'uc_filter_form_field_language_options';
	CONST FILTER_FORM_FIELD_COUNTRY_OPTIONS  = 'uc_filter_form_field_country_options';
	CONST FILTER_FORM_FIELD_GENDER_OPTIONS   = 'uc_filter_form_field_gender_options';
	CONST FILTER_FORM_FIELD_OUTPUT_CONTENT   = 'uc_filter_social_connect_field_output_content';

#Registration Forms Actions

	CONST ACTION_BEFORE_STARTING_REGISTRATION   = 'uc_action_before_starting_registration';
	CONST ACTION_BEFORE_USER_REGISTRATION       = 'uc_action_before_user_registration';
	CONST ACTION_AFTER_USER_REGISTERED          = 'uc_action_after_user_registered';
	CONST ACTION_REGISTRATION_FORM_BOTTOM       = 'uc_action_registration_form_bottom';

#Registration Forms Filters
	CONST FILTER_REGISTRATION_ADDITIONAL_INFO = 'uc_filter_registration_additional_info';


#Login Forms Actions
	CONST ACTION_BEFORE_STARTING_AUTHENTICATION   = 'uc_action_before_starting_authentication';
	CONST ACTION_BEFORE_USER_LOG_IN               = 'uc_action_before_user_log_in';
	CONST ACTION_AFTER_USER_LOGGED_IN             = 'uc_action_after_user_logged_in';
	CONST ACTION_LOGIN_FORM_BOTTOM                = 'uc_action_login_form_bottom';

#Login Forms Filters
	//CONST FILTER_LOGIN_ADDITIONAL_INFO = 'uc_filter_login_additional_info';


#Forgot Password Form Actions
	CONST ACTION_FORGOT_PASSWORD_FORM_BOTTOM = 'uc_action_forgot_password_form_bottom';



#User Actions
	CONST ACTION_BEFORE_CHANGE_USER_STATUS   = 'uc_action_before_change_user_status';
	CONST ACTION_AFTER_USER_STATUS_CHANGED   = 'uc_action_after_user_status_changed';

	CONST ACTION_AFTER_USER_PROFILE_COVER_CHANGED  = 'uc_action_after_user_profile_cover_changed';
	CONST ACTION_AFTER_USER_PROFILE_PHOTO_CHANGED  = 'uc_action_after_user_profile_photo_changed';

#Reset Password Actions
	CONST ACTION_AFTER_RESET_PASSWORD_EMAIL_SENT = 'uc_action_after_reset_password_email_sent';


#User Profile Settings Hooks

	CONST FILTER_ALL_USER_SETTINGS_SECTIONS      = 'uc_filter_user_all_settings_sections';

	CONST FILTER_USER_PROFILE_SETTINGS_SECTIONS      = 'uc_filter_user_profile_settings_sections';
	CONST FILTER_USER_QUICK_LINKS_SETTINGS_SECTIONS  = 'uc_filter_user_quick_links_settings_sections';

	CONST FILTER_USER_SETTINGS_SIDEBAR_NAVIGATION_SECTIONS = 'uc_filter_user_settings_sidebar_navigation_sections';

	CONST ACTION_USER_PROFILE_SETTINGS_SIDEBAR_BEFORE_PROFILE_SECTIONS = 'uc_action_user_profile_settings_sidebar_before_profile_sections';
	CONST ACTION_USER_PROFILE_SETTINGS_SIDEBAR_AFTER_PROFILE_SECTIONS  = 'uc_action_user_profile_settings_sidebar_after_profile_sections';


	CONST FILTER_USER_PROFILE_SETTINGS_SECTION_NAME     = 'uc_filter_user_profile_settings_section_name';
	CONST FILTER_USER_PROFILE_SETTINGS_SECTION_ICON     = 'uc_filter_user_profile_settings_section_icon';

	//CONST ACTION_USER_PROFILE_SETTINGS_SECTION_CONTENT  = 'uc_action_user_profile_settings_section_content';

	CONST ACTION_BEFORE_CREATE_USER_GROUP  = 'uc_action_before_create_user_group';
	CONST ACTION_AFTER_USER_GROUP_CREATED  = 'uc_action_after_user_group_created';

	CONST ACTION_BEFORE_SAVE_USER_GROUP  = 'uc_action_before_save_user_group';
	CONST ACTION_AFTER_USER_GROUP_SAVED  = 'uc_action_after_user_group_saved';

	CONST ACTION_BEFORE_DELETE_USER_GROUP  = 'uc_action_before_delete_user_group';
	CONST ACTION_AFTER_USER_GROUP_DELETED  = 'uc_action_after_user_group_deleted';


	CONST ACTION_USER_PROFILE_HEADER_BEFORE_META_LIST = 'uc_action_user_profile_header_before_meta_list';
	CONST ACTION_USER_PROFILE_HEADER_AFTER_META_LIST  = 'uc_action_user_profile_header_aftermeta_list';

#Directories Hooks

	CONST FILTER_MEMBERS_DIRECTORY_EXCLUDE_MEMBER_IDS  = 'uc_filter_members_directory_excluded_member_ids';
	CONST FILTER_MEMBERS_DIRECTORY_INCLUDE_MEMBER_IDS  = 'uc_filter_members_directory_included_member_ids';
	CONST FILTER_MEMBERS_DIRECTORY_QUERY_ARGUMENTS     = 'uc_filter_members_directory_query_arguments';


	CONST FILTER_MEMBERS_DIRECTORY_PAGE_MEMBERS_ACTIONS_LIST = 'uc_filter_members_directory_page_member_actions_list';
	CONST FILTER_GROUPS_DIRECTORY_PAGE_GROUP_ACTIONS_LIST    = 'uc_filter_groups_directory_page_group_actions_list';


#Groups Profile Page Hooks

	CONST FILTER_GROUP_PROFILE_SECTIONS                   = 'uc_filter_group_profile_sections';
	CONST FILTER_GROUP_PROFILE_PAGE_SECTION_ICON          = 'uc_filter_group_profile_page_section_icon';
	CONST FILTER_GROUP_PROFILE_SECTION_CONTENT            = 'uc_filter_group_profile_section_content';
	CONST FILTER_GROUP_PROFILE_SECTION_TEMPLATE_FILE_PATH = 'uc_filter_group_profile_section_template_file_path';


	CONST ACTION_GROUP_PROFILE_SECTION_RENDER_CONTENT = 'uc_action_group_profile_section_render_content';


#Activity Hooks

	CONST ACTION_ACTIVITY_BEFORE_PUBLISH  = 'uc_action_activity_before_publish';
	CONST ACTION_ACTIVITY_AFTER_PUBLISHED = 'uc_action_activity_after_published';

	CONST ACTION_ACTIVITY_BEFORE_UPDATE   = 'uc_action_activity_before_update';
	CONST ACTION_ACTIVITY_AFTER_UPDATED   = 'uc_action_activity_after_updated';

	CONST FILTER_ACTIVITY_POSTS_FORMAT    = 'uc_filter_activity_posts_format';

	CONST FILTER_ACTIVITY_SHOW_NEW_POST_FORM    = 'uc_filter_activity_show_new_post_form';
	CONST FILTER_ACTIVITY_NEW_POST_FORM_HINT    = 'uc_filter_activity_new_post_form_hint';

	CONST ACTION_ACTIVITY_BEFORE_NEW_POST_FORM  = 'uc_action_activity_before_new_post_form';
	CONST ACTION_ACTIVITY_AFTER_NEW_POST_FORM   = 'uc_action_activity_after_new_post_form';

	CONST ACTION_ACTIVITY_RENDER_HEADER         = 'uc_action_activity_render_header';
	CONST ACTION_ACTIVITY_RENDER_CONTENT        = 'uc_action_activity_render_content';
	CONST ACTION_ACTIVITY_RENDER_FOOTER         = 'uc_action_activity_render_footer';

	CONST ACTION_ACTIVITY_RENDER_COMMENT        = 'uc_action_activity_render_comment';
	CONST ACTION_ACTIVITY_RENDER_COMMENTS_LIST  = 'uc_action_activity_render_comments_list';
	CONST ACTION_ACTIVITY_RENDER_COMMENTS_FORM  = 'uc_action_activity_render_comments_form';

	CONST FILTER_ACTIVITY_FOOTER_ACTIONS        = 'uc_filter_activity_footer_actions';
	CONST ACTION_AFTER_ACTIVITY_FOOTER_ACTIONS  = 'uc_action_after_activity_footer_actions';

	CONST FILTER_ACTIVITY_COMMENT_CONTENT  = 'uc_filter_activity_comment_content';

	CONST FILTER_ACTIVITY_TYPE_OUTPUT_CONTENT = 'uc_filter_activity_type_output_content';

	CONST FILTER_ACTIVITY_SEND_MENTIONS_NOTIFICATION = 'uc_filter_activity_send_mentions_notifications';


#User Relations Hooks
	CONST ACTION_USER_RELATION_AFTER_SAVE   = 'uc_action_user_relation_after_save';
	CONST ACTION_USER_RELATION_AFTER_DELETE = 'uc_action_user_relation_after_delete';


#User Hooks
	//CONST FILTER_USER_ROLES            = 'uc_filter_user_roles';
	CONST FILTER_USER_IS_ONLINE        = 'uc_filter_user_is_online';
	CONST FILTER_USER_CAN_JOIN_GROUP   = 'uc_filter_user_can_join_group';
	CONST FILTER_USER_CAN_CREATE_GROUP = 'uc_filter_user_can_create_group';

	CONST FILTER_USER_AVATAR_FILE_PATH = 'uc_filter_user_avatar_file_path';

#Widgets Hooks
	CONST ACTION_WIDGET_ABOUT_MYSELF_BEFORE_DISPLAY_NAME = 'uc_action_widget_about_myself_before_display_name';
	CONST ACTION_WIDGET_ABOUT_MYSELF_AFTER_DISPLAY_NAME  = 'uc_action_widget_about_myself_after_display_name';

	CONST FILTER_WIDGET_MAIN_SHOW_LOG_OUT_BUTTON  = 'uc_filter_widget_main_show_logout_button';
	CONST FILTER_WIDGET_MAIN_MENU_SECTIONS        = 'uc_filter_widget_main_menu_sections';

#Email Notifications Hooks
	CONST FILTER_EMAIL_TEMPLATES_DIR_PATH  = 'uc_filter_email_templates_dir_path';
	CONST FILTER_EMAIL_NOTIFICATION_ENTITY = 'uc_filter_email_notification_entity';


#UserSubscriptions and Content Restriction Hooks
	CONST FILTER_RESTRICTED_POST_CONTENT    = 'uc_filter_restricted_post_content';
	CONST FILTER_RESTRICTED_CONTENT_MESSAGE = 'uc_filter_restricted_content_message';

	CONST FILTER_HIDE_RESTRICTED_POST_COMMENTS = 'uc_filter_restricted_post_comments';

	CONST FILTER_USER_CAN_BYPASS_RESTRICTION_RULE = 'uc_filter_user_can_bypass_restriction_rule';


//#UserPostSubmissions Hooks
	CONST FILTER_USER_CAN_EDIT_SUBMITTED_POST      = 'uc_filter_user_can_edit_submitted_post';
	CONST FILTER_USER_CAN_DELETE_SUBMITTED_POST    = 'uc_filter_user_can_delete_submitted_post';

	CONST FILTER_SAVE_SUBMITTED_POST_DATA_ARGUMENTS = 'uc_filter_save_submitted_post_data_arguments';
	CONST FILTER_AFTER_SUBMITTED_POST_REDIRECT_URL  = 'uc_filter_after_submitted_post_redirect_url';

	CONST FILTER_USER_DISPLAYABLE_POST_TYPES        = 'uc_filter_user_displayable_post_types';

#Ajax Requests Hooks
	CONST FILTER_AJAX_REGISTERED_ACTIONS     = 'uc_filter_ajax_registered_actions';
	CONST FILTER_AJAX_REGISTERED_CLASS_NAMES = 'uc_filter_ajax_registered_class_names';



########################## DEPRECATED since 2.1 ###############################
	CONST FILTER_USER_PROFILE_SETTINGS_SIDEBAR_ITEMS = 'deprecated_filter_user_profile_settings_sidebar_items';

	private function __construct(){
	}
	private function __clone(){
	}
	private function __wakeup(){
	}
}

