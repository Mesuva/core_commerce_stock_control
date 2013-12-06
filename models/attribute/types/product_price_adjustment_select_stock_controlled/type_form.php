<?php 

function getAttributeOptionHTML($v){
	$pkg = Package::getByHandle('core_commerce');
	
	if ($v == 'TEMPLATE') {
		$akSelectValueID = 'TEMPLATE_CLEAN';
		$akSelectValue = 'TEMPLATE';
		$akSelectAdjustmentValue = 'TEMPLATE_ADJUSTMENT';
		$akSelectStockLevelValue = 'TEMPLATE_STOCK';
		
	} else {
		if ($v->getSelectAttributeOptionTemporaryID() != false) {
			$akSelectValueID = $v->getSelectAttributeOptionTemporaryID();
		} else {
			$akSelectValueID = $v->getSelectAttributeOptionID();
		}
		$akSelectValue = $v->getSelectAttributeOptionValue();
		$akSelectAdjustmentValue = $v->getSelectAttributeOptionAdjustmentValue();
		$akSelectStockLevelValue = $v->getSelectAttributeOptionStockLevel();
	}
		?>
		<div id="akSelectValueDisplay_<?php echo $akSelectValueID?>" >
			<div class="rightCol">
				<input type="button" onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akSelectValueID)?>')" value="<?php echo t('Edit')?>" />
				<input type="button" onClick="ccmAttributesHelper.deleteValue('<?php echo addslashes($akSelectValueID)?>')" value="<?php echo t('Delete')?>" />
			</div>			
			<div class="leftCol">
			<strong onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akSelectValueID)?>')" id="akSelectValueStatic_<?php echo $akSelectValueID?>"><?php echo $akSelectValue ?></strong> -&nbsp;<?php  echo ($pkg->config('CURRENCY_SYMBOL')?$pkg->config('CURRENCY_SYMBOL'):'$'); ?><span onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akSelectValueID)?>')" id="akSelectAdjustmentValueStatic_<?php echo $akSelectValueID?>"><?php echo $akSelectAdjustmentValue ?></span>, <span><?php echo t('Stock'); ?>: <?php echo $akSelectStockLevelValue?></span>
			</div>
		</div>
		<div id="akSelectValueEdit_<?php echo $akSelectValueID?>" style="display:none">
			<div class="rightCol">
				<input type="button" onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akSelectValueID)?>')" value="<?php echo t('Cancel')?>" />
				<input type="button" onClick="ccmAttributesHelper.changeValue('<?php echo addslashes($akSelectValueID)?>')" value="<?php echo t('Done')?>" />
			</div>		
			 	<table>
			 	<tr><td><?php echo t('Option');?> </td> 
				<input name="akSelectValueOriginal_<?php echo $akSelectValueID?>" type="hidden" value="<?php echo $akSelectValue?>" />
				<?php  if (is_object($v) && $v->getSelectAttributeOptionTemporaryID() == false) { ?>
					<input id="akSelectValueExistingOption_<?php echo $akSelectValueID?>" name="akSelectValueExistingOption_<?php echo $akSelectValueID?>" type="hidden" value="<?php echo $akSelectValueID?>" />
				<?php  } else { ?>
					<input id="akSelectValueNewOption_<?php echo $akSelectValueID?>" name="akSelectValueNewOption_<?php echo $akSelectValueID?>" type="hidden" value="<?php echo $akSelectValueID?>" />
				<?php  } ?>
				<td>
				<input id="akSelectValueField_<?php echo $akSelectValueID?>" name="akSelectValue_<?php echo $akSelectValueID?>" type="text" value="<?php echo $akSelectValue?>" size="20" 
				   /></td><tr><td>
				<?php echo t('Price Modification');?> <?php  echo ($pkg->config('CURRENCY_SYMBOL')?$pkg->config('CURRENCY_SYMBOL'):'$'); ?>&nbsp;</td>
				<td>
				<input id="akSelectAdjustmentValueField_<?php echo $akSelectValueID?>" name="akSelectAdjustmentValue_<?php echo $akSelectValueID?>" type="text" value="<?php echo $akSelectAdjustmentValue ?>" size="10" /></td>
				</tr><td>
				<?php echo t('Stock Level');?>&nbsp;</td><td>
				<input id="akSelectAdjustmentStockLevel_<?php echo $akSelectValueID?>" name="akSelectStockLevel_<?php echo $akSelectValueID?>" type="text" value="<?php echo $akSelectStockLevelValue ?>" size="10" />
				</td></tr></table>
			 		
		</div>	
		<div class="ccm-spacer">&nbsp;</div>
		  
		
		
