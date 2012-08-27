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

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('chapterlib.php');

class mod_presenter_mod_form extends moodleform_mod {

    protected $course = null;

    public function mod_presenter_mod_form($current, $section, $cm, $course) {

        $chapter_fields = array(
            'order_id', 'deleted', 'showOnly', 'chapter_name', 'layout', 'video_file', 'video_link', 'video_start',
            'video_end', 'audio_track', 'audio_start', 'slide_image', 'summary'
        );
        //perform this check every time, in case user deleted one or more chapters, remove it from $_POST
        $deleted = isset($_POST['deleted']) ? $_POST['deleted'] : array();
        foreach ($deleted as $index => $bool_value) {
            if ($bool_value == "true") {
                #array_splice each of them
                foreach ($chapter_fields as $field) {
                    array_splice($_POST[$field], $index, 1);
                }
                $_POST['nr_chapters']--;
                $_POST['deleted'][$index] = "false";
            }
        }
        
        //change chapter order, if submitted
        $change_order = false;
        
        if (!empty($_POST['chapter_move_up'])) {
            if ($_POST['chapter_move_direction'] == 'up' && $_POST['chapter_move_index'] > 0) {
                $change_order = true;
                $i1 = intval($_POST['chapter_move_index']);
                $i2 = $i1 - 1;
            }
        } elseif (!empty($_POST['chapter_move_down'])) {
            if ($_POST['chapter_move_direction'] == 'down' && $_POST['chapter_move_index'] < count($_POST['chapter_name']) - 1) {
                $change_order = true;
                $i1 = intval($_POST['chapter_move_index']);
                $i2 = $i1 + 1;
            }
        }

        $_POST['chapter_move_direction'] = $_POST['chapter_move_index'] = "";

        if ($change_order) {
            foreach ($chapter_fields as $field) {
                $_POST[$field]      = swap_array_values($_POST[$field], $i1, $i2);
            }
        }
        
        $this->course = $course;
        parent::moodleform_mod($current, $section, $cm, $course);
    }

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform    =& $this->_form;

        $mform->addElement('header', 'import', get_string('import', 'presenter'));
        $div = '<iframe src="' . $CFG->wwwroot . '/mod/presenter/form_import.php?course=' . $COURSE->id . '&section=' . $this->_section . '" width="100%" frameborder="0" scrolling="no" height="65">';
	    $div .= '</iframe>';
	    $mform->addElement('html', $div);
	    
	    if ($this->_instance) {
            $mform->addElement('header', 'export', get_string('export_this', 'presenter'));
	       	$div = '<iframe src="' . $CFG->wwwroot . '/mod/presenter/form_export.php?course=' . $COURSE->id . '&presenter=' . $this->_instance . '&context=' . $this->context->id . '" width="100%" frameborder="0" scrolling="no" height="75">';
	    	$div .= '</iframe>';
	    	$mform->addElement('html', $div);
	    }

        $s = $this->getStyleSheet();

        $mform->addElement('html', $s);
        
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        $mform->addElement('html', '<div class="first_name">');

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('html', '</div>');

        //displaying the images for the 2 types of layouts a user can choose from

        
        $layout1HTML = '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout_type1.gif" />';
        $layout2HTML = '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout_type2.gif" />';
        
        $html = '
            <div class="fitem">
                <table cellpadding="0" cellspacing="0" class="table_layouts">
                    <tr><th width="50%"></th><th width="50%"></th></tr>
                    <tr>
                        <td align="right" class="img1">' . $layout1HTML . '</td>
                        <td class="img2">' . $layout2HTML . '</td>
                    </tr>
                    <tr>
                        <td class="text1">
        ';

