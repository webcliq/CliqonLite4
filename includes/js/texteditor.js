// Cliqon Text Editor addition

(function ($) {
	$.fn.texteditor = function(options) {

		var te = this;
		var settings = $.extend({
			textfld: '',
			lcd: ''
		}, options );
		var tfld = $('textarea[name="' + settings.textfld + '"]');	
		var tfldx = settings.textfld;	

		// Build Toolbar
		var btnarray = {
			'font':'font',
			'bold': 'bold',
			'italic': 'italic',
			'underline': 'underline',
			'listul': 'list-ul',
			'listol': 'list-ol',
			'lineitem': 'bars',
			'img': 'picture-o',
			'link': 'external-link',
			'break':'share',
			'color':'tint',	
			'snippet': 'html5',		
			'preview': 'clipboard'
		};

		var spanstyle = {
			'width': '98%',
			'height': '20px',
			'background-color': '#DBEAF9',
			'padding': '0px',
			'padding-bottom': '4px',
			'padding-left': '6px'
		};

		var istyle = {
			'padding-right':'6px',
			'cursor':'pointer',
			'height':'16px',
			'vertical-align':'middle',
			// 'border':'border: 1px solid #ccc;',
		};

		var btns = "";
		$.each(btnarray, function(key, val) {
			btns += '<i class="teicn fa fa-' + val + '" rel="' + key + '" data-fld="' + settings.textfld + '"></i>';
		});

		var messagepop = {
			'position':'absolute',
			'background-color':'#fff',
			'border':'1px solid #ccc',
			'cursor':'default',
			'display':'none',
			'margin':'0px',
			'padding':'5px',
			'padding-right':'0px', 
			'text-align':'left',
			'width':'100px',
			'min-height':'100px',
			'z-index':'30000',
			'top': '80px', 'left': '115px',
			'webkit-box-shadow':'3px 3px 6px 0px rgba(50, 50, 50, 0.75)',
			'-moz-box-shadow':'3px 3px 6px 0px rgba(50, 50, 50, 0.75)',
			'box-shadow':'3px 3px 6px 0px rgba(50, 50, 50, 0.75)',
		}

		te.html(btns).css(spanstyle);
		$('.teicn').css(istyle);

		// Build Click function
		$('.teicn').livequery('click', function(e) {
			var action = $(this).attr('rel');
					
			switch (action) {

				// 'font', 'bold', 'italic', 'underline', 'listul', 'listol', 'lineitem', 'img', 'link', 'break', 'color', 'snippet', 'preview'
				case 'bold': wrapText("<strong>", "</strong>"); break;
				case 'italic': wrapText("<i>", "</i>"); break;				
				case 'underline': wrapText("<u>", "</u>"); break;
				case 'listul': wrapText("<ul>", "</ul>"); break;
				case 'listol': wrapText("<ol>", "</ol>"); break;				
				case 'lineitem': wrapText("<li>", "</li>"); break;
				case 'break': insertText("<br/>"); break;

				case 'img':
					var imgname = prompt('Please enter an image name','blank.gif');
					if(imgname != '') {
						var imghtml = '<img src="' + imgname + '" alt="' + imgname + '" title="' + imgname + '" style="height: 50px; width: 50px" class="imgclass" />';
						insertText(imghtml);						
					}
				break;

				case 'link':
					var url = prompt('Please enter a complete URL','http://');
					if(url != '') {
						var urlstr = '<a href="' + url + '" alt="' + url + '" title="' + url + '" target="_blank" class="" style="" >';
						wraptText(urlstr, '</a>');						
					}
				break;

				case 'snippet': default:
					var starting = prompt('Enter starting code','<span>');
					if(starting != '') {
						var ending = prompt('Enter ending code','</span>');
						if(ending != '') {
							wraptText(starting, ending);
						}						
					}
				break;
				
				case 'preview':
					var n = noty({'text': tfld.val(), 'layout': 'topCenter', 'type': 'alert'});
				break;

				// Colour Picker, then wrap
				case 'color': case 'font':
					if(action == 'font') {
						var content = fontPicker();
					} else {
						var content = colorPicker();
					}
					// Generate a div with content and write it to the popup form Div
					var formdiv = tfld.closest('form');
					var picker = '<div id="messagepop">' + popCloser() + '<div id="messagepopinner">' + content + '</div></div>';
					$(formdiv).append(picker);
					$('#messagepop').css(messagepop);
					if(action == 'font') {
						$('#messagepop').css({'width':'250px', 'min-height':'75px'});
					};
					$('#messagepop').show();
				break;				

			}

			$('.messagepopclose').livequery('click', function(e) {
				$('#messagepop').hide().remove();
			})

			function wrapText(openTag, closeTag) {
			    var len = tfld.val().length;
			    if(len > 0) {
				    var start = tfld[0].selectionStart;
				    var end = tfld[0].selectionEnd;
				    var selectedText = tfld.val().substring(start, end);
				    var replacement = openTag + selectedText + closeTag;
				    tfld.val(tfld.val().substring(0, start) + replacement + tfld.val().substring(end, len));	
				    return;		    	
			    } else {
			    	var n = noty({'text': 'Please create a selection', 'layout': 'topCenter', 'type': 'error'});
			    	return;
			    }
			}	

			function insertText(text) {
				var position = tfld.getCursorPosition()
				var content = tfld.val();
				var newContent = content.substr(0, position) + text + content.substr(position);
				tfld.val(newContent);			    
			}

			function popCloser() {
				return '<div style="float:right; text-align:right; width:30px; height: 20px; cursor:pointer; padding-right: 5px;" class="fa fa-times-circle messagepopclose"></div><br />';
			}

			function colorPicker() {
 	
				var colors = {
					'White':'#FFFFFF',
					'LightGrey':'#CCCCCC',
					'Gray':'#808080',
					'Black':'#000000',
					'Red':'#FF0039',
					'Maroon':'#800000',
					'Yellow':'#FFFF00',
					'Olive':'#808000',
					'Lime':'#00FF00',
					'Green':'#3FB618',
					'Bootstrap':'#007FFF',
					'Teal':'#008080',
					'OJBlue':'#003366',
					'Navy':'#000080',
					'Orange':'#FF7518',
					'Purple':'#9954BB',
				};
				var content = '';
				$.each(colors, function(key, val) {
					content += '<div class="choosecolor" rel="' + val + '" style="background-color: ' + val + '; width: 18px; height: 15px; cursor: pointer; border: 1px solid #ccc; padding:0px; margin: 0px 5px 5px 0px; float: left; display: inline;" alt="' + key + '" title="' + key + '"></div>';
				});
				return content;
			}

			function fontPicker() {

				var fonts = {
					0:'Times Roman', 1:'Trebuchet', 2:'Font Awesome', 3:'Neris', 4:'Serif', 5:'Sans'
				};
				var content = '<select id="selectfont" name="selectfont" style="width: 150px; margin: 5px; padding: 4px;" >';
				$.each(fonts, function(key,val) {
					content += '<option value="' + val + '" style=" ' + val + '" >' + val + '</option>';
				});
				content += '</select><button type="button" class="btn btn-default btn-sm selectfont">Select</button>';
				return content;
			}

			$('.choosecolor').livequery('click', function(e) {
				var color = $(this).attr('rel');
				wrapText('<span style="color: ' + color + '">', '</span>');
				return false;
			})		

			$('.selectfont').livequery('click', function(e) {
				var fontfam = $('select[name="selectfont"]').getValue();
				wrapText('<span style="font-family: \'' + fontfam + '\'">', '</span>');
				return;
			})		
 
		})

		var getCursorPosition = function () {
	        var el = $(this).get(0);
	        var pos = 0;
	        if ('selectionStart' in el) {
	            pos = el.selectionStart;
	        } else if ('selection' in document) {
	            el.focus();
	            var Sel = document.selection.createRange();
	            var SelLength = document.selection.createRange().text.length;
	            Sel.moveStart('character', -el.value.length);
	            pos = Sel.text.length - SelLength;
	        }
	        return pos;
	    }
		return;

	};
}(jQuery));