<?php
namespace UltraCommunity\Modules;


use UltraCommunity\MchLib\Modules\MchBasePublicModule;
use UltraCommunity\UltraCommHooks;

class BasePublicModule extends MchBasePublicModule
{
	protected function __construct()
	{
		parent::__construct();
	}

	private function translations()
	{
		esc_html__('view post', 'ultra-community');
		esc_html__('edit post', 'ultra-community');
		esc_html__('has not created any replies!', 'ultra-community');
		esc_html__('has not created any topics!', 'ultra-community');
		esc_html__('has no favorite topics!', 'ultra-community');

		esc_html__('Download', 'ultra-community');
		esc_html__('Submit', 'ultra-community');

		esc_html__('Please review your information and continue registration!', 'ultra-community');

		esc_html__('out of', 'ultra-community');
		esc_html__('reviews', 'ultra-community');

		esc_html__('Add Review', 'ultra-community');
		esc_html__('Write your short review', 'ultra-community');

		esc_html__('Social Share', 'ultra-community');
		esc_html__('Share Profile', 'ultra-community');
		esc_html__('Profile on', 'ultra-community');

		esc_html__( 'Topics', 'ultra-community' );
		esc_html__( 'Replies', 'ultra-community' );
		esc_html__( 'Favorites', 'ultra-community' );


		esc_html__('Posts Submissions', 'ultra-community');
	    esc_html__('Create New Post', 'ultra-community');
		esc_html__('Published Posts', 'ultra-community');
	    esc_html__('Awaiting Review', 'ultra-community');
	    esc_html__('Draft Posts', 'ultra-community')    ;

		esc_html__('Upload', 'ultra-community');

		esc_html__('Post Title', 'ultra-community');
		esc_html__('Post Format', 'ultra-community');
		esc_html__('Post Content', 'ultra-community');
		esc_html__('Post Excerpt', 'ultra-community');
		esc_html__('Post Categories', 'ultra-community');
		esc_html__('Post Featured Image', 'ultra-community');

		esc_html__('Create New Post', 'ultra-community');
		esc_html__('Accepted Posts', 'ultra-community');
		esc_html__('Pending Posts', 'ultra-community');
		esc_html__('Published Posts', 'ultra-community');

		__('Submitted Post Status', 'ultra-community');
		__('Choose whether submitted posts get published right away or wait for your review. Options are Draft, Pending Review, Private, or Published', 'ultra-community');
		__('Submitted Post Type', 'ultra-community');
		__('Choose whether submitted post type is a blog post or a page', 'ultra-community');
		__('Post Categories', 'ultra-community');
		__('The list of categories users are allowed to select from', 'ultra-community');


		__('Default Post Category', 'ultra-community');
		__('The default post category in case the submitted one has no category selected', 'ultra-community');
		__('Available Post Formats', 'ultra-community');
		__('The list of all supported post formats users are allowed to select from', 'ultra-community');
		__('Post Excerpt Required', 'ultra-community');
		__('Enable this option to force users write a post excerpt', 'ultra-community');
		__('Allow Comments', 'ultra-community');
		__('Enable this option if submitted post will allow people to post comments after it gets published', 'ultra-community');
		__('Allow Pingbacks/Trackbacks', 'ultra-community');
		__('Enable this option if submitted post will allow Pingbacks or Trackbacks after it gets published', 'ultra-community');

		__('Invalid request received', 'ultra-community');

		__('You are not allowed to submit this!', 'ultra-community');
		__('You are not allowed to submit posts!', 'ultra-community');
		__('Please type the title of this submission!', 'ultra-community');
		__('Please type the content of this submission!', 'ultra-community');
		__('Please type the excerpt of this submission!', 'ultra-community');
		__("You are not allowed to edit this post!", 'ultra-community');;

		__("Invalid attachment for this post", 'ultra-community');
		__("Your are not allowed to use this attachment as featured image!", 'ultra-community');
		__( "Cannot attach uploaded image to this submission!", 'ultra-community' );
		__( "Invalid image type assigned to this submission!", 'ultra-community' );
		__( "Invalid image dimensions assigned to this submission!", 'ultra-community' );
		__( "Cannot create upload directory path!", 'ultra-community' ) ;
		__( "Cannot save uploaded file!", 'ultra-community' );
		__( "An error was encountered while trying to save uploaded image!", 'ultra-community' );

		__("An error was encountered while saving your submission!", 'ultra-community');
		__('You cannot delete this submission !', 'ultra-community');

		__('Subscriptions', 'ultra-community');
		__('Payment Gateways Settings', 'ultra-community');


############################# Notifications ##################################

		__('Notifications', 'ultra-community');
		__('Mark as Read', 'ultra-community');
		__('Mark as Unread', 'ultra-community');
		__('See all recent notifications', 'ultra-community');

		_x( '%s ago', 'refers to time ago. Ex: 2 weeks ago', 'ultra-community' );

		__('View Post',      'ultra-community');
		__('Delete',         'ultra-community');
		
		esc_html__('Sign out', 'ultra-community');
		esc_html__('Hello!', 'ultra-community');
		
		esc_html__('status', 'ultra-community');
		esc_html__('images', 'ultra-community');
		esc_html__('photos', 'ultra-community');
		esc_html__('quote', 'ultra-community');
		esc_html__('video', 'ultra-community');
		esc_html__('audio', 'ultra-community');
		esc_html__('link', 'ultra-community');
		esc_html__('file', 'ultra-community');
		
		
	}
}