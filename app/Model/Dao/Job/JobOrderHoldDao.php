<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\Job;

use App\Frame\Mvc\AbstractBaseDao;

/**
 * Class to handle data access object for table job_sales.
 *
 * @package    app
 * @subpackage Model\Dao\Job
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOrderHoldDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'joh_id',
        'joh_jo_id',
        'joh_reason',
    ];

    /**
     * Base dao constructor for job_sales.
     *
     */
    public function __construct()
    {
        parent::__construct('job_order_hold', 'joh', self::$Fields);
    }

    /**
     * Abstract function to load the seeder query for table job_sales.
     *
     * @return array
     */
    public function loadSeeder(): array
    {
        return $this->generateSeeder([
            'joh_reason',
        ]);
    }


    /**
     * function to get all available fields
     *
     * @return array
     */
    public static function getFields(): array
    {
        return self::$Fields;
    }
}
