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
class DisguiseMe extends AbstractStudIPSystemPlugin {
    private static $hit_once = false;

    public function getPluginname() {
        return _('Disguise Me');
    }

    public function hasBackgroundTasks() {
        return true;
    }

    public function doBackgroundTasks() {
        if (!$this->is_valid_user() or self::$hit_once) {
            return;
        }
        self::$hit_once = true;

        $template_factory = new Flexi_TemplateFactory(dirname(__FILE__));

        if ($this->is_disguised()) {
            PageLayout::addStylesheet($this->getPluginURL() . '/disguised.css');

            $html = $template_factory->render('disguised', array(
                'random' => PluginEngine::getURL($this, array(
                    'action'    => 'random',
                    'return-to' => $_SERVER['REQUEST_URI'],
                )),
            ));
            PageLayout::addBodyElements($html);

            $script = $template_factory->render('disguised-js', array(
                'link'   => PluginEngine::getURL($this, array('logout' => 1)),
            ));
            $script = "//<![CDATA[\n" . rtrim($script) . "\n//]]>";
            PageLayout::addHeadElement('script', array('type' => 'text/javascript'), $script);
        } elseif (preg_match('/about\.php$/', $_SERVER['PHP_SELF']) and Request::get('username')) {
            $script = $template_factory->render('disguise-js', array(
                'link' => PluginEngine::getURL($this, array('disguise_as' => Request::get('username'))),
            ));
            $script = "//<![CDATA[\n" . rtrim($script) . "\n//]]>";
            PageLayout::addHeadElement('script', array('type' => 'text/javascript'), $script);
        } elseif (preg_match('~dispatch\.php/admin/user/~', $_SERVER['REQUEST_URI'])) {
            $script = $template_factory->render('disguise-search-js', array(
                'link' => PluginEngine::getURL($this, array('disguise_as' => 'REPLACE-WITH-USER')),
            ));
            $script = "//<![CDATA[\n" . rtrim($script) . "\n//]]>";
            PageLayout::addHeadElement('script', array('type' => 'text/javascript'), $script);
        }
    }

    public function actionshow() {
        if (Request::get('action') === 'random') {
            $statement = DBManager::get()->prepare("SELECT user_id, perms, username FROM auth_user_md5 ORDER BY RAND() LIMIT 1");
            $statement->execute(array($username));
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($row)) {
                return;
            }

            $_SESSION['auth']->auth['uid']   = $row['user_id'];
            $_SESSION['auth']->auth['perm']  = $row['perms'];
            $_SESSION['auth']->auth['uname'] = $row['username'];

            $this->relocate(Request::get('return-to'));
        } elseif ($this->is_disguised() and Request::get('logout')) {
            $uname = $_SESSION['auth']->auth['uname'];

            foreach ($_SESSION['old_identity'] as $key => $value) {
                $_SESSION['auth']->auth[$key] = $value;
            }

            $_SESSION['old_identity'] = null;
            unset($_SESSION['old_identity']);

            $this->relocate('about.php?username='.$uname);
        } elseif (!$this->is_disguised() and $username = Request::get('disguise_as')) {
            $statement = DBManager::get()->prepare("SELECT user_id, perms FROM auth_user_md5 WHERE username = ?");
            $statement->execute(array($username));
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($row)) {
                return;
            }

            $_SESSION['old_identity'] = array(
                'uid'   => $_SESSION['auth']->auth['uid'],
                'perm'  => $_SESSION['auth']->auth['perm'],
                'uname' => $_SESSION['auth']->auth['uname'],
            );

            $_SESSION['auth']->auth['uid']   = $row['user_id'];
            $_SESSION['auth']->auth['perm']  = $row['perms'];
            $_SESSION['auth']->auth['uname'] = $username;

            $this->relocate('about.php');
        }
    }

    private function is_valid_user() {
        return $this->is_disguised()
            or $this->getUser()->getPermission()->hasRootPermission();
    }

    private function is_disguised() {
        return !empty($_SESSION['old_identity']);
    }

    private function relocate($url = '') {
        page_close();
        header('Location: ' . URLHelper::getURL($url));
        die;
    }
}
