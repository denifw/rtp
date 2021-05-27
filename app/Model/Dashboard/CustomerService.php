<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dashboard;

use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractDashboardModel;

/**
 * Class to create the view for CustomerService dashboard.
 *
 * @package    app
 * @subpackage Model\Dashboard
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class CustomerService extends AbstractDashboardModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'csHome');
        $this->setParameters($parameters);
    }


    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadView(): void
    {
        $btn = new Button('BtnShipment', 'Coba');
        $btn->setIcon(Icon::Plus)->btnSuccess()->btnMedium()->pullRight();
        $this->View->addButton($btn);
    }

    public function loadData(): array
    {
        // TODO: Implement loadData() method.
    }

    protected function doInsert(): ?int
    {
        // TODO: Implement doInsert() method.
    }

    protected function doUpdate(): void
    {
        // TODO: Implement doUpdate() method.
    }

    protected function loadDefaultButton(): void
    {
        // TODO: Implement loadDefaultButton() method.
    }

    public function loadDashboardItem(): void
    {
        // TODO: Implement loadDashboardItem() method.
    }

}
