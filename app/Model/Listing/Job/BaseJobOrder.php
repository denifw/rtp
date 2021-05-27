<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Job;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Fields\Select;
use App\Frame\Gui\Html\Fields\SingleSelect;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of JobOrder.
 *
 * @package    app
 * @subpackage Model\Listing\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
abstract class BaseJobOrder extends AbstractListingModel
{

    /**
     * JobOrder constructor that will be called when we initiate the class.
     *
     * @param string $nameSpace to store the name space of the child class.
     * @param string $route to store the route of the child class.
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(string $nameSpace, string $route, array $parameters)
    {
        # Call parent construct.
        parent::__construct($nameSpace, $route);
        $this->setParameters($parameters);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    protected function getDefaultOrderBy(): string
    {
        return ' ORDER BY jo.jo_deleted_on DESC, jo.jo_finish_on DESC, jo.jo_id DESC';
    }

    /**
     * Function to job status field.
     *
     * @return Select
     */
    protected function getJobStatusField(): Select
    {
        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');
        $statusField->addOption(Trans::getWord('hold'), '6');

        return $statusField;
    }

    /**
     * Function to job service term field.
     *
     * @param string $serviceCode To store the id of service
     *
     * @return SingleSelect
     */
    protected function getJobServiceTermField(string $serviceCode): SingleSelect
    {
        $field = $this->Field->getSingleSelect('serviceTerm', 'jo_service_term', $this->getStringParameter('jo_service_term'));
        $field->setHiddenField('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $field->addParameter('ssr_ss_id', $this->User->getSsId());
        if (empty($serviceCode) === false) {
            $field->addParameter('srv_code', $serviceCode);
        }
        $field->setEnableNewButton(false);
        $field->setEnableDetailButton(false);
        return $field;
    }

    /**
     * Function to get the where condition.
     *
     * @param bool $soExists To trigger if so exist or not.
     * @return array
     */
    protected function getJoConditions(bool $soExists = true): array
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('jo.jo_ss_id', $this->User->getSsId());
        if ($this->isValidParameter('jo_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_number'));
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_srt_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_srt_id', $this->getIntParameter('jo_srt_id'));
        }
        if ($this->isValidParameter('jo_reference') === true) {
            # Check so wheres
            $orWheres = [];
            if ($soExists === true) {
                $soWheres = [];
                $soWheres[] = '(so_ss_id = ' . $this->User->getSsId() . ')';
                $soOrWheres = [];
                $soOrWheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('jo_reference'));
                $soOrWheres[] = SqlHelper::generateLikeCondition('so.so_customer_ref', $this->getStringParameter('jo_reference'));
                $soOrWheres[] = SqlHelper::generateLikeCondition('so.so_bl_ref', $this->getStringParameter('jo_reference'));
                $soOrWheres[] = SqlHelper::generateLikeCondition('so.so_packing_ref', $this->getStringParameter('jo_reference'));
                $soOrWheres[] = SqlHelper::generateLikeCondition('so.so_aju_ref', $this->getStringParameter('jo_reference'));
                $soOrWheres[] = SqlHelper::generateLikeCondition('so.so_sppb_ref', $this->getStringParameter('jo_reference'));
                $soWheres[] = '(' . implode(' OR ', $soOrWheres) . ')';
                $strSoWhere = ' WHERE ' . implode(' AND ', $soWheres);

                $soWheres[] = '(so.so_id IN (SELECT so_id FROM sales_order ' . $strSoWhere . ' GROUP BY so_id))';
            }
            $orWheres[] = SqlHelper::generateLikeCondition('jo.jo_customer_ref', $this->getStringParameter('jo_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('jo.jo_bl_ref', $this->getStringParameter('jo_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('jo.jo_sppb_ref', $this->getStringParameter('jo_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('jo.jo_packing_ref', $this->getStringParameter('jo_reference'));
            $orWheres[] = SqlHelper::generateLikeCondition('jo.jo_aju_ref', $this->getStringParameter('jo_reference'));
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';

        }
        if ($this->isValidParameter('jo_status') === true) {
            $status = $this->getIntParameter('jo_status');
            if ($status === 1) {
                $wheres[] = '(jo.jo_start_on IS NULL)';
                $wheres[] = '(jo.jo_publish_on IS NULL)';
                $wheres[] = '(jo.jo_deleted_on IS NULL)';
            } elseif ($status === 2) {
                $wheres[] = '(jo.jo_start_on IS NULL)';
                $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
                $wheres[] = '(jo.jo_deleted_on IS NULL)';
            } elseif ($status === 3) {
                $wheres[] = '(jo.jo_start_on IS NOT NULL)';
                $wheres[] = '(jo.jo_finish_on IS NULL)';
                $wheres[] = '(jo.jo_deleted_on IS NULL)';
            } elseif ($status === 4) {
                $wheres[] = '(jo.jo_finish_on IS NOT NULL)';
                $wheres[] = '(jo.jo_deleted_on IS NULL)';
            } elseif ($status === 6) {
                $wheres[] = '(jo.jo_joh_id IS NOT NULL)';
                $wheres[] = '(jo.jo_deleted_on IS NULL)';
            } else {
                $wheres[] = '(jo.jo_deleted_on IS NOT NULL)';
            }
        }
        if ($this->isValidParameter('order_date_from') === true) {
            $operator = '=';
            if ($this->isValidParameter('order_date_until') === true) {
                $operator = '>=';
            }
            $wheres[] = SqlHelper::generateStringCondition('jo.jo_order_date', $this->getStringParameter('order_date_from'), $operator);
        }
        if ($this->isValidParameter('order_date_until') === true) {
            $operator = '=';
            if ($this->isValidParameter('order_date_from') === true) {
                $operator = '<=';
            }
            $wheres[] = SqlHelper::generateStringCondition('jo.jo_order_date', $this->getStringParameter('order_date_until'), $operator);
        }

        if ($this->isValidParameter('start_date_from') === true) {
            if ($this->isValidParameter('start_date_until') === true) {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_start_on', $this->getStringParameter('start_date_from') . ' 00:01:00', '>=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_start_on', $this->getStringParameter('start_date_from') . ' 00:01:00', '>=');
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_start_on', $this->getStringParameter('start_date_from') . ' 23:59:00', '<=');
            }
        }
        if ($this->isValidParameter('start_date_until') === true) {
            if ($this->isValidParameter('start_date_from') === true) {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_start_on', $this->getStringParameter('start_date_until') . ' 23:59:00', '<=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_start_on', $this->getStringParameter('start_date_until') . ' 00:01:00', '>=');
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_start_on', $this->getStringParameter('start_date_until') . ' 23:59:00', '<=');
            }
        }


        if ($this->isValidParameter('complete_date_from') === true) {
            if ($this->isValidParameter('complete_date_until') === true) {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_finish_on', $this->getStringParameter('complete_date_from') . ' 00:01:00', '>=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_finish_on', $this->getStringParameter('complete_date_from') . ' 00:01:00', '>=');
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_finish_on', $this->getStringParameter('complete_date_from') . ' 23:59:00', '<=');
            }
        }
        if ($this->isValidParameter('complete_date_until') === true) {
            if ($this->isValidParameter('complete_date_from') === true) {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_finish_on', $this->getStringParameter('complete_date_until') . ' 23:59:00', '<=');
            } else {
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_finish_on', $this->getStringParameter('complete_date_until') . ' 00:01:00', '>=');
                $wheres[] = SqlHelper::generateStringCondition('jo.jo_finish_on', $this->getStringParameter('complete_date_until') . ' 23:59:00', '<=');
            }
        }

        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_rel_id', $this->User->getRelId());
        }
        if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $wheres[] = '((jo.jo_manager_id = ' . $this->User->getId() . ') OR (jo.jo_id IN (SELECT joo_jo_id
                                        FROM job_officer
                                        WHERE (joo_us_id = ' . $this->User->getId() . ') AND (joo_deleted_on IS NULL)
                                        GROUP BY joo_jo_id)))';
        }
        if ($this->PageSetting->checkPageRight('AllowSeeAllOfficeJob') === false) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_order_of_id', $this->User->Relation->getOfficeId());
        }

        return $wheres;

    }
}
