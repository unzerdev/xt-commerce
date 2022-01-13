<?php 

/* @var $form Customweb_IForm */

?>
<div class="x-panel-body x-panel-body-noheader" style="background-color: rgb(239, 239, 239);">
	<table class="table table-striped" width="100%">
		<thead>
			<tr>
				<th><?php echo $form->getTitle(); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php echo $formHtml; ?>
					<div id="form_panel_<?php echo UnzerCw_Backend_Controller_Form::cleanId($form->getId()); ?>"></div>	
				</td>
			</tr>
		</tbody>
	</table>
</div>
			