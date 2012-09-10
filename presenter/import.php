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
require_once($CFG->dirroot . '/course/lib.php');
require_once("../../lib/datalib.php");
require_once("../../lib/filelib.php");
require_once('lib.php');
require_once('chapterlib.php');
require_once('../../lib/xmlize.php');

error_reporting(E_ALL);

require_login();

global $DB;

$id = required_param('id', PARAM_INT);   // course

if (!$course = $DB->get_record("course", array("id" => $id))) {
    print_error("Course ID is incorrect");
}
$section = optional_param('section', 0, PARAM_INT);

$archive = !empty($_FILES['archive']) ? $_FILES['archive'] : null;

if (is_null($archive)) {
    $err = get_string('no_archive', 'presenter');
    frame_error($err);
}

if (!empty($archive['error'])) {
    $err = get_string('err_archive', 'presenter');
    frame_error($err);
}

$ext = strtolower(end(explode(".", $archive['name'])));

if ($ext != 'zip') {
    $err = get_string("invalid_archive", 'presenter');
    frame_error($err);
}

$archivefile = $archive['tmp_name'];

$ziparchive = new zip_packer();
$files = $ziparchive->list_files($archivefile);

$temppathname = $CFG->dataroot . "/filedir/temp" . time() . "/";
$done = $ziparchive->extract_to_pathname($archivefile, $temppathname);

if (count($done) == 0) {
    $msg = get_string('cannot_extract', 'presenter');
    frame_error($msg);
}

$xml_location = $temppathname . 'Presenter/presenter.xml';
$video_folder = $temppathname . 'Presenter/video/';
$audio_folder = $temppathname . 'Presenter/audio/';
$image_folder = $temppathname . 'Presenter/image/';

$old_version = false;

if (!is_file($xml_location)) {
    //try older versions
    $xml_location = $temppathname . 'presenter.xml';
    if (!is_file($xml_location)) {
        $msg = get_string('invalid_archive_structure', 'presenter');
        frame_error($msg);
    } else {
        # old version detected #
        $old_version = true;
        $video_folder = $audio_folder = $image_folder = $temppathname;
    }
}

$xml_contents = file_get_contents($xml_location);

$xml = xmlize($xml_contents);

