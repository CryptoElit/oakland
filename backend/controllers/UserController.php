<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use app\controllers\ServeController;
use app\models\LoginForm;



class UserController extends ServeController
{



    public function actionLogin()
    {

        $requestDataRes = \Yii::$app->request->post();

        $model = new LoginForm();

        $model->username = $requestDataRes['username'];
        $model->password = $requestDataRes['password'];

        \Yii::info("Username: ". $model->username , __METHOD__);

        if ($model->login()) {
            $user = $model->getUser();
            $accT = \Yii::$app->getUser()->identity;
            \Yii::$app->cache->set($accT, $user, 36600);



            return $user;
        }

            foreach ($model->getErrors() as $key => $error) {
                $errorArray['errors'] = ['type' => $key, 'message' => $error];
            }

            return $errorArray;

    }

    public function actionForgot(){
        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);

        \Yii::info("Starting Console Controller", __METHOD__);


        $model = new ForgotPasswordForm();

        $model->email = $requestDataRes->username;

        \Yii::info("Email: ". $model->email , __METHOD__);

        if ($model->validate()) {
            $model_system_user = User::findByUsername($model->email);
            if ($model_system_user != null) {
                if (Yii::$app->mailcomponent->sendEmail(TemplateType::PASSWORD_RESET, $model_system_user->id)) {
                    $model_system_user->users->status_id = Status::STATUS_PASSWORD_RESET;
                    $model_system_user->save();
                    return ['message' => "Forgot Password e-mail sent."];
                } else {
                    return ['message' => "The email could not be sent, please retry or contact support at support@servsol.co.za"];
                }
                return $this->redirect(['login']);
            } else {
                return ['message' => "Email address does not exist"];
            }
        } else {
            return ['message' => "Email address does not exist"];
        }
    }


    public function actionSearch()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);


$query = "SELECT
    system_user.user_id AS id,
    title_type_id,
    system_user.name,
    surname,
    id_number,
    system_user_company.company_id,
    company.name as company_name,
    username,
    last_login,
       ai.name as user_role,
    status.id AS status,
           status.description AS status_description,
       '' as file
FROM
    system_user
        LEFT JOIN
    system_user_company ON system_user.user_id = system_user_company.system_user_id
        LEFT JOIN
    company ON system_user.company_id = company.user_id
        LEFT JOIN
    auth_assignment aa ON system_user.user_id = aa.user_id
        LEFT JOIN
    auth_item ai ON aa.item_name = ai.name
        LEFT JOIN
    `user` ON user.id = system_user.user_id
      LEFT JOIN
    `status` ON user.status_id = status.id
   WHERE ai.type = 1
    ";

        $ids = SystemUserCompany::getAllCompanyIDsForUser();
        if (!empty($ids)){
            $query .= " AND system_user_company.company_id IN ('.$ids.')";
        }

        $query .= " GROUP BY system_user.user_id";

        $create =  \Yii::$app->getDb()->createCommand($query);

        if (!empty($requestData) && $requestDataRes->id != NULL) {
            return $create->queryOne();
        }
        return $create->queryAll();
    }

public function  actionView() {

    $requestData = \Yii::$app->request->rawBody;
    $requestDataRes = json_decode($requestData);

    $user = User::findOne($requestDataRes->id)->systemUser;

    $data['user'] = [
        'uuid' => $user->user_id,
        'from' => 'finsafe',
        'role' => $user->getRole(),
        'data' => [
            'titletype' => $user->titleType->description,
            'displayName' => $user->name,
            'displaySurname' => $user->surname,
            'extension' => $user->extension,
            'company' => $user->company->trading_name,
            'photoURL' => 'assets/images/avatars/Abbott.jpg',
            'email' => $user->username,
            'permissions' => $user->systemUserCompanyMap
        ]
    ];

    return $data;
}

    public function actionSystemUserCompany()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);


        $query = "SELECT
    system_user_id AS user_id,
    company.name AS company_name,
    company_id,
    permission.id AS permission_type,
    can_edit
