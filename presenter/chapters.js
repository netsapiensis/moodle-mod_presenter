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
Presenter = {}

Presenter.ChapterManager = {

    _chapters : null,

    //TODO: de vazut cum facem cu metadata injector
    _injectedFiles : new Array(),

    _init : function () {

        document.getElementById("moveChapterDirection").value = "";
        document.getElementById("moveChapterIndex").value = "";
        
        //get chapters
        ch = getElementsByClassName(document, "fieldset", "clearfix");
        this._chapters = new Array();
        for (i = 0; i < ch.length; i++) {
            kk = ch[i].id.split("_");
            if (kk[0] == 'chapter') {
                //TODO: injected files
                ch[i].idNumber = kk[1];
                ch[i].fileManagerToolbars = getElementsByClassName(ch[i], "div", "filemanager-toolbar");
                ch[i].fileManagerConainters = getElementsByClassName(ch[i], "div", "filemanager-container");
                ch[i].deleted = false;
                this._chapters.push(ch[i]);

            }
        }
        
        this._updateLayoutSelections();

        this._addEventHandlers();

        elems = getElementsByClassName(document, "input", "showOnlyThis");
        b = false;
        for (i = 0; i < elems.length; i++) {
            if (elems[i].value == 'true') {
                elems[i].value = 'false';
                b = true;
                break;
            }
        }

        if (b) {
            
            for (i = 0; i < this._chapters.length - 1; i++) {
                this._chapters[i].style.display = 'none';
            }
            elems[elems.length - 1].value = 'true';
            y = this._getYPos(this._chapters[this._chapters.length - 1]);
            window.scrollTo(0, y + 100);
            imgs = getElementsByClassName(document, 'input', 'showOnlyOneChapterCaller');
            imgs[imgs.length - 1].style.display = 'none';
            imgs[imgs.length - 1].nextSibling.style.display = 'block';
        }
        
        //order_id - ordering chapters
        order = getElementsByClassName(document, 'input', 'order_id');
        for (i = 0; i < order.length; i++) {
            aux = i + 1;
            order[i].setAttribute('value', aux);
        }

        //deleted chapters
        del = getElementsByClassName(document, 'input', 'delete');
        for (i = 0; i < del.length; i++) {
            if (del[i].getAttribute('value') == 'true') {
                document.getElementById('chapter_' + i).style.display = 'none';
                this._chapters[i].deleted = true;
            }
        }

        Presenter.FormManager._init(this._chapters);
    },

    _addEventHandlers : function () {

        //show / hide chapters event handlers
        var showOnlyCallers = getElementsByClassName(document, "input", "showOnlyOneChapterCaller");
        var showAllCallers = getElementsByClassName(document, "input", "showAllChaptersCaller");

        var moveDownCallers = getElementsByClassName(document, "input", "moveDownCaller");
        var moveUpCallers = getElementsByClassName(document, "input", "moveUpCaller");
        var deleteChapterCallers = getElementsByClassName(document, "button", "deleteChapterCaller");

        var _this = this;
        for (i = 0; i < showOnlyCallers.length; i++) {
            showOnlyCallers[i].onclick = function () {
                return _this._showOnlyChapter(this);
            };
        }
        i = 0;
        //showAll chapters
        for (i = 0; i < showAllCallers.length; i++) {
            showAllCallers[i].onclick = function () {
                return _this._showAllChapters(this);
            };
        }
        i = 0;
        //move down
        for (i = 0; i < moveDownCallers.length; i++) {
            moveDownCallers[i].onclick = function () {
                return _this._moveDownChapter(this);
            };
        }

        i = 0;
        //move up
        for (i = 0; i < moveUpCallers.length; i++) {
            moveUpCallers[i].onclick = function () {
                return _this._moveUpChapter(this);
            };
        }

        i = 0;
        //delete
        for (i = 0; i < deleteChapterCallers.length; i++) {
            deleteChapterCallers[i].onclick = function () {
                return _this._deleteChapter(this);
            };
        }
    },

    //select, enable / disable fields for radio buttons - layout selection
    _updateLayoutSelections : function () {
        
        radio = getElementsByClassName(document, 'input', 'radioBut');
        nr = radio.length;
        b = false;
        for (i = 0; i < nr; i++) {
            radio[i].id = radio[i].id + i;
            radio[i].nextSibling.setAttribute('for', radio[i].id);
            
            if ((i % 6 == 0 && i != 0) || i == nr - 1) {
                if (b == false) {
                    if (i != nr - 1) {
                        radio[i - 6].checked = true;
                    } else {
                        radio[nr - 6].checked = true;
                    }
                }
                b = false;
            }
            if (radio[i].checked == true) {
                b = true;
            }
            var _this = this;
            radio[i].onclick = function () {
                return _this._layoutSelectionChanged(this);
            }

        }
        var j = 0;
        for (j = 0; j < nr; j++) {
            if (radio[j].checked == true) {
                var x = j / 6;
                x = parseInt(x);
                this._layoutSelectionChanged(radio[j], x);
            }
        }
        
    },

    _layoutSelectionChanged : function (radioBut, chapterIndex) {

        var ids, id, chapter;
        if (typeof (chapterIndex) != "undefined") {
            chapter = this._chapters[chapterIndex];
            id = chapter.idNumber;
        } else {
            ids = radioBut.id.split("_radio");
            id = parseInt(ids[1] / 6);
            chapter = this._getChapterByIdNumber(id);
        }
        
        var videoButtons = chapter.fileManagerToolbars[0].children;
        var mp3Buttons = chapter.fileManagerToolbars[1].children;
        var imageButtons = chapter.fileManagerToolbars[2].children;

        switch (radioBut.value) {
            
            case '3':
            case '4':
                //navigation + video + notes
                for (i = 0; i < videoButtons.length; i++) {
                    videoButtons[i].disabled = false;
                    mp3Buttons[i].disabled = true;
                    imageButtons[i].disabled = true;
                }
                document.getElementById("id_video_link_" + id).disabled = false;
                document.getElementById('id_video_start_' + id).disabled = false;
                document.getElementById('id_video_end_' + id).disabled = false;
                document.getElementById('id_audio_end_' + id).disabled = true;
                break;
            case '5':
            case '6':
                //navigation + slide image + mp3 file + notes
                for (i = 0; i < videoButtons.length; i++) {
                    videoButtons[i].disabled = true;
                    mp3Buttons[i].disabled = false;
                    imageButtons[i].disabled = false;
                }
                document.getElementById("id_video_link_" + id).disabled = true;
                document.getElementById('id_video_start_' + id).disabled = true;
                document.getElementById('id_video_end_' + id).disabled = true;

                document.getElementById('id_audio_end_' + id).disabled = false;
                break;
            case '1':
            case '2':
                //navigation + video + slide + notes
                for (i = 0; i < videoButtons.length; i++) {
                    videoButtons[i].disabled    = false;
                    mp3Buttons[i].disabled      = true;
                    imageButtons[i].disabled    = false;
                }
                document.getElementById("id_video_link_" + id).disabled = false;
                document.getElementById('id_video_start_' + id).disabled = false;
                document.getElementById('id_video_end_' + id).disabled = false;
                document.getElementById('id_audio_end_' + id).disabled = true;
                break;
            default:
                break;
        }
    },


    _getChapterByIdNumber : function (idNumber) {
        var i = 0;
        for (i = 0; i < this._chapters.length; i++) {
            if (this._chapters[i].idNumber == idNumber) {
                return this._chapters[i];
            }
        }
        return false;
    },

    _getChapterIndexByIdNumber : function (idNumber) {
        var index = 0;
        for (index = 0; index < this._chapters.length; index++) {
            if (this._chapters[index].idNumber == idNumber) {
                return index;
            }
        }
        return false;
    },

    //show only one chapter
    _showOnlyChapter : function (caller) {
        
        for (i = 0; i < this._chapters.length; i++) {
            this._chapters[i].style.display = 'none';
        }

        chapter = caller.parentNode.parentNode;
        ids = chapter.id.split('_');
        id = ids[1];
        elems = document.getElementsByName('showOnly[' + id + ']');
        
        for (i = 0; i < elems.length; i++) {
            elems[i].value = 'true';
        }

        caller.parentNode.parentNode.style.display = 'block';

        y = this._getYPos(caller);
        window.scroll(0, y - 200);
        caller.style.display = 'none';

        caller.nextSibling.style.display = 'block';
        
        return false;
    },

    _showAllChapters : function (caller) {
        
        for (i = 0; i < this._chapters.length; i++) {
            id = this._chapters[i].idNumber;
            del = document.getElementsByName('deleted[' + id + ']');
            if (del[0].value == 'false')
                this._chapters[i].style.display = 'block';
        }
        
        elems = getElementsByClassName(document, "input", "showOnlyThis");
        for (i = 0; i < elems.length; i++)
            elems[i].value = 'false';

        y = this._getYPos(caller);
        window.scroll(0, y - 200);
        caller.style.display = 'none';
        caller.previousSibling.style.display = 'block';
        return false;
    },

    _deleteChapter : function (elem) {
        par = elem.parentNode.parentNode;
        del = getElementsByClassName(document, 'input', 'delete');
        ids = par.id.split('_');
        var goodID = parseInt(ids[1]);
        this._chapters[goodID].deleted = true;
        for (i = 0; i < del.length; i++) {
            if (i == goodID) {
                par.style.display = 'none';
                del[i].setAttribute('value', 'true');
            }
        }
        return false;
    },

    _moveDownChapter : function (elem) {
        skipClientValidation = true;
        par = elem.parentNode.parentNode;
        ids = par.id.split('_');
        var goodID = parseInt(ids[1]);

        if (goodID < this._chapters.length - 1) {
            
            document.getElementById("moveChapterIndex").value = goodID;
            document.getElementById("moveChapterDirection").value = "down";
            return true;
        }
        return false;
    },

    _moveUpChapter : function (elem) {
        skipClientValidation = true;
        par = elem.parentNode.parentNode;
        ids = par.id.split('_');
        var goodID = parseInt(ids[1]);
        if (goodID > 0) {
            document.getElementById("moveChapterIndex").value = goodID;
            document.getElementById("moveChapterDirection").value = "up";
            return true;
        }
        return false;
    },

    _redrawChapters : function (el) {
        for (i = 0; i < this._chapters.length; i++)
            this._chapters[i].style.display = 'none';
        nam = el.name;
        
        eles = nam.split('[');
        
        ids = eles[1].split(']');
        
        id = ids[0];
        
        this._chapters[id].style.display = 'block';
        imgs = getElementsByClassName(document, 'input', 'showOnlyOneChapterCaller');
        for (i = 0; i < imgs.length; i++) {
            imgs[i].style.display = 'block';
            imgs[i].nextSibling.style.display = 'none';
        }
        imgs[id].style.display = 'none';
        imgs[id].nextSibling.style.display = 'block';
    },

    //singleton
    getInstance: function () {
        var _instance;
        if (this._chapters == null || this._chapters.length == 0) {
            this._init();
            _instance = this;
        }
        
        return _instance;
    },
    _getYPos : function (elem) {
        if (!elem) {
            return 0;
        }
        var y = elem.offsetTop
        var par = this._getYPos(elem.offsetParent);
        y += par;
        return y;
    }

};

