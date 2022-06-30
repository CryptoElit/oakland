<?php

namespace internal\modules\v1\controllers;


use common\models\PoliciesForDateRangeForm;
use Yii;
use internal\controllers\ServsolController;


class ReportController extends ServsolController
{

    public function actionGenerateReport()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);
        if (!empty($requestDataRes)) {
            $model = new PoliciesForDateRangeForm();
            $model->dateFormat = PoliciesForDateRangeForm::MONTHLY;

            $model->startDate = $requestDataRes->start_date;
            $model->endDate = $requestDataRes->end_date;
            if (isset($requestDataRes->company)) {
                $model->company = $requestDataRes->company;
            }

            $reportsComponent = Yii::$app->reportscomponent;

            switch ($requestDataRes->report_name) {
                case 'policies-issued':
                    $report =   $reportsComponent->policiesForDateRange($model, $requestDataRes->user_id);
                    break;
                case 'premiums-paid':
                  $report =  $reportsComponent->premiumsPaid($model, $requestDataRes->user_id);
                    break;
                case 'premiums-unpaid':
                    $report =    $reportsComponent->premiumsUnpaid($model, $requestDataRes->user_id);
                    break;
                case 'project-delta':
                    $report =   $reportsComponent->projectDeltaReport($model->startDate, $model->endDate, true, $requestDataRes->user_id);
                    break;
                case 'terminated-policies':
                    $report =  $reportsComponent->terminatedPoliciesReport($model, $requestDataRes->user_id);
                    break;
                case 'rejected-applications':
                    $report =  $reportsComponent->rejectedApplications($model, $requestDataRes->user_id);
                    break;
                case 'financial-forecast':
                    $report =   $reportsComponent->financialForecastReport($model, $requestDataRes->user_id);
                    break;
                case 'net-settlements':
                    $report =   $reportsComponent->policiesNetSet($model, $requestDataRes->user_id);
                    break;
                case 'quotes':

                    $report =  $reportsComponent->quoteReport($model, $requestDataRes->user_id);
                    break;
                default:
                    return json_encode(["message" => "Could Not Generate Report, please try again"]);
            }
            return   $report;
        }

    }
}


