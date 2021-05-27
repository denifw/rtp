<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of ServiceTermDocument.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ServiceTermDocument extends AbstractListingModel
{

    /**
     * ServiceTermDocument constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serviceTermDocument');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $serviceField = $this->Field->getSingleSelect('service', 'srv_name', $this->getStringParameter('srv_name'));
        $serviceField->setHiddenField('srv_id', $this->getIntParameter('srv_id'));
        $serviceField->addParameter('ssr_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceField->setEnableDetailButton(false);

        $termField = $this->Field->getSingleSelect('serviceTerm', 'srt_name', $this->getStringParameter('srt_name'));
        $termField->setHiddenField('srt_id', $this->getIntParameter('srt_id'));
        $termField->addOptionalParameterById('srt_srv_id', 'srv_id');
        $termField->addParameter('ssr_ss_id', $this->User->getSsId());
        $termField->setEnableNewButton(false);
        $termField->setEnableDetailButton(false);

        $this->ListingForm->addField(Trans::getWord('service'), $serviceField);
        $this->ListingForm->addField(Trans::getWord('serviceTerm'), $termField);
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
            'srv_name' => Trans::getWord('service'),
            'srt_name' => Trans::getWord('serviceTerm'),
            'dct_description' => Trans::getWord('documentType'),
            'std_general' => Trans::getWord('generalDocument'),
        ]);
        # Load the data for ServiceTermDocument.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['std_id']);
        }
        $this->ListingTable->setColumnType('std_general', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (std_id)) AS total_rows
                   FROM service_term_document as std INNER JOIN
                   service_term as srt ON std.std_srt_id = srt.srt_id INNER JOIN
                   service as srv ON srt.srt_srv_id = srv.srv_id INNER JOIN
                   document_type as dct ON std.std_dct_id = dct.dct_id';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT std.std_id, std.std_srt_id, srt.srt_name, srv.srv_name,
                        srv.srv_id, std.std_dct_id, dct.dct_code, dct.dct_description,
                        std.std_general, dct.dct_dcg_id
                   FROM service_term_document as std INNER JOIN
                   service_term as srt ON std.std_srt_id = srt.srt_id INNER JOIN
                   service as srv ON srt.srt_srv_id = srv.srv_id INNER JOIN
                   document_type as dct ON std.std_dct_id = dct.dct_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY std.std_id, std.std_srt_id, srt.srt_name, srv.srv_name,
                    srv.srv_id, std.std_dct_id, dct.dct_code, dct.dct_description,
                    std.std_general, dct.dct_dcg_id';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY srv.srv_name, srt.srt_name, std.std_id';
        }

        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(std.std_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(std.std_deleted_on IS NULL)';

        if ($this->isValidParameter('srv_id') === true) {
            $wheres[] = '(srv.srv_id = ' . $this->getIntParameter('srv_id') . ')';
        }
        if ($this->isValidParameter('srt_id') === true) {
            $wheres[] = '(srt.srt_id = ' . $this->getIntParameter('srt_id') . ')';
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
