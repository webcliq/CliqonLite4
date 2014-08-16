// cliqon.js

var w = 440; var ww = document.body.clientWidth; var wl = ww - w; wl = (wl/2);
var t = 640; var tw = document.body.clientWidth; var tl = tw - t; tl = (tl/2);
var jlcd = "en";

/************************ Login and Logout  *******************************************************/

	function doLogin() {

		var e = this;
		var username = store.get('clq_username');
		if(username === undefined) {
			var formname = "formdialog";
			vex.dialog.open({
			  	message: lstr[0],
			  	input : '<div class="formouter"><p>' + lstr[11] + '</p><div id="loginform"></div></div>',
			  	afterOpen: function() {

					$('#loginform').clqform({
						'action':'#', 'method':'post', 'name' : formname, 'id': formname, 'class':'clqform clqform-aligned', 
			            'html':[
			            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
									'type': 'text', 'placeholder': lstr[12], 'name':'clq_username', 'class': {}, 'style': '', 'caption': lstr[12], 'required':'required'
			            		}},
			            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
									'type': 'password', 'placeholder': lstr[13], 'name': 'clq_password', 'class': {}, 'style': '',  'caption': lstr[13], 'required':'required'
			            		}},
			            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
									'type': 'select', 'name': 'langcd', 'class': {}, 'style': '',  'caption': lstr[14], 'options': {'en':{'html':'English', 'selected':'selected'}, 'es':'Español', 'de': 'Deutsch', 'ca':'Càtala'}
			            		}},
			            		{'type':'hidden', 'name': 'action', 'value': 'login'}
			            ]
			        })

  				},
				buttons: [
				    	$.extend({}, vex.dialog.buttons.YES, {
				      		text: lstr[0]
				    	}), $.extend({}, vex.dialog.buttons.NO, {
				      		text: lstr[1]
				    	})
				  ],
				  callback: function(data) {
			    	if (data === false) {
			      		return console.log('Cancelled');
			    	} else {
						// e.preventDefault(); e.stopImmediatePropagation;
						var clq_username = $('input[name="clq_username"]').getValue();
						var clq_langcd = $('input[name="clq_langcd"]').getValue();						
						var urlstr = './includes/post.php';
						$.post(urlstr, $('#' + formname).serialize(), function(data) {
							console.log(data);
							if(data === "success") {
								store.set('clq_username', clq_username);
								store.set('clq_langcd', clq_langcd);
								jlcd = clq_langcd;
								console.log(clq_username);
	                			var thisurl = '?page=admin&admin=dashboard&langcd=' + store.get('clq_langcd') + '&userid=' + store.get('clq_username');
	                			TINY.box.show({iframe:thisurl, top: 40, boxid:'frameless', width: 800, height: 640, fixed:false, opacity:20});
							} else {
								var n = noty({'text': lstr[2], 'layout': 'topCenter', 'type': 'error'});
							}
						});
						return false;
			    	}
			  	}
			});


		} else {
		    
		    if(store.get('clq_langcd') == '') {
		    	store.set('clq_langcd', 'en');
		    }

		    var thisurl = '?page=admin&admin=dashboard&langcd=' + store.get('clq_langcd') + '&userid=' + store.get('clq_username');
		    TINY.box.show({iframe:thisurl, top: 40, boxid:'frameless', width: 800, height: 640, fixed:false, opacity:20});
		    // $.popwin({windowURL: thisurl});
		}
	}

	function doLogout(e) {
		e.stopImmediatePropagation;		
		var urlstr = './includes/post.php';
		var data = 'action=logout';

	    $.ajax({
	        type: 'POST', url: urlstr,  data: data,
	        success: function() { reLoad(); },
	        failure: function() { reLoad(); }
	    });
		return false;
	}

