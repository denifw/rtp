<?php
/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;
use App\Frame\System\Session\UserSession;
use App\Model\Dao\Setting\DashboardDao;

/**
 * Class to control the generation of menu system.
 *
 * @package    app
 * @subpackage Frame\System
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Menu
{

    /**
     * Property to store all the available menu on the system.
     *
     * @var array
     */
    private $Menus = [];

    /**
     * Property to store all the available page fot the system.
     *
     * @var array
     */
    private $Pages = [];

    /**
     * Property to store the active url.
     *
     * @var string
     */
    private $ActiveUrl;

    /**
     * Property to store the active menu.
     *
     * @var array
     */
    private $ActiveMenus = [];
    /**
     * Property to store the user data.
     *
     * @var UserSession $User
     */
    private $User;

    /**
     * The default function when we call the class.
     *
     * @param string $activeUrl To store the url.
     *                          AdminMenu constructor.
     */
    public function __construct($activeUrl)
    {
        $this->ActiveUrl = $activeUrl;
        $this->User = new UserSession();

    }

    /**
     * Function to load menu from database.
     *
     * @return void
     */
    public function loadMenu(): void
    {
        $this->Menus = SystemSettings::loadSettings('menus');
        if ($this->Menus === null) {
            Message::throwMessage(Trans::getWord('noMenuFound', 'message'), 'ERROR');
        }
    }

    /**
     * Function to prepare the page menu.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $pages = SystemSettings::loadSettings('pages');
        if ($pages === null) {
            Message::throwMessage(Trans::getWord('noActivePageFound', 'message'), 'ERROR');
        }
        foreach ($pages as $page) {
            if (empty($page['pg_mn_id']) === false) {
                if (array_key_exists($page['pg_mn_id'], $this->Pages) === false) {
                    $this->Pages[$page['pg_mn_id']] = [];
                }
                $this->Pages[$page['pg_mn_id']][] = $page;

            }
        }
    }


    /**
     * Function to get the menu in string data.
     *
     * @return string
     */

    public function __toString()
    {
        return $this->createMenu();
    }


    /**
     * Function to create menu
     *
     * @return string
     */

    public function createMenu(): string
    {
        $this->loadMenu();
        $this->loadPage();
        $result = '<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">';
        $result .= '<div class="menu_section">';
        $result .= '<ul class="nav side-menu">';
        foreach ($this->Menus as $menu) {
            if (empty($menu['mn_parent']) === true) {
                if (strtolower($menu['mn_name']) === 'root') {
                    $result .= $this->getItemLinkMenu($menu['mn_id']);
                } else {
                    $result .= $this->getItemMenu($menu);
                }
            }
        }
        $result .= '</ul>';
        $result .= '</div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to get the link item for the menu.
     *
     * @param string $menuId To store the menu id.
     *
     * @return string
     */
    private function getItemLinkMenu($menuId): string
    {
        $result = '';
        if (array_key_exists($menuId, $this->Pages) === true) {
            $pages = $this->Pages[$menuId];
            foreach ($pages as $page) {
                $selected = $this->isSelectedMenu($page['pg_route'], $page['pc_route']);
                # Check Active menu.
                $active = '';
                if ($selected === true && $this->User->isSet() && $this->User->getMenuStyle() !== 'nav-sm') {
                    $this->ActiveMenus[$menuId] = true;
                    $active = ' class="current-page"';
                }
                $result .= '<li' . $active . '>';
                $result .= '<a href="' . url('/' . $this->getPageUrl($page['pg_route'], $page['pc_route'])) . '">';
                if ($menuId === 1) {
                    $result .= '<i class="' . $page['pg_icon'] . '"></i> ';
                }
                $result .= Trans::getWord($page['pg_id'] . '.title', 'page', $page['pg_title']);
                $result .= '</a>';
                $result .= '</li>';
            }
        }

        return $result;
    }

    /**
     * Function to get the parent menu.
     *
     * @param string $pageRoute To store the page route.
     * @param string $pageCategoryRoute To store the page category route.
     *
     * @return boolean
     */
    private function isSelectedMenu(string $pageRoute, string $pageCategoryRoute): bool
    {
        $selected = $this->ActiveUrl === $this->getPageUrl($pageRoute, $pageCategoryRoute);
        if ($selected === false && empty($pageRoute) === false) {
            $selected = mb_strpos($this->ActiveUrl, $pageRoute . '/') === 0;
        }

        return $selected;
    }

    /**
     * Function to get the page url.
     *
     * @param string $pageRoute To store the page route.
     * @param string $pageCategoryRoute To store the page category route.
     *
     * @return string
     */
    private function getPageUrl(string $pageRoute, string $pageCategoryRoute): string
    {
        $url = $pageRoute;
        if (empty($pageCategoryRoute) === false) {
            $url .= '/' . $pageCategoryRoute;
        }

        return $url;
    }

    /**
     * Function to get the parent menu.
     *
     * @param array $menu To store the menu id.
     *
     * @return string
     */
    private function getItemMenu($menu): string
    {
        if ($menu['mn_name'] === 'Dashboard') {
            $childMenu = $this->getItemChildMenuDashboard($menu['mn_id']);
        } else {
            $childMenu = $this->getItemChildMenu($menu['mn_id']);
        }
        $result = '';
        # get the item child menu.
        if (empty($childMenu) === false) {
            $selected = false;
            # set the css style for the menu if the menu selected.
            if (array_key_exists($menu['mn_id'], $this->ActiveMenus) === true) {
                $selected = true;
                if (empty($menu['mn_parent']) === false) {
                    $this->ActiveMenus[$menu['mn_parent']] = true;
                }
            }
            # Check Active menu.
            $active = '';
            if ($selected === true && $this->User->isSet() && $this->User->getMenuStyle() !== 'nav-sm') {
                $active = ' class="active"';
            }
            $result .= '<li' . $active . '>';
            $result .= '<a href="javascript:;">';
            $result .= '<i class="' . $menu['mn_icon'] . '"></i> ';
            $result .= Trans::getWord($menu['mn_id'], 'menu', $menu['mn_name']);
            $result .= ' <span class="' . Icon::ChevronDown . '"></span>';
            $result .= '</a>';
            $result .= $childMenu;
            $result .= '</li>';
        }

        return $result;
    }

    /**
     * Function to get the parent menu.
     *
     * @param string $menuId To store the menu id.
     *
     * @return string
     */
    private function getItemChildMenu($menuId): string
    {
        $result = '';
        # get link item for the link child.
        $strLink = $this->getItemLinkMenu($menuId);
        $strSubMenu = '';
        foreach ($this->Menus as $menu) {
            if ($menu['mn_parent'] === $menuId) {
                # Call recursive function if there is another menu child.
                $strSubMenu .= $this->getItemMenu($menu);
            }
        }
        # create sub menu if the link child or menu child is not empty.
        if (empty($strLink) === false || empty($strSubMenu) === false) {
            $styleActive = '';
            if (array_key_exists($menuId, $this->ActiveMenus) === true) {
                $styleActive = ' style = "display: block;"';
            }
            $result .= '<ul class="nav child_menu" ' . $styleActive . '>';
            $result .= $strLink;
            $result .= $strSubMenu;
            $result .= '</ul>';
        }

        return $result;
    }

    /**
     * Function to get the parent menu.
     *
     * @param string $menuId To store the menu id.
     *
     * @return string
     */
    private function getItemChildMenuDashboard($menuId): string
    {
        $result = '';
        # Load Dashboard User
        $wheres[] = '(dsh.dsh_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(dsh.dsh_us_id = ' . $this->User->getId() . ')';
        $wheres[] = '(dsh.dsh_deleted_on IS NULL)';
        $orderList[] = 'dsh.dsh_order ASC';
        $dashboard = DashboardDao::loadData($wheres, $orderList);
        # get link item for the link child.
        $strLink = '';
        $strSubMenu = '';
        if (empty($dashboard) === false) {
            $selected = $this->isSelectedMenu('home', '');
            # Check Active menu.
            $active = '';
            if ($selected === true && $this->User->isSet() && $this->User->getMenuStyle() !== 'nav-sm') {
                $this->ActiveMenus[$menuId] = true;
                $active = ' class="current-page"';
            }
            foreach ($dashboard as $row) {
                if ((int)request('dsh_id') === $row['dsh_id']) {
                    $strLink .= '<li ' . $active . '>';
                } else {
                    $strLink .= '<li>';
                }
                $strLink .= '<a href="' . url('/home?dsh_id=' . $row['dsh_id']) . '">';
                $strLink .= $row['dsh_name'];
                $strLink .= '</a>';
                $strLink .= '</li>';
            }
        } else {
            $strLink = $this->getItemLinkMenu($menuId);
        }
        foreach ($this->Menus as $menu) {
            if ($menu['mn_parent'] === $menuId) {
                # Call recursive function if there is another menu child.
                $strSubMenu .= $this->getItemMenu($menu);
            }
        }
        # create sub menu if the link child or menu child is not empty.
        if (empty($strLink) === false || empty($strSubMenu) === false) {
            $styleActive = '';
            if (array_key_exists($menuId, $this->ActiveMenus) === true) {
                $styleActive = ' style = "display: block;"';
            }
            $result .= '<ul class="nav child_menu" ' . $styleActive . '>';
            $result .= $strLink;
            $result .= $strSubMenu;
            $result .= '</ul>';

        }

        return $result;
    }


}