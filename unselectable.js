if (window.rcmail) {

  rcmail.addEventListener('init', function(evt) {

        $(document).keydown(function(objEvent) {
            if (objEvent.ctrlKey) {
                if (objEvent.keyCode == 65) {
                    if ((rcmail.env.task == 'mail') && (rcmail.env.action == '')) {
                        rcmail.command('select-all','page',this);
                    }
                    return false;
                }
            }
        });

    function makeUnselectable(node) {
        if (node.nodeType == 1) {
            if (   (node.nodeName == 'INPUT' && node.getAttribute('type') != 'button')
                || node.nodeName == 'TEXTAREA'
                || node.getAttribute('id') == 'messagepreview'
                || node.getAttribute('id') == 'messagebody'
                || node.getAttribute('id') == 'headers-source'
                || node.getAttribute('id') == 'composebody'
                || node.getAttribute('id') == 'messagepreviewheader'
                || (/(^|\s)+data($|\s)+/.test(node.className))
                || (/(^|\s)+email($|\s)+/.test(node.className))
                || (/(^|\s)+text($|\s)+/.test(node.className))
                || (/(^|\s)+select($|\s)+/.test(node.className))
                || (/(^|\s)+namefield($|\s)+/.test(node.className))
                )
            {
                node.setAttribute('unselectable','off');
                return true;
            } else {
                node.setAttribute('unselectable','on');
            }
            if (!node.ondragstart) node.ondragstart = function (){return false;};
        }
        var child = node.firstChild;
        while (child) {
            makeUnselectable(child);
            child = child.nextSibling;
        }
    }

    if ($('body')[0]) {
        makeUnselectable($('body').get(0));
    }

  });
}
