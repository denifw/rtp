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
                $noBlock = str_replace(' ', '', mb_strtolower($row['blok'] . '-' . $row['norumah']));
                $numberList = explode('-', $noBlock);
                $number = '';
                $count = count($numberList);
                if ($count > 0) {
                    $number = $numberList[($count - 1)];
                }
                $order = 0;
                $numberUnit = '';
                if (strpos($noBlock, 'h9') !== false || strpos($noBlock, 'h09') !== false){
                    $numberUnit = 'H09-'.mb_strtoupper($number);
                    $order = 1;
                } else if(strpos($noBlock, 'h10') !== false) {
                    $numberUnit = 'H10-'.mb_strtoupper($number);
                    $order = 2;
                } else if(strpos($noBlock, 'h11') !== false) {
                    $numberUnit = 'H11-'.mb_strtoupper($number);
                    $order = 3;
                }

                $period = $this->getMonthYear($row['keterangan']);
                $month = null;
                $year = null;
                if(empty($period) === false) {
                    $month = $period['m'];
                    $year = (int)$period['y'];
                }
                $colVal = [
                    'rtp_code'=>$row['kodepembayaran'],
                    'rtp_system_unit' => mb_strtoupper($noBlock),
                    'rtp_unit' => $numberUnit,
                    'rtp_order' => $order,
                    'rtp_month' => $month,
                    'rtp_year' => $year,
                    'rtp_pic' => $row['iuranuntuk'],
                    'rtp_amount' => (float)$row['jumlah'],
                    'rtp_paid' => $this->getPaid($row['statuspembayaran']),
                    'rtp_canceled' => $this->getCanceled($row['statuspembayaran']),
                    'rtp_payment_type' => $this->getType($row['pembayaranvia']),
                ];
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
    private function getCanceled(string $status): string
    {
        if ($status === 'Dibatalkan') {
            return 'Y';
        }
        return 'N';
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param string $status
     * @return string
     */
    private function getPaid(string $status): string
    {
        if ($status === 'Sudah Dibayar') {
            return 'Y';
        }
        return 'N';
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param ?string $status
     * @return string
     */
    private function getType(string $status): ?string
    {
        if (empty($status) === true) {
            return null;
        }
        if ($status === 'Manual') {
            return 'M';
        } else {
            return 'A';
        }
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
