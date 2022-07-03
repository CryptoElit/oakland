<?php

namespace common\components;


use common\models\Company;
use Yii;
use yii\base\Component;
use common\models\SystemUser;
use common\models\CorrespondenceTrackingStatus;
use common\models\Template;
use common\models\TemplateType;
use common\models\CorrespondenceTracking;
use common\models\Correspondence;
use common\models\CorrespondenceQueue;
use common\models\TemplateDeliveryChannel;
use common\models\ContactDetails;
use common\models\ContactType;
use common\models\User;
use common\models\UserType;
use common\models\CorrespondenceFiles;
use common\models\PolicyHolder;
use common\models\Creditor;
use yii\i18n\Formatter;
use common\models\PolicyCreditor;
use common\models\Status;
use kartik\alert\Alert;
use common\models\PolicyComment;

class MailComponent extends Component
{

	public $template_id;
	private $top;
	private $content;
	private $template_delivery_channel_id;
	private $to_user_id;
	private $bottom;
	private $subject;
	private $to_email;
	private $attachments = [];
	private $can_send = true;
	private $can_send_error;
	private $brandingImg;
    public $corrRow;

	public $apiCode;
	public $adminEmail;
    public $policy_id = NULL;
	public $systemFromMail;
	public $policy_cc_address;
	
	public function sendEmail($template,$to_user_id,$attachments=[], $policy = NULL, $policy_cc_address = NULL, $static_to_address = NULL, $mail_lock = false) {
		if (isset($to_user_id)){
			$this->to_user_id = $to_user_id;
		}
		
        $this->policy_cc_address = $policy_cc_address;
        $this->brandingImg = isset($policy->broker->companyBrandImage) ? \Yii::$app->awss3component->getInstance()->getObjectUrl($policy->broker->companyBrandImage->file_location) : 'https://admin.insuranceguard.co.za/images/report-bulkmail-logo.jpg';

        //Get the Template needed
		if (!empty($template)) {
			$this->template_id = $template;
			$this->set_template_content_and_subject();
		}
		if (!empty($to_user_id)){
			$this->set_to_email_address($to_user_id, $static_to_address);
			$this->substitute_email_content_User($to_user_id);
			$this->substitute_email_content_ChangePassword($to_user_id);
	
		}
		
		if (!empty($policy)){
			$this->substitute_email_subject_Policy($policy);
			$this->substitute_email_content_Policy($policy, $to_user_id);
            $this->policy_id = $policy->id;
		}
	
		if (isset($attachments)){
			$this->attachments = $attachments;
		}
		
		if ($this->can_send){

		 $this->buildAndSend($mail_lock);
            return $this->corrRow;
		}else{
			\Yii::$app->getSession()->setFlash(Alert::TYPE_WARNING, "No email address found, mail cannot be sent");
			return true;
		}
		
	
	}
	/**
	 * Build and send the email to Sendgrid
	 * @return boolean
	 */
	private function buildAndSend($mail_lock){
		$correspodence = new Correspondence();
		$correspodence->user_id = $this->to_user_id;
        $correspodence->policy_id = $this->policy_id;
		$correspodence->template_id = $this->template_id;
        $correspodence->correspondence_address = $this->to_email;
		$correspodence->content = $this->top.$this->content.$this->bottom;
		$correspodence->subject = $this->subject;
		$correspodence->policy_cc_address = $this->policy_cc_address;
		$correspodence->save(false);


		foreach ($this->attachments as $attachment){
			$correspodenceFile = new CorrespondenceFiles();
			$correspodenceFile->files_id = $attachment->id;
			$correspodenceFile->correspondence_id = $correspodence->id;
			$correspodenceFile->save(false);
		}
	
		$correspodenceTracking = new CorrespondenceTracking();
		$correspodenceTracking->correspondence_id = $correspodence->id;
		$correspodenceTracking->correspondence_tracking_status_id = CorrespondenceTrackingStatus::QUEUED_INTERNALLY;
		$correspodenceTracking->date_created = date('Y-m-d H:i:s');
		$correspodenceTracking->save(false);
        $this->corrRow = ['id' => $correspodence->id, 'date_created' => $correspodenceTracking->date_created, 'subject' => $correspodence->subject];

        if ($this->template_delivery_channel_id === TemplateDeliveryChannel::NON_QUEUED_EMAIL){
		    $ccAddress = [];
		    if(isset($correspodence->policy_cc_address)){
		        $ccAddress = array_merge($ccAddress, [$correspodence->policy_cc_address => $correspodence->policy_cc_address]);
		    }
		    return $this->submitToSendGrid($this->to_email, $this->subject, $correspodence, $this->attachments, $ccAddress);
		}else{
			$correspondenceQueue = new CorrespondenceQueue();
			$correspondenceQueue->correspondence_id = $correspodence->id;
			$correspondenceQueue->date_created = date('Y-m-d H:i:s');
			$correspondenceQueue->locked = $mail_lock;
			$correspondenceQueue->save(false);
			return true;
		}
	}
	
