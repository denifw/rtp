<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Document;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of DocumentGroup.
 *
 * @package    app
 * @subpackage Model\Listing\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Document extends AbstractListingModel
{

    /**
     * DocumentGroup constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'document');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Fields.
        $dcgFields = $this->Field->getSingleSelect('documentGroup', 'dcg_description', $this->getStringParameter('dcg_description'));
        $dcgFields->setHiddenField('dcg_id', $this->getIntParameter('dcg_id'));
        $dcgFields->setDetailReferenceCode('dcg_id');
        $dcgFields->setEnableNewButton(false);
        $dcgFields->setEnableDetailButton(false);

        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_description', $this->getStringParameter('dct_description'));
        $dctFields->setHiddenField('dct_id', $this->getIntParameter('dct_id'));
        $dctFields->addOptionalParameterById('dct_dcg_id', 'dcg_id');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('documentGroup'), $dcgFields);
        $this->ListingForm->addField(Trans::getWord('documentType'), $dctFields);
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getStringParameter('doc_description')));
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
            'dcg_description' => Trans::getWord('documentGroup'),
            'dct_description' => Trans::getWord('documentType'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'doc_action' => Trans::getWord('action')
        ]);
        # Load the data for DocumentGroup.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('doc_action', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (doc.doc_id)) AS total_rows
                   FROM document as doc INNER JOIN
                   document_type as dct ON dct.dct_id = doc.doc_dct_id INNER JOIN
                   document_group as dcg ON dcg.dcg_id = dct.dct_dcg_id INNER JOIN
                   users as us ON doc.doc_created_by = us.us_id';
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
        $query = 'SELECT doc.doc_id, doc.doc_description, dct.dct_description, dcg.dcg_description, doc.doc_created_on, us.us_name as doc_creator
                        FROM document as doc INNER JOIN
                   document_type as dct ON dct.dct_id = doc.doc_dct_id INNER JOIN
                   document_group as dcg ON dcg.dcg_id = dct.dct_dcg_id INNER JOIN
                   users as us ON doc.doc_created_by = us.us_id ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY doc.doc_id, doc.doc_description, dct.dct_description, dcg.dcg_description, doc.doc_created_on, us.us_name';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY dcg.dcg_description, dct.dct_description, doc.doc_description, doc.doc_id';
        }

        $data = $this->loadDatabaseRow($query);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['doc_action'] = $btn;
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            $results[] = $row;
        }

        return $results;
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
        $wheres[] = '(doc.doc_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(doc.doc_deleted_on IS NULL )';

        if ($this->isValidParameter('dcg_id') === true) {
            $wheres[] = '(dcg.dcg_id = ' . $this->getIntParameter('dcg_id') . ')';
        }

        if ($this->isValidParameter('dct_id') === true) {
            $wheres[] = '(dct.dct_id = ' . $this->getIntParameter('dct_id') . ')';
        }

        if ($this->isValidParameter('doc_description') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('doc_description', $this->getStringParameter('doc_description'));
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
