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

$id         	= required_param('id', PARAM_INT);                 // Course Module ID
$chapterid     	= optional_param('chapterid', '', PARAM_INT);    //Chapter ID

$open = optional_param('open', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('presenter', $id)) {
	print_error("Course Module ID was incorrect");
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
	print_error("Course is misconfigured");
}

require_course_login($course, false, $cm);

if (!$presenter = presenter_get_presenter($cm->instance)) {
	print_error("Course module is incorrect");
}

if ($presenter->new_window == 1 && $open == 0) : ?>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/mod/presenter/popup.js"></script>
	<script type="text/javascript">
        if (!openURL('view.php?open=1&id=<?php echo $id ?>')) {
            alert('<?php echo get_string('alert_new_window', 'presenter')?>');
        }
        history.go(-1);
	</script>
<?php endif;

$url = new moodle_url('/mod/presenter/view.php', array('id'=>$id));
if ($chapterid !== null) {
    $url->param('chapterid', $chapterid);
}
if ($open != null) {
    $url->param('open', $open);
}
$PAGE->set_url($url);

if (!$chapterid) {
    $chap = get_first_chapter($presenter->id);
	$chapterid = $chap->id;
}

if (! $chapter = get_chapter($chapterid)) {
	print_error("Chapter ID was incorrect");
}

$strpresenter = get_string('modulename', 'presenter');
$strpresenters = get_string('modulenameplural', 'presenter');

if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
	print_error('badcontext');
}

add_to_log($course->id, "presenter", "view", "view.php?id=$cm->id", $presenter->id, $cm->id);

//filestorage
$fs = get_file_storage();

$video = '';
$video_handle = null;
$youtube = false;

//video link of file?
if ($chapter->video_link) {
    $video = $chapter->video_link;
    $youtube = true;
    $movie_id = get_movie_id($chapter->video_link);
} elseif ($chapter->video_file) {
    $files = $fs->get_area_files($context->id, 'mod_presenter', 'video', $chapterid, 'sortorder', false);
    $file = array_pop($files);
    if (is_object($file)) {
        $path = "/{$context->id}/mod_presenter/video/{$chapter->id}{$file->get_filepath()}{$file->get_filename()}";
        $video = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
        $video_filename = "{$file->get_filename()}";
    }
}
//audio file
$audio = '';
if ($chapter->audio_track) {
    $audioFiles = $fs->get_area_files($context->id, 'mod_presenter', 'audio', $chapterid, 'sortorder', false);
    if (count($audioFiles)) {
        $audioFile = array_pop($audioFiles);
        if (is_object($audioFile)) {
            $path = "/{$context->id}/mod_presenter/audio/{$chapter->id}{$audioFile->get_filepath()}{$audioFile->get_filename()}";
            $audio = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
        }
    }
}

//slide image
$imageStr = '';
$imageURL = '';
if ($chapter->slide_image) {
    $imageFiles = $fs->get_area_files($context->id, 'mod_presenter', 'image', $chapterid, 'sortorder', false);
    if (count($imageFiles)) {
        $imageFile = array_pop($imageFiles);
        if (is_object($imageFile)) {
            $path = "{$chapter->id}{$imageFile->get_filepath()}{$imageFile->get_filename()}";
            $p = "/{$context->id}/mod_presenter/image/{$chapter->id}{$imageFile->get_filepath()}{$imageFile->get_filename()}";
            $imageURL = file_encode_url($CFG->wwwroot.'/pluginfile.php', $p, false);
            $args = explode('/', ltrim($path, '/'));
            
            $imageStr = mod_presenter_get_image_string($course, $cm, $context, 'image', $args);
        }
    }
}

$image = null;
if ($imageStr) {
    $image = imagecreatefromstring($imageStr);
}

$navigation = build_navigation('', $cm);
if ($presenter->new_window != 1) {

    $PAGE->set_title($course->shortname . ': ' . $presenter->name . ': ' . $chapter->chapter_name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'presenter'));
    echo $OUTPUT->header();
} else { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $presenter->name ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php }

$volume = $presenter->volume;
if (isset($SESSION->playerVolume)) {
	$volume = $SESSION->playerVolume;
}
$SESSION->playerVolume = null;

$noplay = optional_param('noplay', 0, PARAM_INT);

$autoPlay = $noplay == 1 ? "false" : "true";

