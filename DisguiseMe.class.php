<?php
// +---------------------------------------------------------------------------+
// DisguiseMe.class.php
//
// Copyright (c) 2014 Jan-Hendrik Willms <tleilax+studip@gmail.com>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+/


/**
 * DisguiseMe.class.php
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version     1.5
 */
class DisguiseMe extends StudIPPlugin implements SystemPlugin
{
    public function getPluginname()
    {
        return _('Disguise Me');
    }

    public function __construct()
    {
        parent::__construct();
        
        if (!$this->is_valid_user()) {
            die('foo');
            return;
        }

        $this->addStylesheet('disguise.less');
        PageLayout::addScript($this->getPluginURL() . '/disguise.js');

        $template_factory = new Flexi_TemplateFactory(dirname(__FILE__));

        if ($this->is_disguised()) {
            $html = $template_factory->render('disguised');
            PageLayout::addBodyElements($html);

            $navigation = Navigation::getItem('/links/logout');
            $navigation->setURL(PluginEngine::getLink($this, array(), 'logout'));
            Navigation::addItem('/links/logout', $navigation);
        }
    }
    
    public function as_action()
    {
        $as = Request::get('username');
        $user = User::findByUsername($as);

        if ($user === null || $user->isNew()) {
            throw new Exception('Cannot disguise as "' . $as . '" since the user is unknown.');
        }

        if (!$this->is_disguised()) {
            $_SESSION['old_identity'] = array(
                'uid'   => $_SESSION['auth']->auth['uid'],
                'perm'  => $_SESSION['auth']->auth['perm'],
                'uname' => $_SESSION['auth']->auth['uname'],
            );
        }

        $_SESSION['auth']->auth['uid']   = $user->id;
        $_SESSION['auth']->auth['perm']  = $user->perms;
        $_SESSION['auth']->auth['uname'] = $user->username;

        $this->relocate('dispatch.php/profile');
    }
    
    public function logout_action()
    {
        if (!$this->is_disguised()) {
            throw new Exception('Cannot log out of undisguised session');
        }

        $uname = $_SESSION['auth']->auth['uname'];

        foreach ($_SESSION['old_identity'] as $key => $value) {
            $_SESSION['auth']->auth[$key] = $value;
        }

        $_SESSION['old_identity'] = null;
        unset($_SESSION['old_identity']);

        $this->relocate('dispatch.php/profile?username=' . $uname);
    }

    private function is_valid_user()
    {
        return $this->is_disguised()
            || $GLOBALS['user']->perms === 'root';
    }

    private function is_disguised()
    {
        return !empty($_SESSION['old_identity']);
    }

    private function relocate($url = '')
    {
        page_close();
        header('Location: ' . URLHelper::getURL($url));
        die;
    }
}
