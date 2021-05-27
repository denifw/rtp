<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Listing\Job\Inklaring;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\Inklaring\JobInklaringDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to manage the creation of the listing JobInklaring page.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class JobInklaring extends BaseJobOrder
{
    /**
     * JobInklaring constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jik', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'));
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        # Custom Document Type
        $cdtField = $this->Field->getSingleSelect('customsDocumentType', 'jik_cdt', $this->getStringParameter('jik_cdt'));
        $cdtField->setHiddenField('jik_cdt_id', $this->getIntParameter('jik_cdt_id'));
        $cdtField->setEnableDetailButton(false);
        $cdtField->setEnableNewButton(false);
        # Port of Loading
        $polField = $this->Field->getSingleSelect('port', 'jik_pol', $this->getStringParameter('jik_pol'));
        $polField->setHiddenField('jik_pol_id', $this->getIntParameter('jik_pol_id'));
        $polField->setEnableNewButton(false);
        # Port of Destination
        $podField = $this->Field->getSingleSelect('port', 'jik_pod', $this->getStringParameter('jik_pod'));
        $podField->setHiddenField('jik_pod_id', $this->getIntParameter('jik_pod_id'));
        $podField->setEnableNewButton(false);
        # Add field into field set.
        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('soNumber'), $this->Field->getText('so_number', $this->getStringParameter('so_number')));
        $this->ListingForm->addField(Trans::getWord('serviceTerm'), $this->getJobServiceTermField('inklaring'));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('customerRef'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $this->ListingForm->addField(Trans::getWord('documentType'), $cdtField);
        $this->ListingForm->addField(Trans::getWord('portOfLoading'), $polField);
        $this->ListingForm->addField(Trans::getWord('portOfDischarge'), $podField);
        $this->ListingForm->addField(Trans::getWord('status'), $this->getJobStatusField());
        $this->ListingForm->setGridDimension(4);
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'jo_number' => Trans::getWord('jobNumber'),
            'so_customer' => Trans::getWord('customer'),
            'so_customer_ref' => Trans::getWord('customerRef'),
            'so_document_type' => Trans::getWord('documentType'),
            'so_pol' => Trans::getWord('pol'),
            'so_pod' => Trans::getWord('pod'),
            'jik_closing_date' => Trans::getWord('closingTime'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
        }
        $this->ListingTable->setColumnType('jik_closing_date', 'datetime');
        $this->disableNewButton(true);

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobInklaringDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = JobInklaringDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $result = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            $departure = '';
            if (empty($row['so_departure_date']) === false) {
                if (empty($row['jik_departure_time']) === false) {
                    $departure = DateTimeParser::format($row['so_departure_date'] . ' ' . $row['so_departure_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $departure = DateTimeParser::format($row['so_departure_date'], 'Y-m-d', 'd M Y');
                }
            }
            $row['so_pol'] = StringFormatter::generateTableView([
                $row['so_pol'], $row['so_pol_country'], $departure
            ]);
            $arrival = '';
            if (empty($row['so_arrival_date']) === false) {
                if (empty($row['so_arrival_time']) === false) {
                    $arrival = DateTimeParser::format($row['so_arrival_date'] . ' ' . $row['so_arrival_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $arrival = DateTimeParser::format($row['so_arrival_date'], 'Y-m-d', 'd M Y');
                }
            }
            $row['jik_pod'] = StringFormatter::generateTableView([
                $row['so_pod'], $row['so_pod_country'], $arrival
            ]);
            $row['so_customer_ref'] = $joDao->concatReference($row, 'so');
            if (empty($row['jik_closing_date']) === false) {
                $row['jik_closing_date'] .= ' ' . $row['jik_closing_time'];
            }

            $row['jo_status'] = $joDao->generateStatus([
                'is_hold' => !empty($row['jo_joh_id']),
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_document' => !empty($row['jo_document_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jo_action_id'],
                'jae_style' => $row['jo_action_style'],
                'jac_action' => $row['jo_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = $this->getJoConditions();
        if ($this->isValidParameter('jik_cdt_id') === true) {
            $wheres[] = '(so.so_cdt_id = ' . $this->getIntParameter('jik_cdt_id') . ')';
        }
        # return the where query.
        return $wheres;
    }

}
