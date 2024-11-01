<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\BaseForm;

use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\MchLib\WordPress\CustomPostType;

abstract class BaseFormPublicModule extends BasePublicModule
{
	/**
	 * @var \UltraCommunity\Modules\Forms\FormFields\BaseField[]
	 */
	protected $arrFormFields;

	protected function __construct()
	{
		parent::__construct();
		$this->arrFormFields = array();
	}

	public function setCustomPostType(CustomPostType $customPostType)
	{
		parent::setCustomPostType($customPostType);

		$this->arrFormFields = (array)$this->getOption(BaseFormAdminModule::OPTION_FORM_FIELDS);

		for($i = 0, $arrSize = count($this->arrFormFields); $i < $arrSize; ++$i)
		{
			!isset($_POST[$this->arrFormFields[$i]->UniqueId]) ?: $this->arrFormFields[$i]->Value = $_POST[$this->arrFormFields[$i]->UniqueId];
			$this->arrFormFields[$this->arrFormFields[$i]->UniqueId] = $this->arrFormFields[$i];
			unset($this->arrFormFields[$i]);
		}

	}


	/**
	 * @param $fieldUniqueId
	 *
	 * @return \UltraCommunity\Modules\Forms\FormFields\BaseField | null
	 */
	public function getFieldByUniqueId($fieldUniqueId)
	{
		return isset($this->arrFormFields[$fieldUniqueId]) ? $this->arrFormFields[$fieldUniqueId] : null;
	}

	/**
	 * @param string $fieldType
	 *
	 * @return \UltraCommunity\Modules\Forms\FormFields\BaseField[]|array
	 */
	public function getFieldsByType($fieldType)
	{
		$arrFields = array();
		foreach($this->arrFormFields as $formField){
			($fieldType !== $formField->Type) ?:  $arrFields[] = $formField;
		}

		return $arrFields;
	}

	/**
	 * @return \UltraCommunity\Modules\Forms\FormFields\BaseField[] | array
	 */
	public function getFieldsByInstance(BaseField $fieldInstance)
	{
		$arrFields = array();
		$formFieldClassName = get_class($fieldInstance);
		foreach($this->arrFormFields as $formField){
			!is_a($formField, $formFieldClassName) ?: $arrFields[] = $formField;
		}

		return $arrFields;
	}

	public function getAllFields()
	{
		return $this->arrFormFields;
	}

	public function replaceField($fieldUniqueId, BaseField $newFormField)
	{

		!isset($this->arrFormFields[$fieldUniqueId]) ?: $this->arrFormFields[$fieldUniqueId] = $newFormField;

	}

	public function deleteField($fieldUniqueId)
	{
		unset($this->arrFormFields[$fieldUniqueId]);
	}

	public function setFieldValue($fieldUniqueId, $fieldNewValue)
	{
		if(isset($this->arrFormFields[$fieldUniqueId]))
		{
			$this->arrFormFields[$fieldUniqueId]->Value = $fieldNewValue;
			return;
		}

		if(empty($this->arrFormFields))
			return;

	}
}