/************************  Administration functions  **********************************************/

	/**
	* Routine for responding to clicks on the sidebar menu
	*/
	$('.itemselect').livequery('click', function(e) {
		var action = $(this).attr('rel'); 
		if(action == 'logout') {
			// store.remove('clq_username')
			store.clear();
			window.parent.TINY.box.hide();
			doLogout(e);
		} else {
			var type = $(this).data('type');
			var lcd = $(this).data('idiom'); var table = $(this).data('table');
			var urlstr = "?page=admin&admin=" + action + "&langcd=" + lcd + "&table=" + table + "&tabletype=" + type + "&userid=" + store.get('clq_username');
			console.log(urlstr);
			fLoad(urlstr);			
		}
	})

	/**
	* Populates the right hand side of a Dashboard
	*/
	function dashBoardTitle(str) {
		
		var title = '<div class="mr20">';
		title += '<div class="left cc">' + str + '</div>';
		title += '<div class="txtright right">';
		title += '<i class="topbutton fa fa-info-circle lp5 pointer" rel="adminhelp" title="' + lstr[4] + '" alt="' + lstr[3] + '" ></i>';
		title += '<i class="topbutton fa fa-refresh lp5 pointer" rel="reset" title="' + lstr[1] + '" alt="' + lstr[1] + '" ></i>';
		title += '<i class="topbutton fa fa-cogs lp5 pointer" id="utilitiesbutton" rel="utilities" title="' + lstr[6] + '" alt="' + lstr[6] + '" ></i>';
		// More here
		title += '</div>';
		title += '</div>';
		return title;			
	}

	/**
	* Populates the right hand side of a Tree or Table
	*/
	function repGenTitle(str) {
		
		var title = '<div class="mr20">';
		title += '<div class="left cc">' + str + '</div>';
		title += '<div class="txtright right">';
		title += '<i class="topbutton fa fa-plus-circle lp5 pointer"  rel="add" title="' + lstr[3] + '" alt="' + lstr[3] + '" ></i>';		
		title += '<i class="topbutton fa fa-info-circle lp5 pointer" rel="adminhelp" title="' + lstr[4] + '" alt="' + lstr[3] + '" ></i>';
		title += '<i class="topbutton fa fa-cogs lp5 pointer" id="utilitiesbutton" rel="utilities" title="' + lstr[6] + '" alt="' + lstr[6] + '" ></i>';
		// More here
		title += '</div>';
		title += '</div>';
		return title;			
	}

	/**
	* Populates the right hand side of a Tree or Table
	*/
	function dataTableTitle(table, type) {
		
		var title = '<div class="mr20">';
		title += '<div class="left cc">' + type + '</div>';
		title += '<div class="txtright right">';
		title += '<i class="topbutton fa fa-plus-circle lp5 pointer"  rel="add" title="' + lstr[3] + '" alt="' + lstr[3] + '" ></i>';		
		title += '<i class="topbutton fa fa-info-circle lp5 pointer" rel="adminhelp" title="' + lstr[4] + '" alt="' + lstr[3] + '" ></i>';
		title += '<i class="topbutton fa fa-refresh lp5 pointer" rel="reset" title="' + lstr[1] + '" alt="' + lstr[1] + '" ></i>';
		title += '<i class="topbutton fa fa-print lp5 pointer" rel="print" title="' + lstr[5] + '" alt="' + lstr[5] + '" ></i>';
		title += '<i class="topbutton fa fa-cogs lp5 pointer" id="utilitiesbutton" rel="utilities" title="' + lstr[6] + '" alt="' + lstr[6] + '" ></i>';
		title += '<i class="topbutton fa fa-scissors lp5 pointer" id="clearcachebutton" rel="clearcache" title="' + lstr[56] + '" alt="' + lstr[56] + '" ></i>';
		// More here
		title += '</div>';
		title += '</div>';
		return title;			
	}	

	function reSet(e, table, type) {
		reLoad();
	}

	function datatableFunctions() {

		// webix_cell where data-id = same
		$('.webix_cell').livequery('hover', function(e) {
			$('.webix_cell').removeClass('cell_hover');
			var rowid = $(this).index();
			var cols = $(this).parent().siblings();
			$(cols).each(function() {
				var cell = $(this).children('div').eq(rowid);
				$(cell).addClass('cell_hover');
			});
			$(this).addClass('cell_hover');
		})

	    $('.datepicker').livequery('click', function(e) { 
	        $(this).datepicker();
	        if(this.id == 'clq_dateto') {
	            var datefrom = $('#clq_datefrom').getValue();
	            var dateto = $(this).getValue();
	            var mdatefrom = moment(datefrom, 'DD-MM-YYYY');
	            var mdateto = moment(dateto, 'DD-MM-YYYY');
	            if(mdateto < mdatefrom) {
	                alert('invalid date');
	            }
	        }
	    });

		// Button Toolbar for Context Menu
		$('.idiombuttons').toolbar({
			content: '#admintoolbar', position: 'top', hideOnClick: true
		}).on('toolbarItemClick', function(event, clicked) {
			toolBarMenu(this, clicked);
		});	

	}

	function repgenFunctions() {
		// Button Toolbar for Context Menu
		$('.repgenitem').toolbar({
			content: '#admintoolbar', position: 'right', hideOnClick: true
		}).on('toolbarItemClick', function(event, clicked) {
			toolBarMenu(this, clicked);
		});	
	}

	function galleryFunctions() {
		// Button Toolbar for Context Menu
		$('.imgbutton').toolbar({
			content: '#admintoolbar', position: 'bottom', hideOnClick: true
		}).on('toolbarItemClick', function(event, clicked) {
			toolBarMenu(this, clicked);
		});	
	}

	function updateVal(table, fld, recid, newval) {
		var urlstr = "./includes/post.php";
		var postdata = "action=updatevalbyid&langcd=" + store.get('clq_langcd') + "&table=" + table + "&fld=" + fld + "&recid=" + recid + "&value=" + newval;
		$.post(urlstr, postdata, function(msg) {
			if(msg) {
				// Test Ok or Not
       			var match = /Success/.test(msg);
        		if (match == true) { 
					var completed = noty({text: lstr[10], 'layout': 'topCenter', 'type': 'success'});
				} else {
					var notcompleted = noty({text: lstr[21], 'layout': 'topCenter', 'type': 'error'});
				}
			} else {
				var notcompleted = noty({text: lstr[22], 'layout': 'topCenter', 'type': 'error'});
			}
		});
	}