switch ($chapter->layout) {
	case '1':
	case '2':
		$presentationWidth = $presenter->presentation_width1;
		$presentationHeight = $presenter->presentation_height1;

		$navWidth = $presenter->player_width1;
		$navHeight = $presentationHeight - $presenter->player_height1;

		$playerWidth = $presenter->player_width1;
		$playerHeight = $presenter->player_height1;

		$mp3PlayerWidth = 0;
		$mp3PlayerHeight = 0;

		$slideWidth = $presentationWidth - $playerWidth;
		$slideHeight = $presentationHeight;

		break;

	case '3':
	case '4':
		$presentationWidth = $presenter->presentation_width2;
		$presentationHeight = $presenter->presentation_height2;

		$navWidth = $presentationWidth - $presenter->player_width2;
		$navHeight = $presentationHeight;

		$playerWidth = $presenter->player_width2;
		$playerHeight = $presenter->player_height2;

		$mp3PlayerWidth = 0;
		$mp3PlayerHeight = 0;

		$slideWidth = 0;
		$slideHeight = 0;

		break;

	case '5':
	case '6':
		$presentationWidth = $presenter->presentation_width2;
		$presentationHeight = $presenter->presentation_height2;

		$navWidth = $presentationWidth - $presenter->player_width2;
		$navHeight = $presentationHeight;

		$playerWidth = 0;
		$playerHeight = 0;

		if ($chapter->audio_track) {
			$mp3PlayerWidth = $presenter->player_width2;
			$mp3PlayerHeight = 24;
			$navHeight += 24;
			$presentationHeight += 24;
		} else {
			$mp3PlayerWidth = 0;
			$mp3PlayerHeight = 0;
		}

		$slideWidth = $presenter->player_width2;
		$slideHeight = $presenter->player_height2;

		break;

}
$summaryWidth = $presentationWidth;
$summaryHeight = $presenter->summary_height * 15;

//setting the mp3 player in back - start / end position
$mp3start = 0;
if (!empty($chapter->audio_start)) {
	$strstart = explode(":", $chapter->audio_start);
	if (is_numeric($strstart[0])) {
		$mp3start = 3600 * intval($strstart[0]);
		if (isset($strstart[1])) {
			$mp3start += 60 * intval($strstart[1]);
		}
		if (isset($strstart[2])) {
			$mp3start += $strstart[2];
		}
	}
}
 
$mp3duration = '';
if (!empty($chapter->audio_end)) {
	$strend = explode(":", $chapter->audio_end);
	$mp3end = 0;
	if (is_numeric($strend[0])) {
		$mp3end = 3600 * intval($strend[0]);
		if (isset($strend[1])) {
			$mp3end += 60 * intval($strend[1]);
		}
		if (isset($strend[2])) {
			$mp3end += $strend[2];
		}
	}
	 
	if ($mp3end != 0) {
		$mp3duration = $mp3end - $mp3start;

	} else {
		$mp3duration = 0;
	}
}

if (empty($mp3duration)) {
	$mp3duration = '0';
}

//skin
$skinUrl = $CFG->wwwroot . '/mod/presenter/video/controls/flowplayer.controls-3.2.3.swf';
switch ($presenter->player_skin) {
	case '1' : //tube skin
		$skinUrl = $CFG->wwwroot . '/mod/presenter/video/controls/flowplayer.controls-tube-3.2.3.swf';
		break;
	case '2': //air skin
		$skinUrl = $CFG->wwwroot . '/mod/presenter/video/controls/flowplayer.controls-air-3.2.3.swf';
		break;
}

//control bar
switch ($presenter->control_bar) {
	case "none" :
		$controlBar = 'null';
		break;
	case "bottom" :
		$controlBar = '{
		        url: \'' . $skinUrl . '\', 
		        stop : true, 
		        left: 0,
		        bottom: 0
		    }';
		break;
	case "over" :
		$controlBar = '{
		        url: \'' . $skinUrl . '\', 
		        bottom: 0,
		        autoHide: \'never\'
		    }';
		break;
}

