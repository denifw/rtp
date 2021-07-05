<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalog
 * @author    Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright 2019 MataLOG
 */

namespace App\Model\Dao\System\Access;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseDao;
use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Class to handle data access object for table user_token.
 *
 * @package    app
 * @subpackage Model\Dao\User
 * @author     Deni Firdaus Waruwu <deni.fw@gmail.com>
 * @copyright  2019 MataLOG
 */
class UserTokenDao extends AbstractBaseDao
{
    /**
     * The field for the table.
     *
     * @var array
     */
    private static $Fields = [
        'ut_id',
        'ut_token',
        'ut_type',
        'ut_us_id',
        'ut_ss_id',
        'ut_active',
    ];

    /**
     * Base dao constructor for user_token.
     *
     */
    public function __construct()
    {
        parent::__construct('user_token', 'ut', self::$Fields);
    }


    /**
     * Function to get user token by token.
     *
     * @param string $token To store the token.
     *
     * @return array
     */
    public function getByToken(string $token): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_token', $token);
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_expired_on', date('Y-m-d H:i:s'), '>=');
        $wheres[] = SqlHelper::generateNullCondition('ut.ut_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('us.us_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('us.us_active', 'Y');
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }


    /**
     * Function to get then user token by the type of token.
     *
     * @param string $userId To store the user id.
     * @param string $systemId To store the system id.
     * @param string $tokenType To store the token type.
     *
     * @return array
     */
    public function getUserTokenByType(string $userId, string $systemId, string $tokenType): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_us_id', $userId);
        if (empty($systemId) === true) {
            $wheres[] = SqlHelper::generateNullCondition('ut.ut_ss_id');
        } else {
            $wheres[] = SqlHelper::generateStringCondition('ut.ut_ss_id', $systemId);
        }
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_type', $tokenType);
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_expired_on', date('Y-m-d H:i:s'), '>=');
        $wheres[] = SqlHelper::generateNullCondition('ut.ut_deleted_on');
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }


    /**
     * Function to generate the token for the user.
     *
     * @param string $userId To store the user id.
     * @param string $tokenType To store the type of the token.
     *
     * @return string
     */
    public function generateTokenByUser(string $userId, string $tokenType): string
    {
        $date = DateTimeParser::createDateTime();
        $key = $userId . $date->format('d/m/Y H:i:s') . $tokenType;

        return md5($key);
    }


    /**
     * Function to generate the token for the user.
     *
     * @param string $userId To store the user id.
     * @param string $systemId To store the system id.
     * @param string $tokenType To store the type of the token.
     *
     * @return string
     */
    public function generateTokenByUserAndSystem(string $userId, string $systemId, string $tokenType): string
    {
        $date = DateTimeParser::createDateTime();
        $key = $userId . $systemId . $date->format('d/m/Y H:i:s') . $tokenType;

        return md5($key);
    }


    /**
     * Function to generate expired on.
     *
     * @param string $tokenType To store the token type.
     * @param bool $remember To set the the value to remember the user.
     *
     * @return string
     */
    public function getExpiredDate($tokenType = 'LOGIN', $remember = false): string
    {
        $date = DateTimeParser::createDateTime();
        if ($tokenType === 'FORGET_PASSWORD') {
            $date->modify('+15 minutes');
        } elseif ($tokenType === 'EMAIL_CONFIRMATION') {
            $date->modify('+1 month');
        } elseif ($tokenType === 'MAPPING_USER') {
            $date->modify('+1 month');
        } else {
            $duration = '+24 hours';
            if ($remember === true) {
                $duration = '+1 month';
            }
            $date->modify($duration);
        }

        return $date->format('Y-m-d H:i:s');
    }


    /**
     * Function to handle the user token.
     *
     * @param array $user To store the user.
     *
     * @return string
     */
    public function manageUserToken(array $user): string
    {
        $result = 'VALID';
        $userToken = $this->getUserToken($user['us_id'], $user['ut_token']);
        if (empty($userToken) === false) {
            if (empty($userToken['ut_deleted_on']) === false) {
                session()->flush();
                session()->regenerate();
                $result = 'DESTROY';
            }
            $colVal = [
                'ut_expired_on' => $this->getExpiredDate('LOGIN', $user['remember'])
            ];
            $this->doUpdateTransaction($userToken['ut_id'], $colVal);
        } else {
            session()->flush();
            session()->regenerate();
            $result = 'EXPIRED';
        }

        return $result;
    }


    /**
     * Function to get all the active record.
     *
     * @param string $userId To store the user id.
     * @param string $token To store the user token.
     *
     * @return array
     */
    public function getUserToken(string $userId, string $token): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_us_id', $userId);
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_token', $token);
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_expired_on', date('Y-m-d H:i:s'), '>=');
        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get data by reference value
     *
     * @param string $referenceValue To store the reference value of the table.
     *
     * @return array
     */
    public static function getByReference(string $referenceValue): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('ut.ut_id', $referenceValue);

        $data = self::loadData($wheres);
        if (count($data) === 1) {
            return $data[0];
        }

        return [];
    }

    /**
     * Function to get all record.
     *
     * @param array $wheres To store the list condition query.
     * @param array $orderBy To store the list condition query.
     * @param int $limit To store the limit of the data.
     * @param int $offset To store the offset of the data to apply limit.
     *
     * @return array
     */
    public static function loadData(array $wheres = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT ut.ut_id, ut.ut_token, ut.ut_type, ut.ut_expired_on, ut.ut_us_id,
                        ut.ut_ss_id, ut.ut_deleted_on
                FROM user_token AS ut
                    INNER JOIN users AS us ON us.us_id = ut.ut_us_id' . $strWhere;
        if (empty($orderBy) === false) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        } else {
            $query .= ' ORDER BY ut.ut_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);

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
        # Generate ID
        $this->Incremental++;
        $uidKey = time() . $this->TablePrefix . $fieldData['ut_us_id'] . $this->Incremental;
        $this->LastInsertId = Uuid::uuid3(Uuid::NAMESPACE_URL, $uidKey);
        # Add value for primary key
        $fieldData[$this->TablePrefix . '_id'] = $this->LastInsertId;
        DB::table($this->table)->insert($fieldData);
    }


    /**
     * Abstract function to load the data.
     *
     * @param string $primaryKeyValue To store the primary key value.
     * @param array $fieldData To store the field value per column.
     *
     * @return void
     */
    public function doUpdateTransaction(string $primaryKeyValue, array $fieldData): void
    {
        DB::table($this->table)
            ->where($this->primaryKey, $primaryKeyValue)
            ->update($fieldData);
    }

    /**
     * Abstract function to load the data.
     *
     * @param string $primaryKeyValue To store the primary key value.
     * @param string $deleteReason To store the message for deleted data.
     *
     * @return void
     */
    public function doDeleteTransaction(string $primaryKeyValue, string $deleteReason = ''): void
    {
        DB::table($this->table)
            ->where($this->primaryKey, $primaryKeyValue)
            ->update([
                $this->TablePrefix . '_deleted_on' => date('Y-m-d H:i:s')
            ]);
    }

}