	public function submitToSendGrid($to_address, $subject, $correspondence, $attachments, $ccs_email_list){
		$sendgrid = new \SendGrid($this->apiCode);
		$mail = new \SendGrid\Mail\Mail();
		$mail->addTo(trim($to_address));
		$mail->setFrom($this->systemFromMail);
		if(!empty($ccs_email_list)){
		    $mail->addCcs($ccs_email_list);
		}
		
		$mail->setSubject($subject);
		$mail->addCustomArgs(["mail_id" => "".$correspondence->id]); // CustomArg must be a string....
		$mail->addContent("text/html", $correspondence->content);
			
		// attach all the attachments
		if (is_array($attachments)) {
			foreach ($attachments as $file) {
				if (isset($file)){
				    $tmp_file_name = \Yii::$app->params['uploadPath'] .'/'. $file->original_file_name;
				    if(!file_exists($tmp_file_name)){
				        \Yii::$app->awss3component->getInstance()->saveObjectToFile($file->file_location, $tmp_file_name );
				    }
				    $file_encoded = base64_encode(file_get_contents($tmp_file_name));
				    $mail->addAttachment($file_encoded, $file->application_type, $file->original_file_name);
				}
			}
		}
	
		//send the mail
		try {
		    $res = $sendgrid->send($mail);
		} catch (\Exception $e) {
		    Yii::info("MailComponent encountered an error : " . json_encode($e->getMessage()));
		    $res = $e->getCode();
		}

		if ($res->statusCode() && ($res->statusCode() == "200" || $res->statusCode() == "202")){
			Yii::info("Mail sent for Correspondence with id: ".$correspondence->id , __METHOD__ );
			$correspodenceTracking = new CorrespondenceTracking();
			$correspodenceTracking->correspondence_id = $correspondence->id;
			$correspodenceTracking->correspondence_tracking_status_id = CorrespondenceTrackingStatus::SUBMITTED;
			$correspodenceTracking->date_created = date('Y-m-d H:i:s');
			$correspodenceTracking->save(false);
			return true;
		} else {
			Yii::info("Mail NOT sent for Correspondence with id: ".$correspondence->id, __METHOD__ );
			Yii::info("Sendgrid Response code is: ". $res->statusCode() , __METHOD__ );
			Yii::info("Sendgrid Response body is: ". $res->body() , __METHOD__ );
			return false;
		}
	}
	
	private function set_template_content_and_subject(){

        $this->top = Template::find()->where('template_type_id = :template_type_id',[':template_type_id' => TemplateType::TEMPLATE_HEADER])->one()->template_content;
        $this->top = str_replace("<<company:brand_image>>", $this->brandingImg, $this->top);

        $this->bottom = Template::find()->where('template_type_id = :template_type_id',[':template_type_id' => TemplateType::TEMPLATE_FOOTER])->one()->template_content;
		$this->bottom = str_replace("<<telephone>>", Yii::$app->params['telephone'], $this->bottom);
		$this->bottom = str_replace("<<fax>>", Yii::$app->params['fax'], $this->bottom);
		$this->bottom = str_replace("<<website>>", Yii::$app->params['website'], $this->bottom);
		$this->bottom = str_replace("<<website_url>>", Yii::$app->params['website_url'], $this->bottom);
	
		$model = Template::findOne($this->template_id);
		$this->content = $model->template_content;
		$this->template_delivery_channel_id = $model->template_delivery_channel_id;
		$this->subject = $model->template_title;
	}
	
