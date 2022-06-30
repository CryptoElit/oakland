<?php

namespace internal\modules\v1\controllers;


use common\models\ContactDetails;
use common\models\PolicyHolder;
use common\models\SystemUser;
use internal\controllers\ServsolController;
use common\models\ContactType;


class PolicyHolderController extends ServsolController
{

    public function actionGlobalSearch()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $query = "SELECT ph.* FROM policy_holder ph left join user s on ph.user_id = s.id where s.status_id in (2, 1, 6)";
        return  \Yii::$app->getDb()->createCommand($query)->queryAll();

    }

    public function actionSearch()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        if (!empty($requestData) && $requestDataRes->params->id === 'all') {

            $query = "SELECT policy_holder.user_id as id,  id_number, name, surname, status.description as status
        FROM `policy_holder`  LEFT JOIN `user` ON `policy_holder`.`user_id` = `user`.`id`
            LEFT JOIN `status` ON `user`.`status_id` = `status`.`id`";

            return \Yii::$app->getDb()->createCommand($query)->queryAll();
        }
        return  false;


    }

    public function actionView()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $phD['main'] = $policyH = PolicyHolder::findOne($requestDataRes->params->id);
        $phD['status'] = $policyH->status;
        $phD['occupation'] = $policyH->occupation;
        $phD['title'] = $policyH->titleType;
        $phD['company'] = $policyH->sourceSystem;
        $phD['contactDetail'] = $policyH->contactDetail;
        $phD['policies'] = $policyH->policies;

        return $phD;



    }


    public function actionCreate()
    {
        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $sysUserNew = new SystemUser();
        if ($sysUserNew->load($requestDataRes)) {
            return $sysUserNew;
        }
        return  false;

    }


     function actionUpdate() {

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


