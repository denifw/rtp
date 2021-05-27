<?php
/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use Illuminate\Support\Facades\DB;

/**
 * Class to manage abstraction of the detail page.
 *
 * @package    app
 * @subpackage Model\Detail
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractDashboardModel extends AbstractBaseLayout
{
    /**
     * Property to store the content of the dashboard.
     *
     * @var string $Content
     */
    protected $Content = '';

    /**
     * Property to store the route name.
     *
     * @var string
     */
    private $RouteName;

    /**
     * Property to store the reference code.
     *
     * @var string
     */
    private $ReferenceCode = 'dsh_id';

    /**
     * Property to store the reference value.
     *
     * @var int
     */
    private $ReferenceValue = 0;

    /**
     * Property to store the temporary object model.
     *
     * @var \App\Frame\Mvc\AbstractBaseDashboardItem $Model
     */
    protected $Model;

    /**
     * Function to store status of transaction.
     *
     * @var bool $SuccessTransaction
     */
    private $SuccessTransaction = false;

    /**
     * Base listing model constructor.
     *
     * @param string $nameSpace To store the name space of the page.
     * @param string $route     To store the name space of the page.
     */
    public function __construct(string $nameSpace, string $route)
    {
        parent::__construct('Dashboard', $nameSpace, $route);
        $this->RouteName = $route;
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    abstract public function loadData(): array;

    /**
     * Abstract function to insert data into database.
     *
     * @return null|int
     */
    abstract protected function doInsert(): ?int;

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    abstract protected function doUpdate(): void;

    /**
     * Abstract function to load the default button of the page.
     *
     * @return void
     */
    abstract protected function loadDefaultButton(): void;

    /**
     * Function to load dashboard item.
     *
     * @return void
     */
    abstract public function loadDashboardItem(): void;

    /**
     * Function to set data.
     *
     * @param string $content To store the data.
     *
     * @return void
     */
    protected function addContent(string $content): void
    {
        if (empty($content) === false) {
            $this->Content .= $content;
        }
    }

    /**
     * Function to do the transaction of database.
     *
     * @return void
     */
    public function doTransaction(): void
    {
        $this->loadValidationRole();
        if ($this->isValidPostValues() === true) {
            DB::beginTransaction();
            try {
                if ($this->isUpdate() === true) {
                    $this->doUpdate();
                    $this->setSucccessTransaction(true);
                    $this->addSuccessMessage(Trans::getWord('successUpdate', 'message'));
                }
                if ($this->isInsert() === true) {
                    $lastInsertId = $this->doInsert();
                    $this->setReferenceValue($lastInsertId);
                    $this->setSucccessTransaction(true);
                    $this->addSuccessMessage(Trans::getWord('successInsert', 'message'));
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                if ($this->isUpdate() === true) {
                    $this->addErrorMessage(Trans::getWord('failedUpdate', 'message'));
                }
                if ($this->isInsert() === true) {
                    $this->addErrorMessage(Trans::getWord('failedInsert', 'message'));
                }
                $this->addErrorMessage($this->doPrepareSqlErrorMessage($e->getMessage()));
            }
        } else {
            # Set the error messages.
            if ($this->isInsert() === true) {
                $this->addErrorMessage(Trans::getWord('failedInsert', 'message'));
            }
            if ($this->isUpdate() === true) {
                $this->addErrorMessage(Trans::getWord('failedUpdate', 'message'));
            }
        }
    }

    /**
     * Function to get the the default route.
     *
     * @return string
     */
    public function getDefaultRoute(): string
    {
        return $this->RouteName;
    }

    /**
     * Function set transaction status.
     *
     * @param bool $result
     */
    public function setSucccessTransaction(bool $result): void
    {
        $this->SuccessTransaction = $result;
    }

    /**
     * Function to get transaction status
     *
     * @return bool
     */
    public function isSuccessTransaction(): bool
    {
        return $this->SuccessTransaction;
    }

    /**
     * Function to prepare sql message.
     *
     * @param string $error To store the error message of the sql.
     *
     * @return string
     */
    public function doPrepareSqlErrorMessage(string $error): string
    {
        $message = $error;
        if (strpos($error, 'duplicate') !== false) {
            $indexUniqueWord = strpos($error, '_unique');
            if ($indexUniqueWord !== false) {
                $message = substr($error, 0, $indexUniqueWord + 8) . '.';
                $message = str_replace('"', '', $message);
            }
        }

        return $message;
    }

    /**
     * Function to set the reference value.
     *
     * @param integer $referenceValue To store the dashboar id.
     *
     * @return void
     */
    public function setReferenceValue($referenceValue): void
    {
        $this->ReferenceValue = $referenceValue;
    }

    /**
     * Function to get the detail reference value.
     *
     * @return integer
     */
    public function getReferenceValue(): int
    {
        if (empty($this->ReferenceValue) === true && empty($this->getReferenceCode()) === false) {
            $this->ReferenceValue = (int)$this->getIntParameter($this->getReferenceCode());
        }

        return $this->ReferenceValue;
    }

    /**
     * Function to get the reference code.
     *
     * @return string
     */
    public function getReferenceCode(): string
    {
        return $this->ReferenceCode;
    }

    /**
     * Function to check is it insert process or not.
     *
     * @return boolean
     */
    public function isInsert(): bool
    {
        $result = false;
        if (empty($this->getReferenceValue()) === true) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to check is it update process or not.
     *
     * @return boolean
     */
    public function isUpdate(): bool
    {
        $result = false;
        if (empty($this->getReferenceValue()) === false) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to remove the active modal
     *
     * @return void
     */
    public function removeActiveModal(): void
    {
        $this->View->setActiveModal('');
    }

    /**
     * Function to load all the view data and convert it to array.
     *
     * @return array
     */
    public function createView(): array
    {
        $this->loadDefaultButton();
        if ($this->isUpdate()) {
            $this->View->setDescription($this->getStringParameter('dsh_name'));
            $this->View->setTitle($this->getStringParameter('dsh_name'));
        } else {
            $this->View->setDescription('My Dashboard');
            $this->View->setTitle('My Dashboard');
        }
        $this->View->addContent('dashboard', $this->Content);
        $this->View->addContent('reference_value', $this->Field->getHidden($this->getReferenceCode(), $this->getReferenceValue()));

        return parent::createView();
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    protected function getNewDashboardModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('NewDshMdl', Trans::getWord('newDashboard'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertDashboard');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertDashboard' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        $dashboardNameField = $this->Field->getText('dsh_name_new', $this->getParameterForModal('dsh_name_new', $showModal));
        $dashboardDescriptionField = $this->Field->getText('dsh_description_new', $this->getParameterForModal('dsh_description_new', $showModal));
        $dashboardOrderField = $this->Field->getNumber('dsh_order_new', $this->getIntParameter('dsh_order_new'));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $dashboardNameField, true);
        $fieldSet->addField(Trans::getWord('description'), $dashboardDescriptionField, true);
        $fieldSet->addField(Trans::getWord('orderNumber'), $dashboardOrderField, true);
        $modal->setBtnOkName(Trans::getWord('save'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    protected function getEditDashboardModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('EditDshMdl', Trans::getWord('editDashboard'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDashboard');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDashboard' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        $dashboardNameField = $this->Field->getText('dsh_name', $this->getParameterForModal('dsh_name', $showModal));
        $dashboardDescriptionField = $this->Field->getText('dsh_description', $this->getParameterForModal('dsh_description', $showModal));
        $dashboardOrderField = $this->Field->getNumber('dsh_order', $this->getIntParameter('dsh_order'));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $dashboardNameField, true);
        $fieldSet->addField(Trans::getWord('description'), $dashboardDescriptionField, true);
        $fieldSet->addField(Trans::getWord('orderNumber'), $dashboardOrderField, true);
        $modal->setBtnOkName(Trans::getWord('update'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    protected function getDeleteDashboardModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('DeleteDshMdl', Trans::getWord('deleteDashboard'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDashboard');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDashboard' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        $dashboardNameField = $this->Field->getText('dsh_name_del', $this->getParameterForModal('dsh_name_del', $showModal));
        $dashboardNameField->setReadOnly();
        $dashboardDescriptionField = $this->Field->getText('dsh_description_del', $this->getParameterForModal('dsh_description_del', $showModal));
        $dashboardDescriptionField->setReadOnly();
        $dashboardOrderField = $this->Field->getText('dsh_order_del', $this->getParameterForModal('dsh_order_del', $showModal));
        $dashboardOrderField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $dashboardNameField);
        $fieldSet->addField(Trans::getWord('description'), $dashboardDescriptionField);
        $fieldSet->addField(Trans::getWord('orderNumber'), $dashboardOrderField);
        $fieldSet->addHiddenField($this->Field->getHidden('dsh_id_del', $this->getParameterForModal('dsh_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to create modal delete.
     *
     * @return \App\Frame\Gui\Modal
     */
    protected function getDeleteWidgetModal(): Modal
    {
        $modal = new Modal('DeleteDsdMdl', Trans::getWord('deleteWidget'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteWidget');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteWidget' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        $titleField = $this->Field->getText('dsd_title_del', $this->getParameterForModal('dsd_title_del', $showModal));
        $titleField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('title'), $titleField);
        $fieldSet->addHiddenField($this->Field->getHidden('dsd_id_del', $this->getParameterForModal('dsd_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;

    }


    /**
     * Function to get last order number of dashboard.
     *
     * @return Int
     */
    protected function getLastOrderNumber(): int
    {
        $orderNumber = 1;
        $query = 'SELECT MAX(dsh_order) AS dsh_order
               FROM dashboard AS dsh
               WHERE dsh_ss_id =' . $this->User->getSsId() . ' AND dsh_us_id =' . $this->User->getId();
        $sqlResult = DB::select($query);
        if (count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0]);
            $orderNumber = $result['dsh_order'] + 1;
        }

        return $orderNumber;
    }

}