/************************  Forms functions  *******************************************************/

	function topButtons(action, table, type, e) {					
		switch(action) {
			case 'add': publishForm('addrecord', table, type, 0, e); break;
			case 'reset': reSet(e, table, type); break;
			case 'utilities': utilities(e, table, type); break;
			case "clearcache": clearCache(); break;
			default: case 'adminhelp': displayAdminHelp(e, table, type); break;
			case 'print': printType(e, table, type); break;
		}
		return false;
	}

	function toolBarMenu(btn, clicked) {

		// Always
		var action = $(clicked).attr('rel');
		var table = $(btn).data('table');
		var type = $(btn).data('type');

		console.log(action, table, type);

		// Some choices
		if( isset( $(btn).data('ref') ) ) {var ref = $(btn).data('ref');}; // maybe one or the other .......
		if( isset( $(btn).data('id') ) ) {var recid = $(btn).data('id');}; //
		if( isset( $(btn).attr('rel') ) ) {var id = $(btn).attr('rel');}; //
		if( isset( $(btn).data('image') ) ) {var thisimg = $(btn).data('image'); thisimg = thisimg.replace('thumbs', ''); thisimg = thisimg.replace('thmb_', '');}; // Converted to overcome difference between Thumb image name and real image
		if( isset( $(btn).data('idiom') ) ) {var thisidiom = $(btn).data('idiom');};

		// Also see publishToolbar in clqadmin.class
		switch(action) {
			case 'editrecord': publishForm('editrecord', table, type, recid, btn); break;
			case 'editset': publishFormSet(table, type, ref, btn); break;
			case 'editcontent': publishTextEditor(table, type, recid); break;
			case 'jsonedit': publishJsonEditor(table, type, recid);	break;
			case 'editimage': publishFormSet(table, type, thisimg, btn); break;
			case 'viewrecord': publishViewRecord(table, type, recid); break;
			case 'viewset': publishViewSet(table, type, ref); break;
			case 'viewimg': publishImage(table, type, thisimg, btn); break;
			case 'runreport': publishReport(id); break;
			case 'viewcontent': publishViewContent(table, recid); break;	

			case 'deleteset': publishDelete(table, type, ref); break;
			case 'deleteimg': publishDelete(table, type, thisimg); break;

			default: alert('Toolbar not defined'); break;	
		}
		return false;
	}

	/**
	* Cliqon form class
	* 
	*/
	function publishForm(formtype, table, type, recid, e) {

		store.set('formtype', formtype);
		var btn = lstr[19], instructions = lstr[26], title = lstr[20];  // Update
		if(recid == 0) {btn = lstr[18]; instructions = lstr[15]; title = lstr[16];}; // Add
		// Get form definition 
		var url = "/includes/get.php?action=getformdef&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&recid=" + recid;
		$.get(url, function(formdef) {
			if(formdef != "") {
				var formname = "formdialog"; var dp = false;
				vex.dialog.open({
				  	message: title + ': ' + recid,
				  	input : '<style>.clqform textarea {font-size; 1em;}</style><div class="formouter"><p>' + instructions + '</p><div id="popupform"></div></div>',
				  	afterOpen: function() {

						$('#popupform').clqform({
							'action':'#', 'method':'post', 'name' : formname, 'id': formname, 'class':'clqform clqform-aligned', 
				            'html':formdef
				        });

						otherFormFunctions(table, type, e);

					},
					buttons: formButtons(table, type, formname, vex, dp)
				});

				// populate here
				if(formtype == "editrecord") {
					// Get details of Record and populate
					var options = [];
					var urlstr = '/includes/get.php?action=getrowbyid&table=' + table + '&recid=' + recid;
					$.getJSON(urlstr, function(data) {
						
						if(data) {
							$('#' + formname).populate(data, options);
						} else {
							var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
							return;
						}
					})				
				}

			} else {
				var n = noty({'text': lstr[22], 'layout': 'topCenter', 'type': 'error'});
			}
		}, 'json');
	} // ends Publish form function

	/**
	* Cliqon formset class
	* 
	*/
	function publishFormSet(table, type, ref, e) {

		store.set('formtype', 'editset');
		// Get form definition 
		var url = "/includes/get.php?action=getformsetdef&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&ref=" + ref;
		$.get(url, function(formdef) {
			if(formdef != "") {
				var formname = "formdialog"; var dp = false;
				vex.dialog.open({
				  	message: lstr[31] + ': ' + ref,
				  	input : '<style>.clqform textarea {font-size; 1em;}</style><div class="formouter"><p>' + lstr[32] + '</p><div id="popupform"></div></div>',
				  	afterOpen: function() {

						$('#popupform').clqform({
							'action':'#', 'method':'post', 'name' : formname, 'id': formname, 'class':'clqform clqform-aligned', 
				            'html':formdef
				        });

						otherFormFunctions(table, type, e);
					},
					buttons: formButtons(table, type, formname, vex, dp)
				});

				// Get details of Set and populate
				var options = [];
				var urlstr = "/includes/get.php?action=getsetbyref&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&ref=" + ref;
				$.getJSON(urlstr, function(data) {
					if(data) {
						$('#' + formname).populate(data, options);
					} else {
						var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
						return;
					}
				})				

			} else {
				var n = noty({'text': lstr[22], 'layout': 'topCenter', 'type': 'error'});
			}
		}, 'json')
	} // ends Publish form function

	// Form Shared functions
	function formButtons(table, type, formname, vex, dp) {

		var btns = [   	
		    $.extend({}, vex.dialog.buttons.NO, {text: lstr[17], className: 'vex-dialog-button-default', click: function($vexContent, e) {
				$vexContent.data().vex.value = 'reset'; vex.close($vexContent.data().vex.id);
			}}), 
		    $.extend({}, vex.dialog.buttons.NO, {text: lstr[7], className: 'vex-dialog-button-default', click: function($vexContent, e) {
				previewButton(formname);
			}}), 
		    $.extend({}, vex.dialog.buttons.NO, {text: lstr[19], className: 'vex-dialog-button-primary', click: function($vexContent, e) {
			    if(dp == false) { // Stops it being sent twice
                    dp = true;
					e.preventDefault(); e.stopImmediatePropagation;
					var urlstr = './includes/post.php';
					$.post(urlstr, $('#' + formname).serialize(), function(msg) { // formHash ??
						console.log(msg);
						if(msg !== "") {
							// Refresh the Table or Tree etc.
							$$("dtable").load("/includes/get.php?action=getdataset&table=" + table + "&tabletype=" + type + "&langcd=" + store.get('clq_langcd'), "json");
							var n = noty({'text': lstr[10], 'layout': 'topCenter', 'type': 'success'});
							vex.close($vexContent.data().vex.id);
							dp = false;
						} else {
							var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
						}
					});
					return false;    
				} else {
					var n = noty({'text': lstr[25], 'layout': 'topCenter', 'type': 'error'});
					return;
				}        				
			}})
		];
		return btns;
	}

	function otherFormFunctions(table, type, e) {

        moment().format();

        $.scriptPath = '/includes/js/';
        jQuery.require([
            'translate.js'
        ]);

        $.datepicker.setDefaults({
            regional: ['".$this->lcd."'],
            dateFormat: 'dd-M-yy'
        });
	    $('.date').livequery('click', function(e) {
	    	$(this).datepicker({inline: true});
	    });

		$('body div #adminspace').tinyscrollbar();

		$('.file-inputbutton').livequery('change', function (e) {
			var btn = this;  return fileButton(btn, e);
		});

        if( $('.rte').length > 0 ) {
 	
 	         jQuery.require([
                'texteditor.js'
            ]);

            $('.texteditor').each(function(e) {
                e.preventDefault;
                var textfld = $(this).attr('rel');
                $(this).texteditor({
                    textfld: textfld,
                    lcd: store.get('clq_langcd')
                });
            });      
        };

    	$('fieldset').collapse({closed : true });
    	$('.tags').tagsInput({width:'98%'});

        // Check if this value is unique
        $('.isunique').livequery('blur', function(e) {
            var fld = this;
            checkUnique(table, type, fld, e);
        }); 

	    $('.nextref').livequery('focus', function(e) {
	        var fld = this; getNextRef(table, type, fld, e)            
	    });

		$('.tl').each(function() {
		    if(store.get('formtype') != 'addrecord') {

		    	// we know id and name which is id_lcdcode
			    var name = $(this).attr('name');
			    var thislcd = name.substr(name.length - 2)
			    if(thislcd != store.get('clq_langcd')) {
			    	var label = $('label[for="'+ $(this).attr('id') +'"]');
			    	$(label).prepend('<i class="translatethiscontent fa fa-external-link-square box1l" data-field="'+name+'" data-thislcd="'+thislcd+'" style=""></i>');
			    }
		    }
		});
   

        $('.filepreviewbutton').livequery('click', function(e) {
            
            e.preventDefault();
            e.stopImmediatePropagation();
            // e.stopPropagation();
            var fldname = $(this).data('fieldname');
            
            var subdir = $(this).data('subdirectory');
            var filename = $('input[name="' + fldname + '"]').getValue();   
            if(filename != '') {    
                vex.dialog.alert('<img src="/' + subdir + filename + '" alt="/' + subdir + filename + '"" title="/' + subdir + filename + '" style="width: 340px;" />');
            } else {
                return;
            }
        });

        /* Translate this Field Content */
        $('.translatethiscontent').livequery('click', function() {
        	var fld = $(this).data('field');
			var thislcd = $(this).data('thislcd');
        	getThisTranslation(fld, thislcd);  
        }) 

        var id = $('.yesno').attr('id');
        var cb = '	<span style="" class="w4 clqform " style="margin-top: 5px;">' ;
        cb += '			<label class="w1 cblabel" style="vertical-align: top; text-align: left; margin-top: 5px;"><input type="radio" name="' + id + '" value="y" checked="checked" style="float:left; width: 30px; margin-top:5px" />' + lstr[37] + '</label>';
        cb += '			<label class="w1 cblabel" style="vertical-align: top; text-align: left; margin-top: 5px;"><input type="radio" name="' + id + '" value="n" style="float:left; width: 30px; margin-top:5px" />' + lstr[38] + '</label>';
        cb += '		</span>';
        $('.yesno').html(cb);

        // if('.lookup') {}
        var opts = $('.lookup').data('options');
        var fldid = $('.lookup').attr('id');
        if(opts != "") {
        	var urlstr = "/includes/get.php?action=getopts&langcd=" + store.get('clq_langcd') + "&list=" + opts;
        	$.getJSON(urlstr, function(data) {
        		var optionsAsString = "";
				$.each(data, function(idx, lbl) {
			      	optionsAsString += "<option value='" + idx + "'>" + lbl + "</option>";
			    });
				$('select[name="'+fldid+'"]').append(optionsAsString);
        	})
        }

        /*
		var custom_options = {
			byPassKeys: [8, 9, 37, 38, 39, 40],
			translation: {
				'0': {pattern: /\d/}, 
				'9': {pattern: /\d/, optional: true}, 
				'#': {pattern: /\d/, recursive: true}, 
				'A': {pattern: /[a-zA-Z0-9]/}, 
				'S': {pattern: /[a-zA-Z]/}
			};
		};
        */

		$('.date').mask('11/11/1111');
		$('.time').mask('00:00:00');
		$('.date_time').mask('00/00/0000 00:00:00');
		$('.phone').mask('(000) 000000');
		$('.mixed').mask('AAA 000-S0S');
		$('.currency').mask("#.##0,00", {reverse: true, maxlength: false});
		$('.ip_address').mask('0ZZ.0ZZ.0ZZ.0ZZ', {translation: {'Z': {pattern: /[0-9]/, optional: true}}});
		$('.percent').mask('##0,00%', {reverse: true});
		$('.clear-if-not-match').mask("00/00/0000", {clearIfNotMatch: true});

		$('.fa-picture-o').livequery('click', function(e) {
			// data-dir, data-extns
			uploadFilesForm();
		})

	}

	function previewButton(frm) {
        var formdata = $('#' + frm).serialize();
        formdata = rawurldecode(formdata);
        formdata = formdata.replace(/&/g, '<br />'); formdata = formdata.replace('?', '<br />');
        var n = noty({'text': '<h5>' + lstr[23] + ':</h5><span style="text-align: left;"><pre>' + formdata + '</pre></span>', layout: 'topCenter', type: 'alert', template: '<div class="noty_message" style="min-height: 200px;"><span class="noty_text" style="" ></span><div class="noty_close" ></div></div>'});
        return false;
	}

	function rawurldecode(str) {
      	return decodeURIComponent((str + '').replace(/%(?![\da-f]{2})/gi, function() {
          	return '%25';
       	}));
    }

    function publishTextEditor(table, type, recid) {
    	var thisurl = "/includes/get.php?action=texteditor&langcd=" + store.get('clq_langcd') + "&tabletype=" + type + "&table=" + table + "&recid=" + recid;
    	TINY.box.show({iframe:thisurl, top: 10, boxid:'frameless', width: 750, height: 590, fixed:false, opacity:20});
    }

    function publishJsonEditor(table, type, recid) {
    	var thisurl = "/includes/get.php?action=jsoneditor&langcd=" + store.get('clq_langcd') + "&tabletype=" + type + "&table=" + table + "&recid=" + recid;
    	TINY.box.show({iframe:thisurl, top: 10, boxid:'frameless', width: 750, height: 590, fixed:false, opacity:20});
    }

    function publishViewRecord(table, type, recid) {
		
		// Get view definition 
		var url = "/includes/get.php?action=getviewdef&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&recid=" + recid;
		$.getJSON(url, function(viewdef) {
			if(viewdef != "") {
				vex.dialog.open({
				  	message: lstr[29] + ': ' + recid,
				  	input : '<div class="formouter"><div id="popupview"></div></div>',
				  	afterOpen: function() {

						// $('#popupview').clqform(tempdef);
						$('#popupview').clqform({
							// 'action':'#', 'method':'post', 'name' : 'viewform', 'id': 'viewform', 'class':'clqform clqform-aligned',
							'type': 'container', 'class': 'clqtable', 'id':'clqtable',
				            'html':viewdef
				        })
				        
					},
					buttons: [   						    
					    $.extend({}, vex.dialog.buttons.NO, {text: lstr[30], className: 'clqbutton clqbutton-primary', click: function($vexContent, e) {
            				vex.close($vexContent.data().vex.id);
        				}}), 
					    $.extend({}, vex.dialog.buttons.NO, {text: lstr[5], className: 'clqbutton clqbutton-default', click: function($vexContent, e) {
            				// Print Content
            				$('#popupview').print();
        				}})
					]
				});

				// Get details of Set and populate
				var options = [];
				var urlstr = "/includes/get.php?action=getrowbyid&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&recid=" + recid;
				$.getJSON(urlstr, function(data) {
					if(data) {
						$('#clqtable').populate(data, options);
					} else {
						var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
						return;
					}
				})	

			} else {
				var n = noty({'text': lstr[22], 'layout': 'topCenter', 'type': 'error'});
			}
		});
	} // ends Publish View function		

    function publishViewSet(table, type, ref) {
		
		// Get view definition 
		var url = "/includes/get.php?action=getviewdef&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&ref=" + ref;
		$.getJSON(url, function(viewdef) {
			if(viewdef != "") {
				vex.dialog.open({
				  	message: lstr[29] + ': ' + ref,
				  	input : '<div class="formouter"><div id="popupview"></div></div>',
				  	afterOpen: function() {

						// $('#popupview').clqform(tempdef);
						$('#popupview').clqform({
							// 'action':'#', 'method':'post', 'name' : 'viewform', 'id': 'viewform', 'class':'clqform clqform-aligned',
							'type': 'container', 'class': 'clqtable', 'id':'clqtable',
				            'html':viewdef
				        })
				        
					},
					buttons: [   						    
					    $.extend({}, vex.dialog.buttons.NO, {text: lstr[30], className: 'clqbutton clqbutton-primary', click: function($vexContent, e) {
            				vex.close($vexContent.data().vex.id);
        				}}), 
					    $.extend({}, vex.dialog.buttons.NO, {text: lstr[5], className: 'clqbutton clqbutton-default', click: function($vexContent, e) {
            				// Print Content
            				$('#popupview').print();
        				}})
					]
				});

				// Get details of Set and populate
				var options = [];
				var urlstr = "/includes/get.php?action=getsetbyref&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&ref=" + ref;
				$.getJSON(urlstr, function(data) {
					if(data) {
						$('#clqtable').populate(data, options);
					} else {
						var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
						return;
					}
				})	

			} else {
				var n = noty({'text': lstr[22], 'layout': 'topCenter', 'type': 'error'});
			}
		});
	} // ends Publish View function	

	function publishViewContent(table, recid)	{
		var url = "/includes/get.php?action=getval&langcd=" + store.get('clq_langcd') + "&fld=clq_text&table=" + table + "&recid=" + recid;
		$.get(url, function(content) {
			vex.dialog.alert('<div class="popupcontent" style="font-size: 12px; line-height: 120%;">' + content + '</div>');
		});
	}       

	function publishDelete(table, type, ref) {
        var notyid = noty({
            text: lstr[27] + ref, type: 'confirm', layout: 'center',
            buttons: [
                {addClass: 'clqbutton clqbutton-primary', text: 'Ok', onClick: function(notyid) {
                    var thisurl = "/includes/post.php"; 
                    var postdata = "action=deleteset&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&ref=" + ref;
                    $.post(thisurl, postdata, function(msg) {
						if(msg) {
							// Test Ok or Not
	               			var match = /Deleted/.test(msg);
	                		if (match == true) { 
								// $$('dtable').load("/includes/get.php?action=getdataset&table=" + table + "&tabletype=" + type + "&langcd=" + store.get('clq_langcd'), "json"); // extra $
								// $$('dtable').refresh();
								// Refresh the Table or Tree etc.
								notyid.close();	
								// var completed = noty({text: lstr[28], 'layout': 'topCenter', 'type': 'success'});
								reLoad();
							} else {
								var notcompleted = noty({text: lstr[22], 'layout': 'topCenter', 'type': 'error'});
							}
						} else {
							var notcompleted = noty({text: lstr[22], 'layout': 'topCenter', 'type': 'error'});
						}
					});
                }},
                {addClass: 'clqbutton', text: lstr[17], onClick: function(notyid) {
                    notyid.close();
                }}
            ]
        });		
	}

	function fileButton(btn, e) {

        var filename = $('#fileuploadbutton').getValue();
        var fldname = $(btn).data('fieldname');
        var subdir = $(btn).data('subdirectory');
        var urlstr = 'post.php?action=fileupload&subdir=' + base64_encode(subdir) + '/';
        var files = e.target.files || e.dataTransfer.files;

        // process all File objects
        for (var i = 0, file; file = files[i]; i++) {

            // Set up the request.
            var xhr = new XMLHttpRequest();
            // Open the connection.
            xhr.open('POST', urlstr, true);
            // Set up a handler for when the request finishes.
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // File(s) uploaded.
                    $('input[name="' + fldname + '"]').setValue(filename);
              } else {
                console.log('ERRORS: ' + xhr.status);
              }
            };     
            xhr.setRequestHeader('X_FILENAME', file.name);  
            // Send the Data.
            xhr.send(file);

        }	
	}

    function checkUnique(table, type, fld, e) {
        
        var recid = $('input[name="recid"]');
        if(recid == 0) {
	        e.stopImmediatePropagation;  
	        var fldname = $(fld).attr('id'); 
	        var val = $(fld).getValue();
	        var url = '/includes/get.php'; 
	        var postdata = 'action=isunique&table=' + table + '&tabletype=' + type + '&fld=' + fldname + '&val=' + val;
	        $.ajax({
	            url: url, data: postdata,
	            success: function(msg) {
	                // Test Ok or Not
	                var match = /Exists/.test(msg);
	                if (match == true) { 
	                    var n = noty({layout: 'topCenter', theme: 'defaultTheme', type: 'error', text: lstr[24]});
	                    $('#' + fldname).val('').focus();
	                    $.noty.closeAll();
	                } 
	            }, failure: function() {
	                var n = noty({layout: 'topCenter', theme: 'defaultTheme', type: 'error', text: lstr[22]});
	            }
	        });        	
        }
        return false;    	
    }

    function getNextRef(table, type, fld, e) {
        
        if(store.get('formtype') == 'addrecord') {
	        e.stopImmediatePropagation; e.stopPropagation;

	        var fldname = $(fld).attr('id'); var val = $('input[name=' + fldname + ']').getValue();
	        var url = '/includes/get.php';
	        var postdata = 'action=getnextref&langcd=' + store.get('clq_langcd') + '&table=' + table + '&tabletype=' + type + '&fld=' + fldname + '&defval=' + val;

	        $.ajax({
	            url: url, data: postdata,
	            success: function(msg) {
	                $('form input[name=\'' + fldname + '\']').val(msg);
	                return false;
	            }, failure: function() {
	                var n = noty({layout: 'topCenter', theme: 'defaultTheme', type: 'error', text: lstr[22]});
	            }
	        });      	
        }
        return false;    	
    }           

    function getThisTranslation(fld, thislcd) {

    	var lcd = store.get('clq_langcd');
        var notyid = noty({
            text: fld + ': ' + lcd + ' >> ' + thislcd, type: 'confirm', layout: 'center',
            buttons: [
                {addClass: 'clqbutton clqbutton-primary clqbutton-sm', text: 'Ok', onClick: function(notyid) {
                    $('#' + fld).translate(lcd, thislcd, {subject:true} );
                    notyid.close();
                }},
                {addClass: 'clqbutton clqbutton-default clqbutton-sm', text: lstr[17], onClick: function(notyid) {
                    notyid.close();
                }}
            ]
        });
        return false;     	
    }

