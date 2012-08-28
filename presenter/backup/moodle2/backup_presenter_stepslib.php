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

/**
 * Define the complete presenter structure for backup, with file and id annotations
 */
class backup_presenter_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // The presenter table
        // 
        $presenter = new backup_nested_element('presenter', array('id'), array(
            'course', 'name', 'nr_chapters', 'presentation_width1', 'presentation_height1', 'presentation_width2',
            'presentation_height2', 'player_width1', 'player_height1', 'player_width2', 'player_height2',
            'new_window', 'player_skin', 'control_bar', 'player_streching', 'volume', 'buffer_length', 'slide_streching',
            'summary_height', 'export_file'
        ));

        // The presenter_chapters table
        // grouped into a `chapters` nest
        // relational to presenter - presenterid - will need to be re-built during restore
        $chapters = new backup_nested_element('chapters');
        $chapter = new backup_nested_element('chapter', array('id'), array(
            'order_id', 'chapter_name', 'video_link', 'video_file', 'video_start',
            'video_end', 'audio_track', 'audio_start', 'audio_end', 'slide_image',
            'summary', 'layout'
        ));

        // The presenter_chapters_users table - chapters completed
        // Grouped within an chapterusers `element` - presenter_id and chapter_id
        // will need to be re-built during restore
        $chapterusers = new backup_nested_element('chapterusers');
        $chapteruser = new backup_nested_element('chapteruser', array('id'), array(
            'userid'
        ));
        
        // Now that we have all of the elements created we've got to put them
        // together correctly.
        $presenter->add_child($chapters);
        $chapters->add_child($chapter);
        
        //TODO: see how exactly to do this
        $chapter->add_child($chapterusers);
        $chapterusers->add_child($chapteruser);

        // Set the source table for the elements that aren't reliant on the user
        // at this point (presenter, presenter_chapters)
        $presenter->set_source_table('presenter', array('id' => backup::VAR_ACTIVITYID));
        //we use SQL here as it must be ordered by order_id so that restore gets the chapters in the right order.
        $chapter->set_source_sql("
                SELECT *
                  FROM {presenter_chapters}
                 WHERE presenterid = ? ORDER BY order_id",
                array(backup::VAR_PARENTID));

        // Check if we are also backing up user information
        if ($this->get_setting_value('userinfo')) {
            // Set the source table for elements that are reliant on the user
            // presenterchapterusers
            $chapteruser->set_source_table('presenter_chapters_users', 
                    array (
                        'presenter_id'  => backup::VAR_ACTIVITYID,
                        'course_id'     => backup::VAR_COURSEID,
                        'chapter_id'    => backup::VAR_PARENTID
                    )
            );
        }

        // Annotate the file areas in use by the lesson module.
        $chapter->annotate_files('mod_presenter', 'audio', null);
        $chapter->annotate_files('mod_presenter', 'video', null);
        $chapter->annotate_files('mod_presenter', 'image', null);
        

        // Prepare and return the structure we have just created for the lesson module.
        return $this->prepare_activity_structure($presenter);
    }
    
}
