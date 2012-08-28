<?php
/*
 * ---------------------------------------------------------------------------------------------------------------------
 * This file is part of the Presenter Activity Module for Moodle
 *
 * The Presenter Activity Module for Moodle software package is Copyright © 2008 onwards NetSapiensis AB and is provided
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

require_once("../../config.php");
require_once("lib.php");
require_once("../../lib/filelib.php");
require_once("chapterlib.php");

global $CFG, $DB;

require_login();

$course_id      = optional_param('course', 0,  PARAM_INT);
$presenter_id   = optional_param('presenter', 0, PARAM_INT);
$context_id     = optional_param('context', 0, PARAM_INT);

if (!$course_id) {
    $msg = get_string('no_course', 'presenter');
    frame_error($msg);
}

if (!$presenter_id) {
    $msg = get_string('no_presenter', 'presenter');
    frame_error($msg);
}

if (!$context_id) {
    $msg = get_string('bad_context', 'presenter');
    frame_error($msg);
}

$presenter      = presenter_get_presenter($presenter_id);

$archiveName    = optional_param('archiveName', '', PARAM_CLEAN);

if (!$context = get_context_instance_by_id($context_id)) {
	$msg = get_string('bad_context', 'presenter');
    frame_error($msg);
}

$values = array (
	'name' 					=> $presenter->name,
	'nr_chapters'			=> $presenter->nr_chapters,
	'presentation_width1'	=> $presenter->presentation_width1,
	'presentation_height1'	=> $presenter->presentation_height1,
	'presentation_width2'	=> $presenter->presentation_width2,
	'presentation_height2'	=> $presenter->presentation_height2,
	'player_width1'			=> $presenter->player_width1,
	'player_height1'		=> $presenter->player_height1,
	'player_width2'			=> $presenter->player_width2,
	'player_height2'		=> $presenter->player_height2,
	'new_window'			=> $presenter->new_window,
	'player_skin'			=> $presenter->player_skin,
	'control_bar'			=> $presenter->control_bar,
	'player_streching'		=> $presenter->player_streching,
	'volume'				=> $presenter->volume,
	'buffer_length'			=> $presenter->buffer_length,
	'slide_streching'		=> $presenter->slide_streching,
	'summary_height'		=> $presenter->summary_height
);

$presenterName = str_replace(" ", "_", $presenter->name);
$date = date('Ymd');
$nr = $presenter->id;

if (!$archiveName) {
	$archiveName = $presenterName . '_' . $date . '_' . $nr . ".zip";
}

$fs = get_file_storage();
$filesdir = "{$CFG->dataroot}/filedir";
$xml_filename = "presenter.xml";

$xml_contents = '';

$ziparchive = new zip_archive();
$ziparchive->open("{$filesdir}/{$archiveName}", zip_archive::CREATE);
$ziparchive->add_directory("Presenter");
$ziparchive->add_directory("Presenter/video");
$ziparchive->add_directory("Presenter/audio");
$ziparchive->add_directory("Presenter/image");


write_tag('root');

write_tag('presenter');

//presenter details
foreach ($values as $k => $v) {
	write_tag($k, $v);
	if ($v == '') {
		close_tag($k);
	}
}

$chapters = get_chapters($presenter->id);
error_reporting(E_ALL);
write_tag('chapters');
foreach ($chapters as $chapter) {
    
    $video_filename = '';
    if ($chapter->video_file) {
        $files = $fs->get_area_files($context->id, 'mod_presenter', 'video', $chapter->id, 'sortorder', false);
        $vfile = array_pop($files);
        if (is_object($vfile)) {
            $video_filename = "{$vfile->get_filename()}";
            $chapter->video_file = $video_filename;
            
            $vfile->archive_file($ziparchive, "Presenter/video/{$vfile->get_filename()}");

        } else {
            $chapter->video_file = '';
        }
    }
    if ($chapter->audio_track) {
        $files = $fs->get_area_files($context->id, 'mod_presenter', 'audio', $chapter->id, 'sortorder', false);
        $afile = array_pop($files);
        if (is_object($afile)) {
            $audio_filename = "{$afile->get_filename()}";
            $chapter->audio_track = $audio_filename;
            $afile->archive_file($ziparchive, "Presenter/audio/{$afile->get_filename()}");

        } else {
            $chapter->audio_track = '';
        }
    }
    if ($chapter->slide_image) {
        $files = $fs->get_area_files($context->id, 'mod_presenter', 'image', $chapter->id, 'sortorder', false);
        $sfile = array_pop($files);
        if (is_object($sfile)) {
            $slide_filename = "{$sfile->get_filename()}";
            $chapter->slide_image = $slide_filename;
            $sfile->archive_file($ziparchive, "Presenter/image/{$sfile->get_filename()}");
        } else {
            $chapter->slide_image = '';
        }
    }
	write_tag('chapter');
	$values = array (
		'order_id' 			=> $chapter->order_id,
		'chapter_name'		=> $chapter->chapter_name,
		'video_link'		=> str_replace("&", "###", $chapter->video_link),
        'video_file'        => $chapter->video_file,
		'video_start'		=> $chapter->video_start,
		'video_end'			=> $chapter->video_end,
		'audio_track'		=> $chapter->audio_track,
		'audio_start'		=> $chapter->audio_start,
		'audio_end'			=> $chapter->audio_end,
		'slide_image'		=> $chapter->slide_image,
		'summary'			=> '<![CDATA[' . $chapter->summary . ']]>',
		'layout'			=> $chapter->layout
	);

	foreach ($values as $k => $v) {
		write_tag($k, $v);
		if ($v == '') {
			close_tag($k);
		}
	}
	close_tag('chapter');
}

close_tag('chapters');
close_tag('presenter');
close_tag('root');

$ziparchive->add_file_from_string("Presenter/{$xml_filename}", $xml_contents);
$ziparchive->close();

//save file to filesystem and update DB

$fileinfo = array (
    'contextid' => $context->id,
    'component' => 'mod_presenter',
    'filearea'  => 'export',
    'itemid'    => $presenter->id,
    'filepath'  => '/',
    'filename'  => $archiveName
);

//delete old versions of this exported presenter
$files = $fs->get_area_files($context->id, 'mod_presenter', 'export', $presenter->id);
foreach ($files as $f) {
    //delete them
    $f->delete();
}

$saved_export_file = $fs->create_file_from_pathname($fileinfo, "{$filesdir}/{$archiveName}");

$presenter->export_file = $archiveName;
$DB->update_record("presenter", $presenter);

//finally, send file to the user
if (is_object($saved_export_file)) {
    @unlink("{$filesdir}/{$archiveName}");
    send_stored_file($saved_export_file);
} else {
    $msg = get_string("cannot_export", "presenter");
    frame_error($msg);
}

function write_tag($name, $value = '')
{
	global $xml_contents;
	$xml_contents .= '<' . $name . '>';
	if ($value != '') {
		$xml_contents .= $value;
		close_tag($name);
	}
}

function close_tag($name)
{
	global $xml_contents;
	$xml_contents .= '</' . $name . '>';
}

?>