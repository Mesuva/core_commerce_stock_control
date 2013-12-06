<?php  defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<?php 
$options = $this->controller->getOptions();
$form = Loader::helper('form');

 foreach($options as $opt) { 
	
	$output .= '<tr><td>' . $opt->getSelectAttributeOptionDisplayValue() . '</td>';
	$output .= '<td style="width: 40%">';
	$output .=  '<input type="number" value="'.$opt->stockLevel.'" style="width: 50px;"  name="att_'.$opt->ID.'" /> ';
	//$output .=  $form->checkbox('attunl_' .$opt->ID , '1', $opt->unlimited) . ' Unlimited';  
	$output .= '</td></tr>';
	
	$opts[$opt->getSelectAttributeOptionID()] = $opt->getSelectAttributeOptionDisplayValue();
}
 
  
 
echo $output;

?>