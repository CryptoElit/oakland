<?php

namespace common\components;

use common\models\ContactDetails;
use common\models\ContactType;
use common\models\CorrespondenceFiles;
use common\models\Policy;
use common\models\PolicyCreditor;
use common\models\PolicyFile;
use common\models\PolicyStatus;
use common\models\Template;
use common\models\TemplateDeliveryChannel;
use common\models\TemplateType;
use common\models\UserType;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;

class CorrespondenceComponent extends Component
{
    public $modelFiles = [];
    public $modelMailer;
    public $creditorFiles = [];
    public $model;
    public $policyTemplate;
    public $creditorTemplate;
    /**
     * ['mailType' => '1',
     * 'mailPackage' => '2',
     * 'subjectLine' => '',
     * 'UserType' => ['2', '3'],
     * 'creditors' => ['25286','23613', '23666','23170'],
     * 'attachments' => ['Creditor_Certificate']
     * ]
     */
    /** Mail Package Listing is as follows - [1 => 'Activated', 2 => 'Issued', 3 => 'Lapsed', 4 => 'Terminated'] */

    public function sendEmailsBasedOnStatus($modelMailer)
    {

        $this->model = $modelMailer->policy;
        $this->modelMailer = $modelMailer;

        switch ($modelMailer->mailPackage) {
            case 1: // 'Activated'
                $attachmentsPol = ["Policy_Schedule", "Welcome_Letter", "Creditor_Certificate", "Credit_Protection", "Disclosure_Notice"];
                $attachmentsCredit = ["Policy_Schedule", "Welcome_Letter", "Creditor_Certificate", "Credit_Protection", "Disclosure_Notice"];
                $this->policyTemplate = Template::FIRST_PREMIUM_RECEIVED_TEMPLATE;

                break;
            case 2: // 'Issued'
                $attachmentsPol = ["Policy_Schedule", "Welcome_Letter", "Credit_Protection", "Disclosure_Notice"];
                $attachmentsCredit = ["Creditor_Certificate"];
                $this->policyTemplate = TemplateType::POLICY_HOLDER_ISSUE;
                $this->creditorTemplate = Template::CREDITOR_ISSUE_TEMPLATE;
                break;
            case 3: // 'Lapsed'
                $attachmentsPol = ["Policy_Schedule", "Welcome_Letter", "Creditor_Certificate", "Credit_Protection", "Disclosure_Notice"];
                $attachmentsCredit = ["Policy_Schedule", "Welcome_Letter", "Creditor_Certificate", "Credit_Protection", "Disclosure_Notice"];
                $this->policyTemplate = Template::POLICY_NON_PAYMENT_LAPSED_TEMPLATE;
                $this->creditorTemplate = Template::FIRST_PREMIUM_RECEIVED_TEMPLATE;
                break;
            case 4: // 'Terminated':
                $attachmentsPol = ["Policy_Schedule", "Welcome_Letter", "Creditor_Certificate", "Credit_Protection", "Disclosure_Notice"];
                $attachmentsCredit = ["Policy_Schedule", "Welcome_Letter", "Creditor_Certificate", "Credit_Protection", "Disclosure_Notice"];
                break;
            default:
                return null;
        }

        if (isset($modelMailer->UserType) && in_array(UserType::CLIENT, $modelMailer->UserType)) {

            $this->sendDCMailer($attachmentsPol, $attachmentsCredit);
        }

        if (isset($modelMailer->UserType) && in_array(UserType::CREDITOR, $modelMailer->UserType)) {

            $this->creditorEmailSend($attachmentsCredit);

        }

        // Policy Holder
        if (isset($modelMailer->UserType) && in_array(UserType::POLICY_HOLDER, $modelMailer->UserType)) {

            // Send the Mail for the Status Driven Package
            $this->policyHolderEmailSend($attachmentsPol);

            // If we are sending the issued package, check for the replaced and action the mailer
            if (isset($modelMailer->mailPackage) && $modelMailer->mailPackage == 2) {


                //Check if this policy replaces another - GLOBAL SO WE KEEP IT HERE
                $replacedPolicies = Policy::findByPolicyHolderAndStatus($this->model->primary_policy_holder_id, $this->model->id, $this->model->policy_status_id != PolicyStatus::STATUS_ISSUED_NOT_ACCEPTED ? [PolicyStatus::STATUS_ACTIVE, PolicyStatus::STATUS_LAPSED, PolicyStatus::STATUS_ISSUED] : [PolicyStatus::STATUS_ISSUED_NOT_ACCEPTED, PolicyStatus::STATUS_REJECTED]);
                if (isset($replacedPolicies) && !empty($replacedPolicies)) {
                    $this->replacedPolicyEmailSend($replacedPolicies);
                }
            }

        }
        return true;
    }

