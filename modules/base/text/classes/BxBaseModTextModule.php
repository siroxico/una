<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    BaseText Base classes for text modules
 * @ingroup     UnaModules
 *
 * @{
 */

/**
 * Base module class for text based modules
 */
class BxBaseModTextModule extends BxBaseModGeneralModule implements iBxDolContentInfoService
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    // ====== ACTIONS METHODS
    public function actionGetPoll()
    {
        $iPollId = (int)bx_get('poll_id');
        $sView = bx_process_input(bx_get('view'));

        $sMethod = 'serviceGetBlockPoll' . bx_gen_method_name($sView);
        if(!method_exists($this, $sMethod))
            return echoJson(array());

        $aBlock = $this->$sMethod($iPollId, true);
        if(empty($aBlock) || !is_array($aBlock))
            return echoJson(array());

        return echoJson(array(
            'content' => $aBlock['content']
        ));
    }
    public function actionDeletePoll()
    {
        $CNF = &$this->_oConfig->CNF;

        $iId = bx_process_input(bx_get('id'), BX_DATA_INT);
        if(empty($iId))
            return echoJson(array());

        $aResult = array();
        if($this->_oDb->deletePolls(array($CNF['FIELD_POLL_ID'] => $iId)))
            $aResult = array('code' => 0);
        else
            $aResult = array('code' => 1, 'message' => _t($CNF['txt_err_cannot_perform_action']));

        echoJson($aResult);
    }

    public function actionGetPollForm()
    {
        echo $this->_oTemplate->getPollForm();
    }

    public function actionSubmitPollForm()
    {
        echoJson($this->getPollForm());
    }


    // ====== SERVICE METHODS
    public function serviceGetBlockPollAnswers($iPollId, $bForceDisplay = false)
    {
        if(!$iPollId)
            return false;

        if(!$bForceDisplay && $this->_oDb->isPollPerformed($iPollId, bx_get_logged_profile_id()))
            return $this->serviceGetBlockPollResults($iPollId);

        return $this->_serviceTemplateFunc('entryPollAnswers', $iPollId, 'getPollInfoById');
    }

    public function serviceGetBlockPollResults($iPollId)
    {
        return $this->_serviceTemplateFunc('entryPollResults', $iPollId, 'getPollInfoById');
    }

    public function serviceGetThumb ($iContentId, $sTranscoder = '') 
    {
        $CNF = &$this->_oConfig->CNF;

        if(empty($sTranscoder) && !empty($CNF['OBJECT_IMAGES_TRANSCODER_GALLERY']))
            $sTranscoder = $CNF['OBJECT_IMAGES_TRANSCODER_GALLERY'];

        $mixedResult = $this->_getFieldValueThumb('FIELD_THUMB', $iContentId, $sTranscoder);
        return $mixedResult !== false ? $mixedResult : '';
    }

	public function serviceGetMenuAddonManageTools()
	{
		bx_import('SearchResult', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'SearchResult';
        $o = new $sClass();
        $o->unsetPaginate();
        $iNumTotal = $o->getNum();
        
        $o->fillFilters(array(
			'status' => 'hidden'
        )); 
        $iNum1 = $o->getNum();
        
        $iNum2 = 0;
        $CNF = &$this->_oConfig->CNF;
        if (isset($CNF['OBJECT_REPORTS'])){
            $o->fillFilters(array('status' => ''));
            $o->fillFiltersByObjects(array('reported' => array('value' => '0', 'field' => 'reports', 'operator' => '>')));
            $iNum2 = $o->getNum();
        }
        return array('counter1_value' => $iNum1, 'counter2_value' => $iNum2, 'counter3_value' => $iNumTotal );
	}

	public function serviceGetMenuAddonManageToolsProfileStats()
	{
		bx_import('SearchResult', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'SearchResult';
        $o = new $sClass();
        $o->fillFilters(array(
			'author' => bx_get_logged_profile_id()
        ));
        $o->unsetPaginate();

        return $o->getNum();
	}

    /**
     * Display public entries
     * @return HTML string
     */
    public function serviceBrowsePublic ($sUnitView = false, $bEmptyMessage = true, $bAjaxPaginate = true)
    {   
        return $this->_serviceBrowse ('public', $sUnitView ? array('unit_view' => $sUnitView) : false, BX_DB_PADDING_DEF, $bEmptyMessage, $bAjaxPaginate);
    }

    /**
     * Display popular entries
     * @return HTML string
     */
    public function serviceBrowsePopular ($sUnitView = false, $bEmptyMessage = true, $bAjaxPaginate = true)
    {
        return $this->_serviceBrowse ('popular', $sUnitView ? array('unit_view' => $sUnitView) : false, BX_DB_PADDING_DEF, $bEmptyMessage, $bAjaxPaginate);
    }

    /**
     * Display recently updated entries
     * @return HTML string
     */
    public function serviceBrowseUpdated ($sUnitView = false, $bEmptyMessage = true, $bAjaxPaginate = true)
    {
        return $this->_serviceBrowse ('updated', $sUnitView ? array('unit_view' => $sUnitView) : false, BX_DB_PADDING_DEF, $bEmptyMessage, $bAjaxPaginate);
    }

    /**
     * Display entries of the author
     * @return HTML string
     */
    public function serviceBrowseAuthor ($iProfileId = 0, $aParams = array())
    {
        return $this->_serviceBrowseWithParam ('author', 'profile_id', $iProfileId, $aParams);
    }

    /**
     * Display entries posted into particular context
     * @return HTML string
     */
    public function serviceBrowseContext ($iProfileId = 0, $aParams = array())
    {
        return $this->_serviceBrowseWithParam ('context', 'profile_id', $iProfileId, $aParams);
    }

    public function _serviceBrowseWithParam ($sParamName, $sParamGet, $sParamVal, $aParams = array())
    {
        if(!$sParamVal)
            $sParamVal = bx_process_input(bx_get($sParamGet), BX_DATA_INT);
        if(!$sParamVal)
            return '';

        $bEmptyMessage = true;
        if(isset($aParams['empty_message'])) {
            $bEmptyMessage = (bool)$aParams['empty_message'];
            unset($aParams['empty_message']);
        }

        $bAjaxPaginate = true;
        if(isset($aParams['ajax_paginate'])) {
            $bAjaxPaginate = (bool)$aParams['ajax_paginate'];
            unset($aParams['ajax_paginate']);
        }

        return $this->_serviceBrowse ($sParamName, array_merge(array($sParamName => $sParamVal), $aParams), BX_DB_PADDING_DEF, $bEmptyMessage, $bAjaxPaginate);
    }
    
    /**
     * Entry author block
     */
    public function serviceEntityAuthor ($iContentId = 0)
    {
        return $this->_serviceTemplateFunc ('entryAuthor', $iContentId);
    }
    
    /**
     * Entry context block
     */
    public function serviceEntityContext ($iContentId = 0)
    {
        return $this->_serviceTemplateFunc ('entryContext', $iContentId);
    }
    
    /**
     * Entry polls block
     */
    public function serviceEntityPolls ($iContentId = 0)
    {
        return $this->_serviceTemplateFunc ('entryPolls', $iContentId);
    }

    public function serviceEntityBreadcrumb ($iContentId = 0)
    {
    	if (!$iContentId)
            $iContentId = bx_process_input(bx_get('id'), BX_DATA_INT);
        if (!$iContentId)
            return false;
        $aContentInfo = $this->_oDb->getContentInfoById($iContentId);
        if (!$aContentInfo)
            return false;

		return $this->_oTemplate->entryBreadcrumb($aContentInfo);
    }

    /**
     * Delete all content by profile 
     * @param $iProfileId profile id 
     * @return number of deleted items
     */
    public function serviceDeleteEntitiesByAuthor ($iProfileId)
    {
        $a = $this->_oDb->getEntriesByAuthor((int)$iProfileId);
        if (!$a)
            return 0;

        $iCount = 0;
        foreach ($a as $aContentInfo)
            $iCount += ('' == $this->serviceDeleteEntity($aContentInfo[$this->_oConfig->CNF['FIELD_ID']]) ? 1 : 0);

        return $iCount;
    }

    // ====== PERMISSION METHODS

    /**
     * @return CHECK_ACTION_RESULT_ALLOWED if access is granted or error message if access is forbidden. So make sure to make strict(===) checking.
     */
    public function checkAllowedSetThumb ($iContentId = 0)
    {
        // check ACL
        $aCheck = checkActionModule($this->_iProfileId, 'set thumb', $this->getName(), false);
        if ($aCheck[CHECK_ACTION_RESULT] !== CHECK_ACTION_RESULT_ALLOWED)
            return $aCheck[CHECK_ACTION_MESSAGE];
        return CHECK_ACTION_RESULT_ALLOWED;
    }

    // ====== COMMON METHODS
    
    public function isEntryActive($aContentInfo)
    {
        $CNF = &$this->_oConfig->CNF;

        if($aContentInfo[$CNF['FIELD_AUTHOR']] == bx_get_logged_profile_id() || $this->_isModerator())
            return true;

        if(isset($CNF['FIELD_STATUS']) && $aContentInfo[$CNF['FIELD_STATUS']] != 'active')
            return false;

        if(isset($CNF['FIELD_STATUS_ADMIN']) && $aContentInfo[$CNF['FIELD_STATUS_ADMIN']] != 'active')
            return false;

        return true;        
    }

    public function getPollForm()
    {
        $CNF = &$this->_oConfig->CNF;

        if(empty($CNF['OBJECT_FORM_POLL']))
            return array('code' => 1, 'message' => '_sys_txt_error_occured');

        $iProfileId = bx_get_logged_profile_id();

        $oForm = BxDolForm::getObjectInstance($CNF['OBJECT_FORM_POLL'], $CNF['OBJECT_FORM_POLL_DISPLAY_ADD'], $this->_oTemplate);
        $oForm->aFormAttrs['action'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'submit_poll_form/';

        $oForm->initChecker();
        if($oForm->isSubmittedAndValid()) {
            $iId = $oForm->insert();
            if($iId)
                return array('code' => 0, 'id' => $iId, 'item' => $this->_oTemplate->getPollItem($iId, $iProfileId, array(
                    'manage' => true
                )));
            else
                return array('code' => 2, 'message' => '_sys_txt_error_entry_creation');
        }

        return array('form' => $oForm->getCode(), 'form_id' => $oForm->id);
    }

    
    // ====== PROTECTED METHODS
    protected function _getImagesForTimelinePostAttach($aEvent, $aContentInfo, $sUrl, $aBrowseParams = array())
    {
        $CNF = &$this->_oConfig->CNF;

        $aResults = parent::_getImagesForTimelinePostAttach($aEvent, $aContentInfo, $sUrl, $aBrowseParams);
        if(!$this->_oConfig->isAttachmentsInTimeline() || empty($CNF['OBJECT_STORAGE_PHOTOS']) || empty($CNF['OBJECT_IMAGES_TRANSCODER_GALLERY_PHOTOS']))
            return $aResults;

        $iContentId = (int)$aContentInfo[$CNF['FIELD_ID']];
        $oStorage = BxDolStorage::getObjectInstance($CNF['OBJECT_STORAGE_PHOTOS']);

        $aGhostFiles = $oStorage->getGhosts($this->serviceGetContentOwnerProfileId($iContentId), $iContentId);
        if(!$aGhostFiles)
            return array();

        $oTranscoder = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_GALLERY_PHOTOS']);

        foreach ($aGhostFiles as $k => $a) {
            $sPhotoSrc = $oTranscoder->getFileUrl($a['id']);
            if(empty($sPhotoSrc))
                continue;

            $aResults[] = array(
                'src' => $sPhotoSrc,
                'src_medium' => $sPhotoSrc,
                'src_orig' => $oStorage->getFileUrlById($a['id']),
            );
        }

        return $aResults;
    }

    protected function _getVideosForTimelinePostAttach($aEvent, $aContentInfo, $sUrl, $aBrowseParams = array())
    {
        $CNF = &$this->_oConfig->CNF;

        $aResults = parent::_getVideosForTimelinePostAttach($aEvent, $aContentInfo, $sUrl, $aBrowseParams); 
        if(!$this->_oConfig->isAttachmentsInTimeline() || empty($CNF['OBJECT_STORAGE_VIDEOS']) || empty($CNF['OBJECT_VIDEOS_TRANSCODERS']))
            return $aResults;

        $iContentId = (int)$aContentInfo[$CNF['FIELD_ID']];
        $oStorage = BxDolStorage::getObjectInstance($CNF['OBJECT_STORAGE_VIDEOS']);

        $aGhostFiles = $oStorage->getGhosts($this->serviceGetContentOwnerProfileId($iContentId), $iContentId);
        if(!$aGhostFiles)
            return array();

        $oTcPoster = BxDolTranscoderVideo::getObjectInstance($CNF['OBJECT_VIDEOS_TRANSCODERS']['poster']);
        $oTcMp4 = BxDolTranscoderVideo::getObjectInstance($CNF['OBJECT_VIDEOS_TRANSCODERS']['mp4']);
        $oTcMp4Hd = BxDolTranscoderVideo::getObjectInstance($CNF['OBJECT_VIDEOS_TRANSCODERS']['mp4_hd']);
        if(!$oTcPoster || !$oTcMp4 || !$oTcMp4Hd)
            return array();

        $aResults = array();
        foreach ($aGhostFiles as $k => $a) 
            $aResults[$a['id']] = array(
                'id' => $a['id'],
                'src_poster' => $oTcPoster->getFileUrl($a['id']),
                'src_mp4' => $oTcMp4->getFileUrl($a['id']),
                'src_mp4_hd' => $oTcMp4Hd->getFileUrl($a['id']),
            );

        return $aResults;
    }

    protected function _getFilesForTimelinePostAttach($aEvent, $aContentInfo, $sUrl, $aBrowseParams = array())
    {
        $CNF = &$this->_oConfig->CNF;

        $aResults = parent::_getFilesForTimelinePostAttach($aEvent, $aContentInfo, $sUrl, $aBrowseParams);
        if(!$this->_oConfig->isAttachmentsInTimeline() || empty($CNF['OBJECT_STORAGE_FILES']))
            return $aResults;

        $iContentId = (int)$aContentInfo[$CNF['FIELD_ID']];
        $oStorage = BxDolStorage::getObjectInstance($CNF['OBJECT_STORAGE_FILES']);

        $aGhostFiles = $oStorage->getGhosts($this->serviceGetContentOwnerProfileId($iContentId), $iContentId);
        if(!$aGhostFiles)
            return array();

        $oTranscoder = null;
        if(!empty($CNF['OBJECT_IMAGES_TRANSCODER_GALLERY_FILES']))
            $oTranscoder = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_GALLERY_FILES']);

        foreach ($aGhostFiles as $k => $a) {
            $bImage = in_array($a['ext'], array('jpg', 'jpeg', 'png', 'gif'));

            $sPhotoSrc = '';
            if($bImage && $oTranscoder)
                $sPhotoSrc = $oTranscoder->getFileUrl($a['id']);

            if(empty($sPhotoSrc) && $oStorage)
                $sPhotoSrc = $this->_oTemplate->getIconUrl($oStorage->getIconNameByFileName($a['file_name']));

            $sUrl = $oStorage->getFileUrlById($a['id']);

            $aResults[] = array(
                'src' => $sPhotoSrc,
                'src_medium' => $sPhotoSrc,
                'src_orig' => $bImage ? $sUrl : '',
                'url' => !$bImage ? $sUrl : ''
            );
        }

        return $aResults;
    }

    protected function _getContentForTimelinePost($aEvent, $aContentInfo, $aBrowseParams = array())
    {
        $aResult = parent::_getContentForTimelinePost($aEvent, $aContentInfo, $aBrowseParams);
        if(!$this->_oConfig->isAttachmentsInTimeline())
            return $aResult;

        $CNF = &$this->_oConfig->CNF;

        $iProfile = bx_get_logged_profile_id();
        $bDynamic = isset($aBrowseParams['dynamic_mode']) && $aBrowseParams['dynamic_mode'] === true;

        $sPolls = '';
        $aPolls = $this->_oDb->getPolls(array('type' => 'content_id', 'content_id' => (int)$aContentInfo[$CNF['FIELD_ID']]));
        if(!empty($aPolls) && is_array($aPolls)) {
            foreach($aPolls as $aPoll) {
                $sPoll = $this->_oTemplate->getPollItem($aPoll, $iProfile, array(
                    'dynamic' => $bDynamic,
                    'switch_menu' => false
                ));
                if(empty($sPoll))
                    continue;

                $sPolls .= $sPoll;
            }

            if(!empty($sPolls)) {
                $sInclude = '';
                $sInclude .= $this->_oTemplate->addJs(array('polls.js'), $bDynamic);
                $sInclude .= $this->_oTemplate->addCss(array('polls.css'), $bDynamic);

                $aResult['raw'] = ($bDynamic ? $sInclude : '') . $this->_oTemplate->getJsCode('poll') . $this->_oTemplate->parseHtmlByName('poll_items_showcase.html', array(
                    'js_object' => $this->_oConfig->getJsObject('poll'),
                    'html_id' => $this->_oConfig->getHtmlIds('polls_showcase') . $aEvent['id'],
                    'polls' => $sPolls
                ));
            }
        }

        return $aResult;
    }

    protected function _buildRssParams($sMode, $aArgs)
    {
        $aParams = array ();
        $sMode = bx_process_input($sMode);
        switch ($sMode) {
            case 'author':
                $aParams = array('author' => isset($aArgs[0]) ? (int)$aArgs[0] : '');
                break;
        }

        return $aParams;
    }
}

/** @} */