/************************  Image functions  ******************************************************/
	
    function publishImage(table, type, thisimg) {
		
    	// Get record from database and display the assembled record
		var url = "/includes/get.php?action=getrow&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&val=" + thisimg;
		$.get(url, function(data) {			
			
			var obj = jQuery.parseJSON(data);
			var content = '<img src=\"' + thisimg + '\" alt=\"' + thisimg + '\" title=\"' + obj.clq_value + '\" style=\"width: 380px; padding: 0; margin: 0;\" caption=\"' + obj.clq_text + '\"  />';
			vex.dialog.alert('<div class="popupcontent showcaption" style="font-size: 12px; line-height: 120%;">' + content + '</div>');
		
		});
    }

/************************  Report functions  ******************************************************/

	function publishReport(id) {
		
		// Result is JSON
		var url = "/includes/get.php?action=getreportdef&langcd=" + store.get('clq_langcd') + "&recid=" + id;
		$.getJSON(url, function(def) {
			$('#reportspace').empty();
			var urlstr = "/includes/get.php?action=runreport&langcd=" + store.get('clq_langcd') + "&recid=" + id;	
			$.getJSON(urlstr, function(data) {
				
				var tbl = '<table id="clqtable" class="pure-table">';
				tbl += '	<thead><tr>';
				_.each(def.columns, function(val, key, list) {
					tbl += '		<th id="' + key + '">' + val + '</th>';
				});
				tbl += '	</tr></thead><tbody>';
				$.each(data, function(i, row) {
					tbl += '	<tr>';
					_.each(def.columns, function(val, key, list) {
						tbl += '		<td id="' + key + row['id'] + '">' + row[key] + '</td>';
					});
					tbl += '	</tr>';	
				})
				tbl += '</tbody></table>';
				$('#reportspace').html(tbl);

				var grid = new webix.ui({
					container: 'reportspace',
				    view:"datatable",
				    scrollY: true
				});
				grid.parse("clqtable", "htmltable");
			});
		})
	}

