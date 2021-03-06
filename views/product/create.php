<?php

use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = Yii::t('app', 'Lisa toode');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tooted'), 'url' => ['/product']];
$this->params['breadcrumbs'][] = $this->title;
// $pid = null
$pid = $model->id;
?>
<div class="product-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
		'soodus' => false,
    ]) ?>
	    <?= $this->render('_componentForm', [
        'model' => $model,
		'pid' => $pid,
    ]) ?>
</div>
<script>
	var idxx = 0;
	
	$( document ).ready(function() {
		
		$( "[id^='product-']" ).each(function() {
			if($ (this).val() == '0' || $ (this).val() == '0.00' ){
				$ (this).val('');
			}
		});	
		
		$( "input[id^='productfield-field_id']" ).each(function( index, element ) {
			var _val = parseInt($(this).val()); // _val on pf.field_id
			if(_val > 0)
			{
				$.ajax({
				  url: "/index.php/product/getfieldname/"+_val,
				  context: document.body,
				  dataType: "text"
				}).done(function(data) {
					$( "input[id^='productfield-field_id_']" ).each(function( index, element ) {
						var _idx = 0;
						var _val1 = parseInt($(this).attr('value'));
						if(_val1 == _val)
						{
							$(this).val(data);	
							idxx ++;
						}
						_idx ++;
					});
				});
			}
		});
	});
</script>
 <script>
	function salvesta()
	{
		var data;
		$('form').each(function( index, element ) {
			// if($(this).attr('id') != 'w0')
			// {
				var _tempindex = index;
				_tempindex --;
				var _id = parseInt($("#productfield-id.form-control").eq(_tempindex).val());
				var _field_id = parseInt($("#productfield-field_id.form-control").eq(_tempindex).val());
				var _product_id = parseInt($("#productfield-product_id.form-control").eq(_tempindex).val());
				if(!isNaN(_id) && _id > 0 && !isNaN(_field_id) && _field_id > 0 && !isNaN(_product_id) && _product_id > 0)
				{
					var posting = $.post( '/index.php/product-field/update/<?php echo $pid; ?>', { 'id': _id, 'product_id': _product_id, 'field_id': _field_id } );
					posting.done(function( data ) {
					});
				}
				else if(!isNaN(_field_id) && _field_id > 0 && !isNaN(_product_id) && _product_id > 0)
				{
					var posting = $.post( '/index.php/product-field/create/<?php echo $pid; ?>', { 'product_id': _product_id, 'field_id': _field_id } );
					posting.done(function( data ) {
					});
				}
			//}
		});
		$("#w0").submit();
	}
</script>