    // Policy Holder

    public function sendDCMailer($attachmentsPolicy, $attachmentsCred)
    {
        // send the DC Mailer
        $dcUser = $this->model->sourceSystem;

        // First we process attachments for the policy holder
        $this->modelMailer->attachments = ['Policy_Schedule'];

        $this->modelFiles = $this->modelMailer->getFilesForAttachment(null, NULL);
        // First we process attachments for the policy holder
        $this->modelMailer->attachments = $attachmentsCred;
        $creds = [];
        foreach ($this->modelMailer->policy->policyCreditors as $creditor) {
            if (!in_array($creditor->creditor_user_id, $creds)) {
                // Get the files for the creditors
                $modelFilesCredit = $this->modelMailer->getFilesForAttachment($creditor->creditor_user_id);

                $creds[] = $creditor->creditor_user_id;
                if (count($modelFilesCredit) > 1) {
                    foreach ($modelFilesCredit as $mfc) {
                        array_push($this->creditorFiles, $mfc);
                    }
                } else {
                    array_push($this->creditorFiles, $modelFilesCredit[0]);
                }
            }
        }


        if (isset($dcUser)) {
            Yii::info("--->>> Sending Issued Report to DC Company: " . $dcUser->trading_name);
            // Merge the Welcome Pack
            Yii::$app->mailcomponent->sendEmail(Template::ISSUED_POLICIES_TO_DC, $dcUser->user_id, array_merge($this->modelFiles, $this->creditorFiles), $this->model);
            Yii::info("--->>> Finished Sending Issued Report to DC Company:" . $dcUser->trading_name);
        }
        return true;
    }

    // Creditors

    // Creditors

    public function creditorEmailSend($attachments)
    {

        // First we process attachments for the policy holder
        $this->modelMailer->attachments = $attachments;



        $mandateFile = PolicyFile::findCliMandateForPolicy($this->modelMailer->policy->id);

        foreach ($this->modelMailer->creditors as $creditor) {
            $creditorMol = PolicyCreditor::findOne($creditor);
            // Get the files for the creditors
            $modelFilesCredit = $this->modelMailer->getFilesForAttachment($creditorMol->creditor_user_id, NULL, $creditorMol->account_number);

            array_push($this->creditorFiles, $modelFilesCredit);

            if (count($modelFilesCredit) > 1) {
                foreach ($modelFilesCredit as $item) {
                    Yii::info("--->>> Sending Creditor " . $this->creditorTemplate . " Start : " . date('Y-m-d H:i:s'), __METHOD__);
                    Yii::$app->mailcomponent->sendEmail($this->creditorTemplate, $creditorMol->creditor_user_id, [$item], $this->model, NULL, NULL);
                    Yii::info("--->>> Sending Creditor " . $this->creditorTemplate . " Start : " . date('Y-m-d H:i:s'), __METHOD__);
                }
            } else {

                Yii::info("--->>> Sending Creditor " . $this->creditorTemplate . " Start : " . date('Y-m-d H:i:s'), __METHOD__);
                Yii::$app->mailcomponent->sendEmail($this->creditorTemplate, $creditorMol->creditor_user_id, $modelFilesCredit, $this->model, NULL, NULL);
                Yii::info("--->>> Sending Creditor " . $this->creditorTemplate . " Start : " . date('Y-m-d H:i:s'), __METHOD__);

            }}

    }

