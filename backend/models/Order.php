<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int $version
 * @property string|null $order_date
 * @property string|null $order_number
 * @property int|null $amount
 * @property int|null $status_id
 * @property int|null $deparment_id
 * @property int|null $user_authorised
 *
 * @property Comment[] $comments
 * @property Correspondence[] $correspondences
 * @property Departments $deparment
 * @property OrderItem[] $orderItems
 * @property Status $status
 * @property User $userAuthorised
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version'], 'required'],
            [['version', 'amount', 'status_id', 'deparment_id', 'user_authorised'], 'integer'],
            [['order_date'], 'safe'],
            [['order_number'], 'string', 'max' => 45],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['status_id' => 'id']],
            [['deparment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Departments::className(), 'targetAttribute' => ['deparment_id' => 'id']],
            [['user_authorised'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_authorised' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'order_date' => 'Order Date',
            'order_number' => 'Order Number',
            'amount' => 'Amount',
            'status_id' => 'Status ID',
            'deparment_id' => 'Deparment ID',
            'user_authorised' => 'User Authorised',
        ];
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['order_id' => 'id']);
    }

    /**
     * Gets query for [[Correspondences]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondences()
    {
        return $this->hasMany(Correspondence::className(), ['order_id' => 'id']);
    }

    /**
     * Gets query for [[Deparment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeparment()
    {
        return $this->hasOne(Departments::className(), ['id' => 'deparment_id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'status_id']);
    }

    /**
     * Gets query for [[UserAuthorised]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuthorised()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_authorised']);
    }
}
