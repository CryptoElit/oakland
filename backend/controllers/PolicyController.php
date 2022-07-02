<?php

namespace internal\modules\v1\controllers;


use common\components\pabx\PabxOutboundCallComponent;
use common\models\ContactDetails;
use common\models\ContactType;
use common\models\FileType;
use common\models\Policy;
use common\models\PolicyMailer;
use common\models\PolicyNominatedBenificiary;
use common\models\PolicyStatus;
use common\models\User;
use common\models\Template;
use common\models\TemplateDeliveryChannel;
use common\models\TransactionType;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use internal\controllers\ServeController;


class PolicyController extends ServeController
{

    public function actionSearch()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);


        $query = "SELECT 
    `policy`.`id` AS `id`,
    date(`date_of_application`) as date_of_application,
    `policy_holder`.`name` AS `policy_holder_name`,
    ifnull(date(date_issued), 'N/A') as date_issued,
    ifnull(policy_number, 'N/A') as policy_number,
    `policy_holder`.`surname` AS `policy_holder_surname`,
    `policy`.`policy_status_id` AS `status`,
    `source_system`.`name` AS `source`,
    `is_joint`,
     `contact_details`.`value` AS `contact`
FROM
    `policy`
        LEFT JOIN
    `policy_holder` ON `policy`.`primary_policy_holder_id` = `policy_holder`.`user_id`
      
        LEFT JOIN
    `company` `source_system` ON `policy`.`source_system_id` = `source_system`.`user_id`
        LEFT JOIN
    `contact_details` ON `policy`.`primary_policy_holder_id` = `contact_details`.`user_id`
