//rcmail.env.exists

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
        rcmail.env.mailpreviewframe_status = false;

        $('#messagelistfooter a').each( function () {
            var onclick;
            if (onclick = this.getAttribute('onclick')) this.setAttribute('onclick', 'rcube_event.cancel(event); ' + onclick);
        });

        $('#superpreview_countcontrols').detach().prependTo('#messagelistfooter');

        rcmail.nextmessage = function() {
            if (rcmail.env.cur_id < rcmail.message_list.rowcount) {
                if (rcmail.env.next_uid) {
                    rcmail.select_all_mode = false;
                    rcmail.message_list.clear_selection();
                    rcmail.show_message(rcmail.env.next_uid, false, true);
                    rcmail.message_list.select_row(rcmail.env.cur_uid, false, false);
                }
            }
        }

        rcmail.previousmessage = function() {
            if (rcmail.env.cur_id > 1) {
                if (rcmail.env.prev_uid) {
                    rcmail.message_list.clear_selection();
                    rcmail.show_message(rcmail.env.prev_uid, false, true);
                    rcmail.message_list.select_row(rcmail.env.cur_uid, false, false);
                }
            }
        }

        rcmail.update_superpreviewdisplay = function(id) {
            rcmail.env.cur_uid = id;

            var message_rows = [], uid;
            for (var i = 1; i < rcmail.message_list.list.rows.length; i++) {
                if ((uid = rcmail.message_list.list.rows[i].uid) && rcmail.message_list.rows[uid])
                    message_rows.push(uid);
            }
            for (var i = 0; i < message_rows.length; i++) {
                if (message_rows[i] == id) {
                    if (i < (message_rows.length - 1)) rcmail.env.next_uid = message_rows[i + 1]; else delete rcmail.env.next_uid;
                    if (i > 0) rcmail.env.prev_uid = message_rows[i - 1]; else delete rcmail.env.prev_uid;
                    rcmail.env.cur_id = i + 1;
                    break;
                }
            }

            if (rcmail.env.cur_id == 1) $('#superpreviewbtn1').addClass('disabled');
            else $('#superpreviewbtn1').removeClass('disabled');

            if (rcmail.env.cur_id == rcmail.message_list.rowcount) $('#superpreviewbtn2').addClass('disabled');
            else $('#superpreviewbtn2').removeClass('disabled');

            $(rcmail.gui_objects.superpreviewdisplay).html(rcmail.get_label('messagenrof').replace('$nr', rcmail.env.cur_id)
                                                        .replace('$count', rcmail.message_list.rowcount));
        }


        rcmail.show_message_orig = rcmail.show_message;
        rcmail.show_message = function(id, safe, preview) {
            rcmail.show_message_orig(id, safe, preview);
            if (id) rcmail.update_superpreviewdisplay(id);
        }

        rcmail.update_selection_orig = rcmail.update_selection;
        rcmail.update_selection = function() {
            rcmail.update_selection_orig();
            if (rcmail.env.cur_uid) rcmail.update_superpreviewdisplay(rcmail.env.cur_uid);
        }


        function AinA (array1, array2) {
            for (var key1 in array1)
                for (var key2 in array2)
                    if (array1[key1] === array2[key2]) return true;
            return false;
        }

        function previewtoggle() {
                    $('#mailview-bottom').toggleClass('mailexpand');
                    $('#mailview-top').toggleClass('mailcollapse');
                    if (rcmail.env.superpreview_hidefolderslist) $('#mailview-left, #mailview-right, #mailviewsplitterv').toggleClass('preview');
                    rcmail.env.mailpreviewframe_status = !rcmail.env.mailpreviewframe_status;
        }

        $('#messagelistfooter, #message').each( function () {
            this.addEventListener('click',  function() { if (rcmail.message_list.selection.length == 1) previewtoggle(); });
        });

        $('#messagetoolbar > a').each( function () {
            var ignored_button = AinA($(this).attr('class').split(' '), ['delete', 'archive', 'markasjunk', 'markasnotjunk', 'markasjunk2', 'markasnotjunk2']);
            this.addEventListener('click',  function() {
                if (rcmail.env.mailpreviewframe_status && (!ignored_button || (ignored_button && !rcmail.message_list.rowcount))) previewtoggle();
            });
        });

        $('#messagetoolbar > span, #messagesearchtools, #mailboxcontainer').each( function () {
            this.addEventListener('click',  function() { if (rcmail.env.mailpreviewframe_status) previewtoggle(); });
        });

  });
}
