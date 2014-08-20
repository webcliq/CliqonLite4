<?php
// Set language for installation. Language code must exist in idiom.lcd array
$lcd = "en"; $rootpath = "../";
$includepath = $rootpath."install/includes/";
require $includepath."error.php";
require "clqinstall.class.php";
$clqlcd = new clqlcd(); 

/*
Returns an array of the following 4 item array for each language the os supports:
0 - full language abbreviation, like en-ca
1 - primary language, like en
2 - full language string, like English (Canada)
3 - primary language string, like English

Use languages() to get Full name from code

To change Language on command line = Domain/install/?langcd=code
*/

if($_REQUEST['langcd']) {
  $lcd = $_REQUEST['langcd'];
  $langcd = $clqlcd->languages();
  $lcdname = $langcd[$lcd];   
} else {
  $langcd = $clqlcd->get_languages('data');
  $lcd = $langcd[0][1];
  $lcdname = $langcd[0][3];   
}; 

// A few essential configuration variables to assist with the installation process       
$url = "http://".$_SERVER['SERVER_NAME']."/";

$config = array(   
    'site.favicon' => $rootpath.'admin/theme/icons/cliqon.ico',
);

require_once $rootpath."install/idiom/idiom_".$lcd.".lcd";

?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Cliqon Lite Install</title>
<link href="styles/smart_wizard.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo $includepath ?>php.js"></script>
<script type="text/javascript" src="<?php echo $includepath ?>jquery.js"></script>
<script type="text/javascript" src="<?php echo $includepath ?>jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $includepath ?>clqstartup.js"></script>
<script type="text/javascript" src="<?php echo $includepath ?>smartwizard.js"></script>
</head>
<body>
	<div id="container" class="container fw" >
		<table border="0" cellpadding="0" cellspacing="0">
			<tr><td> 
				<!-- Smart Wizard -->
				<h2><?php echo $istr[0] ?></h2> 
				<div id="wizard" class="swMain">
					<ul>
						<li><a href="#step-1">
						<label class="stepNumber">1</label>
						<span class="stepDesc">
						   <?php echo $istr[1] ?><br />
						   <small><?php echo $istr[2] ?></small>
						</span>
					</a></li>
						<li><a href="#step-2">
						<label class="stepNumber">2</label>
						<span class="stepDesc">
						   <?php echo $istr[3] ?><br />
						   <small><?php echo $istr[4] ?></small>
						</span>
					</a></li>
						<li><a href="#step-3">
						<label class="stepNumber">3</label>
						<span class="stepDesc">
						   <?php echo $istr[5] ?><br />
						   <small><?php echo $istr[6] ?></small>
						</span>                   
					 </a></li>
						<li><a href="#step-4">
						<label class="stepNumber">4</label>
						<span class="stepDesc">
						   <?php echo $istr[7] ?><br />
						   <small><?php echo $istr[8] ?></small>
						</span>                   
					</a></li>
					</ul>

					<div id="step-1">	
						<h2 class="StepTitle">File system</h2>
						
						<div class="pad">
						<ul type="disk">
						  <li><?php echo $istr[11] ?></li>
								<li><?php echo $istr[12] ?></li>
								<li><?php echo $istr[13] ?></li>
						  <li><?php echo $istr[14] ?></li>
						</ul>

						<p class=""><strong><?php echo $istr[11] ?></strong></p>
						<p><?php echo $istr[51] ?></p>


						<p class=""><strong><?php echo $istr[15] ?></strong></p>
						<p><?php echo $istr[52] ?></p>

						<p><strong><?php echo $istr[13] ?></strong></p>
						<p><?php echo $istr[53] ?></p>

						<p><strong><?php echo $istr[14] ?></strong></p>
						<p><?php echo $istr[54] ?></p> 
						<p><?php echo $istr[55] ?></p>

						<div style="width:120px;"><button id="directorybutton"><?php echo $istr[16] ?></button></div>
						<div style="float:left; margin-left:140px; margin-top:-30px;"><p id="directories" ></p></div>
					  </div>
					</div>


					<div id="step-2">
					<h2 class="StepTitle"><?php echo $istr[18] ?></h2>	
					
					<div class="pad">
					<ul type="disk">
					  <li><?php echo $istr[19] ?></li>
					</ul>

					<p class=""><strong><?php echo $istr[10] ?></strong></p>
					<p><?php echo $istr[56] ?></p>
					<p><?php echo $istr[57] ?></p>
					<p><?php echo $istr[58] ?></p>
					<p><?php echo $istr[59] ?></p>

					<form name="configform" id="configform">
          
					<!-- Form -->
					<div class="form" style="width:200px; float:left;">
					
						<p class="inline-field">
							<label class="label"><?php echo $istr[21] ?>:</label>
							<select class="field size4" name="dbtype" id="dbtype">
							  <option value="mysql" selected="selected">MySQL</option>
							  <option value="sqlite">SQLite</option>
							  <option value="pgsql">Postgres</option>
							  <option value="pgsql">Firebird</option>
							</select>
						</p>           
						<p class="inline-field">
							<label class="label"><?php echo $istr[22] ?>:</label>
							<input type="text" class="field size4" name="db" id="db" value="cliqonlite" />
						</p>
						<p class="inline-field">
							<label class="label"><?php echo $istr[23] ?>:</label>
							<input type="text" class="field size4" name="user" id="user" placeholder="mysqluser" />
						</p>

						<hr />

						<p class="inline-field">
							<label class="label"><?php echo $istr[25] ?>:</label>
							<input type="text" class="field size4" name="adminuser" id="adminuser" placeholder="admin" />
						</p>
						<p class="inline-field">
							<label class="label"><?php echo $istr[28] ?>:</label>
							<input type="text" class="field size4" name="adminpassword" id="adminpassword" placeholder="password" />
						</p>                            
					</div>
            
					<div class="form" style="width:500px; float:left;">

						<p class="inline-field">
							<label class="label"><?php echo $istr[26] ?>:</label>
							<input type="text" class="field size4" name="server" id="server" value="localhost" />
						</p>
						<p class="inline-field">
							<label class="label"><?php echo $istr[27] ?>:</label>
							<input type="text" class="field size4" name="portno" id="portno" value="3306" />
						</p>
						<p class="inline-field">
							<label class="label"><?php echo $istr[24] ?>:</label>
							<input type="text" class="field size4" name="password" id="password" placeholder="mysqlpassword" />
						</p>

						<hr />

						<p class="inline-field">
							<label class="label"><?php echo $istr[29] ?>:</label>
							<input type="text" class="field" name="idiomarray" id="idiomarray" style="width:500px;" placeholder="en|English,es|Español,de|Deutsch" />
						</p>
						<p class="inline-field">
							<label class="label"><?php echo $istr[30] ?>:</label>
							<input type="text" class="field" name="idiomflags" id="idiomflags" style="width:500px;" placeholder="en|en.gif,es|es.gif,de|de.gif" />
						</p>

									
					</div>
					<!-- End Form -->
            
					<!-- Form Buttons -->
					<div style="clear:both;">
							<button id="previewconfigform" ><?php echo $istr[9]; ?></button>
							<button id="submitconfigform" ><?php echo $istr[10]; ?></button>
					</div>
					<!-- End Form Buttons -->
					</form>  
				  </div>       
				</div>  

				<div id="step-3">
				<h2 class="StepTitle"><?php echo $istr[5] ?></h2>	
				<div class="pad">
				<ul type="disk">
				  <li><?php echo $istr[66] ?></li>
				  <li><?php echo $istr[31] ?></li>
				  <li><?php echo $istr[32] ?></li>
				</ul>

				<p class=""><strong><?php echo $istr[66] ?></strong></p>
				<p><?php echo $istr[67] ?></p>

				<div style="width:140px;"><button id="databasecreatebutton"><?php echo $istr[68] ?></button></div>
				<div style="float:left; margin-left:140px; margin-top:-30px;" id="dbresult"></div><br />

				<p class=""><strong><?php echo $istr[31] ?></strong></p>
				<p><?php echo $istr[60] ?></p>
				<p><?php echo $istr[61] ?></p>

				<div style="width:120px;"><button id="tablecreatebutton"><?php echo $istr[33] ?></button></div>
				<div style="float:left; margin-left:140px; margin-top:-30px;" id="tableresult"></div><br />

				<p style=""><strong><?php echo $istr[32] ?></strong></p>
				<p><?php echo $istr[62] ?></p>

				<div style="width:120px;"><button id="dataimportbutton"><?php echo $istr[34] ?></button></div>
				<div style="float:left; margin-left:140px; margin-top:-30px;" id="dataimport" ></div>

			  </div>              				          
			</div>


  			<div id="step-4">
            <h2 class="StepTitle"><?php echo $istr[35] ?></h2>	
            <div class="pad">
            <ul type="disk">
              <li><?php echo $istr[36] ?></li>
              <li><?php echo $istr[37] ?></li>
              <li><?php echo $istr[38] ?></li>
            </ul>

            <p class=""><strong><?php echo $istr[39] ?></strong></p>
            <p><?php echo $istr[63] ?></p>

            <p><strong><?php echo $istr[40] ?></strong></p>
            <p><?php echo $istr[64] ?></p>

            <p><strong><?php echo $istr[41] ?></strong></p>
            <p><?php echo $istr[65] ?></p> 
            <p><?php echo $istr[69] ?></p> 
          </div>                			
        </div>
  		</div>
		<!-- End SmartWizard Content -->  		
 		
		</td></tr>
		</table>
	</div> 	
	<script type="text/javascript">
	var jlcd = '<?php echo $lcd ?>'; 
	var url = 'get.php';
	$(function(){

	  $(this).ajaxStart(function() {
		$('#loading').removeClass('loadinghide');
		$('#loading').show();
	  }).ajaxStop(function() {
		$('#loading').fadeOut(500);
	  });   
	  
	  $('button').button();

	  // Smart Wizard 	
	  $('#wizard').smartWizard();

	  $('.buttonFinish').on('click', function(e) {
		  uLoad('http://' + document.location.hostname + '/?page=admin');
	  });

	  $('#directorybutton').livequery('click', function(e) {
		  e.preventDefault;
		  e.stopImmediatePropagation;
		  $('#directories').load(url + '?langcd=<?php echo $lcd ?>&action=directories');
	  });

	  $('#previewconfigform').on('click', function(e) {
		  
		  e.preventDefault;
		  e.stopImmediatePropagation;

		  var postdata = $('#configform').serialize();
		  postdata = str_replace('&', '<br />', postdata);
		  postdata = str_replace('%5B%5D','', postdata);
		  postdata = str_replace('%2C',',', postdata);
		  postdata = str_replace('%7C','|', postdata);
		  postdata = str_replace('%C3%B1','n', postdata);
						  
		  apprise(postdata);  
		  return false;  
	  });

	  $('#submitconfigform').on('click', function(e) {

		  e.preventDefault;
		  e.stopImmediatePropagation;

		  var postdata = 'langcd=<?php echo $lcd ?>&action=createconfigfile&' + $('#configform').serialize();
		  // apprise(url+'?'+postdata);

		  $.ajax({
			  url: url,
			  data: postdata,
			  type: 'POST',
			  success: function(msg) {
				  if(msg) {
					apprise('<strong>' + msg + '</strong>');
				  } else {
					apprise('<strong><?php echo $istr[20] ?> - ce</strong>');
				  }
			  },
			  failure: function() {
				apprise('<strong><?php echo $istr[20] ?> - ae</strong>');
			  }
		  })  

		  return false;
	  });
	  
	  $('.close').on('click', function() {
		  var id = $(this).attr('rel');
		  $('#' + id).hide();
	  });

	  $('#databasecreatebutton').on('click', function() {
		  $('#dbresult').html('<img src="<?php echo $rootpath ?>install/loader.gif" style="" />');
		  $('#dbresult').load(url + '?langcd=<?php echo $lcd ?>&action=createdatabase');
	  });
	  
	  $('#tablecreatebutton').on('click', function() {
		  $('#tableresult').html('<img src="<?php echo $rootpath ?>install/loader.gif" style="" />');
		  $('#tableresult').load(url + '?langcd=<?php echo $lcd ?>&action=createtables');
	  });    
	  
	  $('#dataimportbutton').on('click', function() {
		  $('#dataimport').html('<img src="<?php echo $rootpath ?>install/loader.gif" style="" />');
		  $('#dataimport').load(url + '?langcd=<?php echo $lcd ?>&action=createbasedata');
	  }); 


	});
	</script>	
</body>
</html>
<div id="container" class="container fw" style="margin-top:20px; position:relative;">
<div id="loading" style="" class="loadinghide" style="display:hidden;"></div>
