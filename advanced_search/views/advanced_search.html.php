<?php defined("SYSPATH") or die("No direct script access.") ?>  
<?= $theme->script("jquery-ui.min.js"); ?>

	<div id="g-advanced_search"> 
	<script type="text/javascript">
	    $(document).ready(function() {
	      $('form input[name^=tags]').ready(function() {
	          $('form input[name^=tags]').gallery_autocomplete(
	            "<?= url::site("/tags/autocomplete") ?>",
	            {max: 30, multiple: true, multipleSeparator: ',', cacheLength: 1});
	        })
	    });
    </script>

	<form id="g-advanced-search-form" action="<?= url::site("advanced_search/search") ?>" method="post" >
	    <?= access::csrf_form_field() ?>
	    <fieldset id="g-advanced-search-view">
	    	<legend>
	       		 <?= t("Advanced Search") ?>
	      	</legend>
			<div id="g-column-left">
		    	
		    	<label for="title"> <?= t("Title").":" ?> </label>
		    	<input id="g-inputs" type="text" name="title" value="<?= $form["title"] ?>" tabindex="1" />

				<label for="fullname"> <?= t("Owner").":" ?> </label>
		    	<input id="g-inputs" type="text" name="fullname" value="<?= $form["fullname"] ?>" tabindex="3" />

		    	<? if($enable_tags){ ?>
			    	<label for="tags"> <?= t("Tags").":" ?> </label>
				    <input type="text" name="tags" value="<?= $form["tags"] ?>" 
				    			class="ac_input" autocomplete="off" id="tags" tabindex="5" />
		    	<? } ?>
		    	<label for="groups"> <?= t("Groups").":" ?> </label>
				<select id="g-groups" name="groups" tabindex="8">
					<? foreach ($groups as $index => $group): ?>
						<option value="<?= $index.':'.$group->id ?>"><?= t($group->name) ?></option>
					<? endforeach ?>
				</select>

				<div id="g-div-combo">
					<div id="g-div-combo-left">
						<label for="orderby"> <?= t("Order by").":" ?> </label>
						<select id="g-orderby" name="orderby" tabindex="9">
							<option value="0"><?= t("Owner") ?></option>
							<option value="1"><?= t("Title") ?></option>
							<option value="2"><?= t("Date captured") ?></option>
							<option value="3"><?= t("Date uploaded") ?></option>
							<option value="4"><?= t("Date modified") ?></option>
						</select>
					</div>

					<div id="g-div-combo-rigth">
					<label for="type"> <?= t("Type").":" ?> </label>
					<select id="g-type" name="type" tabindex="10">
						<option value="0"><?= t("Photo") ?></option>
						<option value="1"><?= t("Movie") ?></option>
						<option value="2"><?= t("Album") ?></option>
						<option value="3"><?= t("All") ?></option>
					</select>
					</div>
				</div>
				<br>
				<input id="btn-search" type="submit" value="<?= t("Search") ?>" tabindex="11" class="g-button ui-icon-left ui-state-default ui-corner-all" />
				<input type="reset" onclick="javascript:window.location.href='<?= url::site("advanced_search") ?>'" 
					value="<?= t("Clear") ?>" tabindex="12" class="g-button ui-icon-left ui-state-default ui-corner-all"/>				
	    	</div>

	    	<div id="g-column-right">
		    	
		    	<label for="description"> <?= t("Description").":" ?> </label>
		    	<input id="g-inputs" type="text" name="description" value="<?= $form["description"] ?>" tabindex="2" />

		    	<label for="login"> <?= t("Login").":" ?> </label>
		    	<input id="g-inputs" type="text" name="login" value="<?= $form["login"] ?>" tabindex="4" />

		    	<label for="filterby"> <?= t("Filter By").":" ?> </label>
		    	<? if($enable_tags){ ?>
			    	<br>		    	
			    	<input id="withouttag" type="checkbox" name="without[]" value="withouttag" tabindex="6" 
			    			onchange="javascript:document.getElementById('tags').disabled = this.checked"/><?= t("Without Tags") ?> <br>

			    	<? if($form["withouttag"]) { ?>
						<script type="text/javascript">
							document.getElementById('withouttag').checked = true;
							document.getElementById('tags').disabled = true;
						</script>
		    		<? } ?> 
		    	<? } ?>

	    		<? if($enable_exif_gps){ ?> 
		    		<input id="withoutgps" type="checkbox" name="without[]" value="withoutgps" tabindex="7" /><?= t("Without Coordinates")?>
		    			    		
		    		<? if($form["withoutgps"]) {  ?>
						<script type="text/javascript">
							document.getElementById('withoutgps').checked = true;
						</script>
		    		<? } ?> 
				<? } ?>
				<br>
				<div id="g-div-combo-left">
						<label for="dateby"> <?= t("Date by").":" ?> </label>
						<select id="g-dateby" name="dateby" tabindex="9">
							<option value="0"><?= t("All") ?></option>
							<option value="1"><?= t("Date captured") ?></option>
							<option value="2"><?= t("Date uploaded") ?></option>
							<option value="3"><?= t("Date modified") ?></option>
						</select>
				</div>
				<br><br><br>
				<label for="datefrom"> <?= t("Date From").":" ?> </label>
		    	<input class="date-pick" type="text" name="datefrom" value="<?= $form["datefrom"] ?>" tabindex="10" />
		    	<label for="dateto"> <?= t("Date To").":" ?> </label>
		    	<input class="date-pick" type="text" name="dateto" value="<?= $form["dateto"] ?>" tabindex="11" />

	    	</div>
	    	 <script type="text/javascript">
				document.getElementById('g-groups').selectedIndex = ("<?= $form['groups'] ?>").split(":")[0];
				document.getElementById('g-orderby').selectedIndex = "<?= $form['orderby'] ?>";
				document.getElementById('g-dateby').selectedIndex = "<?= $form['dateby'] ?>";
				document.getElementById('g-type').selectedIndex = "<?= $form['type'] ?>";
				
				var datepick = $('.date-pick'); 

				function checkSelect() {
					var all = $('select[name="dateby"]').val();
				    if(all == "0") {       
			        	datepick.attr('disabled','disabled');
			        } else {
			        	datepick.removeAttr('disabled');          	
			        }
				}

				$(document).ready(function() {
					datepick.datepicker({ dateFormat: "dd/mm/yy" });
					$('#ui-datepicker-div a').removeClass('ui-state-highlight');
				    datepick.attr('disabled','disabled');      
				    $('select[name="dateby"]').change(checkSelect);
				    checkSelect();
				});
			</script>
	    </fieldset>
		<br>
		<fieldset>
		  <legend>
		    <?= t("Result") ?>
		  </legend>
		    <? if (count($items) > 0){ ?>
		    <table>
		    <? foreach ($items as $item): 
		 		$user = $users[$item->owner_id]; ?>
			        <tr>
			          <td style="width: 140px">
			              <a href=<?= $item->url() ?> onclick="window.open(this.href);return false;">
						   <?= $item->thumb_img(array("class" => "g-thumbnail"), 200) ?>
						  </a>
			          </td>
			          <td>
			            <ul>
			              <li>
			                <?= t("Title of ").$item->type.": ".$item->title ?>
			              </li>
			              <li>
			                <?= t("Owner").": ".$user->full_name ?> 
			              </li>
			              <li>
			                <?= t("Login").": ".$user->name." - ".t("E-mail: ").$user->email ?> 
			              </li>
			              <li>
			                <?= t("Date captured").": ".gallery::date_time($item->captured); ?> 
			              </li>
			              <li>
			              	<?= t("Date uploaded").": ".gallery::date_time($item->created); ?> 
			              </li>
			              <li>
			                <?= t("Date modified").": ".gallery::date_time($item->updated); ?> 
			              <li>
			             	<br>
			             	<? if(access::can("edit",$item)){ ?>
			             	<a href="<?= url::site("advanced_search/form_edit/$item->id") ?>"
				             	class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"> <?= t("Edit") ?>  </a>
			            <? } ?>
						<? if(access::can("add",$item)){ ?>
		              		<a href="<?= url::site("advanced_search/form_delete/$item->id") ?>"
				             	class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"> <?= t("Delete") ?> </a>
			            <? } ?>
						<? if(access::can("view_full",$item)){ ?>
							<a href="<?= url::site("downloadfullsize/send/$item->id") ?>"
								class="g-button ui-icon-left ui-state-default ui-corner-all"><?= t("Download") ?></a>
						<? } ?>
		          		</li>
		 		    </ul>
			          </td>
			        </tr>
			      
		  <? endforeach ?>
		  </table>
		    <? } else {  ?>
		    	<br><label> <?= t("There is no photos") ?></label><br><br>
		    <? } ?>
	 	</fieldset>
	 	<div>
	 		<input type="hidden" id="g-offset" name="offset" value="<?= $offset ?>" >
	 		<input type="hidden" id="g-total" name="total" value="<?= $total ?>" >
	 		<input type="hidden" id="g-limit" name="limit" value="<?= $limit ?>" >

			<ul class="g-paginator ui-helper-clearfix">
			  <li class="g-first">
			    <a class="g-button ui-icon-left ui-state-default ui-corner-all" onclick="first_page();">
			       <span class="ui-icon ui-icon-seek-first"></span><?= t("First") ?></a>
			    <a class="g-button ui-icon-left ui-state-default ui-corner-all" onclick="previous_page();">
			      <span class="ui-icon ui-icon-seek-prev"></span><?= t("Previous") ?></a>
			  </li>

			  <li class="g-info">
			  	<? if ($total > 0){ 
			  		if($limit > $total){
			  			$limit = $total;	
			  		}
			        echo t("%offset of %total", array("offset" => $offset+$limit, "total" => $total));
			    } ?>
			  </li>

			  <li class="g-text-right">
			  	<a class="g-button ui-icon-right ui-state-default ui-corner-all" onclick="next_page();">
			      <span class="ui-icon ui-icon-seek-next"></span><?= t("Next") ?></a>
			    <a class="g-button ui-icon-right ui-state-default ui-corner-all" onclick="last_page();">
			        <span class="ui-icon ui-icon-seek-end"></span><?= t("Last") ?></a>
			  </li>
			</ul>
		</div>

		
		 <script type="text/javascript">

			var limit = <?= $limit? $limit:0 ?>;
			var offset = <?= $offset? $offset:0 ?>;
			var total =  <?= $total? $total:0 ?>;

			function first_page(){
				if(offset > 0){
					document.getElementById('g-offset').value = 0;
					submit_form();		
				}
			}
			function last_page(){
				if(offset+limit != total){
					document.getElementById('g-offset').value = total-limit;
					submit_form();
				}
			}
			function previous_page(){
				if(offset > 0){
					document.getElementById('g-offset').value = offset-limit;
					submit_form();
				}
			}
			function next_page(){
				if(offset+limit != total){
					document.getElementById('g-offset').value = offset+limit;
					submit_form();
				}
			}
			function submit_form(){
				document.getElementById('g-advanced-search-form').submit();
			}

		</script>
 	</form>
</div>
