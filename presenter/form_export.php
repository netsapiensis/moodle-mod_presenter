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

require_once('lib.php');
require_once("../../config.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once("../../lib/datalib.php");
require_once("../../lib/filelib.php");
require_once($CFG->dirroot.'/mod/presenter/lib.php');
require_once($CFG->dirroot.'/mod/presenter/chapterlib.php');

require_login();

?>
<!--<div style="padding-top: 10px; color: blue" align="center">Not implemented yet.</div>-->
<?php

$presenter_id = optional_param('presenter', 0, PARAM_INTEGER);
$course_id    = optional_param('course', 0, PARAM_INTEGER);
$context_id   = optional_param('context', 0, PARAM_INTEGER);

if (!$course_id) {
    $msg = get_string('no_course', 'presenter');
    frame_error($msg, false);
}

if (!$presenter_id) {
    $msg = get_string('no_presenter', 'presenter');
    frame_error($msg, false);
}

if (!$context_id) {
    $msg = get_string('bad_context', 'presenter');
    frame_error($msg, false);
}

$presenter = presenter_get_presenter($presenter_id);

?>

<div align="center">
<?php 
if ($presenter->export_file) {
    $fs = get_file_storage();
    
    $exported_file = $fs->get_file($context_id, 'mod_presenter', 'export', $presenter->id, '/', $presenter->export_file);
    
    if (is_object($exported_file)) :
        $path = "/{$context_id}/mod_presenter/export/{$presenter->id}{$exported_file->get_filepath()}{$exported_file->get_filename()}";
        echo get_string('already_exported', 'presenter'); ?>
        <a target="_parent" href="<?php echo file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false); ?>"><?php echo get_string('here', 'presenter') ?></a> <?php echo get_string('export_again', 'presenter') ?><br /><br />
    <?php endif;
}

$defaultName = str_replace(" ", "_", $presenter->name);
$defaultName .= '_' . date('Ymd') . '_' . $presenter->id . '.zip';
?>
	<form action="export.php" method="POST" id="export">
		<input style="width: 350px; margin-right: 30px;" type="text" name="archiveName" value="<?php echo $defaultName ?>" />
		<input type="hidden" name="course" value="<?php echo $course_id ?>" />
		<input type="hidden" name="presenter" value="<?php echo $presenter->id ?>" />
        <input type="hidden" name="context" value="<?php echo $context_id ?>" />
		<button type="submit"><?php echo get_string('export_short', 'presenter') ?></button>
	</form>
</div>