FROM
    `system_user_company`
        LEFT JOIN
    `company` ON `system_user_company`.`company_id` = `company`.`user_id`
        LEFT JOIN
    `permission` ON `system_user_company`.`permission_id` = `permission`.`id`
     WHERE system_user.company =`system_user_id`= $requestDataRes->id";

      return \Yii::$app->getDb()->createCommand($query)->queryAll();

    }


    public function actionUpdate()
    {

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);
        $id = isset($requestDataRes) ? $requestDataRes->id : NULL;

        $model = $this->findModel($id);
        $model_User = $model->users;

        $auth = \Yii::$app->authManager;
        $model->roles = ArrayHelper::map($auth->getRoles(), 'name', 'name');

        if (User::retrieveRole(\Yii::$app->user->id) != User::FINSAFE_SYSTEM_ADMIN){
            unset($model->roles[User::FINSAFE_SYSTEM_ADMIN]);
        }
        $existingCompany = $model->company_id;

            $model->selected_role = $_POST['User']['selected_role'];
            $model->company_id = intval($_POST['User']['company_id']);
            if ($model_User->validate()){
                $model_User->save();
                if($model->validate()){
                    $model->save();

                    // Company changed, let's revoke all system access and recreate it.
                    if ($existingCompany != $model->company_id){
                        SystemUserCompany::revokeAllAccess($id, $existingCompany);
                        //Create and insert all the relevant SystemUserCompany Permissions, set all to view initially.
                        $permissions = Permission::find()->all();

                        foreach ($permissions as $permission){
                            Yii::info("Adding Permission for users");
                            $systemUserCompany = new SystemUserCompany();
                            $systemUserCompany->system_user_id = $model->user_id;
                            $systemUserCompany->company_id = $model->company_id;
                            $systemUserCompany->permission_id = $permission->id;
                            $systemUserCompany->can_edit = 0;
                            if ($systemUserCompany->validate()){
                                $systemUserCompany->save();
                            }else{
                                Yii::info($systemUserCompany->getErrors());
                            }
                        }
                    }


                    //Revoke All Roles
                    $auth->revokeAll($model->user_id);
                    if ($model_User->status_id != Status::STATUS_INACTIVE &&
                        $model_User->status_id != Status::STATUS_TERMINATED &&
                        $model_User->status_id != Status::STATUS_SUSPENDED) {
                        //Assign the selected Role
                        $auth->assign($auth->getRole($model->selected_role), $model->user_id);
                    }

                    return json_encode(['status' => 200, 'message' => 'System User successfully updated']);

                }else{
                    return json_encode(['status' => 502, 'message' =>  $model->getErrors()]);

                }
            }

    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {


        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);
        $userRole = isset($requestDataRes) ? $requestDataRes->selected_role : NULL;
        $user_id = isset($requestDataRes) ? $requestDataRes->user_id : NULL;
        $model = new User();
        $model_User = new User();
        $model_User->status_id = Status::STATUS_NEW;
        $model_User->user_type_id = UserType::SYSTEM_USER;
        $model_User->date_created = date('Y-m-d H:i:s');

        $auth = \Yii::$app->authManager;
        $model->roles = ArrayHelper::map($auth->getRoles(), 'name', 'name');

        if (User::retrieveRole(Yii::$app->user->id) != User::FINSAFE_SYSTEM_ADMIN){
            unset($model->roles[User::FINSAFE_SYSTEM_ADMIN]);
            $model->company_id = SystemUserCompany::getFirstCompanyForUser($user_id);
        }

        if ($model->load(Yii::$app->request->post())) {
            //If no system user was selected in the form, use the company of the logged in system user
            if (!isset($_POST['User']['company_id'])){
                $model->company_id = isset(User::findOne($user_id)->company_id) ? User::findOne($user_id)->company_id : null;
            }
            $model->selected_role = $_POST['User']['selected_role'];
            if ($model_User->validate()){
                if($model->validate()){
                    $model_User->save();
                    $model->user_id = $model_User->id;
                    $model->save();
                    $resultSuccess = false;
                    if(Yii::$app->mailcomponent->sendEmail(TemplateType::REGISTRATION,$model->user_id)) {
                        Yii::info("The email has been sent");
                        $resultSuccess = json_encode(['status' => 200, 'message' => 'Registration Email Sent']);

                    }else{
                        Yii::info("The email could not be sent, please retry or contact support at support@servsol.co.za");
                        return json_encode(['status' => 503, 'message' => 'The email could not be sent, please retry or contact support at support@servsol.co.za']);

                    }

                    //Assign the selected Role
                    $auth->assign($auth->getRole($model->selected_role), $model->user_id);

                    //Create and insert all the relevant SystemUserCompany Permissions, set all to view initially.
                    $permissions = Permission::find()->all();

                    foreach ($permissions as $permission){
                        Yii::info("Adding Permission for users");
                        $systemUserCompany = new SystemUserCompany();
                        $systemUserCompany->system_user_id = $model->user_id;
                        $systemUserCompany->company_id = $model->company_id;
                        $systemUserCompany->permission_id = $permission->id;
                        $systemUserCompany->can_edit = 0;
                        if ($systemUserCompany->validate()){
                            $systemUserCompany->save();
                        }else{
                         return $systemUserCompany->getErrors();
                        }
                    }
             return $resultSuccess;
                }
            }
        }
    }


    public function actionUpdatePassword()
    {
        $model_changePassword = new ChangePassword();

        $requestData = \Yii::$app->request->rawBody;
        $requestDataRes = json_decode($requestData);
        $model_changePassword->currentPassword = $requestDataRes->currentPassword;
        $model_changePassword->newPassword = $requestDataRes->newPassword;
        $model_changePassword->newPasswordRepeat = $requestDataRes->newPasswordRepeat;

        if($model_changePassword->validate()){
            $model_changePassword->validateCurrentPassword();
            $model_changePassword->saveNewPassword();
            return ['response' => true, 'message' => 'Password Updated Successfully'];
        }else{
            return ['response' => false, 'message' => json_encode($model_changePassword->getFirstErrors())];
        }
    }


}


