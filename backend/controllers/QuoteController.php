<?php

namespace internal\modules\v1\controllers;


use common\models\Quote;
use internal\controllers\ServeController;
use Yii;


class QuoteController extends ServeController
{

	public function actionSearch()
	{

		$requestData = Yii::$app->request->rawBody;
		$requestDataRes = json_decode($requestData);

		$query = "SELECT
    quote.id as id,
    date_of_quote,
    quote_reference,
    version,
    policy_holder.id_number,
    policy_holder.surname,
    quote_status.description as status,
    is_joint
FROM
    quote
        LEFT JOIN
    quote_status ON quote.quote_status_id = quote_status.id
        LEFT JOIN
    policy_holder ON quote.primary_policy_holder_id = policy_holder.user_id ";

		$query .= " order by quote.id DESC ";
		return Yii::$app->getDb()->createCommand($query)->queryAll();
	}


	public function actionViewQuote()
	{

		$requestData = Yii::$app->request->rawBody;
		$requestDataRes = json_decode($requestData);


		$quote = Quote::findModel($requestDataRes->params->id);

		$quoteData = array_merge(['quote' => $quote->attributes, 'creditorSource' => $quote->creditorSource, 'policyHolderData' => [ 'policyHolder' => $quote->primaryPolicyHolder, "policyHoldertitle" => $quote->primaryPolicyHolder->titleType, "contactDetails" => $quote->primaryPolicyHolder->contactDetailsMap], "occupation" => $quote->primaryPolicyHolder->occupation, "status" => $quote->quoteStatus, 'product' => $quote->product, 'broker' => $quote->broker, 'company' => $quote->company->name]);
		if (isset($quote->secondaryPolicyHolder)) {
			$quoteData['secPolicyHolderData'] =  ['secPolicyHolder' => $quote->secondaryPolicyHolder, "secPolicyHolderTitle" => $quote->secondaryPolicyHolder->titleType,  "secContactDetails" => $quote->secondaryPolicyHolder->contactDetailsMap];
		} else {
			$quoteData['secPolicyHolderData'] = false;
		}

		$quoteData['creditors'] = $quote->quoteCreditors;
		$quoteData['comments'] = $quote->quoteComments;
		$quoteData['files'] = $quote->quoteFiles;
		$quoteData['quoteVersions'] = $quote->primaryPolicyHolder->getAllQuotes($requestDataRes->params->id);

		return $quoteData;


	}

}


