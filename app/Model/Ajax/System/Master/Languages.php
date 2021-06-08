<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Project
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Ajax\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Master\LanguagesDao;

/**
 * Class to handle the ajax request fo Language.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Master
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Languages extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Language
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('lg_locale', $this->getStringParameter('search_key'));
        }
        return LanguagesDao::loadSingleSelectData('lg_locale', $wheres);
    }

}
