<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Posts Posts
 * @ingroup     UnaModules
 *
 * @{
 */

class BxPostsConfig extends BxBaseModTextConfig
{
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $aMenuItems2Methods = array (
            'edit-post' => 'checkAllowedEdit',
            'delete-post' => 'checkAllowedDelete',
        );

        $this->CNF = array_merge($this->CNF, array (

            // module icon
            'ICON' => 'file-alt col-red3',

            // database tables
            'TABLE_ENTRIES' => $aModule['db_prefix'] . 'posts',
            'TABLE_ENTRIES_FULLTEXT' => 'title_text',

            // database fields
            'FIELD_ID' => 'id',
            'FIELD_AUTHOR' => 'author',
            'FIELD_ADDED' => 'added',
            'FIELD_CHANGED' => 'changed',
            'FIELD_PUBLISHED' => 'published',
            'FIELD_TITLE' => 'title',
            'FIELD_TEXT' => 'text',
            'FIELD_TEXT_ID' => 'post-text',
            'FIELD_CATEGORY' => 'cat',
            'FIELD_ALLOW_VIEW_TO' => 'allow_view_to',
            'FIELD_COVER' => 'covers',
            'FIELD_PHOTO' => 'pictures',
            'FIELD_VIDEO' => 'videos',
            'FIELD_FILE' => 'files',
            'FIELD_POLL' => 'polls',
            'FIELD_THUMB' => 'thumb',
            'FIELD_ATTACHMENTS' => 'attachments',
            'FIELD_VIEWS' => 'views',
            'FIELD_COMMENTS' => 'comments',
            'FIELD_STATUS' => 'status',
            'FIELD_STATUS_ADMIN' => 'status_admin',
            'FIELD_LOCATION_PREFIX' => 'location',
            'FIELD_LABELS' => 'labels',
            'FIELD_ANONYMOUS' => 'anonymous',
            'FIELDS_WITH_KEYWORDS' => 'auto', // can be 'auto', array of fields or comma separated string of field names, works only when OBJECT_METATAGS is specified
            'FIELD_DISABLE_COMMENTS' => 'disable_comments',
            
            // page URIs
            'URI_VIEW_ENTRY' => 'view-post',
            'URI_AUTHOR_ENTRIES' => 'posts-author',
            'URI_ENTRIES_BY_CONTEXT' => 'posts-context',
            'URI_ADD_ENTRY' => 'create-post',
            'URI_EDIT_ENTRY' => 'edit-post',
            'URI_MANAGE_COMMON' => 'posts-manage',

            'URL_HOME' => 'page.php?i=posts-home',
            'URL_POPULAR' => 'page.php?i=posts-popular',
            'URL_UPDATED' => 'page.php?i=posts-updated',
            'URL_MANAGE_COMMON' => 'page.php?i=posts-manage',
            'URL_MANAGE_ADMINISTRATION' => 'page.php?i=posts-administration',

            // some params
            'PARAM_CHARS_SUMMARY' => 'bx_posts_summary_chars',
            'PARAM_CHARS_SUMMARY_PLAIN' => 'bx_posts_plain_summary_chars',
            'PARAM_NUM_RSS' => 'bx_posts_rss_num',
            'PARAM_SEARCHABLE_FIELDS' => 'bx_posts_searchable_fields',
            'PARAM_PER_PAGE_BROWSE_SHOWCASE' => 'bx_posts_per_page_browse_showcase',

            // objects
            'OBJECT_STORAGE' => 'bx_posts_covers',
            'OBJECT_STORAGE_FILES' => 'bx_posts_files',
            'OBJECT_STORAGE_PHOTOS' => 'bx_posts_photos',
            'OBJECT_STORAGE_VIDEOS' => 'bx_posts_videos',
            'OBJECT_IMAGES_TRANSCODER_PREVIEW' => 'bx_posts_preview',
            'OBJECT_IMAGES_TRANSCODER_GALLERY' => 'bx_posts_gallery',
            'OBJECT_IMAGES_TRANSCODER_COVER' => 'bx_posts_cover',
            'OBJECT_IMAGES_TRANSCODER_PREVIEW_FILES' => 'bx_posts_preview_files',
            'OBJECT_IMAGES_TRANSCODER_GALLERY_FILES' => 'bx_posts_gallery_files',
            'OBJECT_IMAGES_TRANSCODER_PREVIEW_PHOTOS' => 'bx_posts_preview_photos',
            'OBJECT_IMAGES_TRANSCODER_GALLERY_PHOTOS' => 'bx_posts_gallery_photos',
            'OBJECT_VIDEOS_TRANSCODERS' => array(
                'poster' => 'bx_posts_videos_poster', 
            	'poster_preview' => 'bx_posts_videos_poster_preview',
            	'mp4' => 'bx_posts_videos_mp4', 
            	'mp4_hd' => 'bx_posts_videos_mp4_hd'
            ),
            'OBJECT_VIDEO_TRANSCODER_HEIGHT' => '480px',
            'OBJECT_REPORTS' => 'bx_posts',
            'OBJECT_VIEWS' => 'bx_posts',
            'OBJECT_VOTES' => 'bx_posts',
            'OBJECT_REACTIONS' => 'bx_posts_reactions',
            'OBJECT_SCORES' => 'bx_posts',
            'OBJECT_FAVORITES' => 'bx_posts',
            'OBJECT_FEATURED' => 'bx_posts',
            'OBJECT_METATAGS' => 'bx_posts',
            'OBJECT_COMMENTS' => 'bx_posts',
            'OBJECT_CATEGORY' => 'bx_posts_cats',
            'OBJECT_PRIVACY_VIEW' => 'bx_posts_allow_view_to',
            'OBJECT_FORM_ENTRY' => 'bx_posts',
            'OBJECT_FORM_ENTRY_DISPLAY_VIEW' => 'bx_posts_entry_view',
            'OBJECT_FORM_ENTRY_DISPLAY_ADD' => 'bx_posts_entry_add',
            'OBJECT_FORM_ENTRY_DISPLAY_EDIT' => 'bx_posts_entry_edit',
            'OBJECT_FORM_ENTRY_DISPLAY_DELETE' => 'bx_posts_entry_delete',
            'OBJECT_FORM_POLL' => 'bx_posts_poll',
            'OBJECT_FORM_POLL_DISPLAY_ADD' => 'bx_posts_poll_add',
            'OBJECT_MENU_ENTRY_ATTACHMENTS' => 'bx_posts_entry_attachments', // attachments menu in create/edit forms
            'OBJECT_MENU_ACTIONS_VIEW_ENTRY' => 'bx_posts_view', // actions menu on view entry page
            'OBJECT_MENU_ACTIONS_VIEW_ENTRY_ALL' => 'bx_posts_view_actions', // all actions menu on view entry page
            'OBJECT_MENU_ACTIONS_MY_ENTRIES' => 'bx_posts_my', // actions menu on my entries page
            'OBJECT_MENU_SUBMENU' => 'bx_posts_submenu', // main module submenu
            'OBJECT_MENU_SUBMENU_VIEW_ENTRY' => 'bx_posts_view_submenu', // view entry submenu
            'OBJECT_MENU_SUBMENU_VIEW_ENTRY_MAIN_SELECTION' => 'posts-home', // first item in view entry submenu from main module submenu
            'OBJECT_MENU_SNIPPET_META' => 'bx_posts_snippet_meta', // menu for snippet meta info
            'OBJECT_MENU_MANAGE_TOOLS' => 'bx_posts_menu_manage_tools', //manage menu in content administration tools
            'OBJECT_GRID_ADMINISTRATION' => 'bx_posts_administration',
            'OBJECT_GRID_COMMON' => 'bx_posts_common',
            'OBJECT_UPLOADERS' => array('bx_posts_simple', 'bx_posts_html5'),

            // menu items which visibility depends on custom visibility checking
            'MENU_ITEM_TO_METHOD' => array (
                'bx_posts_my' => array (
                    'create-post' => 'checkAllowedAdd',
                ),
                'bx_posts_view' => $aMenuItems2Methods,
            ),

            // some language keys
            'T' => array (
                'txt_sample_single' => '_bx_posts_txt_sample_single',
            	'txt_sample_single_with_article' => '_bx_posts_txt_sample_single_with_article',
            	'txt_sample_comment_single' => '_bx_posts_txt_sample_comment_single',
            	'txt_sample_vote_single' => '_bx_posts_txt_sample_vote_single',
                'form_field_author' => '_bx_posts_form_entry_input_author',
            	'grid_action_err_delete' => '_bx_posts_grid_action_err_delete',
            	'grid_txt_account_manager' => '_bx_posts_grid_txt_account_manager',
                'filter_item_active' => '_bx_posts_grid_filter_item_title_adm_active',
            	'filter_item_hidden' => '_bx_posts_grid_filter_item_title_adm_hidden',
            	'filter_item_select_one_filter1' => '_bx_posts_grid_filter_item_title_adm_select_one_filter1',
            	'menu_item_manage_my' => '_bx_posts_menu_item_title_manage_my',
            	'menu_item_manage_all' => '_bx_posts_menu_item_title_manage_all',
                'txt_all_entries_by' => '_bx_posts_txt_all_entries_by',
                'txt_all_entries_in' => '_bx_posts_txt_all_entries_in',
                'txt_all_entries_by_author' => '_bx_posts_page_title_browse_by_author',
                'txt_all_entries_by_context' => '_bx_posts_page_title_browse_by_context',
                'txt_err_cannot_perform_action' => '_bx_posts_txt_err_cannot_perform_action',
                'txt_poll_answer_vote_do_by' => '_bx_posts_txt_poll_answer_vote_do_by',
                'txt_poll_answer_vote_counter' => '_bx_posts_txt_poll_answer_vote_counter',
                'txt_poll_answer_vote_percent' => '_bx_posts_txt_poll_answer_vote_percent'
            ),
        ));
        
        $this->_aJsClasses = array_merge($this->_aJsClasses, array(
            'manage_tools' => 'BxPostsManageTools'
        ));

        $this->_aJsObjects = array_merge($this->_aJsObjects, array(
            'manage_tools' => 'oBxPostsManageTools'
        ));

        $this->_aGridObjects = array(
            'common' => $this->CNF['OBJECT_GRID_COMMON'],
            'administration' => $this->CNF['OBJECT_GRID_ADMINISTRATION'],
        );

        $this->_bAttachmentsInTimeline = true;
    }
}

/** @} */
