<?php
/*
 * ---------------------------------------------------------------------------------------------------------------------
 * This file is part of the Presenter Activity Module for Moodle
 *
 * The Presenter Activity Module for Moodle software package is Copyright � 2008 onwards NetSapiensis AB and is provided
 * under the terms of the GNU GENERAL PUBLIC LICENSE Version 3 (GPL). This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * The Presenter Activity Module for Moodle includes Flowplayer free version. For more information on Flowplayer see
 * http://www.flowplayer.org.
 *
 * The Flowplayer Free version is released under the GNU GENERAL PUBLIC LICENSE Version 3 (GPL).
 * The GPL requires that you not remove the Flowplayer copyright notices from the user interface. See section 5.d below.
 * Commercial licenses are available. The commercial player version does not require any Flowplayer notices or texts and
 * also provides some additional features.
 *
 * ADDITIONAL TERM per GPL Section 7 for Flowplayer
 * If you convey this program (or any modifications of it) and assume contractual liability for the program to recipients
 * of it, you agree to indemnify Flowplayer, Ltd. for any liability that those contractual assumptions impose on
 * Flowplayer, Ltd.
 *
 * Except as expressly provided herein, no trademark rights are granted in any trademarks of Flowplayer, Ltd. Licensees
 * are granted a limited, non-exclusive right to use the mark Flowplayer and the Flowplayer logos in connection with
 * unmodified copies of the Program and the copyright notices required by section 5.d of the GPL license. For the
 * purposes of this limited trademark license grant, customizing the Flowplayer by skinning, scripting, or including
 * PlugIns provided by Flowplayer, Ltd. is not considered modifying the Program.
 *
 * Licensees that do modify the Program, taking advantage of the open-source license, may not use the Flowplayer mark
 * or Flowplayer logos and must change the fullscreen notice (and the non-fullscreen notice, if that option is enabled),
 * the copyright notice in the dialog box, and the notice on the Canvas as follows:
 *
 * the full screen (and non-fullscreen equivalent, if activated) noticeshould read: "Based on Flowplayer source code";
 * in the context menu (right-click menu), the link to "About Flowplayer free version #.#.#" can remain.
 * The copyright notice can remain, but must be supplemented with an additional notice, stating that the licensee
 * modified the Flowplayer. A suitable notice might read "Flowplayer Source code modified by ModOrg 2009";
 * for the canvas, the notice should read "Based on Flowplayer source code".
 * In addition, licensees that modify the Program must give the modified Program a new name that is not confusingly
 * similar to Flowplayer and may not distribute it under the name Flowplayer.
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------------------------------------------------------
 */

$string['presentation_width'] = 'Presenter width (pixels)';
$string['presentation_height'] = 'Presenter height (pixels)';
$string['player_width'] = 'Player width (pixels)';
$string['player_height'] = 'Player height (pixels)';
$string['window'] = 'Window';
$string['player_skin'] = 'Player Skin';
$string['control_bar'] = 'Control Bar';
$string['player_streching'] = 'Player Streching';
$string['volume'] = 'Volume (0 - 100%%)';
$string['buffer_length'] = 'Buffer length (seconds)';
$string['slide_streching'] = 'Slide streching';
$string['summary_height'] = 'Notes height (lines)';
$string['chapter'] = 'Chapter';
$string['video_link'] = 'Video link';
$string['video_file'] = 'Video file';
$string['chapter_name'] = 'Chapter name';
$string['video_start'] = 'Video start (hh:mm:ss)';
$string['video_end'] = 'Video end (hh:mm:ss)';
$string['audio_track'] = 'Audio track';
$string['audio_start'] = 'Audio start (hh:mm:ss)';
$string['audio_end'] = 'Audio end (hh:mm:ss)';
$string['slide_image'] = 'Slide image';
$string['summary'] = 'Notes';
$string['completion_factor'] = 'Completion factor (%%)';
$string['add_chapter'] = 'Add new chapter';
$string['nr_chapters'] = 'Number of chapters';
$string['move_down'] = 'Move down';
$string['move_up'] = 'Move up';
$string['remove'] = 'Remove';

$string['modulename'] = 'Presenter';
$string['modulenameplural'] = 'Presenters';
$string['export_long'] = 'Export presenters';
$string['export_short'] = 'Export';
$string['export_this'] = 'Export this presenter';

$string['already_exported'] = 'You have already exported this presenter. You can download it from ';

$string['import'] = 'Import presenter';
$string['download'] = 'Download';
//settings
$string['injector'] = 'Path to metadata injector file';
$string['injector_method'] = 'Type the exact path to the command-line metadata injector you want to use to inject metadata in the flv files you
				might use in presenters. E.g. /home/server/yamdi1.4/yamdi';

$string['zippath'] = 'Path to zip';
$string['zip_path_method'] = 'The location of your zip program (Unix only, optional). If specified, this will be used to create zip archives on the server. 
	You can change this setting from Server -> System Paths settings -> Path to zip';

$string['unzippath'] = 'Path to unzip';
$string['unzip_path_method'] = 'The location of your unzip program (Unix only, optional). If specified, this will be used to extract from zip archives on the server. 
	You can change this setting from Server -> System Paths settings -> Path to unzip';

$string['alert_new_window'] = 'You have to disable popup blocker for this site in order to open the presentation in a new window.';
$string['here'] = 'here';
$string['export_again'] = 'or export it again : ';
$string['xmlreader_required'] = 'The import functionality needs "xmlreader" and "zip" extensions in order to function properly. Please contact your server administrator.';
$string['zip_file_info'] = 'Location of .zip file (must be created with presenter "export" functionality)';
$string['xmlreader_error'] = 'The import functionality needs "xmlreader" and "zip" extensions in order to function properly. Please contact your server administrator.';
$string['cannot_add'] = 'Presenter could not be added';
$string['import_success'] = 'The presenter has been successfully imported. <br />You will be redirected to the ';
$string['import_after'] = 'course full page ';
$string['import_after_after'] = 'in order to rebuild the cache and see the imported presenter in ';
$string['import_error'] = 'There was an error while importing the presenter';
$string['import_str'] = 'Import a presenter';
$string['generalconfig'] = '';
$string['explaininjector'] = '';
$string['zip_path'] = '';
$string['explainzippath'] = '';
$string['unzip_path'] = '';
$string['explainunzippath'] = '';
$string['presenteradministration'] = '';

$string['or'] = 'OR: ';
$string['explain_video_link'] = 'Please enter a valid URL for a youtube video. 
            Please note that if you enter information in this field, the video file will be ignored, so this field has higher priority than the "Video file" field.';
$string['allowed_files'] = 'Allowed file types';

$string['pluginname'] = 'Presenter';

$string['no_archive'] = 'No file provided!';
$string['err_archive'] = 'Error uploading archive file!';
$string['go_back'] = 'Go back';
$string['invalid_archive'] = 'Invalid archive specified';
$string['cannot_extract'] = "Cannot extract from zip archive! Maybe it wasn't created using export?";
$string['invalid_archive_structure'] = 'Invalid archive structure!';
$string['invalid_xml'] = 'Invalid XML file';
$string['cannot_add_cm'] = 'Cannot create course module!';
$string['bad_context'] = 'Invalid context.';
$string['cannot_add_chapter'] = 'Cannot create chapter';
$string['no_course'] = 'No course id specified';
$string['no_presenter'] = 'No module id specified';
$string['cannot_export'] = 'Cannot save export file';
$string['pluginadministration'] = "Presenter administration";
$string['presenter:addinstance'] = 'Add a new Presenter';