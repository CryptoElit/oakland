<?php

namespace internal\modules\v1\controllers;


use common\models\User;
use internal\controllers\ServeController;


class ContactDetailsController extends ServeController
{

    public function actionGetContactDetails()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $contactDetials = User::findOne($requestDataRes->user_id )->contactDetails;

        return $contactDetials;


    }

    public function actionUpdateContactDetails() {

    }


}


