<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_details".
 *
 * @property int $id
 * @property int $user_id
 * @property string $value
 * @property int $contact_type_id
 *
 * @property ContactType $contactType
 * @property SystemUser $user
 */
class ContactDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contact_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'value', 'contact_type_id'], 'required'],
            [['user_id', 'contact_type_id'], 'integer'],
            [['value'], 'string', 'max' => 200],
            [['contact_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContactType::className(), 'targetAttribute' => ['contact_type_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => SystemUser::className(), 'targetAttribute' => ['user_id' => 'user_id']],
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
            'value' => 'Value',
            'contact_type_id' => 'Contact Type ID',
        ];
    }

    /**
     * Gets query for [[ContactType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContactType()
    {
        return $this->hasOne(ContactType::className(), ['id' => 'contact_type_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(SystemUser::className(), ['user_id' => 'user_id']);
    }
}