ob_start();
?>
<div id="audio" style="width: <?php echo $mp3PlayerWidth ?>px; height: 24px;"></div>
<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/mod/presenter/video/flowplayer-3.1.4.min.js"></script>
<script type="text/javascript">
	flowplayer("audio", "<?php echo $CFG->wwwroot ?>/mod/presenter/video/flowplayer-3.2.5.swf", {
		playlist : [
			{ url : '<?php echo $audio ?>', duration : '<?php echo $mp3duration ?>' }
		],
		plugins : {
			controls: <?php echo $controlBar ?>
		},
        onFinish : function () {
            chapterCompleted('<?php echo $course->id ?>', '<?php echo $USER->id ?>', '<?php echo $chapterid ?>', '<?php echo $presenter->id ?>');
		},
        clip : {
            autoPlay : <?php echo $autoPlay ?>,
            onStart : function () {
                this.setVolume(<?php echo $volume ?>);
			}
        }
    });
</script>

<?php
$mp3HTML = ob_get_contents();
ob_end_clean();

$videoStart = 0;
$videoEnd = 0;

if ($chapter->video_start) {
	$strstart = explode(":", $chapter->video_start);
	if (is_numeric($strstart[0])) {
		$videoStart = 3600 * intval($strstart[0]);
		if (isset($strstart[1])) {
			$videoStart += 60 * intval($strstart[1]);
		}
		if (isset($strstart[2])) {
			$videoStart += $strstart[2];
		}
	}
	 
	if ($chapter->video_end) {
		$strend = explode(":", $chapter->video_end);
		$videoEnd = 0;
		if (is_numeric($strend[0])) {
			$videoEnd = 3600 * intval($strend[0]);
			if (isset($strend[1])) {
				$videoEnd += 60 * intval($strend[1]);
			}
			if (isset($strend[2])) {
				$videoEnd += $strend[2];
			}
		}
	}
}
if ($videoEnd) {
	$duration = $videoEnd - $videoStart;
} else {
	$duration = '0';
}

$streching = $presenter->player_streching;

$bufferLength = $presenter->buffer_length;

$nextChapterId = get_next_chapter_id($chapter);

?>
<script type="text/javascript">
<?php if ($nextChapterId) : ?>
	var loc = 'view.php?open=1&id=<?php echo $id ?>&chapterid=<?php echo $nextChapterId ?>';
<?php else : ?>
	var loc = 'view.php?noplay=1&open=1&id=<?php echo $id ?>&chapterid=<?php echo get_last_chapter_id($chapter) ?>';
