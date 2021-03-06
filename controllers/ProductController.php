<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use app\models\ProductForMod;
use app\models\ProductField;
use app\models\Field;
use app\models\FieldType;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii2mod\cart\Cart;
use zxbodya\yii2\galleryManager\GalleryManagerAction;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

	public function actions()
	{
		return [
		   'galleryApi' => [
			   'class' => GalleryManagerAction::className(),
			   // mappings between type names and model classes (should be the same as in behaviour)
			   'types' => [
				   'product' => Product::className()
			   ]
		   ],
		];
	}

    /**
     * Lists all Product models.
     * @return mixed
     */ 
    public function actionIndex()
    {
		
		if(Yii::$app->getRequest()->getPathInfo() == 'product')
		{
			$models = Product::find()->where(['=', 'cut_price', 0])->all();		
			$soodus = false;
		}
		else
		{	
			$models = Product::find()->where(['>', 'cut_price', 0])->all();
			$soodus = true;
		}
		foreach($models as $model)
		{
			$model->product_field = ProductField::find()->where(['product_id' => $model->getAttribute('id')])->all();			
			$model->field_type[] = FieldType::find()->orderBy('order_by')->all();
			foreach ($model->product_field as $pf) {
				$field = Field::find()->where(['id' => $pf->getAttribute('field_id')])->all();
				$model->field[] = $field;
			}
		}

        return $this->render('index', [
            'models' => $models,
			'soodus' => $soodus,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$model = $this->findModel($id);
		
		if($model->cut_price > 0){
			$soodus = true;
		} else {
			$soodus = false;
		}	
		
        return $this->render('view', [
            'model' => $model,
			'soodus' => $soodus,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
		if(!$this->IsAdmin())
		{
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Puuduvad piisavad õigused.']);
		}
        $model = new Product();
			$model->setAttribute('mfr', '-');
			$model->setAttribute('model', '-');
			$model->setAttribute('description', '-');
			$model->setAttribute('price', 0);
			$model->setAttribute('cut_price', 0);
			$model->setAttribute('stock', 0);
			$model->setAttribute('active', 1);
			
		$model->save();
		
		return $this->redirect(['update', 'id' => $model->id]);

    }
	
	public function actionCreateCut()
	{
		if(!$this->IsAdmin())
		{
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Puuduvad piisavad õigused.']);
		}
       $model = new Product();
			$model->setAttribute('mfr', '0');
			$model->setAttribute('model', '0');
			$model->setAttribute('description', '0');
			$model->setAttribute('price', 0);
			$model->setAttribute('cut_price', 0);
			$model->setAttribute('stock', 0);
			$model->setAttribute('active', 1);
			
		$model->save();
		
		return $this->redirect(['update', 'id' => $model->id]);
	
	}

	public function actionCopy()
    {
		if(!$this->IsAdmin())
		{
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Puuduvad piisavad õigused.']);
		}
		$id = Yii::$app->getRequest()->getQueryParam('id');
		$product = Product::findOne($id);
		$product_field = ProductField::find()->where(['product_id' => $id])->all();
	
		$new_product = new Product();
		$new_product->setAttribute('mfr', $product->getAttribute('mfr'));
		$new_product->setAttribute('model', $product->getAttribute('model'));
		$new_product->setAttribute('price', $product->getAttribute('price'));
		$new_product->setAttribute('cut_price', $product->getAttribute('cut_price'));
		$new_product->setAttribute('stock', $product->getAttribute('stock'));
		$new_product->setAttribute('active', $product->getAttribute('active'));
		$new_product->setAttribute('description', $product->getAttribute('description'));
		$new_product->save();
		
		foreach($product_field as $pf) {
			$new_product_field = new ProductField();
			$new_product_field->setAttribute('product_id', $new_product->getAttribute('id'));
			$new_product_field->setAttribute('field_id', $pf->getAttribute('field_id'));
			$new_product_field->save(false);
		}
		
		if($product->getAttribute('cut_price')==0){
			$produktid = Product::find()->where(['active' => 1])->andWhere(['<=', 'cut_price', '0'])->all();
		} else {
			$produktid = Product::find()->where(['active' => 1])->andWhere(['>=', 'cut_price', '1'])->all();
		}
		
		if($produktid == null)
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Page not found.']);
			
		foreach ($produktid as $p) {
			$product_fields = ProductField::find()->where(['product_id' => $p->getAttribute('id')])->all();
			foreach ($product_fields as $pf) {
				$field = Field::find()->where(['id' => $pf->getAttribute('field_id')])->all();
				$p->field[] = $field;
				foreach ($field as $f) {
					$field_type = FieldType::find()->where(['id' => $f->getAttribute('type_id')])->all();
					$p->field_type[] = $field_type;
				}
			}
			$p->product_field = $product_fields;
		}
		
		if($product->cut_price > 0){
			return $this->redirect(['index']);
		} else {
			return $this->redirect(['/product']);
		}
    }
	
    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
		if(!$this->IsAdmin())
		{
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Puuduvad piisavad õigused.']);
		}
        $model = $this->findModel($id);
		
		if($model->getAttribute('cut_price') == null){
			$model->setAttribute('cut_price', '0.00');
		}
		
		$model->load(Yii::$app->request->post());
		$model->save();
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
		if(!$this->IsAdmin())
		{
			return $this->render('error', ['name' => 'Not Found (#404)', 'message' => 'Puuduvad piisavad õigused.']);
		}
		$product = Product::findOne($id);
        if($product->cut_price > 0){
			$this->findModel($id)->delete();			
			return $this->redirect(['index']);
		} else {		
			$this->findModel($id)->delete();		
			return $this->redirect(['/product']);
		}
    }
	
    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
	public function actionGetfieldname($id)
	{
		$field = Field::find()->where(['id' => $id])->one();
		if($field)
		{
			return $field->getAttribute('name').' '.$field->getAttribute('model').' '.$field->getAttribute('value');
		}
		return "";
	}
	 
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function actionToCart($id)
	{
		$model = Product::findOne($id);		
		$model->product_field = ProductField::find()->where(['product_id' => $model->getAttribute('id')])->all();			
		$model->field_type[] = FieldType::find()->orderBy('order_by')->all();
		foreach ($model->product_field as $pf) {
			$field = Field::find()->where(['id' => $pf->getAttribute('field_id')])->all();
			$model->field[] = $field;
		}
		$new_item = new ProductForMod();
		$new_item->setAttribute('id', $model->getAttribute('id'));
		$new_item->setAttribute('mfr', $model->getAttribute('mfr'));
		$new_item->setAttribute('model', $model->getAttribute('model'));
		$new_item->setAttribute('price', $model->getAttribute('price'));
		$new_item->setAttribute('cut_price', $model->getAttribute('cut_price'));
		$new_item->setAttribute('stock', $model->getAttribute('stock'));
		$new_item->setAttribute('description', $model->getAttribute('description'));
		
		foreach($model->product_field as $pf)
		{
			$new_item->product_field[] = $pf;
		}
		foreach($model->field as $f)
		{
			$new_item->field[] = $f;
		}
		foreach($model->field_type as $ft)
		{
			$new_item->field_type[] = $ft;
		}
		
		$cart = \Yii::$app->cart;
		$cart->add($new_item);
		return count($cart->getItems());
	}
	
	public function actionToComparison($id)
	{
		$model = Product::findOne($id);		
		$model->product_field = ProductField::find()->where(['product_id' => $model->getAttribute('id')])->all();			
		$model->field_type[] = FieldType::find()->orderBy('order_by')->all();
		foreach ($model->product_field as $pf) {
			$field = Field::find()->where(['id' => $pf->getAttribute('field_id')])->all();
			$model->field[] = $field;
		}
		
		//-------------------------------------------
		$new_item = new ProductForMod();
		$new_item->setAttribute('id', $model->getAttribute('id'));
		$new_item->setAttribute('mfr', $model->getAttribute('mfr'));
		$new_item->setAttribute('model', $model->getAttribute('model'));
		$new_item->setAttribute('price', $model->getAttribute('price'));
		$new_item->setAttribute('cut_price', $model->getAttribute('cut_price'));
		$new_item->setAttribute('stock', $model->getAttribute('stock'));
		$new_item->setAttribute('description', $model->getAttribute('description'));
		
		foreach($model->product_field as $pf)
		{
			$new_item->product_field[] = $pf;
		}
		foreach($model->field as $f)
		{
			$new_item->field[] = $f;
		}
		foreach($model->field_type as $ft)
		{
			$new_item->field_type[] = $ft;
		}
		
		$comparison = \Yii::$app->comparison;
		$comparison->add($new_item);
		return count($comparison->getItems());
	}
	
	public function IsAdmin()
	{
		$identity = Yii::$app->user->identity;
		$is_admin = false;
		if(isset($identity))
		{
			$is_admin = $identity->isAdmin;	
		}
		return $is_admin;
	}
}