        $mform->addElement('html', $html);
        $mform->addElement('text', 'presentation_width1', get_string('presentation_width', 'presenter'), array('value' => '900'));
        $mform->addRule('presentation_width1', null, 'numeric', null, 'client');
        
        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'presentation_width2', get_string('presentation_width', 'presenter'), array('value' => '900'));
        $mform->addRule('presentation_width2', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td></tr><tr><td class="text1">');
        $mform->addElement('text', 'presentation_height1', get_string('presentation_height', 'presenter'), array('value' => '500'));
        $mform->addRule('presentation_height1', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'presentation_height2', get_string('presentation_height', 'presenter'), array('value' => '500'));
        $mform->addRule('presentation_height2', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td></tr><tr><td class="text1">');
        $mform->addElement('text', 'player_width1', get_string('player_width', 'presenter'), array('value' => '320'));
        $mform->addRule('player_width1', null, 'numeric', null, 'client');
        
        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'player_width2', get_string('player_width', 'presenter'), array('value' => '640'));
        $mform->addRule('player_width2', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td></tr><tr><td class="text1">');
        $mform->addElement('text', 'player_height1', get_string('player_height', 'presenter'), array('value' => '240'));
        $mform->addRule('player_height1', null, 'numeric', null, 'client');
        
        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'player_height2', get_string('player_height', 'presenter'), array('value' => '500'));
        $mform->addRule('player_height2', null, 'numeric', null, 'client');
        
        $html = '</td></tr><tr><td colspan="2">&nbsp;</td></tr></table></div><div class="windowing">';
        
        $mform->addElement('html', $html);
        
        $option = array();
        $option[0] = 'Same window';
        $option[1] = 'New window';
        $mform->addElement('select', 'new_window', get_string('window', 'presenter'), $option);
        $mform->setDefault('new_window', 0);
        
        $options = array();
        $options[0] = 'Default';
        $options[1] = 'Tube';
        $options[2] = 'Air';
        $mform->addElement('select', 'player_skin', get_string('player_skin', 'presenter'), $options);
        $mform->setDefault('player_skin', 0);
        
        $options = array();
        $options[0] = 'bottom';
        $options[1] = 'over';
        $options[2] = 'none';
        $mform->addElement('select', 'control_bar', get_string('control_bar', 'presenter'), $options);
        $mform->setDefault('control_bar', 0);

        $options = array();
        $options[0] = 'uniform';
        $options[1] = 'exact fit';
        $options[2] = 'fill';
        $mform->addElement('select', 'player_streching', get_string('player_streching', 'presenter'), $options);
        $mform->setDefault('player_streching', 0);
        
        $mform->addElement('text', 'volume', get_string('volume', 'presenter'), array('size'=>'7', 'value' => '60'));
        $mform->addRule('volume', null, 'numeric', null, 'client');
        
        $mform->addElement('text', 'buffer_length', get_string('buffer_length', 'presenter'), array('size' => '7', 'value' => '3'));
        $mform->addRule('buffer_length', null, 'numeric', null, 'client');
        
        $options = array('uniform', 'exact fit', 'fill');
        $mform->addElement('select', 'slide_streching', get_string('slide_streching', 'presenter'), $options);
        $mform->setDefault('slide_streching', 0);
        
        $mform->addElement('text', 'summary_height', get_string('summary_height', 'presenter'), array('size' => '7'));
        $mform->addRule('summary_height', null, 'numeric', null, 'client');

        $mform->addElement('html', '</div>');
        
//-------------------------------------------------------------------------------
//-----------------javascript for adding multiple chapters-----------------------
		$script = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/chapters.js"></script>';
    	if ($this->_instance){
            $repeatno = $DB->count_records('presenter_chapters', array('presenterid' => $this->_instance));
        } else {
            $repeatno = 1;
        }

        $repeatarray = array();
		
        $repeatarray[] = $mform->createElement('header', 'chapter', get_string('chapter', 'presenter').' {no}');
        $theme = current_theme();

        $showThis = '<input class="showOnlyOneChapterCaller" type="image" src="'. $CFG->wwwroot . '/mod/presenter/pix/one.gif" style="float: right; margin-right: 17px;" title="Show only this chapter" />';
        $showAll = '<input class="showAllChaptersCaller" type="image" src="'. $CFG->wwwroot . '/mod/presenter/pix/all.gif" style="display: none; float: right; margin-right: 17px;" title="Show all chapters" />';
        
        $repeatarray[] = $mform->createElement('html', $showThis);
        $repeatarray[] = $mform->createElement('html', $showAll);
        
        $btn = '<div style="clear:both"></div><button class="deleteChapterCaller" type="button" title="'. get_string('remove', 'presenter') .'">' . '</button>';
        $repeatarray[] = $mform->createElement('html', $btn);
        
        $btn = '<div style="clear:both"></div><input value="move_up{no}" type="submit" class="moveUpCaller" name="chapter_move_up" title="' . get_string('move_up', 'presenter') . '" />';
        $repeatarray[] = $mform->createElement('html', $btn);
        
        $btn = '<div style="clear:both"></div><input value="move_down{no}" type="submit" class="moveDownCaller" name="chapter_move_down" title="' . get_string('move_down', 'presenter') . '">';
        $repeatarray[] = $mform->createElement('html', $btn);
        
        $radio[] = $mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout1.gif" />', '1', array("class" => "radioBut", "id" => "id_radio"));
        $radio[] = $mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout2.gif" />', '2', array("class" => "radioBut", "id" => "id_radio"));
        $radio[] = $mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout3.gif" />', '3', array("class" => "radioBut", "id" => "id_radio"));
        $radio[] = $mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout4.gif" />', '4', array("class" => "radioBut", "id" => "id_radio"));
        $radio[] = $mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout5.gif" />', '5', array("class" => "radioBut", "id" => "id_radio"));
        $radio[] = $mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout6.gif" />', '6', array("class" => "radioBut", "id" => "id_radio"));

        $html = '<div class="radio_buttons">';
        $repeatarray[] = $mform->createElement('html', $html);
        $repeatarray[] = $mform->createElement('group', 'layout', '', $radio, '');
        $html = '</div>';
        
        $repeatarray[] = $mform->createElement('html', $html);
        $repeatarray[] = $mform->createElement('hidden', 'deleted', 'false', array('class' => 'delete interchange'));
        $repeatarray[] = $mform->createElement('hidden', 'showOnly', 'false', array('class' => 'showOnlyThis interchange'));
        
        $repeatarray[] = $mform->createElement('text', 'chapter_name', get_string('chapter_name', 'presenter'), array('class' => "names interchange"));

        $htmlExplain = '<div class="fitem"><div class="fitemtitle"><label>&nbsp;</label></div><div style="color: #008000; font-size: 90%;" class="felement ftext">' . get_string('allowed_files', 'presenter') . ': <b>*.flv</b></div></div>';
        $repeatarray[] = $mform->createElement('html', $htmlExplain);
        $opt = array();
        $opt['subdirs'] = 0;
        $opt['maxbytes'] = $this->course->maxbytes;
        $opt['maxfiles'] = 1;
        $opt['accepted_types'] = array('video');
        $repeatarray[] = $mform->createElement('filemanager', 'video_file', get_string('video_file', 'presenter'), null, $opt);
        
        $htmlExplain = '<div class="fitem"><div class="fitemtitle"><label>&nbsp;</label></div><div style="color: #008000; font-size: 90%;" class="felement ftext">' . get_string('explain_video_link', 'presenter') . '</div></div>';
        $repeatarray[] = $mform->createElement('html', $htmlExplain);
        $repeatarray[] = $mform->createElement('text', 'video_link', get_string('or', 'presenter') . get_string('video_link', 'presenter'), array('class' => "interchange"));
        
        $repeatarray[] = $mform->createElement('text', 'video_start', get_string('video_start', 'presenter'), array('value' => '0', 'class' => "interchange"));
        
        $repeatarray[] = $mform->createElement('text', 'video_end', get_string('video_end', 'presenter'), array('value' => '0', 'class' => "interchange"));

        $htmlExplain = '<div class="fitem"><div class="fitemtitle"><label>&nbsp;</label></div><div style="color: #008000; font-size: 90%;" class="felement ftext">' . get_string('allowed_files', 'presenter') . ': <b>*.mp3</b></div></div>';
        $repeatarray[] = $mform->createElement('html', $htmlExplain);
        
        $opt = array();
        $opt['subdirs'] = 0;
        $opt['maxbytes'] = $this->course->maxbytes;
        $opt['maxfiles'] = 1;
        $opt['accepted_types'] = array('*.mp3');
        $repeatarray[] = $mform->createElement('filemanager', 'audio_track', get_string('audio_track', 'presenter'), null, $opt);
        
        $repeatarray[] = $mform->createElement('hidden', 'audio_start', get_string('audio_start', 'presenter'), array('value' => '0'), array('class' => "interchange"));
        
        $repeatarray[] = $mform->createElement('text', 'audio_end', get_string('audio_end', 'presenter'), array('value' => '0', 'class' => "interchange"));

        $htmlExplain = '<div class="fitem"><div class="fitemtitle"><label>&nbsp;</label></div><div style="color: #008000; font-size: 90%;" class="felement ftext">' . get_string('allowed_files', 'presenter') . ': <b>*.png, *.gif, *.jpg, *.jpeg</b></div></div>';
        $repeatarray[] = $mform->createElement('html', $htmlExplain);
        
        $opt = array();
        $opt['subdirs'] = 0;
        $opt['maxbytes'] = $this->course->maxbytes;
        $opt['maxfiles'] = 1;
        $opt['accepted_types'] = array('image');
        $repeatarray[] = $mform->createElement('filemanager', 'slide_image', get_string('slide_image', 'presenter'), null, $opt);
        
        $repeatarray[] = $mform->createElement('htmleditor', 'summary', get_string('summary', 'presenter'), array(
		    'canUseHtmlEditor'=>'detect',
		    'rows'  => 10, 
		    'cols'  => 65, 
		    'width' => 0,
		    'height'=> 500, 
		    'course'=> 0,
		));
        
        $repeatarray[] = $mform->createElement('hidden', 'order_id', $repeatno, array('class' => 'order_id interchange'));
        
        
        $repeateloptions = array();
        $nr = $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'nr_chapters', 'add_chapters', 1, 'Add new chapter');
                    
        $md5Script = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/md5.js"></script>';
        $mform->addElement('html', $md5Script);
        $sc = '<script type="text/javascript">
					wwwroot = "' . $CFG->wwwroot . '";
					courseID = "' . $COURSE->id . '";
			   </script>';
        $mform->addElement('html', $sc);
        $mform->addElement('html', $script);

        //2 auxiliary inputs for the move chapter up / down
        $mform->addElement('hidden', 'chapter_move_index', "", array("id" => 'moveChapterIndex'));
        $mform->addElement('hidden', 'chapter_move_direction', "", array("id" => 'moveChapterDirection'));

        $mform->registerNoSubmitButton("chapter_move_up");
        $mform->registerNoSubmitButton("chapter_move_down");
