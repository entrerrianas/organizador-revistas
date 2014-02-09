<?php
	$t_object = $this->getVar("item");
	$va_comments = $this->getVar("comments");
?>

<div id="detail">
	<div class="row">
		<div class='col-xs-1 col-sm-1 col-md-1 col-lg-1'>
			<div class="detailNavBgLeft">
				{{{previousLink}}}{{{resultsLink}}}
			</div><!-- end detailNavBgLeft -->
		</div><!-- end col -->
		<div class='col-xs-10 col-sm-10 col-md-10 col-lg-10'>
			<div class="container"><div class="row">
				<div class='col-md-6 col-lg-6'>
					{{{representationViewer}}}
					<div id="detailTools">
						<div class="detailTool"><a href='#' onclick='jQuery("#detailComments").slideToggle(); return false;'><span class="glyphicon glyphicon-comment"></span>Comments (<?php print sizeof($va_comments); ?>)</a></div><!-- end detailTool -->
						<div id='detailComments'>{{{itemComments}}}</div><!-- end itemComments -->
						<div class="detailTool"><span class="glyphicon glyphicon-share-alt"></span>{{{shareLink}}}</div><!-- end detailTool -->
					</div><!-- end detailTools -->
				</div><!-- end col -->
				<div class='col-md-6 col-lg-6'>
					<h1>{{{^ca_objects.preferred_labels.name}}}</h1>
					<h2>{{{^ca_objects.type_id Planet}}}</h2>
					<h3>Fecha: {{{^ca_objects.fechaRevista}}}</h3>
<!--
						<h4>Contenido:</h4>
-->
					
									
					<?php	
			$va_occurrences = $t_object->get('ca_occurrences',array('returnAsArray' => true));
		//	print_r($va_occurrences);
			if (sizeof($va_occurrences) > 0) {
				print "<h4> Contenido: </h4>";
				print "<dl>";
					
					foreach ($va_occurrences as $occurrence_id => $va_occurrence) {
						
						$t_occurrence = new ca_occurrences($va_occurrence['occurrence_id']);
						$va_related_places = $t_occurrence->get('ca_places.place_id', array('returnAsArray' => true));
						//$va_related_places_direct = $t_occurrence['ca_places'];
						print "<dt>".caNavLink($this->request, $t_occurrence->getLabelForDisplay(true), '', '', 'Detail', 'Occurrences/'.$va_occurrence['occurrence_id'])."</dt>";
						
						print($va_related_places_direct);
						
						
						foreach ($va_related_places as $va_place){
							$t_place = new ca_places($va_place);
						////	$t_place->dump();
							$t_parent = $t_place->get('parent_id');
							$parent_object = new ca_places($t_parent);
							//print $parent_object->get('preferred_labels');
							$t_path = $t_place->get('preferred_labels',array('hierarchicalDelimiter'=> ', ',returnAsArray>= true,removeFirstItems => 1));
							print "<dd> ".$parent_object->get('preferred_labels').', '.$t_path."</dd>";
							//print "<dd> ->".print($t_place->get('preferred_labels',array('hierarchicalDelimiter'=> ', ',returnAsArray>= true,removeFirstItems => 1)))."</dd>";
							//print "The title of the object is ".$t_place->get('ca_places.preferred_labels.name')."<br/>\n";    // get the preferred name of the place
 
							//// do a search and print out the titles of all found objects
							//$o_search = new PlaceSearch();
							//$qr_results = $o_search->search($t_place->get('ca_places.preferred_labels.name'));    //el texto a buscar
 
							//$count = 1;
							//while($qr_results->nextHit()) {
								//print "Hit ".$count.": ".$qr_results->get('ca_places.preferred_labels.name')."<br/>\n";
								//$count++;
							//}
						}				
					}		
				print"</dl>";	
		}
?>
					
				
				</div><!-- end col -->
			</div><!-- end row --></div><!-- end container -->
		</div><!-- end col -->
		<div class='col-xs-1 col-sm-1 col-md-1 col-lg-1'>
			<div class="detailNavBgRight">
				{{{nextLink}}}
			</div><!-- end detailNavBgLeft -->
		</div><!-- end col -->
	</div><!-- end row -->
</div><!-- end detail -->