// :-)
if (!is_array($xml['root']['#']['presenter'][0]['#'])) {
    $msg = get_string('invalid_xml', 'presenter');
    frame_error($msg);
} else {
    $presenter = new stdClass();
    foreach ($xml['root']['#']['presenter'][0]['#'] as $field => $valuearray) {
        if ($field != 'chapters') {
            $presenter->{$field} = $valuearray[0]['#'];
        }
    }
    $presenter->course = $id;
    
    if ($section) {
        $course_section = $DB->get_record("course_sections", array('course' => $id, 'section' => $section));
    } else { //insert it in the first course section
        $course_section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE course = ? AND section > 0", array($id));
    }
    if (! $presenter->id = $DB->insert_record("presenter", $presenter)) {
        $msg = get_string('cannot_add', 'presenter');
        frame_error($msg);
    }
    
    $course_module              = new stdClass();
    $course_module->instance    = $presenter->id;
    $course_module->module      = get_presenter_module_id();
    $course_module->idnumber    = '';
    $course_module->course      = $id;
    $course_module->section     = $course_section->id;
    
    if (! $course_module->id    = $DB->insert_record("course_modules", $course_module)) {
        $DB->delete_records("presenter", array("id" => $presenter->id));
        $msg = get_string('cannot_add_cm', 'presenter');
        frame_error($msg);
    }

    if ($course_section->sequence) {
        $course_section->sequence .= ',' . $course_module->id;
    } else {
        $course_section->sequence = $course_module->id;
    }

    $DB->update_record("course_sections", $course_section);

    if (! $context = get_context_instance(CONTEXT_MODULE, $course_module->id)) {
        $msg = get_string('bad_context');
        frame_error($msg);
    }

    //chapters
    $fs = get_file_storage();
    $chapters = $xml['root']['#']['presenter'][0]['#']['chapters'][0]['#']['chapter'];
    foreach ($chapters as $i => $chapter_array) {
        
        $chapter = new stdClass();
        foreach ($chapter_array['#'] as $field => $val) {
            $chapter->{$field} = $val[0]['#'];
        }
        $chapter->presenterid = $presenter->id;
        if (! $chapter->id = $DB->insert_record("presenter_chapters", $chapter)) {
            $DB->delete_records("presenter", array("id" => $presenter->id));
            $DB->delete_records("presenter_chapters", array("presenterid" => $presenter->id));
            $DB->delete_records("course_modules", array("id" => $course_module->id));
            $msg = get_string('cannot_add_chapter', 'presenter');
            frame_error($msg . ": " . $chapter->chapter_name);
        }
        
        $real_video_folder = $video_folder;
        if ($old_version) {
            if (!empty($chapter->video_link) && is_file("{$video_folder}{$chapter->video_link}")) {

                $nameparts = explode("/", $chapter->video_link);

                foreach ($nameparts as $k => $np) {
                    if ($k < count($nameparts) - 1) {
                        $real_video_folder .= "{$np}/";
                    }
                }
                
                $chapter->video_file = end($nameparts);
                $chapter->video_link = '';
            }
        }
        if (!empty($chapter->video_file) && is_file("{$real_video_folder}{$chapter->video_file}")) {
            $file_record = new stdClass();
            $file_record->contextid = $context->id;
            $file_record->component = 'mod_presenter';
            $file_record->filearea  = 'video';
            $file_record->itemid    = $chapter->id;
            $file_record->filepath  = '/';
            $file_record->filename  = $chapter->video_file;
            
            try {
                $vfile = $fs->create_file_from_pathname($file_record, "{$real_video_folder}{$chapter->video_file}");
                $chapter->video_file = $vfile->get_itemid();
                
            } catch (file_exception $ex) {
                $chapter->video_file = '';
            }
        } else {
            $chapter->video_file = '';
        }
        $real_audio_folder = $audio_folder;
        if (!empty($chapter->audio_track) && is_file("{$audio_folder}{$chapter->audio_track}")) {
            //old version, if any subdirectories present
            if ($old_version) {
                $nameparts = explode("/", $chapter->audio_track);

                foreach ($nameparts as $k => $np) {
                    if ($k < count($nameparts) - 1) {
                        $real_audio_folder .= "{$np}/";
                    }
                }
                $chapter->audio_track = end($nameparts);
            }

            $file_record = new stdClass();
            $file_record->contextid = $context->id;
            $file_record->component = 'mod_presenter';
            $file_record->filearea  = 'audio';
            $file_record->itemid    = $chapter->id;
            $file_record->filepath  = '/';
            $file_record->filename  = $chapter->audio_track;

            try {
                $vfile = $fs->create_file_from_pathname($file_record, "{$real_audio_folder}{$chapter->audio_track}");
                $chapter->audio_track = $vfile->get_itemid();
                
            } catch (file_exception $ex) {
                $chapter->audio_track = '';
            }
        } else {
            $chapter->audio_track = '';
        }
        $real_image_folder = $image_folder;
        if (!empty($chapter->slide_image) && is_file("{$image_folder}{$chapter->slide_image}")) {

            //old version, if any subdirectories present
            if ($old_version) {
                $nameparts = explode("/", $chapter->slide_image);

                foreach ($nameparts as $k => $np) {
                    if ($k < count($nameparts) - 1) {
                        $real_image_folder .= "{$np}/";
                    }
                }
                $chapter->slide_image = end($nameparts);
            }

            $file_record = new stdClass();
            $file_record->contextid = $context->id;
            $file_record->component = 'mod_presenter';
            $file_record->filearea  = 'image';
            $file_record->itemid    = $chapter->id;
            $file_record->filepath  = '/';
            $file_record->filename  = $chapter->slide_image;
            try {
                $vfile = $fs->create_file_from_pathname($file_record, "{$real_image_folder}{$chapter->slide_image}");
                $chapter->slide_image = $vfile->get_itemid();
            } catch (file_exception $ex) {
                $chapter->slide_image = '';
            }
        } else {
            $chapter->slide_image = '';
        }
        $DB->update_record("presenter_chapters", $chapter);
    }
    //remove directory
    rrmdir("{$temppathname}");
}

rebuild_course_cache();

$view = $CFG->wwwroot . '/course/view.php?id=' . $id;
?>
<div style="text-align: center; margin: 0 auto; font-size: 12px;">
    <?php echo get_string('import_success', 'presenter') ?>
    <a href="" onclick="parent.location.href='<?php echo $view ?>'"><?php echo get_string('import_after', 'presenter') ?></a>
	<?php echo get_string('import_after_after', 'presenter') ?><span id="counter">3</span> seconds
</div>
<script type="text/javascript">
    setTimeout('parent.location.href="<?php echo $view ?>"', 3000);
	var seconds = 3;
	function display()
	{
		document.getElementById("counter").innerHTML = seconds;
		if (seconds > 0)
			seconds--;
		setTimeout("display()", 900);
	}
	display();
</script>