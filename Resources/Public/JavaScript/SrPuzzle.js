/***************************************************************
*  Copyright notice
*
*  (c) 2003-2013 Stanislas Rolland <typo3(arobas)sjbr.ca>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* Javascript 'sr_puzzle.js' for the 'sr_puzzle' extension.
*
* @author	Original author unknown
*/

var sr_puzzleBrowser = 'gecko';
if (window.attachEvent && !window.addEventListener) {
    sr_puzzleBrowser = 'msie';
}
var sr_puzzleWindowFeatures = '';
// The coordinates of the puzzle
var sr_puzzleX = 250,
	sr_puzzleY = 100;
var sr_puzzle_linkPopUp = ""; var sr_puzzle_linkAfter = ""; var sr_puzzle_enablePopUp = 1; var sr_puzzle_enableAlert = 0; var sr_puzzle_enableLinkAfter = 0;
var sr_puzzle_successMessage = "Congratulations!"; var sr_puzzle_popUpWidth = 150; var sr_puzzle_popUpHeight = 150;  // Les dimensions de la fen�tre popUp.
var sr_puzzle_screenX = 0; var sr_puzzle_screenY = 0; 
var sr_puzzle_pSize = 60;	// The height of a piece (and its width, as it is square)
var sr_puzzle_nbX = 5; var sr_puzzle_nbY = 6;	// Number of pieces per line and per column.
var sr_puzzle_nbShuffle = 50;	// Number of shuffling permutations.
var sr_puzzle_regExpPiece = /P_ID/;	// Used to replace the variable parts of expressions
var sr_puzzle_selectedId = null;	  // When we start, no piece is selected
var sr_puzzle_PIECE = "document.getElementById(\"PP_ID\")";	// Access to a piece of the puzzle
var sr_puzzle_PIECE_LEFT = "document.getElementById(\"PP_ID\").style.left";	// left property of the piece
var sr_puzzle_PIECE_TOP = "document.getElementById(\"PP_ID\").style.top";	// top property of the piece
var sr_puzzle_PIECE_ZINDEX = "document.getElementById(\"PP_ID\").style.zIndex";	// zIndex property of the piece
var sr_puzzle_EVENT_TARGET = "event.target";	// Access to the piece that triggered the event
var sr_puzzle_EVENT_X = "event.pageX"; var sr_puzzle_EVENT_Y = "event.pageY";	// x and y positions of the mouse

function sr_puzzle_init()  // Initiate the capture of mouse events
{
	if ( sr_puzzleBrowser == "gecko" ) {
		window.onmousedown = sr_puzzle_clickImg;
		window.ontouchstart = sr_puzzle_clickImg;
		window.onmouseup = sr_puzzle_releaseImg;
		window.ontouchend = sr_puzzle_releaseImg;
		window.onmousemove = sr_puzzle_moveImg;
		window.ontouchmove = sr_puzzle_moveImg;
      } else { 
            sr_puzzle_PIECE = "document.all.PP_ID";	// Access to a piece of the puzzle
		sr_puzzle_PIECE_LEFT = "document.all.PP_ID.style.pixelLeft";	// left property of the piece
		sr_puzzle_PIECE_TOP = "document.all.PP_ID.style.pixelTop";	// top property of the piece
		sr_puzzle_PIECE_ZINDEX = "document.all.PP_ID.style.zIndex";	// zIndex property of the piece
		sr_puzzle_EVENT_TARGET = "event.srcElement";	// Access to the piece that triggered the event
		sr_puzzle_EVENT_X = "event.x";	// x position of the mouse
		sr_puzzle_EVENT_Y = "event.y";	// y position of the mouse
		document.onmousedown = sr_puzzle_clickImg;
		document.onmouseup = sr_puzzle_releaseImg;
	    	document.onmousemove = sr_puzzle_moveImg;
      };
	sr_puzzle_selectedId = null;	// When we start, no piece is selected
};
function sr_puzzle_create()  // Initialising the puzzle
{
	var nbi = 0; var nb = 0;
        // Creating the array which contains the id of each piece
        for (var i=1; i<=sr_puzzle_nbX; i++) {
	    for (var j=1; j<=sr_puzzle_nbY; j++) {
		nbi++;
		nb = i + '' + j;
		sr_puzzle_arr[nb] = nbi;
	    }	
        };
        // Shuffling the puzzle
        var nb1; var nb2; var tmp;
        for (var i=1; i<=sr_puzzle_nbShuffle; i++) {
	    nb1 = Math.ceil(Math.random() * sr_puzzle_nbX) + '' + Math.ceil(Math.random() * sr_puzzle_nbY);
	    nb2 = Math.ceil(Math.random() * sr_puzzle_nbX) + '' + Math.ceil(Math.random() * sr_puzzle_nbY);
	    tmp = sr_puzzle_arr[nb1];
	    sr_puzzle_arr[nb1] = sr_puzzle_arr[nb2];
	    sr_puzzle_arr[nb2] = tmp;
        };
};
function sr_puzzle_pieceProp(id, prop)  // Returning property "prop" of oject "id"
{
	return prop.replace(sr_puzzle_regExpPiece, id);
};
function sr_puzzle_eventProp(evt, prop)  // Returning property "prop" of the event "evt:" (IE only)
{
	return eval(prop);
};
	// Handling the mouse click
