<?php

namespace internal\modules\v1\controllers;


use common\components\MailComponent;
use common\components\PolicyFileHandlingComponent;
use common\components\QuoteFileHandlingComponent;
use common\components\SmsComponent;
use common\models\BrokerAvailable;
use common\models\Company;
use common\models\CompanyFile;
use common\models\ContactDetails;
use common\models\ContactType;
use common\models\Correspondence;
use common\models\CorrespondenceFiles;
use common\models\CorrespondenceQueue;
use common\models\CorrespondenceTracking;
use common\models\CorrespondenceTrackingStatus;
use common\models\Creditor;
use common\models\Files;
use common\models\FileType;
use common\models\FileUploadForm;
use common\models\Policy;
use common\models\PolicyMailer;
use common\models\ProductAvailable;
use common\models\ProductBroker;
use common\models\ProductFeeType;
use common\models\SystemUserCompany;
use common\models\Template;
use common\models\TemplateType;
use common\models\UserType;
use internal\controllers\ServeController;
use kartik\alert\Alert;
use Yii;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;


class HelperController extends ServeController
{


    public function actionDownloadFile()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $fileLo = Files::findOne($requestDataRes->params->id);
        $file = $fileLo->file_location;

        if (\Yii::$app->awss3component->getInstance()->doesObjectExist($file)) {
            $signedUrl = \Yii::$app->awss3component->getInstance()->writeObjectFile($file, false, $fileLo->original_file_name);

            return ['message' => 'success', 'response' => $signedUrl, 'filename' => $fileLo->original_file_name];
        }
        return ['message' => 'unsuccessful', 'response' => false, 'filename' => ''];
    }

    public function actionDownloadCorrespondence()
    {


        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);


        $correspondence_file = \Yii::$app->reportscomponent->proofOfCorrespondence($requestDataRes->params->id, 'D');


        if (!empty($correspondence_file)) {
            return ['message' => 'success', 'response' => $correspondence_file];
        }
        return ['message' => 'unsuccessful', 'response' => false, 'filename' => ''];
    }


    public function actionResourceDocuments()
    {


        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $sysFiles = 'SELECT 
    `files_id` as id, `document_title`, `group_name`, `system_file_group`.`id` as group_id, files.original_file_name, `date_created` as updated, `group_colour` as color
FROM
    `system_file`
        LEFT JOIN
    `system_file_group` ON `system_file`.`system_file_group` = `system_file_group`.`id`
        LEFT JOIN
    `files` ON `system_file`.`files_id` = `files`.`id`
WHERE
    (`file_type_id` = ' . $requestDataRes->params->id . ')
        AND (position_index IS NOT NULL)
        AND (`system_file`.`system_file_group` > 0)
GROUP BY `document_title`
ORDER BY `system_file_group`, `position_index` ASC';

        $command = \Yii::$app->db->createCommand($sysFiles);

        $data = $command->queryAll();
        if (!empty($data)) {
            return $data;
        }
        return ['message' => 'unsuccessful', 'response' => false];
    }

    public function actionPermissionUpdate()
    {


        $requestDataRes = json_decode(\Yii::$app->request->rawBody);

        switch ($requestDataRes->params->ref) {
            case 'systemUserCompany':
                $model = SystemUserCompany::findOne($requestDataRes->params->id);

                $model->can_edit = $requestDataRes->params->can_edit;

                break;
            case 'companyBroker':
                $model = BrokerAvailable::findOne($requestDataRes->params->id);
                $model->status_id = $requestDataRes->params->status_id;
                break;
            case 'productBroker':
                $model = ProductBroker::findOne($requestDataRes->params->id);
                $model->status_id = $requestDataRes->params->status_id;
                break;
            case 'companyProductStat':
            case 'companyProductFSB':
                $model = ProductAvailable::findOne($requestDataRes->params->id);
                $requestDataRes->params->ref == 'companyProductFSB' ? $model->perform_fsb_functions = $requestDataRes->params->perform_fsb_functions :
                    $model->status_id = $requestDataRes->params->status_id;
                break;
            case 'ProductFees':
            case 'ProductFeesType':
                $model = ProductFeeType::findOne($requestDataRes->params->id);

                $model[$requestDataRes->params->location] = $requestDataRes->params->val;
                if ($requestDataRes->params->location === 'is_round_up') {
                    $model->is_round_down = $model[$requestDataRes->params->location] == true ? 0 : 1;
                }

                break;

        }

        if ($model->validate() && $model->save()) {

            return ['response' => true, 'message' => 'Updated Successfully'];
        }
        return ['response' => false, 'message' => 'Error while updating'];
    }



    public function actionCreateHelper()
    {

        $requestDataRes = json_decode(\Yii::$app->request->rawBody);
        $className = "common\models" . $requestDataRes->params->className;
        $model = new $className;

    	if ($model->load([StringHelper::basename($className) => json_decode($requestDataRes->params->val, true) ])) {
            if($model->validate()){
                $model->save();
                return ['response' => true, 'message' => 'Created Successfully', 'id' => $model->id];
            }else{
                $message = 'Invalid Entry: ';
                foreach ( $model->getErrors() as $error ) {
                    if (!empty($error)){
                        $message .= "\n- " .  json_encode($error[0]);
                    }
                }
                return ['response' => false, 'message' => $message];
            }
        } else {
            return ['response' => false, 'message' => 'Error while updating'];
        }
    }

    public function actionUpdateHelper()
    {

        $requestDataRes = json_decode(\Yii::$app->request->rawBody);
        $className = "common\models" . $requestDataRes->params->className;
        $model = (new $className)::findOne($requestDataRes->params->val->id);

        if ($model->load([StringHelper::basename($className) => [$requestDataRes->params->val->field => $requestDataRes->params->val->value]])) {
            if($model->validate()){
                $model->save();
                return ['response' => true, 'message' => 'Updated Successfully', 'id' => $model->id];
            }else{
                $message = 'Invalid Entry: ';
                foreach ( $model->getErrors() as $error ) {
                    if (!empty($error)){
                        $message .= "\n- " .  json_encode($error[0]);
                    }
                }
                return ['response' => false, 'message' => $message];
            }
        } else {
            return ['response' => false, 'message' => 'Error while updating'];
        }
    }

    public function actionUploadDocument()
    {
        $id = \Yii::$app->request->post('id');
        $modelFileForm = new FileUploadForm();

        $modelFileForm->file = $_FILES['file'];
        $modelFileForm->file_type_id = \Yii::$app->request->post('file_type_id');


            if (\Yii::$app->request->post('section') == 'quote') {
          $res =  (new QuoteFileHandlingComponent())->generateUploadedFile($modelFileForm, $id);
                return ['response' => true, 'message' => 'Created Successfully', 'rowData' => $res];
            }
            elseif (\Yii::$app->request->post('section') == 'company')  {
                $uploadedFile = UploadedFile::getInstance($modelFileForm, 'file');
                $fileName = preg_replace('/\s+/', '_', FileType::findOne($modelFileForm->file_type_id)->description) . \Yii::$app->user->id . date('is') . "." . $uploadedFile->getExtension();

                if (isset(Yii::$app->params['environment']) && (strtolower(Yii::$app->params['environment']) !== 'unit')) {
                    $uploadedFile->saveAs(Yii::$app->params['uploadPath'] . '/' . $fileName);
                }

                $model_File = new Files();
                $model_File->date_created = date('Y-m-d H:i:s');
                $model_File->file_location = Yii::$app->filehandlingcomponent->create_directories($modelFileForm->file_type_id, $id) . '/' . $model_File->uploadFileName();
                $model_File->original_file_name = basename(Yii::$app->params['uploadPath'] . '/' . $fileName); $model_File->version = 1; $model_File->file_size = $uploadedFile->size; $model_File->application_type = $uploadedFile->type;
                $model_File->save();
                Yii::$app->filehandlingcomponent->persistToS3($model_File->file_location, Yii::$app->params['uploadPath'] . '/' . $fileName);

                $companyFile = new CompanyFile();
                $companyFile->file_type_id = $modelFileForm->file_type_id;
                $companyFile->files_id = $model_File->id;
                $companyFile->user_id = $id;
                $companyFile->save();
                Yii::info("--->>> Creating Policy File Cancellation File End : " . date('Y-m-d H:i:s'), __METHOD__);


                if ($modelFileForm->file_type_id == FileType::COMPANY_BRANDING_IMAGE) {
                    $company = Company::findOne($id);
                    $company->brand_image_file_id = $model_File->id;
                    $company->save();
                }


                return ['response' => true, 'message' => 'Created Successfully', 'id' => $model_File->id];

            } else {


          $res = (new PolicyFileHandlingComponent())->generateUploadedFile($modelFileForm, $id, \Yii::$app->request->post('section'));
             if ($res !== false) {
                 return ['response' => true, 'message' => 'Created Successfully', 'rowData' => $res];
              }
            }

        $message = 'Invalid Entry: ';
        foreach ( $modelFileForm->getErrors() as $error ) {
            if (!empty($error)){
                $message .= "\n- " .  json_encode($error[0]);
            }
        }
        return ['response' => false, 'message' => $message];


    }


    public function actionCreateCorrespondence()
    {


        $requestDataRes = json_decode(\Yii::$app->request->rawBody, true);

        $id = $requestDataRes['id'];
        $type = $requestDataRes['type'];

        $modelMailer = new PolicyMailer();
        $modelMailer->policy = Policy::findOne($id);




        if ($modelMailer->load(['PolicyMailer' => $requestDataRes['PolicyMailer']])) {
            if ($type === 'smsCors') {

                $smsCom = new SmsComponent();
                Yii::info("--->>>  Sending Adhoc SMS to: " . $id);
                $SmsCorrespondence = $smsCom->sendSMS($id, $modelMailer->bodyContent, $modelMailer->subjectLine, Template::CUSTOM_SMS_TO_POLICY_HOLDER);
                if (!$SmsCorrespondence["result"]) {
                    Yii::info("--->>> Failure Sending Adhoc SMS to User with Policy: " . $id . "Details:" . json_encode($SmsCorrespondence));
                    return ['response' => false, 'message' => "Failure Sending Adhoc SMS to User with Policy: " . $id . "Details:" . json_encode($SmsCorrespondence)];
                }
                $rowData = ['id' => $smsCom->corrId, 'receiver_type' => 'Policy Holder', 'receiver' => $modelMailer->policy->primaryPolicyHolder->name . ' ' . $modelMailer->policy->primaryPolicyHolder->surname, 'date_created' => $smsCom->dateCreated, 'subject' => $modelMailer->subjectLine];

                Yii::info("--->>>  Sending Adhoc SMS to User: " . $id);
                return ['response' => true, 'message' => "Sending Adhoc SMS to User: " . $id, 'rowData' => $rowData];

            }
            if ($requestDataRes['PolicyMailer']['UserType'] == UserType::CREDITOR) {
                $mailRows = [];
                foreach ($requestDataRes['PolicyMailer']['creditors'] as $creditor) {
                $creditorMod = Creditor::findOne($creditor);
                    $modelFiles = $modelMailer->getFilesForAttachment($creditor);
                $rowDataAd = ['receiver_type' => 'Creditor', 'receiver' => $creditorMod->name];
                    // The creditors
                  Yii::info("--->>> Sending Credit Certificate Start : " . date('Y-m-d H:i:s'), __METHOD__);
                    $mailRows[] = array_merge($rowDataAd, (new MailComponent())->sendEmail(TemplateType::POLICY_CREDITOR_ISSUE, $creditor, $modelFiles, $modelMailer->policy, NULL, NULL));
                    Yii::info("--->>> Sending Credit Certificate End : " . date('Y-m-d H:i:s'), __METHOD__);
                }

                return ['response' => true, 'message' => 'Email has been sent Successfully!', 'rowData' => $mailRows];
            }
            if ($requestDataRes['PolicyMailer']['UserType'] == UserType::POLICY_HOLDER) {

                $signedRAR = !empty($requestDataRes['PolicyMailer']['filepathImage']) ? $requestDataRes['PolicyMailer']['filepathImage'] : NULL;

                if (in_array('RAR_Signed', $modelMailer->attachments) && empty($signedRAR) ) {
                        return ['response' => false, 'message' => 'Mail Not Sent - There was an issue processing your signature - please try again.'];
                }
                $modelFiles = $modelMailer->getFilesForAttachment(null,$signedRAR);

                $userEmail = (!empty(ContactDetails::findByUserAndType($modelMailer->policy->primaryPolicyHolder->user_id, ContactType::EMAIL)) ? ContactDetails::findByUserAndType($modelMailer->policy->primaryPolicyHolder->user_id, ContactType::EMAIL)->value : null);

                $bottomTemplate = Template::find()->where('template_type_id = :template_type_id', [':template_type_id' => TemplateType::TEMPLATE_FOOTER])->one()->template_content;
                $topTemplate = Template::find()->where('template_type_id = :template_type_id', [':template_type_id' => TemplateType::TEMPLATE_HEADER])->one()->template_content;
                $bottomTemplate = str_replace("<<telephone>>", Yii::$app->params['telephone'], $bottomTemplate);
                $bottomTemplate = str_replace("<<fax>>", Yii::$app->params['fax'], $bottomTemplate);
                $bottomTemplate = str_replace("<<website>>", Yii::$app->params['website'], $bottomTemplate);
                $bottomTemplate = str_replace("<<website_url>>", Yii::$app->params['website_url'], $bottomTemplate);

                $correspondence = new Correspondence();
                $correspondence->user_id = $modelMailer->policy->primaryPolicyHolder->user_id;
                $correspondence->policy_id = $id;
                $correspondence->template_id = Template::CUSTOM_TEMPLATE;
                $correspondence->correspondence_address = $userEmail;
                $correspondence->content = $topTemplate . implode('<br>', array_filter(explode("\n", $modelMailer->bodyContent))) . $bottomTemplate;
                $correspondence->subject = $modelMailer->subjectLine;
                $correspondence->policy_cc_address = NULL;
                $correspondence->save(false);
                if (isset($modelFiles)) {
                    foreach ($modelFiles as $attachment) {

                        $correspodenceFile = new CorrespondenceFiles();
                        $correspodenceFile->files_id = $attachment->id;
                        $correspodenceFile->correspondence_id = $correspondence->id;
                        $correspodenceFile->save(false);
                    }
                }
                $correspondenceTracking = new CorrespondenceTracking();
                $correspondenceTracking->correspondence_id = $correspondence->id;
                $correspondenceTracking->correspondence_tracking_status_id = CorrespondenceTrackingStatus::QUEUED_INTERNALLY;
                $correspondenceTracking->date_created = date('Y-m-d H:i:s');
                $correspondenceTracking->save(false);

                // Add the mail to the Send Queue
                $correspondenceQueue = new CorrespondenceQueue();
                $correspondenceQueue->correspondence_id = $correspondence->id;
                $correspondenceQueue->date_created = date('Y-m-d H:i:s');
                $correspondenceQueue->locked = 0;
                $correspondenceQueue->save(false);

                $rowData = ['id' => $correspondence->id, 'receiver_type' => 'Policy Holder', 'receiver' => $modelMailer->policy->primaryPolicyHolder->name . ' ' . $modelMailer->policy->primaryPolicyHolder->surname, 'date_created' => $correspondenceTracking->date_created, 'subject' => $correspondence->subject];
                return ['response' => true, 'message' => 'Email has been sent Successfully!', 'rowData' => $rowData];

            }
            return ['response' => false, 'message' => 'Error while sending correspondence'];
        }

    }

}
