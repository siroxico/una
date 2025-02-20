<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defdroup    Channels Channels
 * @indroup     UnaModules
 *
 * @{
 */

/**
 * Channels profiles module.
 */

class BxCnlModule extends BxBaseModGroupsModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }
   
    public function checkAllowedSubscribeAdd (&$aDataEntry, $isPerformAction = false)
    {
        return parent::_checkAllowedSubscribeAdd ($aDataEntry, $isPerformAction);
    }
    
    /**
     * Process Hash Tag
     * 
     * @param string $sHashtag - hashtag to be processed.
     * @param string $sModuleName - module name.
     * @param integer $iContentId - ID of the content which has the hashtag.
     * @param integer $iAuthorId - action's author id.
     */
    function processHashtag($sHashtag, $sModuleName, $iContentId, $iAuthorId)
    {
        /*
         * Note! For now metatag object name is used here as module name, because usually it's equal to module's name. This should be changed in Ticket #1596
         * For now if module cannot be created then a channel for such tag shouldn't be created too.
         */
        $oModule = BxDolModule::getInstance($sModuleName);
        if(empty($oModule))
            return;

        $aCheck = checkActionModule($this->_iProfileId, 'create channel auto', $this->getName(), false);
        $mixedCnlId = $this->_oDb->getChannelIdByName($sHashtag);
        if (empty($mixedCnlId) && ($aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED)){
            $CNF = &$this->_oConfig->CNF;
            $oAccountQuery = BxDolAccountQuery::getInstance();
            $aOperators = $oAccountQuery->getOperators();
            if(count($aOperators) > 0){
                $oProfile = BxDolProfile::getInstanceByAccount($aOperators[0]);
                $aContent = $this->serviceEntityAdd($oProfile->id(), array($CNF['FIELD_NAME'] => $sHashtag));
                checkActionModule($this->_iProfileId, 'create channel auto', $this->getName(), true);
                if (isset($aContent['content']) && isset($aContent['content']['id']))
                    $mixedCnlId = $aContent['content']['id'];
            }
        }

        if(empty($mixedCnlId) || (int)$this->_oDb->checkContentInChannel($iContentId, $mixedCnlId, $sModuleName, $iAuthorId) != 0)
            return;

        $iId = (int)$this->_oDb->addContentToChannel($iContentId, $mixedCnlId, $sModuleName, $iAuthorId);
        if($iId == 0)
            return;

        $oCnlProfile = BxDolProfile::getInstanceByContentAndType($mixedCnlId, $this->_oConfig->getName());
        if(!$oCnlProfile)
            return;

        $iCnlProfileId = $oCnlProfile->id();

        bx_alert($this->_aModule['name'], 'hashtag_added', $iId, $iCnlProfileId, array(
            'object_author_id' => $iAuthorId, 
            'privacy_view' => -$iCnlProfileId,

            'timeline_group' => array(
                'by' => $sModuleName . '_' . $iAuthorId . '_' . $iContentId,
                'field' => 'owner_id'
            )
        ));

        bx_alert($this->_aModule['name'], 'hashtag_added_notif', $mixedCnlId, $iCnlProfileId, array(
            'object_author_id' => $iAuthorId, 
            'privacy_view' => -$iCnlProfileId, 
            'subobject_id' => $iId,

            'content_id' => $iContentId,
            'content_author_id' => $iAuthorId
        ));
    }
    
    function removeContentFromChannel($iContentId, $sModuleName)
    {
        $oDolProfileQuery = BxDolProfileQuery::getInstance();
        
        $aData = $this->_oDb->getDataByContent($iContentId, $sModuleName);
        foreach ($aData as $aRow) {
            $iProfileInfo = $oDolProfileQuery->getProfileByContentAndType($aRow['cnl_id'], $this->_aModule['name']);
            if(is_array($iProfileInfo)){
                bx_alert($this->_aModule['name'], 'hashtag_deleted', $aRow['id'], $iProfileInfo['id']);
                bx_alert($this->_aModule['name'], 'hashtag_deleted_notif', $aRow['cnl_id'], $iProfileInfo['id'], array('subobject_id' => $aRow['id']));
            }
        }
        
        return $this->_oDb->removeContentFromChannel($iContentId, $sModuleName);
    }

    public function serviceEntityBreadcrumb($iContentId = 0)
    {
    	if(!$iContentId)
            $iContentId = bx_process_input(bx_get('id'), BX_DATA_INT);
        if(!$iContentId)
            return false;

        $aContentInfo = $this->_oDb->getContentInfoById($iContentId);
        if(!$aContentInfo)
            return false;

        return $this->_oTemplate->entryBreadcrumb($aContentInfo);
    }

    public function serviceEntityParent($iContentId = 0)
    {
        return $this->_serviceTemplateFunc('entryParent', $iContentId);
    }

    public function serviceEntityChilds($iContentId = 0)
    {
        return $this->_serviceTemplateFunc('entryChilds', $iContentId);
    }

    function serviceSearchResultByHashtag($iContentId = 0)
    {
        $CNF = &$this->_oConfig->CNF;
        
        $oSearch = new BxTemplSearch();
        $oSearch->setLiveSearch(0);
        $oSearch->setMetaType('keyword');
        $aContentInfo = $this->_oDb->getContentInfoById(bx_get('id'));
        $_GET['keyword'] = $aContentInfo[$CNF['FIELD_NAME']];
        $sCode = $oSearch->response();
        if (!$sCode)
            $sCode = $oSearch->getEmptyResult();
        
        return $sCode;
    }
    
    /**
     * Data for Timeline module
     */
    public function serviceGetTimelineData()
    {
        $sModule = $this->_aModule['name'];

        return array(
            'handlers' => array(
                array('group' => $sModule . '_hastag', 'type' => 'insert', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_added', 'module_name' => $sModule, 'module_method' => 'get_timeline_post_hashtag', 'module_class' => 'Module',  'groupable' => 0, 'group_by' => ''),
                array('group' => $sModule . '_hastag', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_deleted')
            ),
            'alerts' => array(
                array('unit' => $sModule, 'action' => 'hashtag_added'),
                array('unit' => $sModule, 'action' => 'hashtag_deleted')
            )
        );
    }
    
    public function serviceGetTimelinePostHashtag($aEvent, $aBrowseParams = array())
    {
        if(empty($aEvent) || !is_array($aEvent))
            return false;

        $aEventContent = $this->_oDb->getContentById($aEvent['object_id']);
        if(empty($aEventContent) || !is_array($aEventContent))
            return false;

        if(!BxDolRequest::serviceExists($aEventContent['module_name'], 'get_timeline_post'))
            return false;      

        return BxDolService::call($aEventContent['module_name'], 'get_timeline_post', array(array('owner_id' => $aEventContent['author_id'], 'object_id' => $aEventContent['content_id'])));
    }
    
    /**
     * Data for Notifications module
     */
    public function serviceGetNotificationsData()
    {      
        $a = parent::serviceGetNotificationsData();

        $sModule = $this->_aModule['name'];

        $a['handlers'][] = array('group' => $sModule . '_hastag_notif', 'type' => 'insert', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_added_notif', 'module_name' => $sModule, 'module_method' => 'get_notifications_post_hashtag', 'module_class' => 'Module');
        $a['handlers'][] = array('group' => $sModule . '_hastag_notif', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_deleted_notif');

        $a['settings'][] = array('group' => 'hastag', 'unit' => $sModule, 'action' => 'hashtag_added_notif', 'types' => array('follow_context'));

        $a['alerts'][] = array('unit' => $sModule, 'action' => 'hashtag_added_notif');
        $a['alerts'][] = array('unit' => $sModule, 'action' => 'hashtag_deleted_notif');

        return $a;
    }

    public function serviceGetNotificationsPostHashtag($aEvent)
    {
        if(empty($aEvent) || !is_array($aEvent))
            return '';

        $aContentEvent = $this->_oDb->getContentById($aEvent['subobject_id']);
        if(empty($aContentEvent) || !is_array($aContentEvent))
            return '';

        $oModule = BxDolModule::getInstance($aContentEvent['module_name']);
        if ($oModule) {
            if (isset($oModule->_oConfig->CNF['OBJECT_PRIVACY_VIEW'])){
                $oPrivacy = BxDolPrivacy::getObjectInstance($oModule->_oConfig->CNF['OBJECT_PRIVACY_VIEW']);
                if (!$oPrivacy->check($aContentEvent['content_id']))
                    return '';
            }

            $aRv = $oModule->serviceGetNotificationsPost(array(
                'object_id' => $aContentEvent['content_id']
            ));

            $aRv['lang_key'] = '_bx_channels_ntfs_txt_subobject_added';
            if(method_exists($oModule, 'serviceActAsProfile') && $oModule->serviceActAsProfile())
                $aRv['lang_key'] = '_bx_channels_ntfs_txt_subobject_added_profile';

            $aRv['entry_url'] = $this->serviceGetLink($aContentEvent['cnl_id']);
            return $aRv;
        }

        return '';
    }
    
    public function serviceBrowseAuthor($iProfileId = 0, $aParams = array())
    {
        $CNF = &$this->_oConfig->CNF;

        if(empty($iProfileId))
            $iProfileId = bx_get_logged_profile_id();

        $sResult = isset($aParams['empty_message']) && (bool)$aParams['empty_message'] === true ? MsgBox(_t('_Empty')) : '';

        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_subscriptions');
        $aProfile = $oConnection->getConnectedContent($iProfileId);
        $aVars = array();
        foreach ($aProfile as $iProfileId) {
            $oProfile = BxDolProfile::getInstance($iProfileId);
            if (!$oProfile || $oProfile->getModule() != $this->getName())
                continue;

            $iContentId = $oProfile->getContentId();
            $aContentInfo = $this->_oDb->getContentInfoById($iContentId);
            if (isset($aContentInfo[$CNF['FIELD_NAME']]))
                array_push($aVars, array('title' => $aContentInfo[$CNF['FIELD_NAME']], 'link' => BX_DOL_URL_ROOT . BxDolPermalinks::getInstance()->permalink('page.php?i=' . $CNF['URI_VIEW_ENTRY'] . '&id=' . $iContentId)));
        }

        if(empty($aVars) || !is_array($aVars))
            return $sResult;

        return $this->_oTemplate->parseHtmlByName('my_channels.html', array(
            'bx_if:show_list' => array(
                'condition' => count($aVars) > 0,
                'content' => array(
                    'bx_repeat:items' => $aVars
                )
            )
        ));
    }
    
    public function serviceDeleteProfileFromFansAndAdmins ($iProfileId)
    {
        return true;
    }

    public function serviceGetFollowingChannelsNames($iProfileId)
    {
        if (!($oConn = BxDolConnection::getObjectInstance('sys_profiles_subscriptions')))
            return array();

        if (!($aIds = $oConn->getConnectedContent($iProfileId)))
            return array();

        $a = array();
        foreach ($aIds as $iId) {
            if (!($oProfile = BxDolProfile::getInstance($iId)))
                continue;
            if ($oProfile->getModule() != $this->getName())
                continue;
            $a[] = $oProfile->getDisplayName();
        }
        return $a;
    }
    
    public function checkAllowedCompose (&$aDataEntry, $isPerformAction = false)
    {
        return _t('_sys_txt_access_denied');
    }

    public function followLabels($sModule, $iContentId)
    {
        if (!getParam('bx_channels_labels_autofollow'))
            return;
        
        if (!($oModuleProfiles = BxDolModule::getInstance($sModule)))
            return;

        if (!($aContentInfo = $oModuleProfiles->_oDb->getContentInfoById($iContentId)) || !($oProfile = BxDolProfile::getInstanceByContentAndType($iContentId, $sModule)))
            return;

        $CNF = &$oModuleProfiles->_oConfig->CNF;
        
        if (!$oModuleProfiles->serviceActAsProfile() || !isset($CNF['FIELD_LABELS']) || empty($aContentInfo[$CNF['FIELD_ID']]))
            return;

        if (!($oMetatags = BxDolMetatags::getObjectInstance($CNF['OBJECT_METATAGS'])) || !$oMetatags->keywordsIsEnabled() || !($aLabels = $oMetatags->keywordsGet($aContentInfo[$CNF['FIELD_ID']])))
            return;

        if (!($oConn = BxDolConnection::getObjectInstance('sys_profiles_subscriptions')))
            return;

        foreach ($aLabels as $sLabel) {

            if (!($iContentIdChannel = $this->_oDb->getChannelIdByName($sLabel)))
                continue;

            if (!($oProfileChannel = BxDolProfile::getInstanceByContentAndType($iContentIdChannel, 'bx_channels')))
                continue;

            $oConn->addConnection($oProfile->id(), $oProfileChannel->id());
        }
    }
}

/** @} */