    public function policyHolderEmailSend($attachments)
    {
        // First we process attachments for the policy holder
        $this->modelMailer->attachments = $attachments;

        // Get the Policy Holder Files based on the attachments
        $this->modelFiles = $this->modelMailer->getFilesForAttachment(null, NULL);

        $ccContactDetails = ContactDetails::findByUserAndType($this->model->primaryPolicyHolder->user_id, ContactType::POLICY_CC_EMAIL);
        Yii::info("--->>> Sending Policy Holder Start : " . date('Y-m-d H:i:s'), __METHOD__);
        Yii::$app->mailcomponent->sendEmail($this->policyTemplate, $this->model->primary_policy_holder_id, $this->modelFiles, $this->model, isset($ccContactDetails) ? $ccContactDetails->value : NULL, NULL);
        Yii::info("--->>> Sending Policy Holder End : " . date('Y-m-d H:i:s'), __METHOD__);

    }

    public function replacedPolicyEmailSend($replacedPolicies)
    {
        foreach ($replacedPolicies as $replacedPolicy) {
            $ccContactDetails = ContactDetails::findByUserAndType($this->model->primaryPolicyHolder->user_id, ContactType::POLICY_CC_EMAIL);
            Yii::info("--->>> Sending Consumer Policy Replaced Mailer : " . date('Y-m-d H:i:s'), __METHOD__);
            Yii::$app->mailcomponent->sendEmail(Template::REPLACE_POLICY_CONSUMER_TEMPLATE, $this->model->primary_policy_holder_id, $this->modelFiles, $this->model, isset($ccContactDetails) ? $ccContactDetails->value : NULL, NULL);
            Yii::info("--->>>Sending Consumer Policy Replaced Mailer end : " . date('Y-m-d H:i:s'), __METHOD__);


            Yii::info("--->>> Sending Replaced Policy SMS to: " . $this->model->id);
            $SmsCorrespondence = Yii::$app->smscomponent->sendSMS($this->model->id, "", "", Template::CLI_POLICY_REPLACED);
            if (!$SmsCorrespondence["result"]) {
                Yii::info("--->>> Failure Sending Replaced Policy SMS to: " . $this->model->id . "Details:" . json_encode($SmsCorrespondence));
            }
            Yii::info("--->>> Finished Replaced Policy SMS to: " . $this->model->id);


            foreach ($this->model->policyCreditors as $creditor) {

                $modelFilesCredit = $this->modelMailer->getFilesForAttachment($creditor);
                $creditorUserId = $creditor->creditor_user_id;

                // The creditors
                Yii::info("--->>> Sending Creditor Policy Replaced Mailer : " . date('Y-m-d H:i:s'), __METHOD__);

                // Check for a Mandate and add it if necessary
                $mandateFile = PolicyFile::findCliMandateForPolicy($this->model->id);
                $creditorAttachment = [];
                array_push($creditorAttachment, $modelFilesCredit);
                if (isset($mandateFile)) {
                    array_push($creditorAttachment, $mandateFile->files);
                }

                Yii::$app->mailcomponent->sendEmail(Template::REPLACE_POLICY_CREDITOR_TEMPLATE, $creditorUserId, $creditorAttachment, $this->model, NULL, NULL);
                Yii::info("--->>>Sending Creditor Policy Replaced Mailer end : " . date('Y-m-d H:i:s'), __METHOD__);
            }
        }
    }

    // Not necessary here but keeping for queued future

    public function addCorrespondenceFiles(object $correspondence)
    {
        if (isset($this->modelFiles)) {
            foreach ($this->modelFiles as $attachment) {

                $correspondenceFile = new CorrespondenceFiles();
                $correspondenceFile->files_id = $attachment->id;
                $correspondenceFile->correspondence_id = $correspondence->id;
                $correspondenceFile->save(false);
            }
        }
        return true;

    }


