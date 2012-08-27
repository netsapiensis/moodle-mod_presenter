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

function presenter_add_instance($presenter, $mform) {
    global $SESSION, $DB;

    presenter_process_pre_save($presenter);

    try {
        $presenter->id = $DB->insert_record("presenter", $presenter);
    } catch (dml_exception $e) {
        return false; // bad
    }

    presenter_process_post_save($presenter, $mform);

    return $presenter->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $presenter presenter post data from the form
 * @return boolean
 **/
function presenter_update_instance($presenter, $mform) {

    global $DB;

    $presenter->id = $presenter->instance;

    presenter_process_pre_save($presenter);

    try {
        $result = $DB->update_record("presenter", $presenter);
    } catch (dml_exception $e) {
        return false; // Awe man!
    }

    presenter_process_post_save($presenter, $mform);

    return $result;
}

function presenter_get_presenter($id) {
    global $DB;
    try {
        $presenter = $DB->get_record('presenter', array('id' => $id));
    } catch (dml_exception $e) {
        return false;
    }
    return $presenter;
}

/*******************************************************************/
function presenter_delete_instance($id) {
    /// Given an ID of an instance of this module,
    /// this function will permanently delete the instance
    /// and any data that depends on it.

    global $DB;
    try {
        $presenter = $DB->get_record("presenter", array("id" => $id));
    } catch (dml_exception $e) {
        return false;
    }

    try {
        $DB->delete_records("presenter", array("id" => $presenter->id));
        $DB->delete_records("presenter_chapters", array("presenterid" => $presenter->id));
        $DB->delete_records("presenter_chapters_users", array("presenter_id" => $presenter->id));
    } catch (dml_exception $e) {
        return false;
    }

    return true;
}

function presenter_get_view_actions() {
    return array('view','view all');
}

function presenter_get_post_actions() {
    return array('end','start', 'update grade attempt');
}

/**
 * Runs any processes that must run before
 * a presenter insert/update
 *
 * @param object $presenter presenter form data
 * @return void
 **/
function presenter_process_pre_save(&$presenter) {

    $chaptersnr = 0;
    foreach ($_POST['chapter_name'] as $i => $v) {
        if ($_POST['deleted'][$i] == 'false') {
            $chaptersnr++;
        }
    }

    $presenter->nr_chapters = $chaptersnr;

    switch($presenter->control_bar) {
        case 0:
            $presenter->control_bar = 'bottom';
            break;
        case 1:
            $presenter->control_bar = 'over';
            break;
        case 2:
            $presenter->control_bar = 'none';
            break;
    }

    switch ($presenter->player_streching) {
        case 0:
            $presenter->player_streching = 'uniform';
            break;
        case 1:
            $presenter->player_streching = 'exactfit';
            break;
        case 2:
            $presenter->player_streching = 'fill';
            break;
    }

    switch ($presenter->slide_streching) {
        case 0:
            $presenter->slide_streching = 'uniform';
            break;
        case 1:
            $presenter->slide_streching = 'exactfit';
            break;
        case 2:
            $presenter->slide_streching = 'fill';
            break;
    }
    unset($presenter->layout1);
    unset($presenter->video_link);
    unset($presenter->summary);
}

/**
 * Runs any processes that must be run
 * after a presenter insert/update
 *
 * @param object $presenter presenter form data
 * @return void
 **/
function presenter_process_post_save(&$presenter, $mform) {
    global $CFG, $DB;
    //delete any previous chapters in case of updating
    $oldchapters = $DB->get_records("presenter_chapters", array("presenterid" => $presenter->id));

    $cmid = $presenter->coursemodule;

    //save chapters
    $i = 0;
    foreach ($_POST['chapter_name'] as $i => $value) {
        if ($value != '' && $_POST['deleted'][$i] == 'false') {
            $chapter = new stdClass();
            $chapter->order_id = $_POST['order_id'][$i];
            $chapter->presenterid = $presenter->id;
            $chapter->chapter_name = $value;
            $chapter->video_link = $chapter->video_file = '';

            $chapter->layout = $_POST['layout'][$i]['layout'];

            if (!empty($_POST['video_link'][$i])) {
                $chapter->video_link = $_POST['video_link'][$i];
                unset($_POST['video_file'][$i]);
            } elseif (!empty($_POST['video_file'][$i])) {
                $chapter->video_file = $_POST['video_file'][$i];
            }
            if (isset($_POST['audio_track'][$i])) {
                $chapter->audio_track = $_POST['audio_track'][$i];
            }
            if (isset($_POST['slide_image'][$i]['value'])) {
                $chapter->slide_image = $_POST['slide_image'][$i];
            }
            $chapter->summary = $_POST['summary'][$i];
            if (isset($_POST['video_start'][$i])) {
                $chapter->video_start = $_POST['video_start'][$i];
            }
            if (isset($_POST['video_end'][$i])) {
                $chapter->video_end = $_POST['video_end'][$i];
            }
            if (isset($_POST['audio_start'][$i])) {
                $chapter->audio_start = $_POST['audio_start'][$i];
            }
            if (isset($_POST['audio_end'][$i])) {
                $chapter->audio_end = $_POST['audio_end'][$i];
            }
            //clear fields for audio, slide, video for corresponding layouts
            switch ($chapter->layout) {
                case '1':
                case '2':
                    $chapter->audio_track = '';
                    break;
                case '3':
                case '4':
                    $chapter->audio_track = '';
                    $chapter->slide_image = '';
                    break;
                case '5':
                case '6':
                    $chapter->video_file = '';
                    $chapter->video_link = '';
                    break;
            }
            
            try {
                $chapter->id = $DB->insert_record("presenter_chapters", $chapter);

                $draftitemid = $chapter->video_file;
                $context = get_context_instance(CONTEXT_MODULE, $cmid);
                if ($draftitemid) {
                    file_save_draft_area_files($draftitemid, $context->id, 'mod_presenter', 'video', $chapter->id, array('subdirs' => true, 'max_files' => 1));
                }

                $draftitemid = '';
                $draftitemid = $chapter->audio_track;
                if ($draftitemid) {
                    file_save_draft_area_files($draftitemid, $context->id, 'mod_presenter', 'audio', $chapter->id, array('subdirs'=>true));
                }

                $draftitemid = '';
                $draftitemid = $chapter->slide_image;
                if ($draftitemid) {
                    file_save_draft_area_files($draftitemid, $context->id, 'mod_presenter', 'image', $chapter->id, array('subdirs'=>true));
                }

            } catch (dml_exception $e) {
                error("Error updating chapter " . $chapter->chapter_name);
                $b = 1;
                break;
            }

            foreach ($oldchapters as $c) {
                if (nothing_changed($c, $chapter)) {
                    $histories = $DB->get_records('presenter_chapters_users', array('chapter_id' => $c->id));

                    foreach ($histories as $history) {
                        $history->chapter_id = $chapter->id;
                        $history->presenter_id = $presenter->id;
                        $DB->update_record("presenter_chapters_users", $history);
                    }
                }
            }
        }
    }
    if (!isset($b)) {
        foreach ($oldchapters as $c) {
            $DB->delete_records("presenter_chapters", array("id" => $c->id));
        }
    }

}

function nothing_changed($c1, $c2) {
    $c1->chapter_name = addslashes($c1->chapter_name);
    $c1->video_link = addslashes($c1->video_link);
    $c1->video_file = addslashes($c1->video_file);
    if ($c1->chapter_name == $c2->chapter_name && $c1->video_link == $c2->video_link && $c1->video_file == $c2->video_file &&
            $c1->video_start == $c2->video_start && $c1->video_end == $c2->video_end) {
        return true;
    }

    return false;
}

//new v2 version
function presenter_supports($feature) {

    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:        return true;
        case FEATURE_MOD_INTRO:             return false;
        default:                            return null;
    }
}

