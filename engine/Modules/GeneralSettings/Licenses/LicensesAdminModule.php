<?php
namespace UltraCommunity\Modules\GeneralSettings\Licenses;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\Modules\BaseAdminModule;

class LicensesAdminModule extends BaseAdminModule
{
	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
    {
        static $arrDefaultSettingOptions = null;
        if(null !== $arrDefaultSettingOptions)
                return $arrDefaultSettingOptions;

        $arrDefaultSettingOptions = array();

        foreach(ModulesController::getLicensedModuleNames() as $moduleName)
        {
            if(!ModulesController::isModuleRegistered($moduleName)) {
                    continue;
            }

            $arrDefaultSettingOptions[$moduleName] = array(
                'Value' => null,
                'LabelText' => ModulesController::getModuleDisplayName($moduleName) . ' ' . __('License', 'ultra-community'),
                'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
            );
        }

        return $arrDefaultSettingOptions;

    }

	public function validateModuleSettingsFields( $arrSettingOptions )
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		$arrSettingOptions = array_map('trim', (array)$arrSettingOptions);
		$arrSettingOptions = array_filter((array)$arrSettingOptions);

		foreach($arrSettingOptions as $moduleName => $licenseKey)
		{
			if(!ModulesController::getModuleIdByName($moduleName))
			{
				continue;
			}

			$activateLicenseResult = $this->activateLicense(ModulesController::getModuleIdByName($moduleName), $licenseKey);
			if(true !== $activateLicenseResult)
			{
				$this->registerErrorMessage($activateLicenseResult);
				return $this->getAllSavedOptions();
			}

		}

		$this->registerSuccessMessage(__('Your license was successfully activated!', 'ultra-community'));

		set_site_transient( 'update_plugins', null );
		
		return $arrSettingOptions;

	}


	private function activateLicense($moduleId, $licenseKey)
	{

		$licenseRequestParams = array(
			'edd_action' => 'activate_license',
			'license'    => $licenseKey,
			'item_id'    => $moduleId,
			'url'        => home_url()
		);

		$response = @wp_remote_post( \UltraCommunity::PLUGIN_SITE_URL, array('timeout'   => 15, 'sslverify' => false, 'body' => $licenseRequestParams));

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'ultra-community' );
		}

		$licenseInfo = @json_decode( @wp_remote_retrieve_body( $response ) );

		if(empty($licenseInfo->error) && !empty($licenseInfo->success) && !empty($licenseInfo->license) && $licenseInfo->license === 'valid')
		{
			return true;
		}


		$message = null;

		switch( $licenseInfo->error  )
		{
			case 'expired' :
				$message = sprintf(
					__( 'Your license key expired on %s.' ),
					date_i18n( get_option( 'date_format' ), strtotime( $licenseInfo->expires, current_time( 'timestamp' ) ) )
				);
				break;
			case 'revoked' :
				$message = __( 'Your license key has been disabled.' );
				break;
			case 'missing' :
				$message = __( 'Invalid license.' );
				break;
			case 'invalid' :
			case 'site_inactive' :
				$message = __( 'Your license is not active for this URL.' );
				break;
			case 'item_name_mismatch' :
				$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), ModulesController::getModuleNameById($moduleId) );
				break;
			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.' );
				break;
			default :
				$message = __( 'An error occurred, please try again.' );
				break;
		}


		return $message;
	}


}