//-------------------------------------------------------------------------------        

//-------------------------------------------------------------------------------
        $features = new stdClass;
        $features->groups = false;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons();
        
    }

    function set_data($default_values)
    {
    	if (optional_param('removechapter', '', PARAM_RAW)) {
    		foreach ($_POST['removechapter'] as $k => $v) {
    			break;
    		}
    		unset($_POST['chapter_name'][0]);
    		die;
    	}
    	parent::set_data($default_values);
    }
    /**
     * Enforce defaults here
     *
     * @param array $default_values Form defaults
     * @return void
     **/
    function data_preprocessing(&$default_values) {
        global $module, $DB;
        
        if (!empty ($this->_instance) && $chapters = get_chapters($this->_instance)) {
            
        	$presenter = $DB->get_record('presenter', array('id' => $this->_instance));
        	if ($presenter->control_bar == 'over') {
        		$default_values['control_bar'] = 1;
        	} else if ($presenter->control_bar == 'none') {
        		$default_values['control_bar'] = 2;
        	}
        	if ($presenter->player_streching == 'exactfit') {
        		$default_values['player_streching'] = 1;
        	} else if ($presenter->player_streching == 'fill') {
        		$default_values['player_streching'] = 2;
        	}
        	if ($presenter->slide_streching == 'exactfit') {
        		$default_values['slide_streching'] = 1;
        	} else if ($presenter->slide_streching == 'fill') {
        		$default_values['slide_streching'] = 2;
        	}
        	$i = 0;
        	
        	foreach ($chapters as $chapter) {
        		$default_values['chapter_name'][$i]             = $chapter->chapter_name;
        		$default_values['video_link'][$i]               = $chapter->video_link;
        		$default_values['video_start'][$i]              = $chapter->video_start;
        		$default_values['video_end'][$i]                = $chapter->video_end;
        		$default_values['audio_track'][$i]              = $chapter->audio_track;
        		$default_values['audio_start'][$i]              = $chapter->audio_start;
        		$default_values['audio_end'][$i]                = $chapter->audio_end;
        		$default_values['slide_image'][$i]              = $chapter->slide_image;
        		$default_values['summary'][$i]                  = $chapter->summary;
        		$default_values['layout[' . $i . '][layout]']   = $chapter->layout;

                if (!empty($this->_cm)) {
                    $context = get_context_instance(CONTEXT_MODULE, $this->_cm->id);
                    $draftitemid = file_get_submitted_draft_itemid('video_file[' . $i . ']');
                    file_prepare_draft_area($draftitemid, $context->id, 'mod_presenter', 'video', $chapter->id, array('subdirs' => 0, 'maxbytes' => $this->course->maxbytes, 'maxfiles' => 1));
                    $default_values['video_file'][$i] = $draftitemid;
                    $draftitemid = 0;
                    //audiofile
                    $draftitemid = file_get_submitted_draft_itemid('audio_track[' . $i . ']');
                    file_prepare_draft_area($draftitemid, $context->id, 'mod_presenter', 'audio', $chapter->id, array('subdirs' => 0, 'maxbytes' => $this->course->maxbytes, 'maxfiles' => 1));
                    $default_values['audio_track'][$i] = $draftitemid;
                    $draftitemid = 0;
                    //slide image
                    $draftitemid = file_get_submitted_draft_itemid('slide_image[' . $i . ']');
                    file_prepare_draft_area($draftitemid, $context->id, 'mod_presenter', 'image', $chapter->id, array('subdirs' => 0, 'maxbytes' => $this->course->maxbytes, 'maxfiles' => 1));
                    $default_values['slide_image'][$i] = $draftitemid;
                }
                
        		$i++;
        	}
        }
        
    }

    /**
     * Enforce validation rules here
     *
     * @param object $data Post data to validate
     * @return array
     **/
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['maxtime']) and !empty($data['timed'])) {
            $errors['timedgrp'] = get_string('err_numeric', 'form');
        }

        return $errors;
    }

    function getStyleSheet()
    {
        global $CFG;
        return '<style type="text/css">' . file_get_contents($CFG->wwwroot . "/mod/presenter/style_presenter.css") . '</style>';
    }
    
}