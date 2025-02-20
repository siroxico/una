
-- TABLES
DROP TABLE IF EXISTS `bx_albums_albums`, `bx_albums_files`, `bx_albums_photos_resized`, `bx_albums_files2albums`, `bx_albums_cmts`, `bx_albums_cmts_media`, `bx_albums_votes`, `bx_albums_votes_track`, `bx_albums_votes_media`, `bx_albums_votes_media_track`, `bx_albums_views_track`, `bx_albums_views_media_track`, `bx_albums_meta_keywords`, `bx_albums_meta_keywords_media`, `bx_albums_meta_keywords_media_camera`, `bx_albums_meta_locations`, `bx_albums_meta_mentions`, `bx_albums_reports`, `bx_albums_reports_track`, `bx_albums_favorites_track`, `bx_albums_favorites_media_track`, `bx_albums_scores`, `bx_albums_scores_track`, `bx_albums_scores_media`, `bx_albums_scores_media_track`;


-- STORAGES & TRANSCODERS
DELETE FROM `sys_objects_storage` WHERE `object` = 'bx_albums_files' OR `object` = 'bx_albums_photos_resized';
DELETE FROM `sys_storage_tokens` WHERE `object` = 'bx_albums_files' OR `object` = 'bx_albums_photos_resized';

DELETE FROM `sys_objects_transcoder` WHERE `object` IN('bx_albums_preview', 'bx_albums_browse', 'bx_albums_big', 'bx_albums_video_poster_browse', 'bx_albums_video_poster_preview', 'bx_albums_video_poster_big', 'bx_albums_video_mp4', 'bx_albums_video_mp4_hd', 'bx_albums_proxy_preview', 'bx_albums_proxy_browse', 'bx_albums_proxy_cover');
DELETE FROM `sys_transcoder_filters` WHERE `transcoder_object` IN('bx_albums_preview', 'bx_albums_browse', 'bx_albums_big', 'bx_albums_video_poster_browse', 'bx_albums_video_poster_preview', 'bx_albums_video_poster_big', 'bx_albums_video_mp4', 'bx_albums_video_mp4_hd');
DELETE FROM `sys_transcoder_images_files` WHERE `transcoder_object` IN('bx_albums_preview', 'bx_albums_browse', 'bx_albums_big', 'bx_albums_video_poster_browse', 'bx_albums_video_poster_preview', 'bx_albums_video_poster_big', 'bx_albums_video_mp4', 'bx_albums_video_mp4_hd');
DELETE FROM `sys_transcoder_videos_files` WHERE `transcoder_object` IN('bx_albums_video_mp4', 'bx_albums_video_mp4_hd');


-- FORMS
DELETE FROM `sys_objects_form` WHERE `module` = 'bx_albums';
DELETE FROM `sys_form_displays` WHERE `module` = 'bx_albums';
DELETE FROM `sys_form_inputs` WHERE `module` = 'bx_albums';
DELETE FROM `sys_form_display_inputs` WHERE `display_name` LIKE 'bx_albums%';


-- COMMENTS
DELETE FROM `sys_objects_cmts` WHERE `Name` = 'bx_albums' OR `Name` = 'bx_albums_media';


-- VOTES
DELETE FROM `sys_objects_vote` WHERE `Name` = 'bx_albums' OR `Name` = 'bx_albums_media';


-- SCORES
DELETE FROM `sys_objects_score` WHERE `name` = 'bx_albums' OR `name` = 'bx_albums_media';


-- REPORTS
DELETE FROM `sys_objects_report` WHERE `name` = 'bx_albums';


-- VIEWS
DELETE FROM `sys_objects_view` WHERE `name` = 'bx_albums' OR `Name` = 'bx_albums_media';


-- FAFORITES
DELETE FROM `sys_objects_favorite` WHERE `name` IN ('bx_albums', 'bx_albums_media');


-- FEATURED
DELETE FROM `sys_objects_feature` WHERE `name` IN ('bx_albums', 'bx_albums_media');


-- CONTENT INFO
DELETE FROM `sys_objects_content_info` WHERE `name` IN ('bx_albums', 'bx_albums_media', 'bx_albums_cmts', 'bx_albums_media_cmts');

DELETE FROM `sys_content_info_grids` WHERE `object` IN ('bx_albums');


-- SEARCH EXTENDED
DELETE FROM `sys_objects_search_extended` WHERE `module` = 'bx_albums';


-- STUDIO: page & widget
DELETE FROM `tp`, `tw`, `tpw`
USING `sys_std_pages` AS `tp`, `sys_std_widgets` AS `tw`, `sys_std_pages_widgets` AS `tpw`
WHERE `tp`.`id` = `tw`.`page_id` AND `tw`.`id` = `tpw`.`widget_id` AND `tp`.`name` = 'bx_albums';
