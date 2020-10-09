// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript helper function for SCORM module.
 *
 * @package   report_bbboverview
 * @copyright 2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.report_bbboverview = {};

M.report_bbboverview.init = function(Y) {

    console.log('initializeview');

    // Handle AJAX Request.
    var bbboverview_ajax_request = function(url) {

        var myRequest = NewHttpReq();
        var result = DoRequest(myRequest, url);
        result = Y.JSON.parse(result);
        return result.table;
    };

    var remove_tabledata = function() {
        contentold = Y.one('#session-view table');
        Y.one('#session-view').removeChild(contentold);
    };

    var load_tabledata = function(tabledata) {
		//modal should appear here 
		//$("#modalForm").modal("show");

		console.log("cliked");
		console.log(tabledata);
        Y.one('#session-view').setHTML(tabledata);
        
       var sessionview = $('#bbboverview a.view');

        sessionview.on('click', function(e) {
            e.preventDefault();

            var viewlink = $(this).attr('href');
            console.log(viewlink);
            detailedview = bbboverview_ajax_request(viewlink);
           // remove_tabledata();
            load_tabledata(detailedview);

        });
        
        var textlinkpage = Y.one('#session-view a#textlinkpage');

        textlinkpage.on('click', function(e) {
            e.preventDefault();

            var viewlink = textlinkpage.getAttribute('href');
            detailedview = bbboverview_ajax_request(viewlink);
           // remove_tabledata();
            load_tabledata(detailedview);

        });
    };

    var bbboverview_progress = function() {

        var sessionview = $('#bbboverview a.view');

        sessionview.on('click', function(e) {
            e.preventDefault();

            var viewlink = $(this).attr('href');
            console.log(viewlink);
            detailedview = bbboverview_ajax_request(viewlink);
           // remove_tabledata();
            load_tabledata(detailedview);

        });

    };

    bbboverview_progress();

};

function NewHttpReq() {
    var httpReq = false;
    if (typeof XMLHttpRequest != 'undefined') {
        httpReq = new XMLHttpRequest();
    } else {
        try {
            httpReq = new ActiveXObject("Msxml2.XMLHTTP.4.0");
        } catch (e) {
            try {
                httpReq = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (ee) {
                try {
                    httpReq = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (eee) {
                    httpReq = false;
                }
            }
        }
    }
    return httpReq;
}

function DoRequest(httpReq, url) {

    httpReq.open("POST", url, false);
    httpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    try {
        httpReq.send();
    } catch (e) {
        return false;
    }
    if (httpReq.status == 200) {
        return httpReq.responseText;
    } else {
        return httpReq.status;
    }
}
