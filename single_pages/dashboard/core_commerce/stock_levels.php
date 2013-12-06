<?php  defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<?php 
$settings = Page::getByPath('/dashboard/core_commerce/stock_levels', 'ACTIVE');

$th = Loader::helper('text'); 
$form = Loader::helper('form');


?>
 	 
 	  

<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Update Stock Levels'), false, false, false)?>

<div class="ccm-pane-options" id="ccm-<?php echo $searchInstance?>-pane-options">
<div class="ccm-core-commerce-product-search-form"><?php  Loader::packageElement('search', 'core_commerce_stock_control', array('searchType' => 'DASHBOARD')); ?></div>
</div>

	 <form method="post">
	
	
	<div class="ccm-pane-body  ">
	
	<table class="ccm-results-list">
	<tr>
	<th><a href="<?php echo $productList->getSortByURL('prName', 'asc')?>"><?php echo t('Name')?></a></th>
	<th><a href="<?php echo $productList->getSortByURL('prCurrentPrice', 'asc')?>"><?php echo t('Price')?></a></th>
	<th><a href="<?php echo $productList->getSortByURL('prQuantity', 'asc')?>"><?php echo t('Base Quantity')?></a></th>
	<th>Product Options</th>
	<!--<th><a href="<?php echo $productList->getSortByURL('prStatus', 'asc')?>"><?php echo t('Status')?></a></th>-->
	</tr>
	
	
	<?php foreach($products as $prod) {
			echo '<tr>';
			echo '<td>';
			echo '<p><a href="'. $this->url('/dashboard/core_commerce/products/search/view_detail/' . $prod->productID) . '">'. $prod->prName . '</a></p>';
			
			echo ($prod->prStatus == 1) ? '': '<p>(' . t('Disabled')  . ')</p>';
			
		  	echo '</td><td>';
		  
		  	echo Loader::packageElement('product/price', 'core_commerce', array('product' => $prod, 'displayDiscount' => true)); 
		  	echo '</td><td>';
		   	
		   	echo $form->hidden('product_' .$prod->productID);	
		    
			//echo $form->text('q_' . $prod->productID, ($prod->prQuantityUnlimited == '0' ? $prod->prQuantity : '') , array('type'=>'number', 'style'=>'width: 50px', ($prod->prQuantityUnlimited == '1' ? 'disabled' : '')=>($prod->prQuantityUnlimited == '1' ? 'disabled' : ''))). ' '; 
			
			// avoiding using the form helper here to create a true html5 number input
			echo '<input type="number" value="'.($prod->prQuantityUnlimited == '0' ? $prod->prQuantity : '').'" style="width: 50px;" '.($prod->prQuantityUnlimited == '1' ? 'disabled="disabled' : '').' name="'.'q_' . $prod->productID.'" /> ';
			 
					
			echo $form->checkbox('unl_' .$prod->productID , '1', $prod->prQuantityUnlimited) . ' Unlimited';  
			echo '</td><td>';
		 	
			foreach($specific_options[$prod->productID] as $att) {
				 
				echo '<br /><table style="width: 100%;" class="table-bordered"><tr><th colspan="2"><a href="'. $this->url('/dashboard/core_commerce/products/options/edit/' . $att->akID) . '">'.  $att->akName . '</a>' . ( $att->poakIsRequired == 1 ? ' (' .t('required selection').')' : '') . '</th></tr>';
				 $att->render('controlform');
				 echo'</table>';
			}
			
			echo '<br /></td></tr>';
	}
	
	?>
	</table>
	 
	</div>
	
	<script type="text/javascript">
	$(function() { 
		ccm_coreCommerceSetupSearch(); 
	});
	</script>
	
	<div class="ccm-pane-footer">
		<?php  	$productList->displayPagingV2($bu, false, $soargs); ?>
		 <button class="btn btn-primary pull-right" type="submit">Update Product Quantities</button> 
	</div>
	
	</form>
	
<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false)?>


<script type="text/javascript">
	$(document).ready(function() { 
		$('.ccm-results-list input[type=checkbox]').change(function(){
				if ($(this).is(':checked')) {
					$(this).prev().attr('disabled','disabled').val('');	
				} else {
					$(this).prev().removeAttr('disabled').focus();	
				}
			
		});
	
	});
</script>

	 
	 
 