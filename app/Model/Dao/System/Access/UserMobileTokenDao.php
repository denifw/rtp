<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Exceptions\Message;
use App\Frame\Mvc\AbstractBaseDao;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Class to handle data access object for table user_mobile_token.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserMobileTokenDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'umt_id',
        'umt_us_id',
        'umt_api_token',
    ];

    /**
     * Base dao constructor for user_mobile_token.
     *
     */
    public function __construct()
    {
        parent::__construct('user_mobile_token', 'umt', self::$Fields);
    }

    /**
     * Abstract function to load the data.
     *
     * @param int $usId To store the primary key value.
     *
     * @return void
     */
    public function doDeleteTransactionByUsId($usId): void
    {
        DB::table($this->table)
            ->where('umt_us_id', $usId)
            ->update([
                $this->TablePrefix . '_deleted_on' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Abstract function to do insert transaction.
     *
     * @param array $fieldData To store the field value per column.
     *
     * @return void
     */
    public function doInsertTransaction(array $fieldData): void
    {
        $this->Incremental++;
        $uidKey = time() . $this->TablePrefix . $fieldData['umt_us_id'] . $this->Incremental;
        $fieldData[$this->TablePrefix . '_uid'] = Uuid::uuid3(Uuid::NAMESPACE_URL, $uidKey);
        $this->LastInsertId = DB::table($this->table)->insertGetId($fieldData, $this->primaryKey);
    }

    /**
     * Abstract function to load the data.
     *
     * @param int $usId To store the primary key value.
     *
     * @return string
     */
    public function doManageUserToken($usId): string
    {
        DB::beginTransaction();
        try {
            $token = md5(time() . $usId) . $usId;
            $this->doDeleteTransactionByUsId($usId);
            $colVal = [
                'umt_us_id' => $usId,
                'umt_api_token' => $token,
                'umt_created_on' => date('Y-m-d H:i:s')
            ];
            $this->LastInsertId = DB::table($this->table)->insertGetId($colVal, $this->primaryKey);
            DB::commit();

            return $token;
        } catch (\Exception $e) {
            DB::rollBack();
            Message::throwMessage($e->getMessage(), 'ERROR');
        }

        return '';
    }


}
