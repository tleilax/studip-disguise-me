<?php
// +---------------------------------------------------------------------------+
// DisguiseMe.class.php
//
// Copyright (c) 2011 Jan-Hendrik Willms <tleilax+studip@gmail.com>
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
 * @version     1.3
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

		$template = false;
		if ($this->is_disguised()) {
			$link = PluginEngine::getURL($this, array('logout' => 1));
            $username = $this->get_user_name($GLOBALS['auth']->auth['uid']);
			$template = 'disguised.js';
		} elseif (preg_match('/about\.php$/', $_SERVER['PHP_SELF']) and Request::get('username')) {
			$link = PluginEngine::getURL($this, array('disguise_as' => Request::get('username')));
			$template = 'disguise.js';
		}

		if (!$template) {
            return;
		}

		ob_start();
		include $template;
		$script = ob_get_clean();
        
        $script = "//<![CDATA[\n".rtrim($script)."\n//]]>";

		PageLayout::addHeadElement('script', array('type' => 'text/javascript'), $script);
	}

    private function get_user_name ($user_id) {
        $statement = DBManager::get()->prepare("SELECT CONCAT(Vorname, ' ', Nachname) FROM auth_user_md5 WHERE user_id = ?");
        $statement->execute(array($user_id));
        return $statement->fetchColumn();
    }

	public function actionshow() {
		if ($this->is_disguised() and Request::get('logout')) {
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

			$this->relocate();
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
		header('Location: '.$GLOBALS['ABSOLUTE_URI_STUDIP'].$url);
		die;
	}
}
