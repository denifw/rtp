<?php


namespace App\Model\Statistic\Job\Inklaring;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractStatisticModel;


class JobReport extends AbstractStatisticModel
{
    /**
     * JobReport constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'jikReport');
        $this->setParameters($parameters);
    }

    /**
     * Function loadSearchForm
     */
    public function loadSearchForm(): void
    {
        $relField = $this->Field->getSingleSelect('relation', 'jik_relation', $this->getStringParameter('jik_relation'));
        $relField->setHiddenField('jik_rel_id', $this->getIntParameter('jik_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $this->StatisticForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jik_number', $this->getStringParameter('jik_number')));
        $this->StatisticForm->addField(Trans::getWord('salesOrder'), $this->Field->getText('jik_so_number', $this->getStringParameter('jik_so_number')));
        $this->StatisticForm->addField(Trans::getWord('customer'), $relField);
        $this->StatisticForm->addField(Trans::getWord('reference'), $this->Field->getText('jik_reference', $this->getStringParameter('jik_reference')));
        $this->StatisticForm->addField(Trans::getWord('startFrom'), $this->Field->getCalendar('jik_order_date_from', $this->getStringParameter('jik_order_date_from')), false);
        $this->StatisticForm->addField(Trans::getWord('until'), $this->Field->getCalendar('jik_order_date_until', $this->getStringParameter('jik_order_date_until')), false);
        $this->StatisticForm->setGridDimension(4, 4, 4);
    }

    /**
     * Function load view
     */
    public function loadViews(): void
    {
        $portlet = $this->getInklaringTable();
        $this->addContent('Content1', $portlet);
    }

