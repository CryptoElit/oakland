<?php

namespace app\controllers;

use yii\filters\Cors;
use yii\rest\Controller;

class ServeController extends Controller
{

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        if (\Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', 'http://localhost:3000');
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Headers', 'authorization, X-Requested-With, content-type, x-csrf-token');
            \Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET');
            \Yii::$app->end();
        } else {
            \Yii::$app->getResponse()->getHeaders()->set('Origin', '*');
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Request-Method', 'POST GET');
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Credentials', 'true');
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Max-Age', '3600');
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Headers', 'authorization, X-Requested-With, content-type, x-csrf-token');
        }
        return true;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors ['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Request-Method' => ['POST', 'GET', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,                 // Cache (seconds)
                'Access-Control-Allow-Headers' => ['authorization', 'X-Requested-With', 'content-type', 'x-csrf-token']
            ],
        ];


        return $behaviors;

    }
}