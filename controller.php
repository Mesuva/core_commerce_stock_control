<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));

class CoreCommerceStockControlPackage extends Package {

	protected $pkgHandle = 'core_commerce_stock_control';
	protected $appVersionRequired = '5.5.1.2';
	protected $pkgVersion = '0.96';
	
	public function getPackageDescription() {
		return t("A product attribute to manage stock levels");
	}
	
	public function getPackageName() {
		return t("eCommerce Stock Controlled Product Attribute");
	}
	
	public function install() {
	
	  $pkg = parent::install();
     
      $eakp = AttributeKeyCategory::getByHandle('core_commerce_product');
      $eukp = AttributeKeyCategory::getByHandle('core_commerce_product_option');
  	 
//	  $mtiopt = AttributeType::add('product_multioption','Product Multi Option', $pkg);
//	  $eakp->associateAttributeKeyType(AttributeType::getByHandle('product_multioption'));
//	  
	  $mtinv = AttributeType::add('product_price_adjustment_select_stock_controlled','Select - Product Price, with stock control', $pkg);
	  $eukp->associateAttributeKeyType(AttributeType::getByHandle('product_price_adjustment_select_stock_controlled'));
	  
	  $page = Page::getByPath('/dashboard/core_commerce/stock_levels');
	  if (!is_object($page) || !intval($page->getCollectionID())) {
	      $page = SinglePage::add('/dashboard/core_commerce/stock_levels', $pkg);
	  }
	  if (is_object($page) && intval($page->getCollectionID())) {
	      $page->update(array('cName' => t('Stock Levels'), 'cDescription' => t("Product Stock Levels")));
	  } else throw new Exception(t('Error: /dashboard/core_commerce/stock_levels page not created'));
	  
	  
	}
	
	public function on_start() {
		Events::extend('core_commerce_on_checkout_finish_order','StockLevelUpdater','onOrderCompletion','packages/'.$this->pkgHandle.'/models/stock_level_updater.php', array($order));
		
		
		Events::extend('core_commerce_change_order_status','StockLevelUpdater','onOrderUpdate','packages/'.$this->pkgHandle.'/models/stock_level_updater.php', array($order));	
		
	
	}
}