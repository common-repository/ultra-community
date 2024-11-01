<?php

namespace UltraCommunity\Controllers;

use UltraCommunity\MchLib\Exceptions\MchLibException;
use UltraCommunity\MchLib\Utils\MchDirectoryUtils;
use UltraCommunity\MchLib\Utils\MchFileUtils;
use UltraCommunity\MchLib\Utils\MchImageUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Uploader;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommUtils;

class UploadsController
{
	CONST UPLOAD_REQUEST_NONCE_KEY = 'uploadRequestNonce';

	CONST ACTION_UPLOAD_PROFILE_AVATAR  = 'uc-uploadProfileAvatar';
	CONST ACTION_UPLOAD_PROFILE_COVER   = 'uc-uploadProfileCover';

	private static $allowedMimeType = array(
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
	);

	private function __construct()
	{}

	public static function startListening()
	{


		if(empty($_FILES['files']) || empty($_POST[self::UPLOAD_REQUEST_NONCE_KEY]) || empty($_POST['action']) || empty($_POST['profiledUserSlug']) )
			return;

		if(!UserController::isUserLoggedIn() || !wp_verify_nonce($_POST[self::UPLOAD_REQUEST_NONCE_KEY], __CLASS__)) {
			MchWpUtils::sendAjaxErrorMessage(__('Unauthorized to upload files on this server', 'ultra-community'));
		}

		if(empty($_FILES['files']['type'][0]) || empty($_FILES['files']['tmp_name'][0])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid file type received', 'ultra-community'));
		}

		UserController::setProfiledUserSlug(MchWpUtils::sanitizeText($_POST['profiledUserSlug']));

		if(!UserController::currentUserCanEditProfile()){
			MchWpUtils::sendAjaxErrorMessage(__('Unauthorized to edit this user profile!', 'ultra-community'));
		}


		$uploader = new Uploader();
		$arrUploadResponse = null;

		switch($_POST['action'])
		{
			case self::ACTION_UPLOAD_PROFILE_AVATAR :
				$arrUploadResponse = self::handleAvatarUploads($uploader);
				break;

			case self::ACTION_UPLOAD_PROFILE_COVER :
				$arrUploadResponse = self::handleProfileCoverUploads($uploader);
				break;
		}



		$uploader->generate_response($arrUploadResponse, true);

		exit;

	}

	/**
	 * @return array - The upload response
	 */
	private static function handleAvatarUploads(Uploader $uploader)
	{
		$avatarMaxWidth = $avatarMaxHeight = 200;//(int)UserProfileAppearancePublicModule::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_USER_AVATAR_MAX_WIDTH);

//		if(empty($avatarMaxWidth)){
//			$avatarMaxWidth = $avatarMaxHeight = (int)UserProfileAppearancePublicModule::getInstance()->getDefaultOptionValue(UserProfileAppearanceAdminModule::OPTION_USER_AVATAR_MAX_WIDTH);
//		}

		$uploadedFileInfo = $_FILES['files'];
		$newFilePath      = UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser()) . '/avatar.jpg';
		$newFilePathParts = pathinfo($newFilePath);

		$newFilePathParts['basename'] = wp_unique_filename($newFilePathParts['dirname'], $newFilePathParts['basename']);

		$arrFileInfo = wp_check_filetype_and_ext($uploadedFileInfo['tmp_name'][0], $newFilePathParts['basename'] );

		if(empty($arrFileInfo['ext']) || empty($arrFileInfo['type']) || empty(self::$allowedMimeType[$arrFileInfo['type']])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid file type received', 'ultra-community'));
		}

		$uploadedFileInfo['name'][0] = !empty($arrFileInfo['proper_filename']) ? $arrFileInfo['proper_filename'] : $newFilePathParts['basename'];

		$uploadedFileInfo['name'][0] = MchWpUtils::sanitizeFileName($uploadedFileInfo['name'][0]);

		$_FILES['files'] = $uploadedFileInfo ;

		$arrFilesToDelete = array();

		if(!MchUtils::isNullOrEmpty($avatarFileName = UserController::getProfiledUser()->UserMetaEntity->AvatarFileName))
		{
			$avatarDirectoryPath = UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser());