    public function correspondenceSearch(int $policyID, string $view = 'Mail')
    {
        //Add the correspondence
        $arrayHelper = [];

        //Correspondence
        $sql = "select ct.date_created, ut.description as receiver_type, ifnull(cr.name, concat(ph.name,' ',ph.surname)) as receiver, c.subject, c.id as correspondence_id
                    from policy_file pf
                    left join correspondence_files cf on cf.files_id = pf.files_id
                    left join files f on f.id = cf.files_id
                    left join correspondence c on c.id = cf.correspondence_id
                    left join user u on u.id = c.user_id
                    left join user_type ut on ut.id = u.user_type_id
                    left join policy_holder ph on ph.user_id = c.user_id
                    left join creditor cr on cr.user_id = c.user_id
                    left join correspondence_tracking ct on ct.correspondence_id = c.id
                    where pf.policy_id =  " . $policyID . "
                    and c.policy_id is NULL
                    group by c.id";

        $sqlResults = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($sqlResults as $sqlResult) {
            if (isset($sqlResult['correspondence_id'])) {
                array_push($arrayHelper, $sqlResult);
            }
        }

        $deliveryChannel = ($view == 'Mail' ? " and tem.template_delivery_channel_id NOT IN (" . TemplateDeliveryChannel::SMS_SINGLE . ", " . TemplateDeliveryChannel::SMS_BULK . ", " . TemplateDeliveryChannel::SMS_ADHOC . ")" : " and tem.template_delivery_channel_id IN (" . TemplateDeliveryChannel::SMS_SINGLE . ", " . TemplateDeliveryChannel::SMS_BULK . ", " . TemplateDeliveryChannel::SMS_ADHOC . ")");

        $sql = "select ct.date_created, ut.description as receiver_type, ifnull(cr.name, ifnull(concat(ph.name,' ',ph.surname), cy.name)) as receiver, c.subject, c.id as correspondence_id
                    from correspondence c 
                    left join user u on u.id = c.user_id
                    left join company cy on u.id = cy.user_id
                    left join user_type ut on ut.id = u.user_type_id
                    left join policy_holder ph on ph.user_id = c.user_id
                    left join creditor cr on cr.user_id = c.user_id
                    left join correspondence_tracking ct on ct.correspondence_id = c.id
                    left join template tem on tem.id = c.template_id
                    where c.policy_id =  " . $policyID . "
                    $deliveryChannel
                     group by c.id;";

        $sqlResults = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($sqlResults as $sqlResult) {
            if (isset($sqlResult['correspondence_id'])) {
                array_push($arrayHelper, $sqlResult);
            }
        }

        //Create search model for correspondence
        $searchAttributes = ['date_created', 'receiver_type', 'receiver', 'subject'];
        $searchModel = [];
        $searchColumns = [];
        $searchColumns[] = ['class' => 'kartik\grid\SerialColumn'];
        foreach ($searchAttributes as $searchAttribute) {
            $filterName = 'filter' . $searchAttribute;
            $filterValue = Yii::$app->request->getQueryParam($filterName, '');
            $searchModel[$searchAttribute] = $filterValue;
            $searchColumns[] = [
                'attribute' => $searchAttribute,
                'filter' => '<input class="form-control" name="' . $filterName . '" value="' . $filterValue . '" type="text">',
                'value' => $searchAttribute,
            ];
            $arrayHelper = array_filter($arrayHelper, function ($item) use (&$filterValue, &$searchAttribute) {
                return strlen($filterValue) > 0 ? stripos('/^' . strtolower($item[$searchAttribute]) . '/', strtolower($filterValue)) : true;
            });
        }

        //Build ArrayProvider with the data for each attribute
        return new ArrayDataProvider([
            'allModels' => $arrayHelper,
            'pagination' => ['pageSize' => 1000, 'pageParam' => 'page'],
            'sort' => [
                'attributes' => $searchAttributes,
                'defaultOrder' => [
                    'date_created' => SORT_DESC,
                ]
            ],
        ]);
    }


}