<?php 
	$view = get_view();
?>

<div id="customCitationSetting">
	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Citation style');?></label>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('Choose the citation style for your site.'); ?>
			</p>
			<?php echo $view->formRadio('custom_citation_style', get_option('custom_citation_style'), null, array('omeka' => __('Omeka default style'), 'wikipedia' => __('Wikipedia'), 'apa' => __('APA'), 'ieee' => __('IEEE'), 'mla' => __('MLA'), 'chicago' => __('Chicago/Turabian'), 'harvard' => __('Harvard'))); ?>
		</div>
	</div>
	
	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Add Collection');?></label>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('Add collection info to citation.'); ?>
			</p>
			<?php echo $view->formCheckbox('custom_citation_collection', get_option('custom_citation_collection'), null, array('1','0')); ?>
		</div>
	</div>
</div>