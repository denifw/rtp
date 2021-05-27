<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job;


use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use App\Frame\Mvc\AbstractViewerModel;
use App\Frame\System\Session\UserSession;
use App\Frame\System\View;
use App\Model\Dao\Job\JobActionDao;

abstract class AbstractBaseJobAction
{
    /**
     * Property to store the model object
     *
     * @var AbstractViewerModel $Model
     */
    protected $Model;

    /**
     * Property to store the next action job
     *
     * @var array $Action
     */
    protected $Action = [];

    /**
     * Property to store the user data.
     *
     * @var UserSession $User
     */
    protected $User;

    /**
     * BaseJobAction constructor.
     *
     * @param AbstractViewerModel $Model
     */
    public function __construct(AbstractViewerModel $Model)
    {
        $this->Model = $Model;
        $this->User = new UserSession();
    }

    /**
     * Function to add layout for action to view
     *
     * @param View $View
     *
     * @return void
     */
    abstract public function addActionView(View $View): void;


    /**
     * Function to check is there any next action.
     *
     * @return bool
     */
    public function hasNextAction(): bool
    {
        return !empty($this->Action);

    }

    /**
     * Function to load action.
     *
     * @return void
     */
    protected function loadAction(): void
    {
        $this->Action = JobActionDao::loadNextActionByJobId($this->Model->getDetailReferenceValue());

    }


    /**
     * Function to get document action modal.
     *
     * @param string $btnId       To store the id of the button.
     * @param string $modalId     To store the id of the modal.
     * @param string $btnIcon     To store the icon of the button.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Button
     */
    protected function getDefaultButton($btnId, $modalId, $btnIcon, $actionIndex = ''): Button
    {
        $btnLabel = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.button' . $actionIndex, 'action');
        $btn = new ModalButton($btnId, $btnLabel, $modalId);
        $style = 'btn btn-primary pull-right btn-sm';
        if (empty($this->Action['jac_style']) === false) {
            $style = 'btn btn-' . $this->Action['jac_style'] . ' pull-right btn-sm';
        }
        $btn->addAttribute('class', $style);
        $btn->setIcon($btnIcon);

        return $btn;
    }


    /**
     * Function to get document action modal.
     *
     * @param string $modalId     To store the id of the modal.
     * @param string $modalSubmit To store the id of the modal.
     * @param string $actionIndex To store the id of the modal.
     *
     * @return Modal
     */
    protected function getDefaultModal($modalId, $modalSubmit, $actionIndex = ''): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getWord('actionConfirmation' ));
        $modal->setFormSubmit($this->Model->getMainFormId(), $modalSubmit);
        if ($this->Model->getFormAction() === $modalSubmit && $this->Model->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            if ($this->Model->isValidParameter('jac_date') === false) {
                $this->Model->setParameter('jac_date', date('Y-m-d'));
            }
            if ($this->Model->isValidParameter('jac_time') === false) {
                $this->Model->setParameter('jac_time', date('H:i'));
            }

        }
        # Create Field set
        $fieldSet = new FieldSet($this->Model->getValidation());
        $fieldSet->setGridDimension(12);
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_id', $this->Action['jac_id']));

        $fieldSet->addField(Trans::getWord('actualDate' ), $this->Model->getField()->getCalendar('jac_date', $this->Model->getParameterForModal('jac_date', true)), true);
        $fieldSet->addField(Trans::getWord('actualTime' ), $this->Model->getField()->getTime('jac_time', $this->Model->getParameterForModal('jac_time', true)), true);
        $fieldSet->addField(Trans::getWord('image' ), $this->Model->getField()->getFile('jac_image', ''));
        $event = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.event' . $actionIndex, 'action');
        $fieldSet->addHiddenField($this->Model->getField()->getHidden('action_event', $event));

        # Add content Modal.
        $message = Trans::getWord($this->Action['jac_action'] . $this->Action['jac_service_term'] . '.confirm' . $actionIndex, 'action');
        $p = new Paragraph($message);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('confirm'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get document action modal.
     *
     * @param string $modalId To store the id of the modal.
     * @param string $message To store the message of warning.
     *
     * @return Modal
     */
    protected function getWarningModal($modalId, $message): Modal
    {
        # Create Fields.
        $modal = new Modal($modalId, Trans::getWord('warning' ));
        $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        $p = new Paragraph($message);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setDisableBtnOk();

        return $modal;
    }


}