			if(!empty($avatarDirectoryPath))
			{
				$searchPattern = MchFileUtils::getFileName($avatarFileName) . '-' ."[0-9]*-[0-9]*" . MchFileUtils::getFileExtension($avatarFileName, true);
				$arrFilesToDelete = MchDirectoryUtils::getDirectoryFiles($avatarDirectoryPath, $searchPattern);
			}
		}

		$uploader->setUploadDirectoryPath($newFilePathParts['dirname']);

		$arrResponse = $uploader->post(false);

		unset($arrResponse['files'][0]->deleteUrl, $arrResponse['files'][0]->deleteType);

		if(!empty($arrResponse['files'][0]->error)) {
			MchWpUtils::sendAjaxErrorMessage($arrResponse['files'][0]->error);
		}

		if(empty($arrResponse['files'][0]->name)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading your file!', 'ultra-community'));
		}

		$uploadedImageFilePath = $newFilePathParts['dirname'] . '/' . $arrResponse['files'][0]->name;

		try
		{

			if(MchUtils::isNullOrEmpty($uploadedImageFilePath = self::cropUploadedImage($uploadedImageFilePath)))
				MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading your file!', 'ultra-community'));

			if(MchUtils::isNullOrEmpty($uploadedImageFilePath = self::resizeUploadedImage($uploadedImageFilePath, $avatarMaxWidth, $avatarMaxHeight, 100)))
				MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading your file!', 'ultra-community'));

		}
		catch(MchLibException $ex)
		{
			//echo $ex->getMessage();
			MchFileUtils::delete($uploadedImageFilePath);
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while saving your image!', 'ultra-community'));
		}

		MchFileUtils::delete(UltraCommUtils::getUserAvatarFilePath(UserController::getProfiledUser()));
		foreach($arrFilesToDelete as $oldAvatarFile){
			MchFileUtils::delete($oldAvatarFile);
		}

		UserController::getProfiledUser()->UserMetaEntity->AvatarFileName = MchFileUtils::getFileBaseName($uploadedImageFilePath);
		UserController::saveUserInfo(UserController::getProfiledUser());

		$arrResponse['files'][0]->url = UltraCommHelper::getUserAvatarUrl(UserController::getProfiledUser());

		return $arrResponse;
	}



	/**
	 * @return array - The upload response
	 */
	private static function handleProfileCoverUploads(Uploader $uploader)
	{

		if(MchUtils::isNullOrEmpty($profileCoverMaxWidth  = (int)UserProfileAppearancePublicModule::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_USER_COVER_MAX_WIDTH))){
			$profileCoverMaxWidth = (int)UserProfileAppearancePublicModule::getInstance()->getDefaultOptionValue(UserProfileAppearanceAdminModule::OPTION_USER_COVER_MAX_WIDTH);
		}

		if(MchUtils::isNullOrEmpty($profileCoverMaxHeight  = (int)UserProfileAppearancePublicModule::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_USER_COVER_MAX_HEIGHT))){
			$profileCoverMaxHeight = (int)UserProfileAppearancePublicModule::getInstance()->getDefaultOptionValue(UserProfileAppearanceAdminModule::OPTION_USER_COVER_MAX_HEIGHT);
		}

		$uploadedFileInfo = $_FILES['files'];
		$newFilePath      = UltraCommUtils::getProfileCoverBaseDirectoryPath(UserController::getProfiledUser()) . '/profile-cover.jpg';
		$newFilePathParts = pathinfo($newFilePath);

		$newFilePathParts['basename'] = wp_unique_filename($newFilePathParts['dirname'], $newFilePathParts['basename']);

		$arrFileInfo = wp_check_filetype_and_ext($uploadedFileInfo['tmp_name'][0], $newFilePathParts['basename'] );

		if(empty($arrFileInfo['ext']) || empty($arrFileInfo['type']) || empty(self::$allowedMimeType[$arrFileInfo['type']])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid file type received', 'ultra-community'));
		}

		$uploadedFileInfo['name'][0] = !empty($arrFileInfo['proper_filename']) ? $arrFileInfo['proper_filename'] : $newFilePathParts['basename'];

		$uploadedFileInfo['name'][0] = wp_basename($uploadedFileInfo['name'][0]);

		$_FILES['files'] = $uploadedFileInfo;

		$arrFilesToDelete = array();

