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

function xmldb_presenter_upgrade($oldversion = 0)
{
    global $CFG, $THEME, $DB;

    $db_man = $DB->get_manager();

    if ($oldversion > 2010112000 && $oldversion < 2010112401) {

        $table = new xmldb_table('presenter_chapters_users');

        $old_field = new xmldb_field('username');

        if ($db_man->field_exists($table, $old_field)) {
            $old_field->set_attributes(XMLDB_TYPE_INTEGER, 11, null, XMLDB_NOTNULL, null, 0, 'presenter_id');
            $db_man->rename_field($table, $old_field, 'userid');
        }
    }

    //change the 'window' field from the table, as it is a reserved word in PostgreSQL
    if ($oldversion < 2012082702) {
        $table = new xmldb_table('presenter');
        
        $old_field = new xmldb_field('window');
        if ($db_man->field_exists($table, $old_field)) {
            $old_field->set_attributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, 0);
            $db_man->rename_field($table, $old_field, 'new_window');
        }
    }
    return true;
}
