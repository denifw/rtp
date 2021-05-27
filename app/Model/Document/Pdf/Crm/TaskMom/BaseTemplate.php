<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Document\Pdf\Crm\TaskMom;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\TablePdf;
use App\Model\Dao\Crm\TaskDao;
use App\Model\Dao\Crm\TaskParticipantDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 *
 *
 * @package    app
 * @subpackage Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class BaseTemplate extends AbstractBasePdf
{
    /**
     * @var array
     */
    protected $Data = [];

    /**
     * @var array
     */
    protected $Participant = [];

    public function __construct()
    {
        parent::__construct(Trans::getCrmWord('minuteofMeeting') . '.pdf');
    }

    /**
     * Function load Content
     */
    public function loadContent(): void
    {
        # Get data
        $this->loadData();
        try {
            $this->MPdf->SetHeader();
            $header = $this->getDefaultHeader($this->User->getRelId());
            $footer = $this->getDefaultFooter();
            $topMargin = (int)$this->MPdf->_getHtmlHeight($header) + 8;
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, $topMargin, 5, 5, 5);
            $this->MPdf->SetHTMLHeader($header, '', true);
            $this->MPdf->SetHTMLHeader($header, 'E', true);
            $this->MPdf->SetHTMLFooter($footer);
            $this->MPdf->SetHTMLFooter($footer, 'E');
            $this->MPdf->WriteHTML($this->createDocumentTitle(Trans::getCrmWord('minuteofMeeting')));
            # Get general information
            $this->MPdf->WriteHTML($this->getGeneralInformation());
            # Get participant
            if ($this->Participant !== null) {
                $this->MPdf->WriteHTML($this->getParticipantView());
            }
            # Get agenda
            $this->MPdf->WriteHTML($this->getAgendaView());
            # Get result
            $this->MPdf->WriteHTML($this->getResultView());
            # Get next step
            $this->MPdf->WriteHTML($this->getNextStepView());
            # Get created by
            $this->MPdf->WriteHTML($this->getSignatureView());
        } catch (Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function General Information
     *
     * @return string
     */
    private function getGeneralInformation(): string
    {
        $result = '<table class="table-info" style="font-weight: bold; width: 100%; ">';
        $result .= '<tr>';
        $result .= '<td>';
        $result .= $this->getGeneralView();
        $result .= '</td>';
        $result .= '<td >';
        $result .= $this->getDetailView();
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';
        return $result;
    }

    /**
     * Function get createdBy
     *
     * @return string
     */
    private function getSignatureView(): string
    {
        $data = [
            [
                'label' => Trans::getWord('createdBy'),
                'name' => $this->Data['tsk_created_by'],
            ],
        ];
        $label = '';
        $temp = RelationDao::loadDataForDocumentHeader($this->User->getRelId());
        $label .= $temp['stt_name'] . ', ';
        $label .= date('d M Y');
        $result = '<p class="pdf-date-label" style="font-weight: bold">' . $label . '</p>';
        $result .= '<table style="width:100%;">';
        $result .= '<tr>';
        $result .= '<td style="width:33%;">';
        $result .= '</td>';
        $result .= '<td style="width:33%;">';
        $result .= '</td>';
        $result .= '<td style="width:33%;">';
        $result .= $this->generateSignatureView($data);
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '</table>';
        return $result;
    }

    /**
     * Function General View
     *
     * @return string
     */
    private function getGeneralView(): string
    {
        $data = [
            [
                'label' => Trans::getCrmWord('subject'),
                'value' => $this->Data['tsk_subject'],
            ],
            [
                'label' => Trans::getCrmWord('taskType'),
                'value' => $this->Data['tsk_type_name'],
            ],
            [
                'label' => Trans::getCrmWord('location'),
                'value' => $this->Data['tsk_location'],
            ],
        ];
        return $this->createTableView($data, false);
    }

    /**
     * Function getDetail View
     *
     * @return string
     */
    private function getDetailView(): string
    {
        $startDateTime = '';
        if (empty($this->Data['tsk_start_date']) === false) {
            if (empty($this->Data['tsk_start_time']) === false) {
                $startDateTime = DateTimeParser::format($this->Data['tsk_start_date'] . ' ' . $this->Data['tsk_start_time'], 'Y-m-d H:i:s', 'd M Y H:i');
            } else {
                $startDateTime = DateTimeParser::format($this->Data['tsk_start_date'], 'Y-m-d', 'd M Y');
            }
        }
        $endDateTime = '';
        if (empty($this->Data['tsk_end_date']) === false) {
            if (empty($this->Data['tsk_end_time']) === false) {
                $endDateTime = DateTimeParser::format($this->Data['tsk_end_date'] . ' ' . $this->Data['tsk_end_time'], 'Y-m-d H:i:s', 'd M Y H:i');
            } else {
                $endDateTime = DateTimeParser::format($this->Data['tsk_end_date'], 'Y-m-d', 'd M Y');
            }
        }
        $data = [
            [
                'label' => Trans::getCrmWord('startDate'),
                'value' => $startDateTime,
            ],
            [
                'label' => Trans::getCrmWord('endDate'),
                'value' => $endDateTime,
            ],

        ];
        return $this->createTableView($data, false);
    }

    /**
     * Function Participant View
     *
     * @return string
     */
    private function getParticipantView(): string
    {
        # Set pariticipant table
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getCrmWord('participant') . '</p>';
        $tbl = new TablePdf('partTbl');
        $tbl->setHeaderRow([
            'tp_cp_name' => Trans::getCrmWord('name'),
            'tp_rel_name' => Trans::getCrmWord('relation'),
            'tp_cp_email' => Trans::getCrmWord('email'),
        ]);
        $tbl->addRows($this->Participant);
        $result .= $tbl->createTable();
        return $result;
    }

    /**
     * Function Agenda View
     *
     * @return string
     */
    private function getAgendaView(): string
    {
        # Set table agenda/description
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getCrmWord('agenda') . '</p>';
        $tbl = new TablePdf('agTbl');
        $tbl->setHeaderRow([
            'tsk_description' => Trans::getCrmWord('agenda'),
        ]);
        $data['tsk_description'] = StringFormatter::replaceNewLineToBr($this->Data['tsk_description']);
        $tbl->addRow($data);
        $tbl->setDisableLineNumber();
        $result .= $tbl->createTable();
        return $result;
    }

    /**
     * Function Result View
     *
     * @return string
     */
    private function getResultView(): string
    {
        # Set table result
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getCrmWord('result') . '</p>';
        $tbl = new TablePdf('restTbl');
        $tbl->setHeaderRow([
            'tsk_result' => Trans::getCrmWord('result'),
        ]);
        $data['tsk_result'] = StringFormatter::replaceNewLineToBr($this->Data['tsk_result']);
        $tbl->addRow($data);
        $tbl->setDisableLineNumber();
        $result .= $tbl->createTable();
        return $result;
    }

    /**
     * Function next step
     *
     * @return string
     */
    private function getNextStepView(): string
    {
        # Set table next step
        $result = '';
        $result .= '<p class="title-4" style="font-weight: bold"> ' . Trans::getCrmWord('nextStep') . '</p>';
        $tbl = new TablePdf('nextStepTbl');
        $tbl->setHeaderRow([
            'tsk_next_step' => Trans::getCrmWord('nextStep'),
        ]);
        $data['tsk_next_step'] = StringFormatter::replaceNewLineToBr($this->Data['tsk_next_step']);
        $tbl->addRow($data);
        $tbl->setDisableLineNumber();
        $result .= $tbl->createTable();
        return $result;
    }

    /**
     * @return string
     */
    public function loadHtmlContent(): string
    {
        return '';
    }

    /**
     * Function load data
     *
     * @return void
     */
    private function loadData(): void
    {
        if ($this->isValidParameter('tsk_id') === false) {
            Message::throwMessage('Invalid parameter for tsk_id.');
        } else {
            # Get data from dao
            $this->Data = TaskDao::getByReferenceAndSystem($this->getIntParameter('tsk_id'), $this->User->getSsId());
            $wheres[] = SqlHelper::generateNumericCondition('tp_tsk_id', $this->getIntParameter('tsk_id'));
            $wheres[] = '(tp_deleted_on IS NULL)';
            $this->Participant = TaskParticipantDao::loadData($wheres);
        }
    }
}
