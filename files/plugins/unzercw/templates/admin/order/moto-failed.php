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
		
		<div class="alert alert-danger"><?php echo UnzerCw_Language::_('The transaction failed:'); ?> <?php echo $errorMessage?></div>
		
		<button onclick="window.opener.contentTabs.getActiveTab().getUpdater().refresh(); self.close();" class="btn btn-success">
			<?php echo UnzerCw_Language::_('Close'); ?>
		</button>
	</div>
</body>
</html>