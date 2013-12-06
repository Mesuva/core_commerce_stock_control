<?php     
class StockLevelUpdater Extends Model{
	function onOrderUpdate($order){
		Loader::model('attribute/categories/core_commerce_product_option','core_commerce');
		
		$db = Loader::db();
		$order  = $order->getProducts();
		 
		   $pkg = Package::getByHandle('core_commerce');
			if ($pkg->config('MANAGE_INVENTORY') == 1) {
				if (($pkg->config('MANAGE_INVENTORY_TRIGGER') == 'SHIPPED' && $order->getOrderStatus() == $order::STATUS_SHIPPED) 
				|| ($pkg->config('MANAGE_INVENTORY_TRIGGER') == 'COMPLETED' && $order->getOrderStatus() == $order::STATUS_COMPLETE)
				|| ($pkg->config('MANAGE_INVENTORY_TRIGGER') == 'FINISHED')
				) {
					 
					 if(is_array($order) && count($order)) {
					 	foreach($order as $p) {
					 		$opID = $p->getOrderProductID();
					 		$pID = $p->getProductID();
					 		
					 		$attributes = CoreCommerceProductOptionAttributeKey::getAttributes($opID);
					 		
					 		foreach($attributes as $att){
					 			if(is_a($att, 'CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption')){  // if the attribute object is a stock control atttribute
					 				
					 				$qty = $p->getQuantity();
					 				$db->Execute("UPDATE atCoreCommerceProductAdjustmentSelectStockControlledOptions SET stockLevel = (stockLevel - $qty) WHERE ID = $att->ID"); 
					 			}
					 		}
					 	}
					 }
					 
					 
				}
			}
		
		
	}
	
	
}