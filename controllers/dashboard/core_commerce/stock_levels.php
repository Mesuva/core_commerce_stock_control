<?php 
defined('C5_EXECUTE') or die("Access Denied.");
Loader::model('product/model', 'core_commerce');
Loader::model('product/list', 'core_commerce');

// load in the same controller as the product search, as we want to use most of it.
$page = Page::getByPath('/dashboard/core_commerce/products/search');
Loader::controller($page);

class DashboardCoreCommerceStockLevelsController extends DashboardCoreCommerceProductsSearchController {
 	public function on_start() {
 		
 	}
  	
 	public function view() {
  		$html = Loader::helper('html');
  		$form = Loader::helper('form');
  		$db = Loader::db();
  		$this->set('form', $form);
  		$this->addHeaderItem($html->javascript('stock.control.js', 'core_commerce_stock_control')); 
  		//$this->addHeaderItem($html->css('ccm.core.commerce.search.css', 'core_commerce')); 
  		
  		$pkg = Package::getByHandle('core_commerce');
  		if($pkg->config('MANAGE_INVENTORY') != 1) {	
  			$this->set('error', 'The eCommerce \'Automatic Inventory Management\' setting is currently set to \'No\' - stock levels will not be automatically updated.<br />Change the settings in the <a href="'. View::url('/dashboard/core_commerce/settings/inventory/'). '">eCommerce Inventory Settings</a>');
  		}
  		 
  		
  		
  		if ($this->post()) {
  			$data = $this->post();	
  			
  			$productupdatecount = 0;
  			$attupdatecount = 0;
  			
  			foreach($data as $key=>$value) {
  				$value = (int)$value;
  			 
  				if (strpos($key, 'product_') === 0) {
  					$product_id = str_replace('product_', '', $key);	
  					
  					$query = "UPDATE `CoreCommerceProducts` SET `prQuantityUnlimited` = '0' WHERE `productID` = ?";
  					$db->Execute($query, array($product_id));
  					$productupdatecount++;
  				}
  				
  				
  				if (strpos($key, 'q_') === 0) {
  					$product_id = str_replace('q_', '', $key);	
  					
  					$query = "UPDATE `CoreCommerceProducts` SET `prQuantity` = ? WHERE `productID` = ?";
  					$db->Execute($query, array($value, $product_id));
  				}
  				
  				
  				if (strpos($key, 'att_') === 0) {
  					$att_id = str_replace('att_', '', $key);	
  					
  					$query = "UPDATE `atCoreCommerceProductAdjustmentSelectStockControlledOptions` SET `stockLevel` = ? WHERE `ID` = ?";
  					$db->Execute($query, array($value, $att_id));
  					$attupdatecount++;
  				}
  				
  				
  				if (strpos($key, 'unl_') === 0) {
  					$product_id = str_replace('unl_', '', $key);	
  					$query = "UPDATE `CoreCommerceProducts` SET `prQuantity` = '0', `prQuantityUnlimited` = '1' WHERE `productID` = ?";
  					$db->Execute($query, array($product_id));
  				}
  			 	 
  			}
  				$this->set('message', 'Stock levels updated.');
  				
  		}
  		
  		$productList = $this->getRequestedSearchResults();
  		$products = $productList->getPage();
  		
  		$specific_options = array();
  		
  		foreach($products as $prod) {
  			$atts = $prod->getProductConfigurableAttributes();
  			
  			$attarray = array();
  			
  			foreach($atts as $att) {
  				if ($att->atHandle == 'product_price_adjustment_select_stock_controlled') {
  					$attarray[] = $att;
  				}
  			}	
  			
  			
  			$specific_options[$prod->productID] = $attarray;
  			
  		}
  				
  		$this->set('specific_options', $specific_options);		
  		$this->set('productList', $productList);		
  		$this->set('products', $products);		
  		$this->set('pagination', $productList->getPagination());
  		
  		
  		
  		
   	}
		 
}
 
?>