//		if(!MchUtils::isNullOrEmpty($avatarFileName = UserController::getProfiledUser()->UserMetaEntity->AvatarFileName))
//		{
//			$avatarDirectoryPath = UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser());
//
//			if(!empty($avatarDirectoryPath))
//			{
//				$searchPattern = MchFileUtils::getFileName($avatarFileName) . '-' ."[0-9]*-[0-9]*" . MchFileUtils::getFileExtension($avatarFileName, true);
//				$arrFilesToDelete = MchDirectoryUtils::getDirectoryFiles($avatarDirectoryPath, $searchPattern);
//			}
//		}

		$uploader->setUploadDirectoryPath($newFilePathParts['dirname']);

		$arrResponse = $uploader->post(false);

		unset($arrResponse['files'][0]->deleteUrl, $arrResponse['files'][0]->deleteType);

		if(!empty($arrResponse['files'][0]->error)) {
			MchWpUtils::sendAjaxErrorMessage($arrResponse['files'][0]->error);
		}

		if(empty($arrResponse['files'][0]->name)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading your file!', 'ultra-community'));
		}

		$uploadedImageFilePath = $newFilePathParts['dirname'] . '/' . $arrResponse['files'][0]->name;

		try
		{

			if(MchUtils::isNullOrEmpty($uploadedImageFilePath = self::cropUploadedImage($uploadedImageFilePath)))
				MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading your file!', 'ultra-community'));

			if(MchUtils::isNullOrEmpty($uploadedImageFilePath = self::resizeUploadedImage($uploadedImageFilePath, $profileCoverMaxWidth, $profileCoverMaxHeight, 90)))
				MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading your file!', 'ultra-community'));

		}
		catch(MchLibException $ex)
		{
			//echo $ex->getMessage();
			MchFileUtils::delete($uploadedImageFilePath);
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while saving your image!', 'ultra-community'));
		}

		MchFileUtils::delete(UltraCommUtils::getUserProfileCoverFilePath(UserController::getProfiledUser()));

		UserController::getProfiledUser()->UserMetaEntity->ProfileCoverFileName = MchFileUtils::getFileBaseName($uploadedImageFilePath);

		UserController::saveUserInfo(UserController::getProfiledUser());

		$arrResponse['files'][0]->url = UltraCommHelper::getUserProfileCoverUrl(UserController::getProfiledUser());

		return $arrResponse;
	}

















	private static function cropUploadedImage($imageFilePath)
	{
		$arrValues   = array();
		$arrValues[] = MchValidator::isNullOrEmpty($_POST['cropX'], true)      ? null : $_POST['cropX'];
		$arrValues[] = MchValidator::isNullOrEmpty($_POST['cropY'], true)      ? null : $_POST['cropY'];
		$arrValues[] = MchValidator::isNullOrEmpty($_POST['cropWidth'], true)  ? null : $_POST['cropWidth'];
		$arrValues[] = MchValidator::isNullOrEmpty($_POST['cropHeight'], true) ? null : $_POST['cropHeight'];

		$arrValues = array_map(function($value){
			return MchWpUtils::sanitizeText($value);
		}, $arrValues);

		$arrValues = array_filter($arrValues);

		if(count($arrValues) !== 4)
			return $imageFilePath;

		if(MchUtils::isNullOrEmpty($arrImageInfo = MchImageUtils::getSize($imageFilePath)))
			throw new MchLibException(__('Cannot read image dimensions', 'ultra-community'));

//		if($arrImageInfo['width'] < $arrValues[2] || $arrImageInfo['height'] < $arrValues[3])
//			return $imageFilePath;



		$arrResult =  MchImageUtils::crop($imageFilePath, $imageFilePath, $arrValues[0], $arrValues[1], $arrValues[2], $arrValues[3]);

		return !empty($arrResult['path']) ? $arrResult['path'] : $imageFilePath;
	}

	private static function resizeUploadedImage($imageFilePath, $width, $height, $imageQuality)
	{
		if(MchUtils::isNullOrEmpty($arrImageInfo = MchImageUtils::getSize($imageFilePath)))
			throw new MchLibException(__('Cannot read image dimensions', 'ultra-community'));

		$arrResult = MchImageUtils::resize($imageFilePath, $imageFilePath, $width, $height, $imageQuality);

		return !empty($arrResult['path']) ? $arrResult['path'] : $imageFilePath;

	}




	public static function getUploadNonce()
	{
		function_exists('wp_create_nonce') ?: require_once( ABSPATH . WPINC . '/pluggable.php' );

		return \wp_create_nonce(__CLASS__);
	}



}