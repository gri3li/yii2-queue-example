<?php

namespace app\models;

/**
 * This is the model class for table "requests".
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $term
 * @property string $status
 */
class Request extends \yii\db\ActiveRecord
{
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_IN_PROCESS = 'in_process';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'requests';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_APPROVED, self::STATUS_DECLINED, self::STATUS_IN_PROCESS]],
            ['user_id', 'unique', 'targetAttribute' => ['user_id' => 'user_id'], 'filter' => ['status' => self::STATUS_APPROVED]],
        ];
    }
}
