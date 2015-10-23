<?php
/**
 * DisguiseMe.class.php
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @package  IBIT_StudIP
 * @version  2.1
 * @license  GPL2 or any later version
 */
class DisguiseMe extends StudIPPlugin implements SystemPlugin
{
    private static $hit_once = false;

    public function getPluginname()
    {
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

        if ($this->is_disguised()) {
            // Improvement by anoack: Hide activity when disguised so the user
            // won't show as online
            $stealth_mode = function () {
                if (is_object($GLOBALS['sess'])) {
                    @session_write_close();
                }
                throw new NotificationVetoException();
            };
            NotificationCenter::addObserver($stealth_mode, '__invoke', 'PageCloseWillExecute');
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
