<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class ProductPriceAdjustmentSelectStockControlledAttributeTypeController extends SelectAttributeTypeController  {
 
	private $akSelectAllowMultipleValues;
	private $akSelectAllowOtherValues;
	private $akSelectOptionDisplayOrder;

	protected $searchIndexFieldDefinition = 'X NULL';
	
	public function type_form() {
		$path1 = $this->getView()->getAttributeTypeURL('type_form.js');
		$path2 = $this->getView()->getAttributeTypeURL('type_form.css');
		$this->addHeaderItem(Loader::helper('html')->javascript($path1));
		$this->addHeaderItem(Loader::helper('html')->css($path2));
		$this->set('form', Loader::helper('form'));		
		$this->load();
		//$akSelectValues = $this->getSelectValuesFromPost();
		//$this->set('akSelectValues', $akSelectValues);
		
		if ($this->isPost()) {
			$akSelectValues = $this->getSelectValuesFromPost();
			$this->set('akSelectValues', $akSelectValues);
		} else if (isset($this->attributeKey)) {
			$options = $this->getOptions();
			$this->set('akSelectValues', $options);
		} else {
			$this->set('akSelectValues', array());
		}
	}
	
	protected function load() {
		$ak = $this->getAttributeKey();
		if (!is_object($ak)) {
			return false;
		}
		
		$db = Loader::db();
		$row = $db->GetRow('select akSelectAllowMultipleValues, akSelectOptionDisplayOrder, akSelectAllowOtherValues from atSelectSettings where akID = ?', $ak->getAttributeKeyID());
		$this->akSelectAllowMultipleValues = $row['akSelectAllowMultipleValues'];
		$this->akSelectAllowOtherValues = $row['akSelectAllowOtherValues'];
		$this->akSelectOptionDisplayOrder = $row['akSelectOptionDisplayOrder'];

		$this->set('akSelectAllowMultipleValues', $this->akSelectAllowMultipleValues);
		$this->set('akSelectAllowOtherValues', $this->akSelectAllowOtherValues);			
		$this->set('akSelectOptionDisplayOrder', $this->akSelectOptionDisplayOrder);			
	}

	public function duplicateKey($newAK) {
		$this->load();
		$db = Loader::db();
		$db->Execute('insert into atSelectSettings (akID, akSelectAllowMultipleValues, akSelectOptionDisplayOrder, akSelectAllowOtherValues) values (?, ?, ?, ?)', array($newAK->getAttributeKeyID(), $this->akSelectAllowMultipleValues, $this->akSelectOptionDisplayOrder, $this->akSelectAllowOtherValues));	
		$r = $db->Execute('select value, adjustmentValue, displayOrder, isEndUserAdded from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ?', $this->getAttributeKey()->getAttributeKeyID());
		while ($row = $r->FetchRow()) {
			$db->Execute('insert into atCoreCommerceProductAdjustmentSelectStockControlledOptions (akID, value, adjustmentValue, stockLevel, displayOrder, isEndUserAdded) values (?, ?, ?, ?, ?, ?)', array(
				$newAK->getAttributeKeyID(),
				$row['value'],
				$row['adjustmentValue'],
				$row['stockLevel'],
				$row['displayOrder'],
				$row['isEndUserAdded']
			));
		}
	}
	
	private function getSelectValuesFromPost() {
		$options = new SelectAttributeTypeOptionList();
		$displayOrder = 0;		
		foreach($_POST as $key => $value) {
			if( !strstr($key,'akSelectValue_') || $value=='TEMPLATE' ) continue; 
			$opt = false;
			// strip off the prefix to get the ID
			$id = substr($key, 14);
			
			// get the price Adjustment Value
			$adjustmentValue = $_POST['akSelectAdjustmentValue_' . $id];
			if(is_numeric($adjustmentValue)) {
				$adjustmentValue = (float) $adjustmentValue;
			} else {
				$adjustmentValue = 0;
			}
			
			$stockLevel = $_POST['akSelectStockLevel_' . $id];
		 			
			// now we determine from the post whether this is a new option
			// or an existing. New ones have this value from in the akSelectValueNewOption_ post field
			if ($_POST['akSelectValueNewOption_' . $id] == $id) {
				$opt = new CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption(0, $value, $adjustmentValue, $stockLevel, $displayOrder);
				$opt->tempID = $id;
			} else if ($_POST['akSelectValueExistingOption_' . $id] == $id) {
				$opt = new CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption($id, $value, $adjustmentValue, $stockLevel, $displayOrder);
			}
			
			if (is_object($opt)) {
				$options->add($opt);
				$displayOrder++;
			}
		}
		
		return $options;
	}
	
	public function form() {
		$this->load();
		$options = $this->getSelectedOptions();
		$selectedOptions = array();
		foreach($options as $opt) {
			$selectedOptions[] = $opt->getSelectAttributeOptionID();
		}
		$this->set('selectedOptions', $selectedOptions);
	}
	
	public function search() {
		$this->load();	
		$selectedOptions = $this->request('atSelectOptionID');
		if (!is_array($selectedOptions)) {
			$selectedOptions = array();
		}
		$this->set('selectedOptions', $selectedOptions);
	}
	/*
	public function deleteValue() {
		$db = Loader::db();
		$db->Execute('delete from atSelectOptionsSelected where avID = ?', array($this->getAttributeValueID()));
	}
	*/

	public function deleteKey() {
		$db = Loader::db();
		$db->Execute('delete from atSelectSettings where akID = ?', array($this->attributeKey->getAttributeKeyID()));
		$r = $db->Execute('select ID from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ?', array($this->attributeKey->getAttributeKeyID()));
		while ($row = $r->FetchRow()) {
			$db->Execute('delete from atSelectOptionsSelected where atSelectOptionID = ?', array($row['ID']));
		}
		$db->Execute('delete from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ?', array($this->attributeKey->getAttributeKeyID()));
	}

	public function saveForm($data) {
		$this->load();

		if ($this->akSelectAllowOtherValues && is_array($data['atSelectNewOption'])) {
			foreach($data['atSelectNewOption'] as $newoption) {
			 	$optobj = CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption::add($this->attributeKey, $newoption, 1);
				$data['atSelectOptionID'][] = $optobj->getSelectAttributeOptionID();
			}
		}

		$db = Loader::db();
		$db->Execute('delete from atSelectOptionsSelected where avID = ?', array($this->getAttributeValueID()));
		if (is_array($data['atSelectOptionID'])) {
			foreach($data['atSelectOptionID'] as $optID) {
				if ($optID > 0) {
					$db->Execute('insert into atSelectOptionsSelected (avID, atSelectOptionID) values (?, ?)', array($this->getAttributeValueID(), $optID));
					if ($this->akSelectAllowMultipleValues == false) {
						break;
					}
				}
			}
		}
	}
	
	// Sets select options for a particular attribute
	// If the $value == string, then 1 item is selected
	// if array, then multiple, but only if the attribute in question is a select multiple
	// Note, items CANNOT be added to the pool (even if the attribute allows it) through this process.
	public function saveValue($value) {
		$db = Loader::db();
		$this->load();
		$options = array();		
		
		if (is_array($value) && $this->akSelectAllowMultipleValues) {
			foreach($value as $v) {
				$opt = CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption::getByValue($v);
				if (is_object($opt)) {
					$options[] = $opt;	
				}
			}
		} else {
			if (is_array($value)) {
				$value = $value[0];
			}
			
			$opt = CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption::getByValue($value);
			if (is_object($opt)) {
				$options[] = $opt;	
			}
		}
		
		$db->Execute('delete from atSelectOptionsSelected where avID = ?', array($this->getAttributeValueID()));
		if (count($options) > 0) {
			foreach($options as $opt) {
				$db->Execute('insert into atSelectOptionsSelected (avID, atSelectOptionID) values (?, ?)', array($this->getAttributeValueID(), $opt->getSelectAttributeOptionID()));
				if ($this->akSelectAllowMultipleValues == false) {
					break;
				}
			}
		}
	}

	
	public function validateForm($p) {
		$this->load();
		$options = $this->request('atSelectOptionID');
		if ($this->akSelectAllowMultipleValues) {
			return count($options) > 0;
		} else {
			if ($options[0] != false) {
				return $options[0] > 0;
			}
		}
		return false;
	}
	
	public function searchForm($list) {
		$options = $this->request('atSelectOptionID');
		$optionText = array();
		$db = Loader::db();
		$tbl = $this->attributeKey->getIndexedSearchTable();
		if (!is_array($options)) {
			return $list;
		}
		foreach($options as $id) {
			if ($id > 0) {
				$opt = SelectAttributeTypeOption::getByID($id);
				$optionText[] = $opt->getSelectAttributeOptionValue();
			}
		}
		if (count($optionText) == 0) {
			return false;
		}
		
		$i = 0;
		foreach($optionText as $val) {
			$val = $db->quote('%||' . $val . '||%');
			$multiString .= 'REPLACE(' . $tbl . '.ak_' . $this->attributeKey->getAttributeKeyHandle() . ', "\n", "||") like ' . $val . ' ';
			if (($i + 1) < count($optionText)) {
				$multiString .= 'OR ';
			}
			$i++;
		}
		$list->filter(false, '(' . $multiString . ')');
		return $list;
	}
	
	public function getValue() {
		$list = $this->getSelectedOptions();
		if ($list->count() == 1) {
			return $list->get(0);
		}
		return $list;	
	}
	
	public function getDisplayValue() {
		$list = $this->getSelectedOptions();
		$html = '';
		foreach($list as $l) {
			$html .= $l->getSelectAttributeOptionDisplayValue(). '<br/>';
		}
		return $html;
	}
	
	public function getPriceValue() {
		$this->load();
		$list = $this->getSelectedOptions();
		$adj = 0;
		foreach($list as $l) {
			$adj += $l->getSelectAttributeOptionAdjustmentValue();
		}
		return $adj;
	}
	
	public function getSearchIndexValue() {
		$str = "\n";
		$list = $this->getSelectedOptions();
		foreach($list as $l) {
			$str .= $l . "\n";
		}
		return $str;
	}
	
	public function getSelectedOptions() {
		if (!isset($this->akSelectOptionDisplayOrder)) {
			$this->load();
		}
		$db = Loader::db();
		switch($this->akSelectOptionDisplayOrder) {
			case 'popularity_desc':
				$options = $db->GetAll("select ID, value, adjustmentValue, stockLevel, displayOrder, (select count(s2.atSelectOptionID) from atSelectOptionsSelected s2 where s2.atSelectOptionID = ID) as total from atSelectOptionsSelected inner join atCoreCommerceProductAdjustmentSelectStockControlledOptions on atSelectOptionsSelected.atSelectOptionID = atCoreCommerceProductAdjustmentSelectStockControlledOptions.ID where avID = ? order by total desc, value asc", array($this->getAttributeValueID()));
				break;
			case 'alpha_asc':
				$options = $db->GetAll("select ID, value, adjustmentValue, stockLevel, displayOrder from atSelectOptionsSelected inner join atCoreCommerceProductAdjustmentSelectStockControlledOptions on atSelectOptionsSelected.atSelectOptionID = atCoreCommerceProductAdjustmentSelectStockControlledOptions.ID where avID = ? order by value asc", array($this->getAttributeValueID()));
				break;
			default:
				$options = $db->GetAll("select ID, value, adjustmentValue, stockLevel, displayOrder from atSelectOptionsSelected inner join atCoreCommerceProductAdjustmentSelectStockControlledOptions on atSelectOptionsSelected.atSelectOptionID = atCoreCommerceProductAdjustmentSelectStockControlledOptions.ID where avID = ? order by displayOrder asc", array($this->getAttributeValueID()));
				break;
		}
		$db = Loader::db();
		$list = new SelectAttributeTypeOptionList();
		foreach($options as $row) {
			$opt = new CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption($row['ID'], $row['value'], $row['adjustmentValue'], $row['stockLevel'], $row['displayOrder']);
			$list->add($opt);
		}
		return $list;
	}
	
	/**
	 * returns a list of available options optionally filtered by an sql $like statement ex: startswith%
	 * @param string $like
	 * @return SelectAttributeTypeOptionList
	 */
	public function getOptions($like = NULL) {
		if (!isset($this->akSelectOptionDisplayOrder)) {
			$this->load();
		}
		$db = Loader::db();
		switch($this->akSelectOptionDisplayOrder) {
			case 'popularity_desc':
				if(isset($like) && strlen($like)) {
					$r = $db->Execute('select ID, value, adjustmentValue, stockLevel, displayOrder, count(atSelectOptionsSelected.atSelectOptionID) as total 
						from atCoreCommerceProductAdjustmentSelectStockControlledOptions left join atSelectOptionsSelected on (atCoreCommerceProductAdjustmentSelectStockControlledOptions.ID = atSelectOptionsSelected.atSelectOptionID) 
						where akID = ? AND atCoreCommerceProductAdjustmentSelectStockControlledOptions.value LIKE ? group by ID order by total desc, value asc', array($this->attributeKey->getAttributeKeyID(),$like));
				} else {
					$r = $db->Execute('select ID, value, adjustmentValue, stockLevel, displayOrder, count(atSelectOptionsSelected.atSelectOptionID) as total 
						from atCoreCommerceProductAdjustmentSelectStockControlledOptions left join atSelectOptionsSelected on (atCoreCommerceProductAdjustmentSelectStockControlledOptions.ID = atSelectOptionsSelected.atSelectOptionID) 
						where akID = ? group by ID order by total desc, value asc', array($this->attributeKey->getAttributeKeyID()));
				}
				break;
			case 'alpha_asc':
				if(isset($like) && strlen($like)) {
					$r = $db->Execute('select ID, value, adjustmentValue, stockLevel, displayOrder from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ? AND atCoreCommerceProductAdjustmentSelectStockControlledOptions.value LIKE ? order by value asc', array($this->attributeKey->getAttributeKeyID(),$like));
				} else {
					$r = $db->Execute('select ID, value, adjustmentValue, stockLevel, displayOrder from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ? order by value asc', array($this->attributeKey->getAttributeKeyID()));
				}
				break;
			default:
				if(isset($like) && strlen($like)) {
					$r = $db->Execute('select ID, value, adjustmentValue, stockLevel, displayOrder from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ? AND atCoreCommerceProductAdjustmentSelectStockControlledOptions.value LIKE ? order by displayOrder asc', array($this->attributeKey->getAttributeKeyID(),$like));
				} else {
					$r = $db->Execute('select ID, value, adjustmentValue, stockLevel, displayOrder from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ? order by displayOrder asc', array($this->attributeKey->getAttributeKeyID()));
				}
				break;
		}
		$options = new SelectAttributeTypeOptionList();
		while ($row = $r->FetchRow()) {
			$opt = new CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption($row['ID'], $row['value'], $row['adjustmentValue'], $row['stockLevel'], $row['displayOrder']);
			$options->add($opt);
		}
		return $options;
	}
		
	public function validateKey($args) {
		$e = parent::validateKey($args);
		
		// additional validation for select type
		
		$vals = $this->getSelectValuesFromPost();

		if ($vals->count() < 1 && $this->post('akSelectAllowOtherValues') == 0) {
			$e->add(t('A select attribute type must have at least one value.'));
		}
		
		return $e;
	}

	public function saveKey($data) {
		$ak = $this->getAttributeKey();
		
		$db = Loader::db();

		$initialOptionSet = $this->getOptions();
		$selectedPostValues = $this->getSelectValuesFromPost();
		
		$akSelectAllowMultipleValues = $data['akSelectAllowMultipleValues'];
		$akSelectAllowOtherValues = $data['akSelectAllowOtherValues'];
		$akSelectOptionDisplayOrder = $data['akSelectOptionDisplayOrder'];
		
		if ($data['akSelectAllowMultipleValues'] != 1) {
			$akSelectAllowMultipleValues = 0;
		}
		if ($data['akSelectAllowOtherValues'] != 1) {
			$akSelectAllowOtherValues = 0;
		}
		if (!in_array($data['akSelectOptionDisplayOrder'], array('display_asc', 'alpha_asc', 'popularity_desc'))) {
			$akSelectOptionDisplayOrder = 'display_asc';
		}
				
		// now we have a collection attribute key object above.
		$db->Replace('atSelectSettings', array(
			'akID' => $ak->getAttributeKeyID(), 
			'akSelectAllowMultipleValues' => $akSelectAllowMultipleValues, 
			'akSelectAllowOtherValues' => $akSelectAllowOtherValues,
			'akSelectOptionDisplayOrder' => $akSelectOptionDisplayOrder
		), array('akID'), true);
		
		// Now we add the options
		$newOptionSet = new SelectAttributeTypeOptionList();
		$displayOrder = 0;
		foreach($selectedPostValues as $option) {
			$opt = $option->saveOrCreate($ak);
			if ($akSelectOptionDisplayOrder == 'display_asc') {
				$opt->setDisplayOrder($displayOrder);
			}
			$newOptionSet->add($opt);
			$displayOrder++;
		}
		
		// Now we remove all options that appear in the 
		// old values list but not in the new
		foreach($initialOptionSet as $iopt) {
			if (!$newOptionSet->contains($iopt)) {
				$iopt->delete();
			}
		}
	}
	
}

class CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption extends SelectAttributeTypeOption {

	public function __construct($ID, $value, $adjustmentValue, $stockLevel, $displayOrder) {
		$this->ID = $ID;
		$this->value = $value;
		$this->adjustmentValue = $adjustmentValue;
		$this->displayOrder = $displayOrder;
		$this->th = Loader::helper('text');	
		$this->stockLevel = $stockLevel;
	}

	public function getSelectAttributeOptionDisplayValue() {
		$adjValue = $this->getSelectAttributeOptionAdjustmentValue();
		$txt = $this->value;
		if($adjValue != 0) {
			if($adjValue > 0) {
				$txt .= ": +";
			} else {
				$txt .= ": ";
			}
			$txt .= CoreCommercePrice::format($adjValue);
		}
		return $txt;
		
	}
	
	public function getSelectAttributeOptionAdjustmentValue() { return $this->adjustmentValue; }
		
	public function getSelectAttributeOptionStockLevel() { return $this->stockLevel; }	
		
	public static function add($ak, $value, $adjustmentValue, $stockLevel, $isEndUserAdded = 0) {
		$db = Loader::db();
		// this works because displayorder starts at zero. So if there are three items, for example, the display order of the NEXT item will be 3.
		$displayOrder = $db->GetOne('select count(ID) from atCoreCommerceProductAdjustmentSelectStockControlledOptions where akID = ?', array($ak->getAttributeKeyID()));			

		$v = array($ak->getAttributeKeyID(), $displayOrder, $value, $adjustmentValue, $stockLevel, $isEndUserAdded);
		$db->Execute('insert into atCoreCommerceProductAdjustmentSelectStockControlledOptions (akID, displayOrder, value, adjustmentValue, stockLevel, isEndUserAdded) values (?, ?, ?, ?, ?, ?)', $v);
		
		return CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption::getByID($db->Insert_ID());
	}
	
	public function setDisplayOrder($num) {
		$db = Loader::db();
		$db->Execute('update atCoreCommerceProductAdjustmentSelectStockControlledOptions set displayOrder = ? where ID = ?', array($num, $this->ID));
	}
	
	public static function getByID($id) {
		$db = Loader::db();
		$row = $db->GetRow("SELECT ID, displayOrder, value, adjustmentValue, stockLevel FROM atCoreCommerceProductAdjustmentSelectStockControlledOptions WHERE ID = ?", array($id));
		if (isset($row['ID'])) {
			$obj = new CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption($row['ID'], $row['value'], $row['adjustmentValue'], $row['stockLevel'], $row['displayOrder']);
			return $obj;
		}
	}
	
	public static function getByValue($value) {
		$db = Loader::db();
		$row = $db->GetRow("select ID, displayOrder, value, adjustmentValue, stockLevel from atCoreCommerceProductAdjustmentSelectStockControlledOptions where value = ?", array($value));
		if (isset($row['ID'])) {
			$obj = new CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption($row['ID'], $row['value'], $row['adjustmentValue'],  $row['stockLevel'], $row['displayOrder']);
			return $obj;
		}
	}
	
	public function delete() {
		$db = Loader::db();
		$db->Execute('delete from atCoreCommerceProductAdjustmentSelectStockControlledOptions where ID = ?', array($this->ID));
		$db->Execute('delete from atSelectOptionsSelected where atSelectOptionID = ?', array($this->ID));
	}
	
	public function saveOrCreate($ak) {
		if ($this->tempID != false || $this->ID==0) {
			return CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption::add($ak, $this->value, $this->adjustmentValue, $this->stockLevel);
		} else {
			$db = Loader::db();
			$db->Execute('UPDATE atCoreCommerceProductAdjustmentSelectStockControlledOptions SET value = ?, adjustmentValue = ?, stockLevel = ? WHERE ID = ?', array($this->value, $this->adjustmentValue, $this->stockLevel, $this->ID));
			return CoreCommerceProductAdjustmentSelectAttributeTypeStockControlledOption::getByID($this->ID);
		}
	}
	
}
