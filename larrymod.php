<?php

// Simple html dom 1.5. Website: http://sourceforge.net/projects/simplehtmldom/
if (!function_exists('file_get_html'))
    require_once 'lib/simple_html_dom.php';

/**
 * Larry MOD
 *
 * Larry skin mod: minimize header, hide toolbar labels, hide topbar labels, unselectable interface element, expand/collapse mailpreview frame
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Sergey Sidlyarenko
 */

class larrymod extends rcube_plugin
{
    public $noajax  = true;
    private $rc;
    private $headermini = false;
    private $hidelabels = false;
    private $unselectable = false;
    private $superpreview = false;
    private $superpreview_hidefolderslist = false;

    function init()
    {
        $this->rc = rcmail::get_instance();
        $this->load_config();

        $skin = $this->rc->config->get('skin');

        if ($skin == "larry") {

            if ($this->rc->task == 'settings') {
                $this->add_hook('preferences_list', array($this, 'prefs_list'));
                $this->add_hook('preferences_save', array($this, 'prefs_save'));
            }

            $this->headermini = ($this->rc->task != 'login') && ($this->rc->task != 'logout')
                                && (empty($_REQUEST['_framed']) || (($this->rc->task == 'mail') && ($this->rc->action == 'get') && !empty($_REQUEST['_frame'])))
                                ? $this->rc->config->get('larrymod_headermini', false) : false;

            $this->hidelabels = (($this->rc->task == 'mail') || ($this->rc->task == 'addressbook')) && empty($_REQUEST['_framed'])
                                ? $this->rc->config->get('larrymod_hidelabels', false) : false;

            $this->hidetoplabels = ($this->rc->task != 'login') && ($this->rc->task != 'logout') && empty($_REQUEST['_framed'])
                                ? $this->rc->config->get('larrymod_hidetoplabels', false) : false;

            $this->unselectable = $this->rc->config->get('larrymod_unselectable', false);

            $this->superpreview = ($this->rc->task == 'mail') && ($this->rc->action == '')
                                ? $this->rc->config->get('larrymod_superpreview', false) : false;

            $this->superpreview_hidefolderslist = ($this->rc->task == 'mail') && ($this->rc->action == '')
                                ? $this->rc->config->get('larrymod_superpreview_hidefolderslist', false) : false;

            if ($this->unselectable) {
                $this->include_script('unselectable.js');
                $this->include_stylesheet($this->local_skin_path().'/unselectable.css');
            }

            if ($this->superpreview) {
                $this->include_script('superpreview.js');
                $this->include_stylesheet($this->local_skin_path().'/superpreview.css');
                $this->add_hook('render_page', array($this, 'superpreview_add'));
            }

            if ($this->headermini) {
                $this->include_stylesheet($this->local_skin_path().'/headermini.css');
                setcookie ('minimalmode', '0');
            }

            if ($this->hidelabels)
                $this->include_stylesheet($this->local_skin_path().'/hidelabels.css');

            if ($this->headermini || $this->hidelabels)
                $this->add_hook('render_page', array($this, 'content_update'));

        }
    }


    function prefs_list($args)
    {
        if ($args['section'] != 'general') {
            return $args;
        }

        $this->load_config();

        $this->add_texts('localization/');

        $dont_override = array_merge((array) $this->rc->config->get('dont_override', array()) , (array) $this->rc->config->get('larrymod_dont_override', array()));

        foreach (array('headermini', 'hidelabels', 'hidetoplabels', 'superpreview', 'superpreview_hidefolderslist', 'unselectable') as $type) {
            $key = 'larrymod_' . $type;
            if (!in_array($key, $dont_override)) {

                if (empty($args['blocks']['skin']['name']))
                    $args['blocks']['skin'] = array('name' => rcube::Q($this->gettext('skin')),);

                $field_id = '_' . $key;
                $input  = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));

                $args['blocks']['skin']['options'][$key] = array(
                    'title' => html::label($field_id, rcube::Q($this->gettext($type))),
                    'content' => $input->show($this->rc->config->get($key, false))
                );
            }
        }

        return $args;
    }

    function prefs_save($args)
    {
        if ($args['section'] != 'general') {
            return $args;
        }

        $this->load_config();

        $dont_override = array_merge((array) $this->rc->config->get('dont_override', array()) , (array) $this->rc->config->get('larrymod_dont_override', array()));

        foreach (array('headermini', 'hidelabels', 'hidetoplabels', 'superpreview', 'superpreview_hidefolderslist', 'unselectable') as $type) {
            $key = 'larrymod_' . $type;
            if (!in_array($key, $dont_override)) {
                $args['prefs'][$key] = rcube_utils::get_input_value('_'.$key, rcube_utils::INPUT_POST) ? true : false;
            }
        }

        return $args;
    }

    function superpreview_add($p)
    {
        $this->rc->output->add_label('messagenrof');
        $this->rc->output->set_env('superpreview_hidefolderslist', $this->superpreview_hidefolderslist);

        $this->rc->output->add_gui_object('superpreviewdisplay', 'superpreviewdisplay');
        $out = html::span(array('id' => 'superpreviewdisplay', 'class' => 'countdisplay'), '');

        $out .= html::a(array('onclick' => 'rcube_event.cancel(event); return rcmail.previousmessage();', 'id' => 'superpreviewbtn1', 'href' => '#',
                 'title' => rcube::Q($this->gettext('previousmessage')), 'class' => 'button prevpage disabled'), html::span(array('class' => 'inner'), '&lt;'));

        $out .= html::a(array('onclick' => 'rcube_event.cancel(event); return rcmail.nextmessage();', 'id' => 'superpreviewbtn2', 'href' => '#',
                 'title' => rcube::Q($this->gettext('nextmessage')), 'class' => 'button nextpage disabled'), html::span(array('class' => 'inner'), '&gt;'));

        $out = html::div(array('id' => 'superpreview_countcontrols', 'class' => 'pagenav dark'), $out);

        $this->rc->output->add_footer($out);

    }



    function content_update($args)
    {
        $html = str_get_html($args['content'], true, true, DEFAULT_TARGET_CHARSET, false);

        if ($this->hidetoplabels) {

            foreach ($html->find('#topnav > #taskbar > a > span.button-inner') as $span) {
                $span->parentNode()->setAttribute('title', $span->innertext);
                $span->innertext = '';
            }

            $logout = $html->find('#topline > .topright > .button-logout', 0);
            if (isset($logout->innertext))
                $logout->setAttribute('title', $logout->innertext);
            $logout->innertext = '&nbsp';
            $logout->style = 'text-decoration: none';

        }

        if ($this->headermini) {

            if ($toplogo = $html->find('#toplogo', 0)) $toplogo->outertext = '';

            if (!$html->find('#header #topnav .button-logout', 0)) {
                $args['content'] = $html->innertext;
                $html->clear();
                unset($html);
                return $args;
            }

            $html->find('#header #topnav .minmodetoggle', 0)->outertext = '';
            $html->find('#header #topnav .button-logout', 0)->outertext = '';


            $topnav = $html->find('#header #topnav', 0);
            $html->find('#header #topline .topleft', 0)->outertext .=  $topnav->outertext;
            $topnav->outertext = '';
        }

        $args['content'] = $html->innertext;
        $html->clear();
        unset($html);

        return $args;
    }

}
