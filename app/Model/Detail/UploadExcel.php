<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2023 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail;

use App\Frame\Document\ParseExcel;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\RtPintarDao;

/**
 * Class to handle the creation of detail UploadExcel page
 *
 * @package    app
 * @subpackage Model\Detail\
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2023 Deni Firdaus Waruwu.
 */
class UploadExcel extends AbstractFormModel
{

    private $month = [
        'Januari' => 1,
        'Februari' => 2,
        'Maret' => 3,
        'April' => 4,
        'Mei' => 5,
        'Juni' => 6,
        'Juli' => 7,
        'Agustus' => 8,
        'September' => 9,
        'Oktober' => 10,
        'November' => 11,
        'Desember' => 12,
    ];

    private $unit = [
        'H10-H10-22' => [
            'block' => 'H',
            'unit' => 'H10-22'
        ],
        'BlokH9-H9-10' => [
            'block' => 'H',
            'unit' => 'H09-10'
        ]
    ];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'up', 'up_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $file = $this->getFileParameter('up_file');
        if ($file !== null) {
            $parser = new ParseExcel($this->getFileParameter('up_file'));
            $parser->doParseData('Worksheet');
            $data = $parser->getData();
            $rtpDao = new RtPintarDao();
            $rtpDao->clearData();
            foreach ($data as $row) {
                $unit = $this->getUnit($row);
                $date = $this->getMonthYear($row['keterangan']);
                $colVal = [];
                $colVal['rtp_code'] = $row['kodepembayaran'];
                $colVal['rtp_description'] = $row['keterangan'];
                $colVal['rtp_amount'] = $row['jumlah'];
                if (empty($date) === false) {
                    $colVal['rtp_month'] = $date['m'];
                    $colVal['rtp_year'] = (int)$date['y'];
                }
                $colVal['rtp_status_text'] = $row['statuspembayaran'];
                $colVal['rtp_status'] = $this->getStatus($row['statuspembayaran']);
                $colVal['rtp_payment_time'] = $row['tanggalbayar'];
                $colVal['rtp_contact'] = $row['iuranuntuk'];
                if (empty($unit) === false) {
                    $colVal['rtp_block'] = $unit['block'];
                    $colVal['rtp_number'] = $unit['unit'];
                }
                $rtpDao->doInsertTransaction($colVal);
            }
        }
        return '';
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param string $status
     * @return string
     */
    private function getStatus(string $status): string
    {
        if ($status === 'Sudah Dibayar') {
            return 'Y';
        }
        if ($status === 'Belum Dibayar') {
            return 'N';
        }
        return 'D';
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param string $data
     * @return array
     */
    private function getMonthYear(string $data): array
    {
        $list = explode('-', $data);
        $temp = trim($list[count($list) - 1]);
        $date = explode(' ', $temp);
        if (count($date) === 2) {
            return [
                'm' => $this->month[$date[0]],
                'y' => $date[1]
            ];
        }
        return [];
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param array $row
     * @return array
     */
    private function getUnit(array $row): array
    {
        $keyUnit = $row['blok'] . '-' . $row['norumah'];
        $keyUnit = str_replace(' ', '', $keyUnit);
        if (array_key_exists($keyUnit, $this->unit) === true) {
            return $this->unit[$keyUnit];
        }
        return [
            'block' => $row['blok'],
            'unit' => str_replace(' ', '', $row['norumah'])
        ];
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return [];
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('up_file');
            $this->Validation->checkFile('up_file');
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('UpGnlPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);

        $fieldSet->addField('Upload File', $this->Field->getFile('up_file', ''));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
