<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Setting\Action;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of SystemAction.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemAction extends AbstractListingModel
{

    /**
     * SystemAction constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemAction');
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
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('ac_description', $this->getStringParameter('ac_description')));
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
            'ac_description' => Trans::getWord('description'),
            'sac_order' => Trans::getWord('orderNumber'),
            'total_event' => Trans::getWord('numberOfEvent'),
        ]);
        # Load the data for SystemAction.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['sac_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['sac_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sac_id']);
        }
        $this->ListingTable->setColumnType('total_event', 'integer');
        $this->ListingTable->setColumnType('sac_order', 'integer');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (sac_id)) AS total_rows
                   FROM system_action as sac INNER JOIN
                   action as ac ON sac.sac_ac_id = ac.ac_id INNER JOIN
                   service_term as srt ON ac.ac_srt_id = srt.srt_id INNER JOIN
                   service as srv ON srt.srt_srv_id = srv.srv_id';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @param array $outFields To store the out field from selection data.
     *
     * @return array
     */
    private function loadData(array $outFields): array
    {
        # Set Select query;
        $query = "SELECT sac.sac_id, srv.srv_id, srv.srv_name, srt.srt_id, srt.srt_name, ac.ac_id, ac.ac_description,
                      (CASE WHEN (sae.total IS NULL) THEN 0 ELSE sae.total END) as total_event, sac.sac_order
                   FROM system_action as sac INNER JOIN
                   action as ac ON sac.sac_ac_id = ac.ac_id INNER JOIN
                   service_term as srt ON ac.ac_srt_id = srt.srt_id INNER JOIN
                   service as srv ON srt.srt_srv_id = srv.srv_id LEFT OUTER JOIN
                   (SELECT sae_sac_id, count(sae_id) as total
                      FROM system_action_event
                      WHERE (sae_deleted_on IS NULL) AND (sae_active = 'Y')
                      GROUP BY sae_sac_id) as sae ON sac.sac_id = sae.sae_sac_id ";
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY sac.sac_id, srv.srv_id, srv.srv_name, srt.srt_id, srt.srt_name, ac.ac_id, ac.ac_description,sae.total';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY srv.srv_name, srt.srt_name, sac.sac_order';
        }

        return $this->loadDatabaseRow($query, $outFields);
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
        $wheres[] = '(sac.sac_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(sac.sac_deleted_on IS NULL)';
        if ($this->isValidParameter('ac_description') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('ac.ac_description', $this->getStringParameter('ac_description'));
        }
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
