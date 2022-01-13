<?php 

/* @var $this UnzerCw_Backend_Controller_Transaction */
/* @var $transaction UnzerCw_Entity_Transaction */


?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="../plugins/unzercw/css/frontend.css" />
	<link rel="stylesheet" type="text/css" href="../plugins/unzercw/css/moto.css" />
	
</head>
<body>
	<div class="col-md-12">
		<h1>Unzer: <?php echo $transaction->getPaymentMethod()->getPaymentMethodDisplayName(); ?></h1>
		<form action="<?php echo $formActionUrl; ?>" method="POST" class="form-horizontal unzercw-payment-form">
		
			<?php if (!empty($visibleFormFields)):?>
				<?php echo $visibleFormFields;?>
			<?php endif;?>
		
			<?php if (!empty($hiddenFields)):?>
				<?php echo $hiddenFields;?>
			<?php endif;?>
			
			<input type="submit" class="btn btn-success" value="<?php echo UnzerCw_Language::_('Charge'); ?>" />
			
		</form>
	</div>
</body>
</html>