    /**
     *  Function load data
     */
    private function loadData(): array
    {
        $wheres [] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('jik_relation') === true) {
            $wheres [] = SqlHelper::generateNumericCondition('rel.rel_id', $this->getStringParameter('jik_rel_id'));
        }
        if ($this->isValidParameter('jik_so_number')) {
            $wheres [] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('jik_so_number'));
        }
        if ($this->isValidParameter('jik_number')) {
            $wheres [] = SqlHelper::generateLikeCondition('jo.jo_number ', $this->getStringParameter('jik_number'));
        }
        if ($this->isValidParameter('jik_order_date_from')) {
            $wheres [] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('jik_order_date_from'), '>=');
        }
        if ($this->isValidParameter('jik_reference')) {
            $wheres[] = SqlHelper::generateOrLikeCondition([
                'so.so_bl_ref',
                'so.so_aju_ref',
                'so.so_sppb_ref',
            ], $this->getStringParameter('jik_reference'));
        }
        if ($this->isValidParameter('jik_order_date_until')) {
            $wheres [] = SqlHelper::generateStringCondition('so.so_order_date', $this->getStringParameter('jik_order_date_until'), '<=');
        }
        $wheres [] = '(jo.jo_deleted_on is null )';
        $wheres [] = '(so.so_deleted_on is null )';
        $query = $this->getJobInklaring($wheres);

        return $this->loadDatabaseRow($query);
    }

    /**
     * Function getJobInklaring
     *
     * @param array $wheres
     *
     * @return string
     */
    private function getJobInklaring(array $wheres): string
    {
        $strWhereJik = ' WHERE ' . implode(' and ', $wheres);
        $query = 'select so.so_order_date as jik_order_date, jo.jo_number as jik_number, so.so_aju_ref as jik_aju_ref, so.so_bl_ref as jik_bl_ref, rel.rel_id as jik_rel_id, rel.rel_name as jik_customer,so.so_number as jik_so_number,
        so.so_arrival_date as jik_ata, soc.soc_id as jik_party, soc.soc_ct_id as jik_soc_ct_id, ct.ct_code as jik_ct_code, cdt.cdt_name as jik_document_type, cct.cct_name as jik_line, so.so_sppb_ref as jik_sppb_ref,
        jik.jik_approve_pabean_on, jik.jik_id, so.so_id as jik_so_id, so.so_plb as jik_so_status, so.so_container as jik_container
        from job_inklaring as jik
        inner join job_order jo on jik.jik_jo_id = jo.jo_id
        inner join sales_order so on jik.jik_so_id = so.so_id
        inner join relation rel on so.so_rel_id=rel.rel_id
        left outer join sales_order_container soc on  soc.soc_so_id = so.so_id
        left outer join container as ct on soc.soc_ct_id= ct.ct_id
        left outer join customs_document_type cdt on  cdt.cdt_id = so.so_cdt_id
        left outer join customs_clearance_type cct on so.so_cct_id = cct.cct_id' . $strWhereJik;
        $query .= "order by so.so_order_date DESC, so.so_id, soc.soc_ct_id, so.so_bl_ref, soc.soc_id, jik.jik_id";

        return $query;
    }

    /**
     * Function getInklaringTable
     *
     * @return Portlet
     */
    protected function getInklaringTable(): Portlet
    {
        $table = new TableDatas('inkTbl');
        $table->setHeaderRow([
            'jik_order_date' => Trans::getWord('receiveData'),
            'jik_number' => Trans::getWord('joNumber'),
            'jik_aju_ref' => Trans::getWord('ajuRef'),
            'jik_bl_ref' => Trans::getWord('bl'),
            'jik_customer' => Trans::getWord('customer'),
            'jik_ata' => Trans::getWord('ataDate'),
            'jik_combine' => Trans::getWord('party'),
            'jik_document_type' => Trans::getWord('documentType'),
            'jik_line' => Trans::getWord('lineStatus'),
            'jik_so_status' => Trans::getWord('status'),
            'jik_approve_pabean_on' => Trans::getWord('date')
        ]);
        $table->setRowsPerPage('50');
        $table->addColumnAttribute('jik_combine', 'style', 'text-align:center;');
        $table->addColumnAttribute('jik_document_type', 'style', 'text-align:center;');
        $table->addColumnAttribute('jik_so_status', 'style', 'text-align:center;');
        $dataPrepare = $this->doPrepareData();
        $table->addRows($dataPrepare);
        $portlet = new Portlet('InklaringPtl', Trans::getWord('inklaring'));
        $portlet->addTable($table);
        $this->addDatas('jobReport', $portlet);

        return $portlet;
    }

    /**
     * Function prepare data
     *
     * @return array
     */
    private function doPrepareData(): array
    {
        $data = $this->loadData();
        $results = [];
        $tempJikNumber = [];
        $tempJikCode = [];
        $partyCombine = [];
        $dt = new DateTimeParser();
        foreach ($data as $row) {
            $jikNumber = trim($row['jik_number'] . '-' . $row['jik_so_number'] . '-' . $row['jik_bl_ref']);
            $jikCode = $jikNumber . '-' . $row['jik_ct_code'];
            if (in_array($jikNumber, $tempJikNumber, true) === false) {
                $tempJikNumber[] = $jikNumber;
                $tempJikCode[] = $jikCode;
                $tempJikCode[$jikCode]['party'] = 1;
                $row['jik_order_date'] = $dt->formatDate($row['jik_order_date'], 'Y-m-d', 'd M Y');
                $row['jik_ata'] = $dt->formatDate($row['jik_ata'], 'Y-m-d', 'd M Y');
                $row['jik_approve_pabean_on'] = $dt->formatDate($row['jik_approve_pabean_on'], 'Y-m-d H:i:s', 'd M Y');
                $row['jik_so_status'] = $row['jik_so_status'] !== 'Y' ? 'SPPB' : 'SPPD';
                $partyText = 'LCL';
                if (empty($row['jik_party']) === false && $row['jik_container'] === 'Y') {
                    $partyText = '1x' . $row['jik_ct_code'];
                }
                $partyCombine[$jikNumber][$jikCode] = $partyText;
                $row['jik_combine'] = $partyText;
                $results[] = $row;
            } else {
                $index = array_search($jikNumber, $tempJikNumber, true);
                if (in_array($jikCode, $tempJikCode, true) === false) {
                    $tempJikCode[] = $jikCode;
                    $tempJikCode[$jikCode]['party'] = 1;
                    $partyCombine[$jikNumber][$jikCode] = '1x' . $row['jik_ct_code'];
                } else {
                    $tempJikCode[$jikCode]['party']++;
                    $partyCombine[$jikNumber][$jikCode] = $tempJikCode[$jikCode]['party'] . 'x' . $row['jik_ct_code'];
                }
                $results[$index]['jik_combine'] = implode(',', $partyCombine[$jikNumber]);
            }
        }

        return $results;
    }

}