<?php  }
$pkg = Package::getByHandle('core_commerce');
?>

<table class="entry-form" cellspacing="1" cellpadding="0">
 
  <?php echo $form->hidden('akSelectOptionDisplayOrder', 'display_asc');?> 
 
<tr>
	<td colspan="3" class="subheader"><?php echo t('Values')?></td>
</tr>
<tr>
	<td colspan="3">
	<div id="attributeValuesInterface">
	<div id="attributeValuesWrap">
	<?php 
	Loader::helper('text');
	foreach($akSelectValues as $v) { 
		if ($v->getSelectAttributeOptionTemporaryID() != false) {
			$akSelectValueID = $v->getSelectAttributeOptionTemporaryID();
		} else {
			$akSelectValueID = $v->getSelectAttributeOptionID();
		}
		?>
		<div id="akSelectValueWrap_<?php echo $akSelectValueID?>" class="akSelectValueWrap <?php  if ($akSelectOptionDisplayOrder == 'display_asc') { ?> akSelectValueWrapSortable <?php  } ?>">
			<?php echo getAttributeOptionHTML( $v )?>
		</div>
	<?php  } ?>
	</div>
	<div class="ccm-spacer"></div>
	
	<div id="akSelectValueWrapTemplate" class="akSelectValueWrap" style="display:none">
		<?php echo getAttributeOptionHTML('TEMPLATE') ?>
	</div>
	<div class="ccm-spacer"></div>
	
	<div id="addAttributeValueWrap"> 
		
		<h3><?php echo t('Add New Option');?></h3>
		
		<table>
			<tr>
		<td><?php echo t('Option');?>&nbsp;</td> 
		<td>
		<input id="akSelectValueFieldNew" name="akSelectValueNew" type="text"   size="40"  
		 />
		</td></tr><tr><td>
		<?php echo t('Price Modification');?> <?php  echo ($pkg->config('CURRENCY_SYMBOL')?$pkg->config('CURRENCY_SYMBOL'):'$'); ?>
		&nbsp;</td><td> 
		 
		<input id="akSelectAdjustmentValueFieldNew" name="akSelectAdjustmentValueNew" type="text" value="<?php echo $defaultNewOptionNm ?>" size="10"  
		 />
		 </td></tr><tr><td><?php echo t('Stock Level');?>&nbsp;</td><td>
		 
		 <input id="akSelectStockLevelValueFieldNew" name="akSelectStockLevelValueNew" type="text" value="<?php echo $defaultNewOptionNm ?>" size="10"  />
	  	</td></tr></table>
	  	
		<input type="button" onClick="ccmAttributesHelper.saveNewOption(); $('#ccm-attribute-key-form').unbind()" value="<?php echo t('Add') ?>" />
	</div>
	
	<?php  if ($attributeType == 'page') { ?>
	<div id="allowOtherValuesWrap" style="display:<?php echo ($akType != 'SELECT' && $akType != 'SELECT_MULTIPLE')?'none':'block' ?>">
		<input type="checkbox" name="akAllowOtherValues" style="vertical-align: middle" <?php  if ($akAllowOtherValues) { ?> checked <?php  } ?> /> <?php echo t('Allow users to add to this list.')?>
	</div>
	<?php  } ?>

</div>
	</td>
</tr>
</table>

<?php  if ($akSelectOptionDisplayOrder == 'display_asc') { ?>
<script type="text/javascript">
$(function() {
	ccmAttributesHelper.makeSortable();
});
</script>
<?php  } ?>