/************************  Calendar functions  ****************************************************/

/************************ Utilities and associated Functions  *************************************/

	function capitalize(s) {
	    return s[0].toUpperCase() + s.slice(1);
	}

	function printType(e, table, type) {
		// Assemble the Params and then displayPopup with Report
		var title = lstr[5];
		var params = {'table': table, 'tabletype': type, 'action': 'runreport', 'recid': 249};
		displayPopup(e, title, params);
	}

	function displayAdminHelp(e, table, type) {

		$("#popup").dialog({
			bgiframe:true, modal:false, autoOpen: false,
			width:w, resizeable:false,
			title:lstr[04],
			position:[wl,20],
			show: {
				effect: "blind",
				duration: 400
			},
			hide: {
				effect: "fade",
				duration: 500
			},
			modal: false			
		});
		$("#popup").load('/includes/get.php?langcd=' + store.get('clq_langcd') + '&action=getadminhelp&table=' + table + '&tabletype=' + type);
		$("#popup").dialog('open');
	}

	function utilities(e, table, type) {
		$('#contextmenu').removeClass('hide').addClass('show');
	}

	function utilMenuClick(evt) {
		$('#contextmenu').removeClass('show').addClass('hide');
		var action = $(evt).attr('rel');
		switch (action) {

			case "export": 
				var formdef = [
					{"type":"hidden", "name":"action", "value": "doexport"},
					{"type":"hidden", "name":"langcd", "value": store.get('clq_langcd')},
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'select', 'id':'table', 'name': 'table', 'class': 'w5', 'css': {}, 'required':'required',  'caption': lstr[39], 
						'options': {'clqstring':{'html':'Clqstring', 'selected':'selected'}, 'clqdata':'Clqdata', 'clqitem': 'Clqitem'},
            		}},
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'select', 'required':'required',  'name': 'fields[]', 'id':'fields', 'class': 'w5', 'css': {}, 'multiple':'multiple', 'height':'80', 'caption': lstr[40]
						// Dynamic fields
            		}},
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'text', 'value':',', 'name': 'separator', 'class': 'w1', 'css': {},  'caption': lstr[42], 'required':'required'
            		}},
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'text', 'value':'"', 'name': 'enclosed', 'class': 'w1', 'css': {},  'caption': lstr[43], 'required':'required'
            		}},       
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'textarea', 'name':'query', 'class': 'w8 h3', 'css': {}, 'caption': lstr[45]
            		}},   
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'text', 'value': 'cliqonexport.csv', 'name':'filename', 'class': 'w8', 'css': {}, 'caption': lstr[41], 'required':'required'
            		}}
				] // JSON Form definition

				exportForm(formdef);

				$('#table').livequery('change', function(e) {
					var thistable = $(this).getValue();
					$('#fields').empty();
					switch(thistable) {
						case "clqstring":
							var newOptions = {
								'id': 'Id', 'clq_langcd': 'Language', 'clq_reference': 'Reference', 'clq_text': 'Content', 'clq_image': 'Image', 'clq_value': 'Value', 
								'clq_common': 'Common', 'clq_order': 'Order', 'clq_type': 'Type', 'clq_extra': 'Extra', 'clq_notes': 'Notes'
							}
						break;

						case "clqdata":
							var newOptions = {
						        'id': 'Id', 'clq_langcd': 'Language', 'clq_reference': 'Reference', 'clq_datefrom': 'Date From', 'clq_dateto': 'Date To',
						        'clq_display': 'Display (y/n)', 'clq_title': 'Title', 'clq_summary': 'Summary', 'clq_text': 'Content',
						        'clq_image': 'Image', 'clq_parent': 'Parent', 'clq_value': 'Value', 'clq_usage': 'Usage','clq_common': 'Common',
						        'clq_type': 'Type', 'clq_order': 'Order', 'clq_options': 'Options', 'clq_ownerid': 'Owner','clq_feed': 'News feed',
						        'clq_category': 'Category','clq_keywords': 'Keywords','clq_url': 'URL', 'clq_authorid': 'Author', 'clq_revision': 'Revision Number',
						        'clq_archive': 'Archive(y/n)','clq_extra': 'Extra', 'clq_notes': 'Notes'
							}
						break;

						case "clqitem":
							var newOptions = {
						        'id': 'Id', 'clq_reference': 'Reference', 'clq_datefrom': 'Date From', 'clq_dateto': 'Date To',
						        'clq_display': 'Display (y/n)', 'clq_title': 'Title', 'clq_summary': 'Summary', 'clq_text': 'Content',
						        'clq_image': 'Image', 'clq_parent': 'Parent', 'clq_value': 'Value', 'clq_usage': 'Usage','clq_common': 'Common',
						        'clq_type': 'Type', 'clq_order': 'Order', 'clq_options': 'Options', 'clq_ownerid': 'Owner','clq_category': 'Category',
						        'clq_keywords': 'Keywords','clq_url': 'URL', 'clq_archive': 'Archive(y/n)','clq_extra': 'Extra', 'clq_notes': 'Notes'
							}
						break;
					};
					$.each(newOptions, function(key, value) {
  						$('#fields').append($("<option></option>").attr("value", key).text(value));
					});
				})
			break;

			case "import": 
				var formdef = [
					{"type":"hidden", "name":"action", "value": "doimport"},
					{"type":"hidden", "name":"langcd", "value": store.get('clq_langcd')},

            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'checkbox', 'id': 'testimport', 'name': 'testimport', 'class': 'w1', 'css': {'float':'right', 'margin':'8px 275px 0px 0px'},  'checked':'checked',  'caption': lstr[49], 'value':'y'
            		}},

            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'checkbox', 'id': 'deleterecords', 'name': 'deleterecords', 'class': 'w1', 'css': {'float':'right', 'margin':'8px 275px 0px 0px'}, 'caption': lstr[51], 'value':'y'
            		}},

            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'checkbox', 'id': 'updaterecords', 'name': 'updaterecords', 'class': 'w1', 'css': {'float':'right', 'margin':'8px 275px 0px 0px'}, 'caption': lstr[52], 'value':'y'
            		}},

            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'text', 'placeholder': 'clqstring', 'name':'table', 'class': 'w4', 'css': {}, 'caption': lstr[39], 'required':'required'
            		}},

            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'text', 'value': 'cliqonexport.csv', 'name':'filename', 'class': 'w7', 'css': {}, 'caption': lstr[41], 'required':'required'
            		}}
				] // JSON Form definition

				var title = lstr[47];
				var instructions = lstr[48];
				importForm(formdef, title, instructions);
			break;

			case "query":
				var formdef = [
					{"type":"hidden", "name":"action", "value": "doquery"},
					{"type":"hidden", "name":"langcd", "value": store.get('clq_langcd')},	
            		{'type': 'container', 'class': 'clqcontrol-group', 'html': {
						'type': 'textarea', 'placeholder': 'SELECT * FROM clqstring', 'name':'query', 'class': 'w8 h8', 'caption': lstr[45]
            		}}
				] // JSON Form definition

				var title = lstr[45];
				var instructions = lstr[50];
				utilForm(formdef, title, instructions);
			break;

			case "changetype": 
	        	var titletext = lstr[34]; 
	        	var oncomplete = "datatable_reset";
	        	var postdata = "action=changetype&langcd=" + store.get('clq_langcd') + "&table=" + store.get('table') + "&tabletype=" + store.get('type'); // goes to Post.Php
	        	var template = "<h2 class='txtleft'>" + titletext + "</h2><div class='noty_message' style='border-top: 1px solid #ccc;'><span class='noty_text'>" + 
	        			"<form action='#' name='notypopupform' id='notypopupform' class='clqform clqform-aligned' method='post'>" + 
	        			"<div class='clqcontrol-group' style='padding-top:10px;'>" +
	        				"<label class=''>" + lstr[35] + "</label>" + 
	        				"<input type='text' class='' name='clq_reference' placeholder='ref'  />" +
	        			"</div>" +
	        			"<div class='clqcontrol-group'>" +
	        				"<label class=''>" + lstr[36] + "</label>" + 
	        				"<input type='text' class='' name='clq_type' placeholder='newtype'  />" +
	        			"</div></form>" +
	        			"</span><div class='noty_close'></div></div>";
        		notyForm(postdata, template, oncomplete, evt);
			break;

			// Update system
			case "update": 
				alert('Update facility');
			break;

			case "copycontent":
	        	var titletext = lstr[59]; 
	        	var oncomplete = "noaction";
	        	var postdata = "action=copycontent&langcd=" + store.get('clq_langcd') + "&table=" + store.get('table') + "&tabletype=" + store.get('type'); // goes to Post.Php
	        	var template = "<h2 class='txtleft'>" + titletext + "</h2><div class='noty_message' style='border-top: 1px solid #ccc;'><span class='noty_text'>" + 
	        			"<form action='#' name='notypopupform' id='notypopupform' class='clqform clqform-aligned' method='post'>" + 
	        			"<div class='clqcontrol-group'>" +
	        				"<label class=''>" + lstr[35] + "</label>" + 
	        				"<input type='text' class='' name='clq_reference' placeholder='reference'  />" +
	        			"</div></form>" +
	        			"</span><div class='noty_close'></div></div>";
        		notyForm(postdata, template, oncomplete, evt);
			break;

			case "uploadfiles":
				uploadFilesForm();
			break;

			default: alert(action); break;
		}
		return false;
	} 

	function uploadFilesForm() {
		
		// Will be perfect for Gallery 
		$("#popup").empty();
		$("#popup").dialog({
			bgiframe:true, autoOpen: true, width:w, resizeable:true, title:lstr[53], position:[wl,20], zIndex: 200000
		});
		var urlstr = "/includes/upload.php";
		var uploader = webix.ui({
			container:"popup", width: (w-60), align: 'center', minHeight: 150, borderless: true,
			view: "form", name: "uploadform", id: "uploadform", rows: [
				{ view:"text", label: lstr[54], labelAlign:"right", name:"directory", placeholder: "../data" },
				{ view:"text", label: lstr[41], labelAlign:"right", name:"filename", placeholder: lstr[41] },
				{ view: "uploader", name:"ufiles", id: "ufiles", value: lstr[55], multiple: false, autosend: false, upload: urlstr },
				{ view:"button", label: lstr[8], id: 'submitbutton', click: function(e) {
			        
					// var subdir = $('input[name="directory"]').getValue();
					// var newfilename = $('input[name="filename"]').getValue();
					// urlstr += '?langcd=' + store.get('clq_langcd') + '&newfilename=' + newfilename + '&subdir=' + subdir;		
						        
			        $$("ufiles").send(function(response) { //sending files
						
						$$("ufiles").onreadystatechange = function () {
							if (this.readyState == 4 && this.status == 200) {
								var response = this.responseText;
								alert('done use firebug to see response');
							}
						}								
						var file_id = $$("ufiles").files.getFirstId(); //getting the ID
						var fileobj = $$("ufiles").files.getItem(file_id).file; //getting file object
						filename - fileobj.name;
						console.log(filename);
						var completed = noty({text: response.status, 'layout': 'topCenter', 'type': 'success'})
				    })
				}}
			]					
		});		
	}

	// Display a Noty with micro form
	function notyForm(postdata, template, oncomplete, evt) {

        var notyid = noty({
            text: template, type: 'confirm', layout: 'center', modal: true, force: true, killer: true,
            buttons: [
                {addClass: 'clqbutton clqbutton-primary clqbutton-sm', text: 'Ok', onClick: function(notyid) {
                    var thisurl = "/includes/post.php";
                    postdata += "&" + $('#notypopupform').serialize();
                    $.post(thisurl, postdata, function(msg) {
						if(msg) {
							// Test Ok or Not
	               			var match = /Ok/.test(msg);
	                		if (match == true) { 
								// Refresh the Table or Tree etc.
								onComplete(oncomplete);
								notyid.close();	
								var completed = noty({text: lstr[33], 'layout': 'topCenter', 'type': 'success'});
							} else {
								var notcompleted = noty({text: lstr[21], 'layout': 'topCenter', 'type': 'error'});
							}
						} else {
							var notcompleted = noty({text: lstr[22], 'layout': 'topCenter', 'type': 'error'});
						}
					});
                }},
                {addClass: 'clqbutton clqbutton-sm', text: lstr[17], onClick: function(notyid) {
                    notyid.close();
                }}
            ]
        });	
		return notyid;
	}

	function onComplete(action) {
		
		switch(action) {

			case "noaction": return;	break;

			case "datatable_reset": 
			default: 
				$$("dtable").load("/includes/get.php?action=getdataset&table=" + store.get('table') + "&tabletype=" + store.get('type') + "&langcd=" + store.get('clq_langcd'), "json");
			break;
		}
	}

	// Display a Vex Dialogue with major Form
	function utilForm(formdef, title, instructions) {

		var formname = "utildialog"; var dp = false;
		vex.dialog.open({
		  	message: title,
		  	input : '<div class="formouter"><p>' + instructions + '</p><div id="popupform"></div></div>',
		  	afterOpen: function() {
				$('#popupform').clqform({
					'action':'#', 'method':'post', 'name' : formname, 'id': formname, 'class':'clqform clqform-aligned', 
		            'html':formdef
		        });
			},
			buttons: [   	
			    // Cancel
			    $.extend({}, vex.dialog.buttons.NO, {text: lstr[17], className: 'vex-dialog-button-default', click: function($vexContent, e) {
					$vexContent.data().vex.value = 'reset'; vex.close($vexContent.data().vex.id);
				}}), 
				// Submit
			    $.extend({}, vex.dialog.buttons.NO, {text: lstr[8], className: 'vex-dialog-button-primary', click: function($vexContent, e) {
				    if(dp == false) { // Stops it being sent twice
	                    dp = true;
						e.preventDefault(); e.stopImmediatePropagation;
						var urlstr = './includes/post.php';
						$.post(urlstr, $('#' + formname).serialize(), function(msg) { // formHash ??
							console.log(msg);
							if(msg !== "") {
								// Refresh the Table or Tree etc.

								var n = noty({'text': lstr[10], 'layout': 'topCenter', 'type': 'success'});
								vex.close($vexContent.data().vex.id);
								dp = false;
							} else {
								var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
							}
						});
						return false;    
					} else {
						var n = noty({'text': lstr[25], 'layout': 'topCenter', 'type': 'error'});
						return;
					}        				
				}})
			]
		});
	} // ends Publish form function

	// Display a jQueryUI Dialog
	function displayPopup(e, title, params) {
		
		$("#popup").dialog({
			bgiframe:true, autoOpen: false, width:t, resizeable:true, title:title, position:[tl,20],
			show: {
				effect: "blind",
				duration: 400
			},
			hide: {
				effect: "fade",
				duration: 500
			}, modal: false,
			iconButtons: [
                {
                    text: "Print",
                    icon: "ui-icon-print",
                    click: function(e) {
                        $("#popup").print();
                    }
                }
            ]
		});
		var data = ""; 
		$.each(params, function(name, value) {
			data += '&' + name + '=' + value;
		});
		var urlstr = '/includes/get.php?langcd=' + store.get('clq_langcd') + data;
		console.log(urlstr);
		$("#popup").load(urlstr);
		$("#popup").dialog('open');	
	}

	// Display a Vex Dialogue with support for Export
	function exportForm(formdef) {

		var title = lstr[46];
		var instructions = lstr[44];
		var formname = "utildialog"; var dp = false;
		vex.dialog.open({
		  	message: title,
		  	input : '<div class="formouter"><p>' + instructions + '</p><div id="popupform"></div></div>',
		  	afterOpen: function() {
				$('#popupform').clqform({
					'action':'#', 'method':'post', 'name' : formname, 'id': formname, 'class':'clqform clqform-aligned', 
		            'html':formdef
		        });
			},
			buttons: [   	
			    // Cancel
			    $.extend({}, vex.dialog.buttons.NO, {text: lstr[17], className: 'vex-dialog-button-default', click: function($vexContent, e) {
					$vexContent.data().vex.value = 'reset'; vex.close($vexContent.data().vex.id);
				}}), 
				// Export
			    $.extend({}, vex.dialog.buttons.NO, {text: lstr[46], className: 'vex-dialog-button-primary', click: function($vexContent, e) {
				    if(dp == false) { // Stops it being sent twice
	                    dp = true;
						e.preventDefault(); e.stopImmediatePropagation;
						var urlstr = './includes/post.php';
					    $.fileDownload(urlstr, {
					        failMessageHtml: noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'}),
					        httpMethod: "POST",
					        data: $('#' + formname).serialize()
					    });
					} else {
						var n = noty({'text': lstr[25], 'layout': 'topCenter', 'type': 'error'});
						return;
					}        				
				}})
			]
		});
	} // ends Publish form function

	// Display a Vex Dialogue with support for importing records
	function importForm(formdef) {

		var title = lstr[46];
		var instructions = lstr[44];
		var formname = "importdialog"; 
		vex.dialog.open({
		  	message: title,
		  	input : '<div class="formouter"><p>' + instructions + '</p><div id="popupform"></div></div>',
		  	afterOpen: function() {
				$('#popupform').clqform({
					'action':'#', 'method':'post', 'name' : formname, 'id': formname, 'class':'clqform clqform-aligned', 
		            'html':formdef
		        });
			},
			buttons: [   	
			    // Cancel
			    $.extend({}, vex.dialog.buttons.NO, {text: lstr[17], className: 'vex-dialog-button-default', click: function($vexContent, e) {
					$vexContent.data().vex.value = 'reset'; vex.close($vexContent.data().vex.id);
				}}), 
				// Export
			    $.extend({}, vex.dialog.buttons.NO, {text: lstr[46], className: 'vex-dialog-button-primary', click: function($vexContent, e) {
					e.preventDefault(); e.stopImmediatePropagation;
					var urlstr = './includes/post.php';
					$.post(urlstr, $('#' + formname).serialize(), function(data) { // formHash ??
						if(data !== "") {

							$("#popup").dialog({
								bgiframe:true, autoOpen: false, width:w, resizeable:true, title:title, position:[wl,20], modal: false,
								iconButtons: [
					                {
					                    text: lstr[5],
					                    icon: "ui-icon-print",
					                    click: function(e) {
					                        $("#popup").print();
					                    }
					                }
					            ]
							});
							$("#popup").html(data);
							$("#popup").dialog('open');	

						} else {
							var n = noty({'text': lstr[21], 'layout': 'topCenter', 'type': 'error'});
						}
					});
					return false;    			
				}})
			]
		});
	} // ends Import form function

	function clearCache() {
		var urlstr = './includes/get.php?langcd=' + store.get('clq_langcd') + '&action=clearcache';
		$.get(urlstr, function(msg) { // formHash ??
			// Test Ok or Not
       		var match = /Success/.test(msg);
        	if (match == true) { 
				var n = noty({'text': lstr[28], 'layout': 'topCenter', 'type': 'success'});
			} else {
				var n = noty({'text': lstr[22] + ': ' + msg, 'layout': 'topCenter', 'type': 'error'});
			}
		});
		return false;		
	}