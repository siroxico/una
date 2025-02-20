<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    BaseGeneral Base classes for modules
 * @ingroup     UnaModules
 * @{
 */

define('BX_DOL_MANAGE_TOOLS_ADMINISTRATION', 'administration');
define('BX_DOL_MANAGE_TOOLS_COMMON', 'common');

class BxBaseModGeneralGridAdministration extends BxTemplGrid
{
	protected $MODULE;
	protected $_oModule;

	protected $_sManageType;
	protected $_sParamsDivider;

    public function __construct ($aOptions, $oTemplate = false)
    {
    	$this->_oModule = BxDolModule::getInstance($this->MODULE);
    	if(!$oTemplate)
			$oTemplate = $this->_oModule->_oTemplate;

        parent::__construct ($aOptions, $oTemplate);

        $this->_sManageType = BX_DOL_MANAGE_TOOLS_ADMINISTRATION;
        $this->_sParamsDivider = '#-#';

        $this->_sDefaultSortingOrder = 'DESC';
    }

	public function performActionDelete($aParams = array())
    {
    	$CNF = &$this->_oModule->_oConfig->CNF;

        $iAffected = 0;
        $aIds = bx_get('ids');
        if(!$aIds || !is_array($aIds)) {
            echoJson(array());
            exit;
        }

        $aIdsAffected = array ();
        foreach($aIds as $iId) {
			$aContentInfo = $this->_getContentInfo($iId);
	    	if($this->_oModule->checkAllowedDelete($aContentInfo) !== CHECK_ACTION_RESULT_ALLOWED)
	    		continue;

        	if(!$this->_doDelete($iId, $aParams))
                continue;

			if(!$this->_onDelete($iId, $aParams))
				continue;

			$this->_oModule->checkAllowedDelete($aContentInfo, true);

            $aIdsAffected[] = $iId;
            $iAffected++;
        }

        echoJson($iAffected ? array('grid' => $this->getCode(false), 'blink' => $aIdsAffected) : array('msg' => _t($CNF['T']['grid_action_err_delete'])));
    }

    protected function _getActionDelete($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = array())
    {
		if($this->_sManageType == BX_DOL_MANAGE_TOOLS_ADMINISTRATION && $this->_oModule->checkAllowedEditAnyEntry() !== CHECK_ACTION_RESULT_ALLOWED)
			return '';

    	return $this->_getActionDefault($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _getActionSettings($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = array())
    {
    	$sJsObject = $this->_oModule->_oConfig->getJsObject('manage_tools');
    	$sMenuName = $this->_oModule->_oConfig->CNF['OBJECT_MENU_MANAGE_TOOLS'];

    	$oMenu = BxDolMenu::getObjectInstance($sMenuName);
    	$oMenu->setContentId($aRow['id']);

    	$sMenu = $oMenu->getCode();
    	if(empty($sMenu))
    		return '';

    	$a['attr'] = array_merge($a['attr'], array(
    		"bx-popup-id" => $sMenuName . "-" . $aRow['id'],
    		"onclick" => "$(this).off('click'); " . $sJsObject . ".onClickSettings('" . $sMenuName . "', this);"
    	));

    	return $this->_getActionDefault($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _getFilterSelectOne($sFilterName, $sFilterValue, $aFilterValues)
    {
        if(empty($sFilterName) || empty($aFilterValues))
            return '';

		$CNF = &$this->_oModule->_oConfig->CNF;
		$sJsObject = $this->_oModule->_oConfig->getJsObject('manage_tools');

		foreach($aFilterValues as $sKey => $sValue)
			$aFilterValues[$sKey] = _t($sValue);

        $aInputModules = array(
            'type' => 'select',
            'name' => $sFilterName,
            'attrs' => array(
                'id' => 'bx-grid-' . $sFilterName . '-' . $this->_sObject,
                'onChange' => 'javascript:' . $sJsObject . '.onChangeFilter(this)'
            ),
            'value' => $sFilterValue,
            'values' => array_merge(array('' => _t($CNF['T']['filter_item_select_one_' . $sFilterName])), $aFilterValues)
        );

        $oForm = new BxTemplFormView(array());
        return $oForm->genRow($aInputModules);
    }

	protected function _getSearchInput()
    {
        $sJsObject = $this->_oModule->_oConfig->getJsObject('manage_tools');

        $aInputSearch = array(
            'type' => 'text',
            'name' => 'search',
            'attrs' => array(
                'id' => 'bx-grid-search-' . $this->_sObject,
                'onKeyup' => 'javascript:$(this).off(\'keyup focusout\'); ' . $sJsObject . '.onChangeFilter(this)',
                'onBlur' => 'javascript:' . $sJsObject . '.onChangeFilter(this)',
            )
        );

        $oForm = new BxTemplFormView(array());
        return $oForm->genRow($aInputSearch);
    }

	protected function _getContentInfo($iId)
    {
    	return $this->_oModule->_oDb->getContentInfoById($iId);
    }
    
    protected function _getProfileObject($iId)
    {
        return BxDolProfile::getInstanceMagic($iId);
    }

	protected function _getManageAccountUrl($sFilter = '')
    {
    	$sModuleAccounts = 'bx_accounts';
    	if(!BxDolModuleQuery::getInstance()->isEnabledByName($sModuleAccounts))
    		return '';

		$sTypeUpc = strtoupper($this->_sManageType);
		$oModuleAccounts = BxDolModule::getInstance($sModuleAccounts);
		if(!$oModuleAccounts || empty($oModuleAccounts->_oConfig->CNF['URL_MANAGE_' . $sTypeUpc]))
			return '';

		$sLink = $oModuleAccounts->_oConfig->CNF['URL_MANAGE_' . $sTypeUpc];

		$sLink = BX_DOL_URL_ROOT . BxDolPermalinks::getInstance()->permalink($sLink);
		
		if(!empty($sFilter))
			$sLink = bx_append_url_params($sLink, array('filter' => $sFilter));

		return $sLink;
    }

	protected function _doDelete($iId, $aParams = array())
    {
    	return $this->_oModule->serviceDeleteEntity($iId) == '';
    }

    protected function _onDelete($iId, $aParams = array())
    {
    	return true;
    }

    protected function _getCellSwitcher ($mixedValue, $sKey, $aField, $aRow)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if(!in_array($aRow[$CNF['FIELD_STATUS']], array('active', 'hidden'))) {
            $sStatusKey = '_sys_status_' . $aRow[$CNF['FIELD_STATUS']];
            if(!empty($CNF['T']['txt_status_' . $aRow[$CNF['FIELD_STATUS']]]))
                $sStatusKey = $CNF['T']['txt_status_' . $aRow[$CNF['FIELD_STATUS']]];

            return parent::_getCellDefault(_t($sStatusKey), $sKey, $aField, $aRow);
        }

        return parent::_getCellSwitcher ($mixedValue, $sKey, $aField, $aRow);
    }

    protected function _getCellHeaderReports ($sKey, $aField)
    {
        $s = parent::_getCellHeaderDefault($sKey, $aField);
        return preg_replace ('/<a(.*?)>(.*?)<\/a>/', '<a$1 title="' . bx_html_attribute(_t('_sys_txt_reports_title')) . '"><i class="sys-icon exclamation-triangle"></i></a>', $s);
    }
    
    protected function _getCellReports($mixedValue, $sKey, $aField, $aRow)
    {
        if ($mixedValue == 0){
            $mixedValue = '';
        }
        else{
            $CNF = &$this->_oModule->_oConfig->CNF;
            $oReports = isset($CNF['OBJECT_REPORTS']) ? BxDolReport::getObjectInstance($CNF['OBJECT_REPORTS'], $aRow[$CNF['FIELD_ID']]) : null;
            if ($oReports){
                $mixedValue = $oReports->getCounter().$oReports->getJsScript();
            }
        }
        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }
}

/** @} */
