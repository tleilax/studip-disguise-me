<?php
// +---------------------------------------------------------------------------+
// DisguiseMe.class.php
//
// Copyright (c) 2012 Jan-Hendrik Willms <tleilax+studip@gmail.com>
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
 * @package     IBIT_StudIP
 * @version     1.3.1
 */
class DisguiseMe extends StudIPPlugin implements SystemPlugin
{
    private static $hit_once = false;

    public function getPluginname() {
        return _('Disguise Me');
    }

    public function __construct()
    {
        parent::__construct();
        
        if (!$this->is_valid_user() or self::$hit_once) {
            return;
        }
        self::$hit_once = true;

        $template_factory = new Flexi_TemplateFactory(dirname(__FILE__));

        if ($this->is_disguised()) {
            $this->addStylesheet('disguised.less');
            PageLayout::addScript($this->getPluginURL() . '/disguised.js');

            $navigation = Navigation::getItem('/links/logout');
            $navigation->setURL(PluginEngine::getURL($this, array(), 'logout'));
            Navigation::addItem('/links/logout', $navigation);
        } elseif (preg_match('~dispatch\.php/profile~', $_SERVER['REQUEST_URI']) && Request::get('username')) {
            $script = $template_factory->render('disguise-js', array(
                'link' => PluginEngine::getURL($this, array('username' => Request::get('username')), 'disguise'),
            ));
            PageLayout::addHeadElement('script', array(), $script);
        } elseif (preg_match('~dispatch\.php/admin/user/~', $_SERVER['REQUEST_URI'])) {
            $script = $template_factory->render('disguise-search-js', array(
                'link' => PluginEngine::getURL($this, array('username' => 'REPLACE-WITH-USER'), 'disguise'),
            ));
            PageLayout::addHeadElement('script', array(), $script);
        }
    }

    public function perform($unconsumed_path)
    {
        if ($unconsumed_path === 'logout') {
            $username = $GLOBALS['user']->username;

            foreach ($_SESSION['old_identity'] as $key => $value) {
                $_SESSION['auth']->auth[$key] = $value;
            }

            $_SESSION['old_identity'] = null;
            unset($_SESSION['old_identity']);

            $this->relocate('dispatch.php/profile?username=' . $username);
        } elseif ($unconsumed_path === 'disguise' && $username = Request::get('username')) {
            $user = User::findByUsername($username);

            if (!$user) {
                return;
            }

            $_SESSION['old_identity'] = array(
                'uid'   => $_SESSION['auth']->auth['uid'],
                'perm'  => $_SESSION['auth']->auth['perm'],
                'uname' => $_SESSION['auth']->auth['uname'],
            );

            $_SESSION['auth']->auth['uid']   = $user->id;
            $_SESSION['auth']->auth['perm']  = $user->perms;
            $_SESSION['auth']->auth['uname'] = $user->username;

            $this->relocate('dispatch.php/profile');
        }
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