function sr_puzzle_clickImg(event) {
		// Was the click on a piece of the puzzle ?
	if (sr_puzzleBrowser == "gecko") var clic = eval(sr_puzzle_EVENT_TARGET);
		else var clic = sr_puzzle_eventProp(event,sr_puzzle_EVENT_TARGET);
	sr_puzzle_selectedId = clic.id;
	
	if (isNaN(sr_puzzle_selectedId) ) {
		sr_puzzle_selectedId = null;
	} else {
		puzzleSelectedNumber = Number(sr_puzzle_selectedId);
		if (puzzleSelectedNumber < 1 || puzzleSelectedNumber > sr_puzzle_nbX*sr_puzzle_nbY) sr_puzzle_selectedId = null;
	}
	if (sr_puzzle_selectedId == null) {
		return false;
	} else {
			// Bringing the piece in the front
		document.getElementById("P" + sr_puzzle_selectedId).style.zIndex = 20;
		return false;
	}
};
function sr_puzzle_releaseImg(event)  // Handling the release of the mouse click
{
	if (sr_puzzle_selectedId == null) {
		return false;
        } else {
			// Moving the piece back to the plane of the puzzle
		document.getElementById("P" + sr_puzzle_selectedId).style.zIndex = 10;
		sr_puzzle_snapImg(); // Snaping the piece to the grid
		sr_puzzle_selectedId = null; // Indicating that piece was released
			// Checking if the puzzle is solved
		if (sr_puzzle_check()) {
			if (sr_puzzle_enableAlert == 1) window.alert(sr_puzzle_successMessage);
			if (sr_puzzle_enablePopUp == 1) {
				if (sr_puzzleBrowser == "gecko") {
					sr_puzzleWindowFeatures = 'width=' + sr_puzzle_popUpWidth + ',height=' + sr_puzzle_popUpHeight + ',screenX=' + sr_puzzle_screenX + ',screenY=' + sr_puzzle_screenY + ',status=no,menubar=no,scrollbars=no,resizable=no,toolbar=no';
				} else {
					sr_puzzleWindowFeatures = 'width=' + sr_puzzle_popUpWidth + ',height=' + sr_puzzle_popUpHeight + ',top=' + sr_puzzle_screenY + ',left=' + sr_puzzle_screenX + ',status=no,menubar=no,scrollbars=no,resizable=no,toolbar=no,fullscreen=no';
				}
				window.open(sr_puzzle_linkPopUp,'sr_puzzle_PopUP',sr_puzzleWindowFeatures);
			}
			if (sr_puzzle_enableLinkAfter == 1 && sr_puzzle_linkAfter != '') self.location = sr_puzzle_linkAfter;
		}
		return true;
        }
};
function sr_puzzle_snapImg()  // Snaping the piece to the grid
{
        var sX, sY;
	// Looking for the nearest cell
	if (sr_puzzleBrowser == "gecko") {
		sX = (Math.round((parseInt(eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_LEFT))) - sr_puzzleX) / sr_puzzle_pSize) * sr_puzzle_pSize) + sr_puzzleX;
		sY = (Math.round((parseInt(eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_TOP))) - sr_puzzleY) / sr_puzzle_pSize) * sr_puzzle_pSize) + sr_puzzleY;
	} else { 
		sX = (Math.round((eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_LEFT)) - sr_puzzleX) / sr_puzzle_pSize) * sr_puzzle_pSize) + sr_puzzleX;
		sY = (Math.round((eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_TOP)) - sr_puzzleY) / sr_puzzle_pSize) * sr_puzzle_pSize) + sr_puzzleY;
	}
        // and placing the piece in the cell
	if (sr_puzzleBrowser == "gecko") {
		document.getElementById("P" + sr_puzzle_selectedId).style.left = sX + "px";
		document.getElementById("P" + sr_puzzle_selectedId).style.top = sY + "px";
	} else {
		eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_LEFT) + " = " + sX);
		eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_TOP) + " = " + sY);
	}
};
function sr_puzzle_check()  // Checking if the puzzle is solved
{
	var nbi = 0; var cX; var cY;
	for (var i=1; i<=sr_puzzle_nbX; i++) {
		for (var j=1; j<=sr_puzzle_nbY; j++) {
			nbi++;
			// Checking if each piece is in the correct cell, examples :
			// piece 1 must be at coordinates 1:0 , piece 12 must be at coordinates 2:1
			if ( sr_puzzleBrowser == "gecko" ) {
				cX = (parseInt(eval(sr_puzzle_pieceProp(nbi, sr_puzzle_PIECE_LEFT))) - sr_puzzleX) / sr_puzzle_pSize;
				cY = (parseInt(eval(sr_puzzle_pieceProp(nbi, sr_puzzle_PIECE_TOP))) - sr_puzzleY) / sr_puzzle_pSize;
			} else { 
				cX = (eval(sr_puzzle_pieceProp(nbi, sr_puzzle_PIECE_LEFT)) - sr_puzzleX) / sr_puzzle_pSize;
				cY = (eval(sr_puzzle_pieceProp(nbi, sr_puzzle_PIECE_TOP)) - sr_puzzleY) / sr_puzzle_pSize;
  		      };	
			if (((cY*sr_puzzle_nbY) + cX + 1) != nbi) {
				return false;
                  };
            };
      };
	return true;
};
function sr_puzzle_moveImg(event)  // Handling a move of the mouse
{
	// If no piece is not selected, there is nothing to do
	if (sr_puzzle_selectedId == null) { return false; }
	// Otherwise, the piece will follow the position of the mouse
	else {
		if (sr_puzzleBrowser == "gecko") {
			document.getElementById("P" + sr_puzzle_selectedId).style.left = (eval(sr_puzzle_EVENT_X) - sr_puzzle_pSize/2) + "px";
			document.getElementById("P" + sr_puzzle_selectedId).style.top = (eval(sr_puzzle_EVENT_Y) - sr_puzzle_pSize/2) + "px";
        	} else {
			eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_LEFT) + ' = ' + (sr_puzzle_eventProp(event, sr_puzzle_EVENT_X) - sr_puzzle_pSize/2));
			eval(sr_puzzle_pieceProp(sr_puzzle_selectedId, sr_puzzle_PIECE_TOP) + ' = ' + (sr_puzzle_eventProp(event, sr_puzzle_EVENT_Y) - sr_puzzle_pSize/2));
        	}
		return false;
      }
};