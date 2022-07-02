<?php

namespace internal\modules\v1\controllers;


use common\models\Company;
use common\models\ContactDetails;
use common\models\Product;
use common\models\ProductFeeType;
use common\models\UserType;
use internal\controllers\ServeController;
use yii\base\BaseObject;

class SystemSettingsController extends ServeController
{


    public function actionSearchCompany()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        if (!empty($requestDataRes) && $requestDataRes->params->id === 'all') {

            $query = "SELECT company.name, company.user_id as id, user_type.description as type, status.description as status FROM `company`
                        LEFT JOIN `user` ON `company`.`user_id` = `user`.`id` 
                        LEFT JOIN `status` ON `user`.`status_id` = `status`.`id` 
                        LEFT JOIN `user_type` ON `user`.`user_type_id` = `user_type`.`id`";

            return \Yii::$app->getDb()->createCommand($query)->queryAll();
        }
        return  false;


    }

    public function actionViewCompany()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $phD['main'] = $policyH = Company::findOne($requestDataRes->params->id);
            $phD['files'] = $policyH->companyFiles;
        $phD['contactDetail'] = $policyH->contactDetailMap;
            $phD['brokers'] = $policyH->brokerAvailable;
        $phD['products'] = $policyH->productAvailable;
        if($policyH->user->user_type_id == UserType::BROKER || $policyH->user->user_type_id == UserType::CLIENT_BROKER) {
            $phD['productBroker'] = $policyH->productBroker;
        }
        $phD['type'] = $policyH->user->userType;
        $phD['status'] = $policyH->user->status;
        return $phD;



    }


    public  function actionSearchProduct() {
        $requestDataRes = json_decode(\Yii::$app->request->rawBody);

      $query = "SELECT 
       product.id  as id,
    product.description as `description`,
    code,
    insurer.name,
    underwriter.name AS underwriter,
    status_id,
    product_type.description AS product_type
FROM
    `product`   
        LEFT JOIN
    `company` `underwriter` ON `product`.`underwriter_user_id` = `underwriter`.`user_id`
        LEFT JOIN
    `company` `insurer` ON `product`.`insurer_user_id` = `insurer`.`user_id`
        LEFT JOIN
    `status` ON `product`.`status_id` = `status`.`id`
        LEFT JOIN
    `product_type` ON `product`.`product_type_id` = `product_type`.`id`";


        if (!empty($requestDataRes)) {

            $res = \Yii::$app->getDb()->createCommand($query)->queryAll();
        if (!empty($res)) {
            return $res;
        }
        return false;
        }
        return false;
    }


    public function actionViewProduct()
    {
        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $phD['main'] = $policyH = Product::findOne($requestDataRes->params->id);
        $phD['productFees'] = $policyH->productFees;
        $phD['underwriter'] = $policyH->underwriter;
        $phD['insurer'] = $policyH->insurer;
        $phD['status'] = $policyH->status;
        $phD['productType'] = $policyH->productType;
        return $phD;



    }


    public function actionUpdateProductFee()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $model = ProductFeeType::findOne($requestDataRes->params->id);

        $model[$requestDataRes->params->field] = $requestDataRes->params->props->value;
            if ($model->validate()) {
                $model->save();
                return ['response' => true, 'message' => 'Updated Successfully'];
            } else {
                return ['response' => false, 'message' => 'Error while updating'];
            }

    }

    function actionUpdateContactDetails()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);
        $value = $requestDataRes->params->value;
        $id = $requestDataRes->params->id;
        $contactDetails = ContactDetails::findOne($id);
        if (isset ($contactDetails)) {
            if ($contactDetails->value !== $value) {
                $contactDetails->value = $value;
                if ($contactDetails->validate()) {
                    $contactDetails->save();
                } else {
                    foreach ($contactDetails->getFirstErrors() as $error) {
                        array_push($errorArray, $error);
                        \Yii::info("Contact Details NOT Inserted: " . $error, __METHOD__);
                    }
                    return true;
                }
            }

        }
        return true;

    }

}


