<?php

namespace internal\modules\v1\controllers;


use common\models\ContactDetails;
use common\models\ContactType;
use common\models\Creditor;
use common\models\User;
use internal\controllers\ServsolController;
use yii\helpers\StringHelper;


class CreditorsController extends ServsolController
{

    public function actionSearch()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);


        $query =  "SELECT 
    `creditor`.`user_id` AS id,
    `creditor`.`name`,
    `creditor`.`cp_number`,
    `creditor`.`cr_number`,
    `contact_details`.`value` AS 'credit_life_email',
    `creditor_type`.`description` AS creditor_type,
    `creditor_source`.`source_name`,
    `status`.`description` AS status,
    `creditor_type`.`is_secured` AS secured_ref
FROM
    `creditor`
        LEFT JOIN
    `creditor_type` ON `creditor`.`creditor_type_id` = `creditor_type`.`id`
        LEFT JOIN
    `creditor_source` ON `creditor`.`creditor_source_id` = `creditor_source`.`id`
        LEFT JOIN
    `user` ON `creditor`.`user_id` = `user`.`id`
        LEFT JOIN
    `status` ON `status`.`id` = `user`.`status_id`
        LEFT JOIN
    `contact_details` ON `contact_details`.`user_id` = `creditor`.`user_id`
        AND `contact_details`.`contact_type_id` = " . ContactType::CREDIT_LIFE_SUBSTITUTION_EMAIL;


        if (!empty($requestData) && $requestDataRes->id != NULL) {
            $query .= " WHERE creditor.user_id =  $requestDataRes->id  ";

            return  \Yii::$app->getDb()->createCommand($query)->queryOne();

        }

        $query .= " GROUP BY `creditor`.`user_id` ";

        return  \Yii::$app->getDb()->createCommand($query)->queryAll();


    }


    public function actionUpdate()
    {
        $requestDataRes = json_decode(\Yii::$app->request->rawBody);
        $className = 'common\models\Creditor';
        $classNameUsr = 'common\models\User';
     $cred = json_decode($requestDataRes->Creditor, true);
        $user = json_decode($requestDataRes->User, true);

        $model = Creditor::findOne($cred['id']);
        $model_User = User::findOne($cred['id']);


        unset($cred['id']);
        if ($model->load([StringHelper::basename($className) => $cred])) {
            if (!empty($user) && $model_User->load([StringHelper::basename($classNameUsr) => $user])) {
                if (isset($model->credit_life_email)) {
                    $contact_detail = ContactDetails::findByUserAndType($model->user_id, ContactType::CREDIT_LIFE_SUBSTITUTION_EMAIL);
                    if (!empty($contact_detail)) {
                        $contact_detail->value = $model->credit_life_email;
                        $contact_detail->save();
                    } else {
                        $contactDetails = new ContactDetails();
                        $contactDetails->user_id = $model->user_id;
                        $contactDetails->value = $model->credit_life_email;
                        $contactDetails->contact_type_id = ContactType::CREDIT_LIFE_SUBSTITUTION_EMAIL;
                        if ($contactDetails->validate()) {
                            $contactDetails->save();
                            return ['response' => true, 'message' => 'Updated Successfully', 'id' => $model->id];
                        } else {
                            $message = 'Invalid Entry Cont: ';
                            foreach ($contactDetails->getErrors() as $error) {
                                if (!empty($error)) {
                                    $message .= "\n- " . json_encode($error[0]);
                                }
                            }
                            return ['response' => false, 'message' => $message];
                        }
                    }
                }
            }
                if ($model->save() && $model_User->save())
                    return ['response' => true, 'message' => 'Updated Successfully', 'id' => $model->user_id];
              } else {
            $message = 'Invalid Entry: ';
            foreach ( $model->getErrors() as $error ) {
                if (!empty($error)){
                    $message .= "\n- " .  json_encode($error[0]);
                }
            }
            return ['response' => false, 'message ' => $message];
        }
        
    }


}


