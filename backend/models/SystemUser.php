<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "system_user".
 *
 * @property int $user_id
 * @property string $name
 * @property string $surname
 * @property int $user_type
 * @property string $username
 * @property string|null $password
 * @property string|null $last_login
 * @property string|null $auth_key
 *
 * @property Comment[] $comments
 * @property ContactDetails[] $contactDetails
 * @property Correspondence[] $correspondences
 * @property Departments[] $departments
 * @property Order[] $orders
 * @property Supplier[] $suppliers
 */
class SystemUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'system_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'surname', 'user_type', 'username'], 'required'],
            [['user_id', 'user_type'], 'integer'],
            [['last_login'], 'safe'],
            [['name', 'surname', 'username', 'password', 'auth_key'], 'string', 'max' => 100],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'name' => 'Name',
            'surname' => 'Surname',
            'user_type' => 'User Type',
            'username' => 'Username',
            'password' => 'Password',
            'last_login' => 'Last Login',
            'auth_key' => 'Auth Key',
        ];
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['user_id' => 'user_id']);
    }

    /**
     * Gets query for [[ContactDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContactDetails()
    {
        return $this->hasMany(ContactDetails::className(), ['user_id' => 'user_id']);
    }

    /**
     * Gets query for [[Correspondences]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondences()
    {
        return $this->hasMany(Correspondence::className(), ['user_id' => 'user_id']);
    }

    /**
     * Gets query for [[Departments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartments()
    {
        return $this->hasMany(Departments::className(), ['supervisor_id' => 'user_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_authorised' => 'user_id']);
    }

    /**
     * Gets query for [[Suppliers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuppliers()
    {
        return $this->hasMany(Supplier::className(), ['user_id' => 'user_id']);
    }


    /**
     * Gets query for [[Suppliers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findByUsername($username)
    {
        return SystemUser::find()->where(['username' => $username])->one();
    }
}
