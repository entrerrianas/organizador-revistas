<?php
	$t_item = $this->getVar('item');
	
	print $t_item->get('ca_places.parent.preferred_labels.name');
	//$t_item->dump();
	
?>

<div id='pageArea' class='occurrences'>
	<div id='pageTitle'>
		<?php print ucwords($t_item->get('ca_places.type_id', array('convertCodesToDisplayText' => true))); ?>
	</div>
