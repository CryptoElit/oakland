<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "correspondence".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $order_id
 * @property string $address
 * @property string|null $content
 * @property string|null $bcc_address
 * @property string|null $subject
 *
 * @property Order $order
 * @property User $user
 */
class Correspondence extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'correspondence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'address'], 'required'],
            [['user_id', 'order_id'], 'integer'],
            [['content', 'subject'], 'string'],
            [['address', 'bcc_address'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'address' => 'Address',
            'content' => 'Content',
            'bcc_address' => 'Bcc Address',
            'subject' => 'Subject',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
}