WHERE
    `contact_type_id` = 2 order by policy.id desc ";

        return  \Yii::$app->getDb()->createCommand($query)->queryAll();


    }

    public function actionViewPolicy()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $policyData = [];
        $policyData['policy'] = $policy = Policy::findOne($requestDataRes->params->id);
        $policyData['policyHolderData'] = array_merge( ['policyHolder' => $policy->primaryPolicyHolder] , ["occupation" => $policy->primaryPolicyHolder->occupation]);
        $policyData['secPolicyHolder'] = $policy->secondaryPolicyHolder;
        $policyData['policyProduct'] = array_merge( ['product' => $policy->product->description], ['broker' =>  $policy->broker]);
        $policyData['sourceSystem'] = $policy->sourceSystem;
        $policyData['replacedBy'] = $policy->replacedBy;
        $policyData['contactDetail'] = $policy->contactDetailMap;
        $policy->fileTypeArr = [FileType::CLAIM_REPUDIATED_LETTER, FileType::CLAIM_APPROVAL_LETTER, FileType::CLAIM_DOCUMENT, FileType::AFFIDAVIT, FileType::BANK_STATEMENTS_3_MONTHS, FileType::CAS_DOCUMENT, FileType::CHILD_BIRTH_CERTIFICATE, FileType::COMPANY_NOTICE, FileType::DEATH_CERTIFICATE, FileType::DOCTORS_REPORT, FileType::DOCTORS_LETTER, FileType::EMPLOYER_LETTER, FileType::EMPLOYMENT_CONTRACT, FileType::IDENTITY_DOCUMENTS, FileType::LETTER_OF_AUTHORITY, FileType::LIQUIDATION_CERTIFICATE, FileType::MEDICAL_REPORT, FileType::NOMINATED_BENEFICIARY_IDENTITY_DOCUMENT, FileType::NOTICE_OF_DEATH_DHA_1663_BI1680, FileType::POLICE_STATEMENT, FileType::POST_MORTEM_REPORT, FileType::PROOF_OF_BANK_DETAILS, FileType::PROOF_OF_PAYMENT, FileType::PROOF_OF_RESIDENCE, FileType::REMUNERATION_LETTER_EMPLOYER, FileType::RETRENCHMENT_FORM_HR, FileType::RETRENCHMENT_LETTER, FileType::ROAD_ACCIDENT_REPORT, FileType::SUPPORTING_MEDICAL_EVIDENCE, FileType::CRF_CLAIM_FORM, FileType::AFFIDAVIT_MONTHLY_FOLLOW_UP, FileType::BANK_STATEMENT_MONTHLY_FOLLOW_UP, FileType::CONFIRMATION_OF_EMPLOYMENT];
        $policyData['claims'] = $policy->policyFilesMap;

        $policy->fileTypeArr =  [FileType::POLICY_ROA, FileType::POLICY_RAR, FileType::POLICY_DOCUMENT, FileType::CREDITOR_CERTIFICATE, FileType::POLICY_HOLDER_TERMINATION_LETTER, FileType::CREDITOR_TERMINATION_LETTER, FileType::REPORT, FileType::CREDIT_PROTECTION, FileType::CERTIFICATE_OF_BALANCE, FileType::PROOF_OF_TRANSMISSION, FileType::CANCELATION_NOTICE, FileType::MANDATE_CLI, FileType::NOMINATED_BENEFICIARY_FORM, FileType::CLIENT_RECORD_OF_ADVICE_RA, FileType::REPLACEMENT_ADVICE_RECORD_RAR, FileType::CANCELLATION_INDEMNITY_FORM, FileType::GENERAL, FileType::VOICE_RECORDING_MP3_WAV_FILE, FileType::FICA, FileType::FICA_UN_SANCTIONS_LIST, FileType::FICA_TFS_LIST, FileType::FICA_CHECKLIST, FileType::MANDATE_SHORT_TERM, FileType::SHORT_TERM_QUOTE_REQUEST, FileType::SHORT_TERM_ROA, FileType::SHORT_TERM_ANNEXURE_A_REPLACEMENT];
        $policyData['files'] = $policy->policyFilesMap;
        $policyData['comments'] = $policy->policyComments;
        $policyData['creditors'] = $policy->policyCreditorsMap;
        $policy->transaction_type = [TransactionType::ACTUAL,  TransactionType::NET_SETTLEMENT];
        $policyData['transactions']['actualTrans'] = $policy->policyFinancialTransactionsMap;
        $policy->transaction_type = [TransactionType::SYSTEM_GENERATED_EXPECTED];
        $policyData['transactions']['expectedTransNatv'] = $policy->policyFinancialTransactionsMap;
        $policy->transaction_type = [TransactionType::EXPECTED];
        $policyData['transactions']['expectedTransThird'] = $policy->policyFinancialTransactionsMap;
        $policy->correspondence_type = [TemplateDeliveryChannel::SMS_ADHOC, TemplateDeliveryChannel::SMS_BULK, TemplateDeliveryChannel::SMS_SINGLE];
        $policyData['correspondence']['smsCors'] = $policy->policyCorrespondenceMap;
        $policy->correspondence_type = [TemplateDeliveryChannel::NON_QUEUED_EMAIL, TemplateDeliveryChannel::QUEUED_EMAIL];
        $policyData['correspondence']['emailCors'] = $policy->policyCorrespondenceMap;
        $policyData['nominatedBens'] = $policy->policyNominatedBeneficiariesMap;
        return  $policyData;


    }

    public function actionFetchCallHistory() {


        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

    $callH = (new PabxOutboundCallComponent($requestDataRes->params->id))->callHistory(true);

    if (isset($callH) && !empty($callH)) {
        return ['response' => true, 'callHistory' => $callH];
    }
        return ['response' => false, 'callHistory' => null];
    }


    public  function actionAddNominatedBeneficiary() {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        // {"id":"5866","name":"Joseph","surname":" Harty","id_number":"9708203144031","address":"111 Test","tel_home":"02111111111","tel_work":"0213714443","tel_mobile":"0811111111","email":"test@test.com","relationship_type_id":"10","percent_allocation":"22"}
        $id = $requestDataRes->params->policyID;

        $modelBenefit = PolicyNominatedBenificiary::findOne($requestDataRes->params->nomID);
        $modelBenefit->load(Yii::$app->request->post());
        $modelBenefit->save();
        (new PolicyNominatedBenificiary())->totlPercAllBen($id);
        $modelMailer = new PolicyMailer();
        $modelMailer->policy = $policy = Policy::findOne($id);
        $modelFiles = $modelMailer->getFilesForAttachmentBeneficiaryUpdate([PolicyStatus::STATUS_ISSUED, PolicyStatus::STATUS_ACTIVE, PolicyStatus::STATUS_POLICY_INACTIVE]);
        // Adding the CC Address from the Policy Holder Level.
        $ccContactDetails = ContactDetails::findByUserAndType($policy->primaryPolicyHolder->user_id, ContactType::POLICY_CC_EMAIL);
        Yii::info("--->>> Sending Policy Beneficial Email with the updated Policy Schedule");
        Yii::$app->mailcomponent->sendEmail(Template::UPDATE_NOMINATED_BENEFICIARY, $policy->primary_policy_holder_id, $modelFiles, $policy, isset($ccContactDetails) ? $ccContactDetails->value : NULL);

    }
    public function actionCreate()
    {
        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        $sysUserNew = new User();
        if ($sysUserNew->load($requestDataRes)) {
            return $sysUserNew;
        }
        return  false;

    }



}


