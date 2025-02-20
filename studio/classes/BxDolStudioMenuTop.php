<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaStudio UNA Studio
 * @{
 */

require_once(BX_DOL_DIR_STUDIO_INC . 'utils.inc.php');

define('BX_DOL_STUDIO_MT_LEFT', 'left');
define('BX_DOL_STUDIO_MT_CENTER', 'center');
define('BX_DOL_STUDIO_MT_RIGHT', 'right');

define('BX_DOL_STUDIO_MTB_CIRCLE', 'circ');
define('BX_DOL_STUDIO_MTB_RECTANGLE', 'rect');

class BxDolStudioMenuTop extends BxDol
{
    public static $sHistorySessionKey = 'sys_studio_history';
    public static $iHistoryLength = 5;

    protected $aItems;
    protected $aVisible;
    protected $aSelected;

    public function __construct()
    {
        parent::__construct();

        $this->aVisible = array(
            BX_DOL_STUDIO_MT_LEFT => true,
            BX_DOL_STUDIO_MT_CENTER => true,
            BX_DOL_STUDIO_MT_RIGHT => true
        );

        $this->aSelected = array(
            BX_DOL_STUDIO_MT_LEFT => '',
            BX_DOL_STUDIO_MT_CENTER => '',
            BX_DOL_STUDIO_MT_RIGHT => ''
        );

        $this->aItems = array(
            BX_DOL_STUDIO_MT_LEFT => '',
            BX_DOL_STUDIO_MT_CENTER => '',
            BX_DOL_STUDIO_MT_RIGHT => ''
        );

        $this->aItems[BX_DOL_STUDIO_MT_LEFT] = array(
            'template' => 'menu_floating_blocks.html',
            'menu_items' => array(
                'launcher' => array(
                    'name' => 'launcher',
                    'icon' => 'th',
                    'link' => BX_DOL_URL_STUDIO,
                    'title' => '_adm_tmi_cpt_launcher'
                ),
                'site' => array(
                    'name' => 'site',
                    'icon' => 'home',
                    'link' => BX_DOL_URL_ROOT,
                    'title' => '_adm_tmi_cpt_site'
                ),
                'logout' => array(
                    'name' => 'logout',
                    'icon' => 'sign-out-alt',
                    'link' => 'logout.php',
                    'onclick' => $this->getJsObject() . '.clickLogout(this);',
                    'title' => '_adm_tmi_cpt_logout'
                )
            )
        );

        $aHistory = BxDolSession::getInstance()->getValue(self::$sHistorySessionKey);
        if(!empty($aHistory) && is_array($aHistory))
            $this->aItems[BX_DOL_STUDIO_MT_LEFT]['menu_items'] = bx_array_insert_before(array_reverse($aHistory), $this->aItems[BX_DOL_STUDIO_MT_LEFT]['menu_items'], 'site');

        $aMatch = array();
        $iResult = preg_match("/^(https?:\/\/)?(.*)\/$/", BX_DOL_URL_ROOT, $aMatch);
        $this->aItems[BX_DOL_STUDIO_MT_CENTER] = $iResult ? $aMatch[2] : '';

        $this->aItems[BX_DOL_STUDIO_MT_RIGHT] = array(
            'template' => 'menu_top_toolbar.html',
            'menu_items' => array(
                'edit' => array(
                    'name' => 'edit',
                    'icon' => 'magic',
                    'onclick' => $this->getJsObject() . '.clickEdit(this);',
                    'title' => '_adm_tmi_cpt_edit'
                ),
                'favorite' => array(
                    'name' => 'favorite',
                    'icon' => 'star',
                    'onclick' => $this->getJsObject() . '.clickFavorite(this);',
                    'title' => '_adm_tmi_cpt_favorite'
                ),
                'extensions' => array(
                    'name' => 'extensions',
                    'icon' => 'plus',
                    'link' => BX_DOL_URL_STUDIO . 'store.php?page=goodies',
                    'title' => '_adm_tmi_cpt_extensions'
                ),
                'tour' => array(
                    'name' => 'tour',
                    'icon' => 'question',
                    'link' => 'javascript:void(0);',
                    'onclick' => 'glTour.start()',
                    'title' => '_adm_tmi_cpt_tour'
                )
            )
        );
    }

    public function setContent($sPosition, $mixedContent)
    {
        $this->aItems[$sPosition] = $mixedContent;
    }

    public function setSelected($sPosition, $sValue)
    {
        if(!isset($this->aItems[$sPosition]['menu_items'][$sValue]))
            return;

        $this->aItems[$sPosition]['menu_items'][$sValue]['selected'] = true;
    }

    public function setVisible($sPosition, $bValue = true)
    {
        $this->aVisible[$sPosition] = $bValue;
    }

    public function setVisibleAll($bValue = true)
    {
        $this->aVisible = array(
            BX_DOL_STUDIO_MT_LEFT => $bValue,
            BX_DOL_STUDIO_MT_CENTER => $bValue,
            BX_DOL_STUDIO_MT_RIGHT => $bValue
        );
    }
}

/** @} */
