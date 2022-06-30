<?php

namespace internal\modules\v1\controllers;


use common\components\PolicyTransactionsComponent;
use common\models\FileType;
use common\models\FileUploadForm;
use common\models\Quote;
use common\models\TransactionFile;
use internal\controllers\ServsolController;
use Yii;
use yii\base\BaseObject;


class ManualTransactionsController extends ServsolController
{

	public function actionSearch()
	{

		$requestData = Yii::$app->request->rawBody;

        $query = TransactionFile::find();
             $query->select(['transaction_file.id', 'creditor_source_id', 'file_type_id', 'files_id', 'original_file_name', 'description']);
        $query->joinWith(['fileType']);
        $query->joinWith(['files']);


		return $query->asArray()->orderBy('files_id DESC')->all();

	}


	public function actionView()
	{

		$requestData = Yii::$app->request->rawBody;
		$requestDataRes = json_decode($requestData);


		$model = TransactionFile::findOne($requestDataRes->params->id);


		$modelData['systemUser'] = $model->user->systemUser;
		$modelData['file'] = $model->files;
        $modelData['fileType'] = $model->fileType;

        if (isset(Yii::$app->params['environment']) && (strtolower(Yii::$app->params['environment']) !== 'unit')) {
            $tmp_file_name = Yii::$app->params['uploadPath'] . '/' . $model->files->original_file_name;
            if (!file_exists($tmp_file_name)) {
                Yii::$app->awss3component->getInstance()->saveObjectToFile($model->files->file_location, $tmp_file_name);
            }
        } else {
            if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', null])) {
                $base_path = '/srv/www/html/finsafe/';
            } else {
                $base_path = '/srv/www/finsafe/current';
            }
            $base_path = getenv('CODESHIP') ? '/home/rof/clone' : $base_path;
            $base_path = getenv('TRAVIS') ? getenv('TRAVIS_BUILD_DIR') : $base_path;
            $tmp_file_name = $base_path . '/common/tests/_data/test-transact.csv';
        }
        $data = Yii::$app->filehandlingcomponent->allocateNewTransactPerDC($tmp_file_name, $model->creditor_source_id, true);
        $banner = ($model->file_type_id == FileType::TRANSACTIONS_MANUAL_SUCCESS ? 'success' : 'danger');
        $message = ($banner == 'success' ? "Upload was Successful. All transactions were processed without encountering errors" : 'Transaction Upload Failed - ' . $data['error']);
        $modelData['fileRes'] = ['data' => $data, 'banner' => $banner, 'message' => $message];

        return ['response' => true, 'transaction' => $modelData];



	}
	
    public function actionUploadTransaction()
    {
        $modelFileForm = new FileUploadForm();

        $modelFileForm->file = $_FILES['file'];
        $modelFileForm->creditor_source = \Yii::$app->request->post('creditor_source_id');

        $transaction = (new PolicyTransactionsComponent())->uploadTransactionFile($modelFileForm);

        return $transaction && empty($transaction['error'])? ['response' => true, 'message' => 'Transactions have been allocated & Transaction CSV File Saved', 'rowData' => $transaction] : ['response' => false, 'message' => empty($transaction['error']) ? 'Failed to Uploaded Transactions. Unsuccessful Document Saved for Historical Review' : $transaction['error']];

        }



}


