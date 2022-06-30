<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property int $user_id
 * @property string $name
 * @property string|null $api_token
 * @property string|null $trading_name
 * @property string|null $registration_number
 * @property string|null $vat_number
 * @property string|null $fsb_number
 * @property string|null $compliance_officer
 * @property int $api_integration
 * @property int|null $brand_image_file_id
 *
 * @property Files $brandImageFile
 * @property BrokerAvailable[] $brokerAvailables
 * @property BrokerAvailable[] $brokerAvailables0
 * @property CompanyFile[] $companyFiles
 * @property ProductAvailable[] $productAvailables
 * @property ProductBroker[] $productBrokers
 * @property Product[] $products
 * @property Product[] $products0
 * @property Product[] $products1
 * @property Quote[] $quotes
 * @property SystemUserCompany[] $systemUserCompanies
 * @property SystemUser[] $systemUsers
 * @property User $user
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name'], 'required'],
            [['user_id', 'api_integration', 'brand_image_file_id'], 'integer'],
            [['name', 'fsb_number'], 'string', 'max' => 45],
            [['api_token', 'trading_name', 'registration_number', 'compliance_officer'], 'string', 'max' => 200],
            [['vat_number'], 'string', 'max' => 100],
            [['user_id'], 'unique'],
            [['brand_image_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::className(), 'targetAttribute' => ['brand_image_file_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'api_token' => 'Api Token',
            'trading_name' => 'Trading Name',
            'registration_number' => 'Registration Number',
            'vat_number' => 'Vat Number',
            'fsb_number' => 'Fsb Number',
            'compliance_officer' => 'Compliance Officer',
            'api_integration' => 'Api Integration',
            'brand_image_file_id' => 'Brand Image File ID',
        ];
    }

    /**
     * Gets query for [[BrandImageFile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrandImageFile()
    {
        return $this->hasOne(Files::className(), ['id' => 'brand_image_file_id']);
    }

    /**
     * Gets query for [[BrokerAvailables]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrokerAvailables()
    {
        return $this->hasMany(BrokerAvailable::className(), ['company_id' => 'user_id']);
    }

    /**
     * Gets query for [[BrokerAvailables0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrokerAvailables0()
    {
        return $this->hasMany(BrokerAvailable::className(), ['brokerage_user_id' => 'user_id']);
    }

    /**
     * Gets query for [[CompanyFiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyFiles()
    {
        return $this->hasMany(CompanyFile::className(), ['user_id' => 'user_id']);
    }

    /**
     * Gets query for [[ProductAvailables]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAvailables()
    {
        return $this->hasMany(ProductAvailable::className(), ['company_id' => 'user_id']);
    }

    /**
     * Gets query for [[ProductBrokers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductBrokers()
    {
        return $this->hasMany(ProductBroker::className(), ['company_id' => 'user_id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['insurer_user_id' => 'user_id']);
    }

    /**
     * Gets query for [[Products0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts0()
    {
        return $this->hasMany(Product::className(), ['underwriter_user_id' => 'user_id']);
    }

    /**
     * Gets query for [[Products1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts1()
    {
        return $this->hasMany(Product::className(), ['underwriter_manager_id' => 'user_id']);
    }

    /**
     * Gets query for [[Quotes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuotes()
    {
        return $this->hasMany(Quote::className(), ['company_id' => 'user_id']);
    }

    /**
     * Gets query for [[SystemUserCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSystemUserCompanies()
    {
        return $this->hasMany(SystemUserCompany::className(), ['company_id' => 'user_id']);
    }

    /**
     * Gets query for [[SystemUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSystemUsers()
    {
        return $this->hasMany(SystemUser::className(), ['company_id' => 'user_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