<?php endif ?>
var xmlHttp;
function chapterCompleted(course, user, chapter, presenter)
{
	xmlHttp = GetXmlHttpObject();
		
	if (xmlHttp == null) {
		 alert ("Browser does not support HTTP Request");
		 return;
	}
<?php if ($chapter->audio_track) : ?>
	volume = $f("audio").getVolume();
    $f("audio").stopBuffering();
<?php else : ?>
    volume = $f("player").getVolume();
    $f("player").stopBuffering();
<?php endif ?>
	var url = "<?php echo $CFG->wwwroot ?>" + "/mod/presenter/ajax.php?course=" + course + "&user=" + user + "&chapter=" + chapter + "&presenter=" + presenter + "&volume=" + volume;
	
	xmlHttp.onreadystatechange = function() {
		
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
			var r = xmlHttp.responseText;
            if (loc != '') { 
                location.href = loc;
            }
		 }
	};
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function chapterCompletedYoutube(course, user, chapter, presenter)
{
	xmlHttp = GetXmlHttpObject();
		
	if (xmlHttp == null) {
		 alert ("Browser does not support HTTP Request");
		 return;
	}
	
	//ytplayer should alreaby be instantiated
	volume = ytplayer.getVolume();
	var url = "<?php echo $CFG->wwwroot ?>" + "/mod/presenter/ajax.php?course=" + course + "&user=" + user + "&chapter=" + chapter + "&presenter=" + presenter + "&volume=" + volume;
	
	xmlHttp.onreadystatechange = function() {
		
		if (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") {
			var r = xmlHttp.responseText;
            if (loc != '') { 
                location.href = loc;
            }
        }
	};
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function GetXmlHttpObject()
{
	var xmlHttp=null;
	try {
	 	xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
	return xmlHttp;
}
</script>
<?php

switch ($presenter->player_streching) {
	case 'uniform':
		$scaling = 'orig';
		break;
	case 'exactfit':
		$scaling = 'fit';
		break;
	case 'fill':
		$scaling = 'scale';
		break;
}
$player = '<div style="overflow: hidden; width: ' . $playerWidth . 'px;height: ' . $playerHeight . 'px"><div style="overflow: hidden; width: ' . $playerWidth . 'px;height: ' . $playerHeight . 'px" id="player"></div></div>';

if ($youtube) {
    $autoplay = $autoPlay == "false" ? '0' : '1';

	$color = '';
	if ($presenter->player_skin == 1) {
		$color = '&color2=0xff0000';
	}
    ob_start();
    ?>
<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/mod/presenter/swfobject.js"></script>
<script type="text/javascript">
	var params = { allowScriptAccess: "always" };
	var atts = { id: "ytplayer" };
	swfobject.embedSWF("http://www.youtube.com/v/<?php echo $movie_id ?>?showsearch=0&showinfo=1&iv_load_policy=3&rel=0&enablejsapi=1&playerapiid=ytplayer<?php echo $color ?>&autoplay=<?php echo $autoplay ?>&start=<?php echo $videoStart ?>&fs=1",
                       "player", "<?php echo $playerWidth ?>", "<?php echo $playerHeight ?>", "8", null, null, params, atts);
	var ytplayer = null;
	function onYouTubePlayerReady() {
		ytplayer = document.getElementById("ytplayer");
		ytplayer.setVolume(<?php echo $volume ?>);
		ytplayer.addEventListener("onStateChange", "youTubePlayerStateChange");
    }
    function youTubePlayerStateChange(newState) {
    	if (newState == 0) {
    		chapterCompletedYoutube('<?php echo $course->id ?>', '<?php echo $USER->id ?>', '<?php echo $chapterid ?>', '<?php echo $presenter->id ?>');
    	}
    }    
</script>
<?php
    $playerScript = ob_get_contents();
    ob_end_clean();
} else {
    ob_start();
?>
<?php if ($video) :
    $SESSION->mod_presenter_course  = $course->id;
    $SESSION->mod_presenter_cm      = $cm->id;
    $SESSION->mod_presenter_context = $context->id;
    $SESSION->mod_presenter_chapter = $chapterid;
    $SESSION->mod_presenter_filename= $video_filename;
    $videoURL = $CFG->wwwroot . '/mod/presenter/flowplayer_streamer.php/' . $video_filename;
?>

<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/mod/presenter/video/flowplayer-3.1.4.min.js"></script>
<script type="text/javascript">
    var stopped = 0;
    flowplayer("player", "<?php echo $CFG->wwwroot ?>/mod/presenter/video/flowplayer-3.2.5.swf", {
        plugins: {
            lighttpd: {
                url: '<?php echo $CFG->wwwroot ?>/mod/presenter/video/flowplayer.pseudostreaming-3.2.5.swf'
            },
            controls: <?php echo $controlBar ?>
        },
        
        // clip properties
        clip: {
            start : '<?php echo $videoStart ?>',
            autoPlay : <?php echo $autoPlay ?>,
            bufferLength : <?php echo $bufferLength ?>,
            duration : <?php echo $duration ?>,
            url : "<?php echo $videoURL ?>",
            scaling : '<?php echo $scaling ?>',
            provider : 'lighttpd',
            onFinish : function () {
                chapterCompleted('<?php echo $course->id ?>', '<?php echo $USER->id ?>', '<?php echo $chapterid ?>', '<?php echo $presenter->id ?>');
            },
            onStart : function () {
                stopped = 0;
                this.setVolume(<?php echo $volume ?>);
            },
            onStop : function () {
                if (stopped == 0) {
                    stopped = 1;
                    this.stopBuffering();
                }
                this.closeConnection();
            }
        }
    });
</script>
<?php endif ?>
<?php
    $playerScript = ob_get_contents();
    ob_end_clean();
}

if ($chapter->layout == '5' || $chapter->layout == '6') {
	$playerScript = '';
}
$player .= $playerScript;

$slide = '<div style="line-height: 0;overflow: hidden; text-align: center; width: ' . $slideWidth . 'px;height: ' . $slideHeight . 'px">';

//minus the mp3 player height
if (!empty($chapter->audio_track)) {
    $slideHeight -= 24;
}
if (!is_null($image)) {
	$imgWidth = imagesx($image);
	$imgHeight = imagesy($image);
	if ($slideWidth * $imgHeight / $imgWidth <= $slideHeight) {
		$imgStyle = 'width: ' . $slideWidth . 'px';
	} else {
		$imgStyle = 'height: ' . $slideHeight . 'px';
	}
} else {
	$imgStyle = 'width: ' . $slideWidth . 'px';
}

$style = 'style="';
switch ($presenter->slide_streching) {
	case 'uniform':
        break;
	case 'fill':
		$style .= 'width: ' . $slideWidth . 'px; height: ' . $slideHeight . 'px';
		break;
	case 'exactfit':
		$style .= $imgStyle;
		break;
}

$style .= '"';
if (!empty($imageURL)) {
	$slide .=  "<img src=\"{$imageURL}\" {$style} />";
}
if (!empty($chapter->audio_track)) {
	$slide .= $mp3HTML;
}

$slide .= '</div>';
//end image settings

$summary = '';

if ($summaryHeight) {
	//summary
	$summary = '<div class="summarytext" style="border-top: 1px solid #CCCCCC; font-size: 14px; overflow-y: scroll; width: ' . $summaryWidth . 'px;height: ' . ($summaryHeight - 1) . 'px">';
	$summary .= $chapter->summary;

	$summary .= '&nbsp;</div>';
}

//building navigation
$nav = '<div id="aaaa" style="clear: left;margin-right: 0;width: ' . $navWidth . 'px;height: ' . ($navHeight) . 'px; overflow-x: hidden; overflow-y: auto">';
$aux = $navWidth - 20;
$nav .= '<table width="' . $aux . 'px" cellpadding="0" cellspacing="0">';
$nav .= '<th width="5%"></th><th width="5%"></th><th width="90%"></th>';
$chapters = get_chapters($presenter->id, 0);
$i = 1;
$index = 0;
$baseUrl = $CFG->wwwroot . '/mod/presenter/view.php?open=1&id=' . $id . '&chapterid=';
foreach ($chapters as $ch) {
	$aux = "<td style=\"vertical-align: top\">";
	if (chapter_completed($ch->id, $USER->id) === $USER->id) {
		$aux .= '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/check.gif" width="16" style="margin-bottom: -3px;" border="0" />';
	}
    $aux .= "</td>";
	$nav .= '<tr>' . $aux . '<td style="text-align: right; vertical-align: top">' . $i . '.</td><td style="padding-left: 5px"><a name="' . $ch->id .'" href="' . $baseUrl . $ch->id . '"';
	if ($ch->id == $chapter->id) {
		$nav .= ' style="color:#AA0000"';
		$index = $i;
	}
	$nav .= ">{$ch->chapter_name}</a></td></tr>";
	$i++;
}

$nav .= '</table></div>';

$col1 = '<div style="float: left;">';
$col2 = '<div style="float: left;">';

switch($chapter->layout) {
	case '1':
		$col1 .= $player . $nav;
		$col2 .= $slide;
		break;
	case '2':
		$col1 .= $slide;
		$col2 .= $player . $nav;
		break;
	case '3':
		$col1 .= $nav;
		$col2 .= $player;
		break;
	case '4':
		$col1 .= $player;
		$col2 .= $nav;
		break;
	case '5':
		$col1 .= $nav;
		$col2 .= $slide;
		break;
	case '6':
		$col1 .= $slide;
		$col2 .= $nav;
		break;
}

if ($presenter->new_window != 1) {
	echo '<div class="box generalbox generalboxcontent boxaligncenter" style="width: ' . $presentationWidth . 'px; height: ' . ($presentationHeight + $summaryHeight) . 'px">';
}


$col1 .= '</div>';
$col2 .= '</div>';
$clear = '<div style="clear: both; float: none; height: 0px"></div>';

if ($presenter->new_window == 1) {
	echo '<div style="width: 100%; text-align: center;">';
	echo '<div style="width: ' . ($presentationWidth + 2) . 'px; margin: 0 auto; border: 1px solid #CCC;">';
}

echo $col1 . $col2 . $clear . $summary;

if ($presenter->new_window == 1) {
	echo '</div></div>';
} else {
    echo '</div>';
}

$number = ($navHeight / 18) - 1;

$number = intval($number / 2);
if ($index > $number) {
	$scrollTop = ($index - $number) * 18;
} else {
	$scrollTop = 0;
}

$s = '<script type="text/javascript">document.getElementById("aaaa").scrollTop = ' . $scrollTop . ';</script>';
echo $s;
if ($presenter->new_window != 1) {
    echo $OUTPUT->footer();
} else { ?>
</body>
</html>
<?php } ?>