	private function set_to_email_address($to_user_id, $static_to_address = NULL){


		if ( isset(Yii::$app->params['environment']) && (strtolower(Yii::$app->params['environment']) === 'production' || strtolower(Yii::$app->params['environment']) === 'test')){
			$model = SystemUser::findOne($to_user_id);
	
			if (isset($model)){
				$this->to_email = $model->username;
			}else{
			    $contact = ContactDetails::findByUserAndType($to_user_id, ContactType::CREDIT_LIFE_SUBSTITUTION_EMAIL);
			    if (isset($contact)){
			        $this->to_email = $contact->value;
			    }else{
			        $contact = ContactDetails::findByUserAndType($to_user_id, ContactType::EMAIL);
			        if (isset($contact)){
			            $this->to_email = $contact->value;
			        }
			    }
			}
            // Over-ride if static is set	
            if (isset($static_to_address)) {
                if(isset(Yii::$app->params['environment']) && (strtolower(Yii::$app->params['environment']) === 'production')){
                    $this->to_email = $static_to_address;
                }else{
                    $this->to_email = Yii::$app->params['developmentEmail'];
                }
            }

        }else{
			$this->to_email = Yii::$app->params['developmentEmail'];
		}

        if (isset( $this->policy_cc_address) &&  isset(\Yii::$app->params['environment']) && strtolower(Yii::$app->params['environment']) === 'production' &&  ( $this->policy_cc_address == $this->to_email)){
                $this->policy_cc_address = NULL;
            }

		if (!isset($this->to_email)){
			$this->can_send = FALSE;
		}
		
	}
	
	private function substitute_email_content_User($to_user_id){
		$userModel = User::findOne($to_user_id);
		
		if (isset($userModel )){
			if ($userModel->user_type_id == UserType::SYSTEM_USER){
				$model = SystemUser::findOne($to_user_id);
				$this->content = str_replace("<<systemUsers:title>>", $model->titleType->description, $this->content);
				$this->content = str_replace("<<systemUsers:first_name>>", $model->name, $this->content);
				$this->content = str_replace("<<systemUsers:surname>>", $model->surname, $this->content);
				$this->content = str_replace("<<systemUsers:username>>", $model->username, $this->content);
			}elseif ($userModel->user_type_id == UserType::POLICY_HOLDER){
				$model = PolicyHolder::findOne($to_user_id);
				$this->content = str_replace("<<policyHolder:title>>", $model->titleType->description, $this->content);
				$this->content = str_replace("<<policyHolder:name>>", $model->name, $this->content);
				$this->content = str_replace("<<policyHolder:surname>>", $model->surname, $this->content);
			}elseif ($userModel->user_type_id == UserType::CREDITOR) {
                $model = Creditor::findOne($to_user_id);
                $this->content = str_replace("<<creditor:name>>", $model->name, $this->content);
            }elseif ($userModel->user_type_id == UserType::CLIENT){
            $model = Company::findOne($to_user_id);
            $this->content = str_replace("<<company:name>>", $model->name, $this->content);
        }
		}
	
		$this->content = str_replace("<<adminEmail>>", Yii::$app->params['adminEmail'], $this->content);
        $this->content = str_replace("<<infoEmail>>", Yii::$app->params['infoEmail'], $this->content);
        $this->content = str_replace("<<adminWebsite>>", Yii::$app->params['adminWebsite'], $this->content);
        $this->content = str_replace("<<supportEmail>>", Yii::$app->params['supportEmail'], $this->content);
        $this->content = str_replace("<<complaintsEmail>>", Yii::$app->params['complaintsEmail'], $this->content);
		$this->content = str_replace("<<website_url>>", Yii::$app->params['website_url'], $this->content);
        $this->content = str_replace("<<companyName>>", Yii::$app->params['companyName'], $this->content);
	}
	
	private function substitute_email_content_ChangePassword($to_user_id){
		if (!empty(strstr($this->content, "<<systemUsers:password>>"))){
			$this->content = str_replace("<<systemUsers:password>>", Yii::$app->passwordcomponent->return_new_user_password($to_user_id), $this->content);
		}
	}
	
