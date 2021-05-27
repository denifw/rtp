<?php

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;

class PageSetting
{

    /**
     * Property to store all the right for current page.
     *
     * @var array
     */
    private $Rights = [];

    /**
     * Property to store the page.
     *
     * @var array $Page
     */
    private $Page = [];

    /**
     * Property to store the page category.
     *
     * @var string $pageCategory
     */
    private $pageCategory;

    /**
     * Property to store the page route.
     *
     * @var string $pageRoute
     */
    private $pageRoute;

    /**
     * Property to store the user object
     *
     * @var UserSession $User
     */
    private $User;

    /**
     * Base model constructor.
     *
     * @param string      $pageCategory To store the page category of the model.
     * @param string      $pageRoute    To store the name space model to load the page setting.
     * @param UserSession $user         To store the user object.
     *
     */
    public function __construct($pageCategory, $pageRoute, UserSession $user)
    {
        $this->pageCategory = $pageCategory;
        $this->pageRoute = $pageRoute;
        $this->User = $user;
    }

    /**
     * Function load the page.
     *
     * @return void
     */
    public function loadPageSetting(): void
    {
        $this->loadPage($this->pageCategory, $this->pageRoute);
        $this->loadPageRights();
    }

    /**
     * Function load the page.
     *
     * @param string $pageCategory To store the page category of the model.
     * @param string $pageRoute    To store the name space model to load the page setting.
     *
     * @return void
     */
    public function loadPage($pageCategory, $pageRoute): void
    {
        $page = null;
        $pages = SystemSettings::loadSettings('pages');
        if ($pages !== null) {
            foreach ($pages as $p) {
                if ($p['pc_name'] === $pageCategory && $p['pg_route'] === $pageRoute) {
                    $page = $p;
                }
            }
        }
        if ($page === null) {
            Message::throwMessage(Trans::getWord('doNotHavePermission', 'message'), 'ERROR');
        }
        $this->Page = $page;
    }

    /**
     * Function to load all the right per page.
     *
     * @return void
     */
    private function loadPageRights(): void
    {
        $rights = SystemSettings::loadSettings('pageRights');
        if (array_key_exists($this->getPageId(), $rights) === true) {
            $data = $rights[$this->getPageId()];
            foreach ($data as $row) {
                $this->Rights[$row['pr_name']] = true;
            }
        }
    }

    /**
     * Function to load all the right per page.
     *
     * @param int $idPage The page id.
     *
     * @return array
     */
    public function loadPageRightsByIdPage(int $idPage): array
    {
        $results = [];
        $rights = SystemSettings::loadSettings('pageRights');
        if (array_key_exists($idPage, $rights) === true) {
            $data = $rights[$idPage];
            foreach ($data as $row) {
                $results[$row['pr_name']] = true;
            }
        }
        return $results;
    }

    /**
     * Function to get the page title.
     *
     * @return string
     */
    public function getPageTitle(): string
    {
        return Trans::getWord($this->Page['pg_id'] . '.title', 'page', $this->Page['pg_title']);
    }

    /**
     * Function to get the page title.
     *
     * @param string $right To store the right name.
     *
     * @return boolean
     */
    public function checkPageRight(string $right): bool
    {
        if ($this->User->isUserSystem()) {
            return true;
        }
        if (array_key_exists($right, $this->Rights) === true) {
            $result = $this->Rights[$right];
        } else {
            $result = false;
        }

        return $result;
    }


    /**
     * Function to get the page title.
     *
     * @return array
     */
    public function getPageRight(): array
    {
        return $this->Rights;
    }

    /**
     * Function to get the page id.
     *
     * @return string
     */
    public function getPageId(): string
    {
        return $this->Page['pg_id'];
    }

    /**
     * Function to get the page description.
     *
     * @return string
     */
    public function getPageDescription(): string
    {
        return $this->Page['pg_description'];
    }

    /**
     * Function to get the page path.
     *
     * @return string
     */
    public function getPageRoute(): string
    {
        return $this->Page['pg_route'];
    }

    /**
     * Function to get the page path.
     *
     * @return string
     */
    public function getPageCategory(): string
    {
        return $this->pageCategory;
    }

    /**
     * Function to get the page path.
     *
     * @return string
     */
    public function getPageUrl(): string
    {
        $url = $this->Page['pg_route'];
        if (empty($this->Page['pc_route']) === false) {
            $url .= '/' . $this->Page['pc_route'];
        }

        return $url;
    }

    /**
     * Function to get full url.
     *
     * @return string
     */
    public function getPageFullUrl(): string
    {
        return url()->full();
    }
}
