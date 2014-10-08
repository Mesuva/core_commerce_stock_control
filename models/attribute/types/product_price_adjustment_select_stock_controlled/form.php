<?php  defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<?php 
$options = $this->controller->getOptions();
$form = Loader::helper('form');

$opts = array('- ' . $this->controller->getattributeKey()->akName . ' -');
foreach($options as $opt) { 
	  $opts[$opt->getSelectAttributeOptionID()] = $opt->getSelectAttributeOptionDisplayValue() . ($opt->stockLevel <= 0 ? ' (' . t('Unavailable') .')': '');
}

?>
<?php $selectoutput =  $form->select($this->field('atSelectOptionID') . '[]', $opts, $selectedOptions[0]); 


 foreach($options as $opt) {
 	 if ($opt->stockLevel <= 0) {
 		$selectoutput = str_replace('option value="'.$opt->ID.'"', 'option value="'.$opt->ID.'" disabled="disabled"', $selectoutput);
 		//$selectoutput = str_replace($opt->value . '</option>', $opt->value .' (Unavailable) </option>', $selectoutput);
 	}


 }
 
 echo $selectoutput;

?>