	private function substitute_email_content_Policy($policy, $to_user_id){
        $finalDate =  date('Y-m-d', strtotime($policy->date_issued. ' + 1 year'));

        $brokerUser = $policy->broker->user_id;
        $brokerPhone     =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::PHONE)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::PHONE)->value : NULL;
        $brokerAdMail   =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::EMAIL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::EMAIL)->value : NULL;

         $brokerSupMail  =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::SUPPORT_EMAIL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::SUPPORT_EMAIL)->value : NULL;
        $brokerCompMail =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::COMPLAINT_EMAIL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::COMPLAINT_EMAIL)->value : NULL;
        $brokerInfoMail =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::INFO_EMAIL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::INFO_EMAIL)->value : NULL;
        $brokerWeb      =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::WEBSITE_URL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::WEBSITE_URL)->value : NULL;
        $complEmail      =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::COMPLIANCE_TEL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::COMPLIANCE_TEL)->value : NULL;
        $brokerPhysAdd  =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::PHYSICAL_ADDRESS)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::PHYSICAL_ADDRESS)->value : NULL;
        $brokerPostAdd  =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::POSTAL_ADDRESS)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::POSTAL_ADDRESS)->value : NULL;
        $brokerCell     =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::CELL)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::CELL)->value : NULL;
        $contactPerson  =  !empty(ContactDetails::findByUserAndType($brokerUser, ContactType::CONTACT_PERSON)) ? ContactDetails::findByUserAndType($brokerUser, ContactType::CONTACT_PERSON)->value : null;


        $this->content = str_replace("<<policyHolder:title>>", $policy->primaryPolicyHolder->titleType->description, $this->content);
		$this->content = str_replace("<<policyHolder:name>>", $policy->primaryPolicyHolder->name, $this->content);
		$this->content = str_replace("<<policyHolder:surname>>", $policy->primaryPolicyHolder->surname, $this->content);
		
		$this->content = str_replace("<<policyHolder:id_number>>", $policy->primaryPolicyHolder->id_number, $this->content);
		$this->content = str_replace("<<policy:number>>", $policy->policy_number, $this->content);

        $this->content = str_replace("<<policy:date_issued_year_inc>>", $finalDate, $this->content);
        $this->content = str_replace("<<policy:product_name>>", $policy->product->description, $this->content);
		$this->content = str_replace("<<sourceSystem:name>>", $policy->sourceSystem->name, $this->content);
        $this->content = str_replace("<<policy:broker>>", $policy->broker->name, $this->content);
        $this->content = str_replace("<<policy:broker_sup_mail>>", $brokerSupMail, $this->content);
        $this->content = str_replace("<<policy:broker_comp_mail>>", $brokerCompMail, $this->content);
        $this->content = str_replace("<<policy:broker_info_mail>>", $brokerInfoMail, $this->content);
        $this->content = str_replace("<<policy:website_url>>", $brokerWeb, $this->content);
        $this->content = str_replace("<<policy:compliance_tel>>", $complEmail, $this->content);
        $this->content = str_replace("<<policy:broker_cell>>", $brokerCell, $this->content);
        $this->content = str_replace("<<policy:broker_location>>", $brokerPhysAdd, $this->content);
        $this->content = str_replace("<<policy:broker_postal>>", $brokerPostAdd, $this->content);
        $this->content = str_replace("<<policy:contact_person>>", $contactPerson, $this->content);

		// Broker
        $this->content = str_replace("<<policy:fsb_number>>", $policy->broker->fsb_number, $this->content);
        $this->content = str_replace("<<policy:registration_number>>", $policy->broker->registration_number, $this->content);
        $this->content = str_replace("<<policy:broker_admin_mail>>", $brokerAdMail, $this->content);
        $this->content = str_replace("<<policy:broker_tel>>", $brokerPhone, $this->content);
		$this->content = str_replace("<<policyCreditor:accountNumber>>", PolicyCreditor::findAccountNumbersByPolicyAndCreditor($policy->id, $to_user_id), $this->content);
		$this->content = str_replace("<<policyCreditors:creditorsTable>>", $this->buildPolicyCreditorsTable($policy), $this->content);
		
		$this->content = str_replace("<<date>>", date('j F Y'), $this->content);
		
		$this->content = str_replace("<<policy:installment>>", \Yii::$app->formatter->asCurrency($policy->installment), $this->content);
		$this->content = str_replace("<<policy:total>>", \Yii::$app->formatter->asCurrency($policy->secured_insured_amount + $policy->unsecured_insured_amount), $this->content);
		$this->content = str_replace("<<policy:inception_date>>", date('01/m/Y', strtotime($policy->date_issued)), $this->content);
		if (!empty($policy->date_terminated)){
			$this->content = str_replace("<<policy:termination_date>>", date('d/m/Y', strtotime($policy->date_terminated)), $this->content);
		}

        $modelComment = PolicyComment::find()->where(['policy_id' => $policy->id])->orderBy(['id' => SORT_DESC])->one();

        $terminationReason = (isset($modelComment->comment) ? strtok($modelComment->comment, "\n") : 'Policy Terminated');
        
        $this->content = str_replace("<<policy:termination_reason>>", $terminationReason, $this->content);

        $this->content = str_replace("<<policy:policy_active_date>>", isset($policy->date_loaded) ?  date('Y-m-d',strtotime($policy->date_loaded)) : date('Y-m-d',strtotime($policy->date_balance_last_updated)), $this->content);

        $replacedPolicy = $policy->findByReplacedByPolicyId($policy->id);
        $this->content = str_replace("<<policy:replaced_installment>>", isset($replacedPolicy) ? \Yii::$app->formatter->asCurrency($replacedPolicy->installment) : \Yii::$app->formatter->asCurrency($policy->installment), $this->content);

    }
	
	private function substitute_email_subject_Policy($policy){
		$this->subject = str_replace("<<policyHolder:title>>", $policy->primaryPolicyHolder->titleType->description, $this->subject);
		$this->subject = str_replace("<<policyHolder:name>>", $policy->primaryPolicyHolder->name, $this->subject);
		$this->subject = str_replace("<<policyHolder:surname>>", $policy->primaryPolicyHolder->surname, $this->subject);
		$this->subject = str_replace("<<policyHolder:id_number>>", $policy->primaryPolicyHolder->id_number, $this->subject);
		$this->subject = str_replace("<<policy:number>>", $policy->policy_number, $this->subject);
        $this->subject = str_replace("<<policy:fsb_number>>", $policy->broker->fsb_number, $this->subject);
        $this->subject = str_replace("<<policy:broker>>", $policy->broker->name, $this->subject);
        $this->subject = str_replace("<<policy:registration_number>>", $policy->broker->registration_number, $this->subject);

    }
	
	private function buildPolicyCreditorsTable($policy){
	    $policyCreditors = [];
	    foreach ($policy->policyCreditors as $creditor){
	        if ($creditor->status_id == Status::STATUS_ACTIVE){
	            array_push($policyCreditors, [
	                    'creditor_name'=> $creditor->creditor->name,
	                    'category'=> $creditor->creditor->creditorType->description,
	                    'account_no' => $creditor->account_number,
	                    'insured' => 'R'.number_format(floatval($creditor->insured_amount),2),
	                    'premium' => 'R'.number_format(floatval($creditor->insured_premium),2)
	            ]);
	        }
	    }

	    array_push($policyCreditors, [
	            'creditor_name'=> 'Identity Theft',
	            'category'=> 'Identity Theft',
	            'account_no' => 'n/a',
	            'insured' => 'R'.number_format(floatval(10000),2),
	            'premium' => 'R'.number_format(floatval(0.00),2)
	    ]);
	
	    $table = "<table border='1' cellpadding='3' cellspacing='0' style='width:100%'>
                    <tr align='center' >
                        <th>Credit Provider</th>
                        <th>Category</th>
                        <th>Account No</th>
    	                <th>Sum Insured</th>
	                    <th>Premium</th>
                    </tr>";
	
	
	    foreach ($policyCreditors as $creditor){
	        $table = $table."<tr align='center'>
                                <td>".$creditor['creditor_name'] ."</td>
                                <td>".$creditor['category'] ."</td>
                                <td>".$creditor['account_no'] ."</td>
                                <td>".$creditor['insured'] ."</td>
                                <td>".$creditor['premium'] ."</td>
                             </tr>";
	    }
	
	    $table = $table."</table>";
	
	    return $table;
	
	}
}