function mod_presenter_get_video_file_obj($course_id, $cm, $context_id, $chapterid, $filename) {
    global $CFG, $DB;
    
    require_course_login($course_id, true, NULL);
    
    $fs = get_file_storage();
    
    $fullpath = "/$context_id/mod_presenter/video/{$chapterid}/{$filename}";

    $file = $fs->get_file_by_hash(sha1($fullpath));
    
    if (is_object($file) && !$file->is_directory()) {
        
        return $file;
    } else {
        return false;
    }
}

function mod_presenter_get_image_string($course, $cm, $context, $filearea = 'image', $args = array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    $allowed = array('image');

    if (!in_array($filearea, $allowed)) {

        return false;
    }
    
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_presenter/$filearea/$relativepath";
    
    $file = $fs->get_file_by_hash(sha1($fullpath));
    
    if (is_object($file) && !$file->is_directory()) {
        // should we apply filters?
        $mimetype = $file->get_mimetype();
        if ($mimetype = 'text/html' or $mimetype = 'text/plain') {
            $filter = $DB->get_field('resource', 'filterfiles', array('id' => $cm->instance));
        } else {
            $filter = 0;
        }
        
        ob_start();
        $file->readfile();
        $fileContents = ob_get_contents();
        ob_end_clean();
        
        return $fileContents;
    } else {
        return false;
    }
}

function mod_presenter_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {

    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }
    
    require_course_login($course, true, $cm);
    $allowed = array('video', 'image', 'audio', 'export');

    if (!in_array($filearea, $allowed)) {

        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_presenter/$filearea/$relativepath";
    
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (is_object($file) && !$file->is_directory()) {
        // should we apply filters?
        $mimetype = $file->get_mimetype();
        if ($mimetype = 'text/html' or $mimetype = 'text/plain') {
            $filter = $DB->get_field('resource', 'filterfiles', array('id'=>$cm->instance));
        } else {
            $filter = 0;
        }
        
        // finally send the file
        send_stored_file($file, 86400, $filter, $forcedownload);

    } else {
        return false;
    }

}

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") {
                    rrmdir($dir . "/" . $object); 
                } else {
                    @unlink($dir."/".$object);
                }
            }
        }
    }
    reset($objects);
    rmdir($dir);
}

function frame_error($msg, $back_link = true)
{
    echo
    '<div style="text-align: center; color: red; padding: 10px;">' . $msg;
    if ($back_link) {
        echo '<br /><a href="#" onclick="history.go(-1);return false;">' . get_string("go_back", "presenter") . '</a>';
    }
    echo '</div>';
    exit();
}

function swap_array_values($array, $index1, $index2)
{
    $return = $array;
    $aux = $return[$index1];
    $return[$index1] = $return[$index2];
    $return[$index2] = $aux;
    return $return;
}