Presenter.FormManager = {

    _chapters : null,
    
    _form : null,

    _init : function (chapterArray) {
        this._chapters = chapterArray;

        this._form = getElementsByClassName(document, "form", "mform")[0];
        this._addSubmitHandler();

    },

    _addSubmitHandler : function () {
        var func = this._form.onsubmit;
        if (typeof (func) == 'function') {
            var _this = this;
            this._form.onsubmit = function () {
                if (func()) {
                    return _this._onSubmit();
                }
                return false;
            }
        } else {
            this._form.onsubmit = this._onSubmit();
        }
    },

    _onSubmit : function () {
        //moodle form validation ok, validate chapter related fields
        b = true;
        if (!skipClientValidation) {
            
            var width1 = document.getElementById("id_presentation_width1");
            var height1 = document.getElementById("id_presentation_height1");
            var player_width1 = document.getElementById("id_player_width1");
            var player_height1 = document.getElementById("id_player_height1");

            var width2 = document.getElementById("id_presentation_width2");
            var height2 = document.getElementById("id_presentation_height2");
            var player_width2 = document.getElementById("id_player_width2");
            var player_height2 = document.getElementById("id_player_height2");

            w1 = parseInt(width1.value);
            h1 = parseInt(height1.value);
            pw1 = parseInt(player_width1.value);
            ph1 = parseInt(player_height1.value);

            w2 = parseInt(width2.value);
            h2 = parseInt(height2.value);
            pw2 = parseInt(player_width2.value);
            ph2 = parseInt(player_height2.value);

            if (ph1 > h1) {
                this._gotoField(player_height1);
                alert('Player height must be smaller than the presentation height');
                return false;
            }

            if (ph2 > h2) {
                this._gotoField(player_height2);
                alert('Player height must be smaller than the presentation height');
                return false;
            }

            if (pw1 > w1) {
                this._gotoField(player_width1);
                alert('Player width must be smaller than the presentation height');
                return false;
            }

            if (pw2 > w2) {
                player_width2.style.border = '1px solid red';
                this._gotoField(player_width2);
                alert('Player width must be smaller than the presentation height');
                return false;
            }

            names = getElementsByClassName(document, "input", "names");
            for (i = 0; i < names.length; i++) {
                if (this._chapters[i].style.display != 'none') {
                    if (names[i].value == '') {
                        this._gotoField(names[i]);
                        alert('Please enter a chapter name');
                        return false;
                    }
                    
                    st = document.getElementById('id_video_start_' + i);
                    en = document.getElementById('id_video_end_' + i);
                    if (st.value != '' && en.value != '') {
                        if (en.value < st.value && en.value != "0") {
                            this._gotoField(en);
                            alert('Please enter a value bigger than "Video start" or 0 to play the movie to the end.');
                            en.value = "0";
                            return false;
                        }
                    }
                    
                    video_link = document.getElementById('id_video_link_' + i);
                    if (video_link.value) {
                        val = video_link.value;
                        youtube = "youtube";
                        _youtube = "youtu.be";
                        if (val.indexOf(youtube) == -1 && val.indexOf(_youtube) == -1) {
                            this._gotoField(video_link);
                            alert('The video link must be a youtube link.');
                            return false;
                        }
                    }

                }
            }
        }
        
        //validate chapter video, mp3 & audio files
        for (var i = 0; i < this._chapters.length; i++) {
        	var navVideo = parseInt(this._chapters[i].idNumber) * 6 + 2;
        	var videoNav = navVideo + 1;
        	var radio1 = document.getElementById("id_radio" + navVideo);
        	var radio2 = document.getElementById("id_radio" + videoNav);
        	if (radio1.checked || radio2.checked) {
        		var videoInput = document.getElementById("id_video_link_" + this._chapters[i].idNumber);
        		if (videoInput.value == '') {
        			//check for video files
        			var videoFileContainer = this._chapters[i].fileManagerConainters[0];
        			if (getElementsByClassName(videoFileContainer, "span", "fm-menuicon").length == 0) {
        				this._gotoField(videoInput);
        				if (!confirm("Are you sure you don't want to select a video file / link for this chapter?")) {
        					return false;
        				}
        			}
        		}
        	}
        }
        
        return true;
    },

    _gotoField : function (fieldElem) {
        fieldElem.style.border = '1px solid red';
        y = this._getYPos(fieldElem);
        window.scroll(0, y - 200);
    },

    _getYPos : function (elem) {
        if (!elem) {
            return 0;
        }
        var y = elem.offsetTop
        var par = this._getYPos(elem.offsetParent);
        y += par;
        return y;
    }

};

Presenter.Helper = {

    _openURL : function () {
        var popup = false;
        try	{
            popup = window.open( url );
            if ( popup == null )
                return false;
            if ( window.opera )
                if (!popup.opera)
                    return false;

        } catch(err) {
            return false;
        }
        
        return popup;
    },

    trim : function (str) {
        str = str.replace(/^\s+/, "").replace(/\s+$/, "");
        return str;
    }
};

var func = window.onload;
if (typeof (func) == 'function') {
    window.onload = function () {
        func();
        Presenter.ChapterManager._init();
    };
} else {
    window.onload = function () {
        Presenter.ChapterManager._init();
    };
}
