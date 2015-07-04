<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Product;
use app\models\ProductField;
use app\models\Field;
use app\models\FieldType;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
		return $this->render('index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionKontakt()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('kontakt', [
                'model' => $model,
            ]);
        }
    }
			
	public function actionPage($id)
    {
		$model = Page::findOne($id);
		if($model == null)
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Page not found.']);
		
        return $this->render('page', [
			'model' => $model,
		]);
    }
	
	public function actionEditPage($id)
    {
		$model = Page::findOne($id);
		if($model == null)
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Page not found.']);
		
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			$model->save();
			return $this->render('page', [
				'model' => $model, 'message' => 'Lehekülg on edukalt muudetud!'
			]);
		} else {
			return $this->render('pageEdit', [
				'model' => $model,
			]);
		}
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionTest()
    {
        return $this->render('test');
    }

    public function actionSoodus()
    {
        return $this->render('soodus');
    }
	
    public function actionTooted()
    {	
		//$produktid = Product::find()->with('profield')->where(['active' => 1])->all();
		$produktid = Product::find()->with('product_field')->where(['active' => 1])->all();
		//$produktid = Product::find()->where(['active' => 1])->all();
		if($produktid == null)
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Page not found.']);
		
		/*foreach ($produktid as $p) {
			var_dump($p);
		}*/
		foreach ($produktid as $p) {
			$product_fields = ProductField::find()->where(['product_id' => $p->getAttribute('id')])->all();
			//foreach ($product_fields as $pf) {
			//	$fields = Field::find()->where(['id' => $pf->getAttribute('field_id')])->all();
			//	$pf->link('field', $fields);
			//}
			$p->link('product_field', $product_fields);
			//var_dump($p->getRelatedRecords());
		}
		
		/*echo "XXXXXXXXXXXXXXXXX";
		echo "XXXXXXXXXXXXXXXXX";
		echo "XXXXXXXXXXXXXXXXX";
		echo "XXXXXXXXXXXXXXXXX";
		echo "XXXXXXXXXXXXXXXXX";
		//$produktid->komponendid = "test";
		var_dump($produktid);
		die;*/
		
        return $this->render('product', [ 'model' => $produktid]);
        //return $this->render('product');
    }

    public function actionKasulikku()
    {
        return $this->render('kasulikku');
    }

    public function actionJarelmaks()
    {
        return $this->render('jarelmaks');
    }

    public function actionRent()
    {
        return $this->render('rent');
    }

    public function actionTeenused()
    {
        return $this->render('teenused');
    }

    public function actionRemont()
    {
        return $this->render('remont');
    }

    public function actionEkraanivahetus()
    {
        return $this->render('ekraanivahetus');
    }

    public function actionBoonused()
    {
        return $this->render('boonused');
    }
}
