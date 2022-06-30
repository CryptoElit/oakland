<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_type".
 *
 * @property int $id
 * @property string|null $description
 *
 * @property ContactDetails[] $contactDetails
 */
class ContactType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contact_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['description'], 'string', 'max' => 45],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[ContactDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContactDetails()
    {
        return $this->hasMany(ContactDetails::className(), ['contact_type_id' => 'id']);
    }
}
