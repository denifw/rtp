<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\wlog\Detail\Relation;


use App\Model\Dao\Setting\SerialNumberDao;
use App\Model\Dao\System\SerialCodeDao;
use App\Model\Dao\System\Service\ServiceDao;
use App\Model\Dao\System\Service\ServiceTermDao;

/**
 * Class to handle the creation of detail Relation page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Relation extends \App\Model\Detail\Relation\Relation
{


    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doGenerateSerialJob') {
            $srvId = $this->getIntParameter('srv_id');
            $service = ServiceDao::getByReference($srvId);
            if (array_key_exists('srv_code', $service) && $service['srv_code'] === 'warehouse') {
                $sc = SerialCodeDao::getBycode('JobOrder');
                $serviceTerms = ServiceTermDao::getIdByService($srvId);
                $alias = $this->getStringParameter('rel_short_name');
                $separator = '-';
                $srt = [
                    [
                        'srt_id' => $serviceTerms['joWhInbound'],
                        'srt_prefix' => 'IN' . $separator . $alias,

                    ],
                    [
                        'srt_id' => $serviceTerms['joWhOutbound'],
                        'srt_prefix' => 'OUT' . $separator . $alias,

                    ],
                    [
                        'srt_id' => $serviceTerms['joWhOpname'],
                        'srt_prefix' => 'OP' . $separator . $alias,

                    ],
                    [
                        'srt_id' => $serviceTerms['joWhStockAdjustment'],
                        'srt_prefix' => 'ADJ' . $separator . $alias,
                    ],

                ];
                $snDao = new SerialNumberDao();
                foreach ($srt as $row) {
                    $colVal = [
                        'sn_ss_id' => $this->User->getSsId(),
                        'sn_sc_id' => $sc['sc_id'],
                        'sn_rel_id' => $this->getDetailReferenceValue(),
                        'sn_srv_id' => $srvId,
                        'sn_srt_id' => $row['srt_id'],
                        'sn_prefix' => $row['srt_prefix'],
                        'sn_separator' => $separator,
                        'sn_postfix' => '',
                        'sn_yearly' => 'Y',
                        'sn_monthly' => 'Y',
                        'sn_length' => 5,
                        'sn_increment' => 1,
                        'sn_active' => 'Y',
                    ];
                    $snDao->doInsertTransaction($colVal);
                }

            }
        } elseif ($this->getFormAction() === 'doGenerateSerialSo') {
            $sc = SerialCodeDao::getBycode('SalesOrder');
            $snDao = new SerialNumberDao();
            $colVal = [
                'sn_ss_id' => $this->User->getSsId(),
                'sn_sc_id' => $sc['sc_id'],
                'sn_rel_id' => $this->getDetailReferenceValue(),
                'sn_prefix' => 'SO-' . $this->getStringParameter('rel_short_name'),
                'sn_separator' => '-',
                'sn_postfix' => '',
                'sn_yearly' => 'Y',
                'sn_monthly' => 'Y',
                'sn_length' => 5,
                'sn_increment' => 1,
                'sn_active' => 'Y',
            ];
            $snDao->doInsertTransaction($colVal);
        } else {
            parent::doUpdate();
        }
    }

}
