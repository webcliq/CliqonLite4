/*
* This Class - clqcart joins together the smartcart js
* and Cliqon cart functions
*
* Cart dependencies are:
* clqjsdb.js, transparency.js, accounting.js, // in clqstartup.js
*/

/**********************  SmartCart  ************************************************************************************/

  (function($){

    /*
     * SmartCart 2.0 plugin
     * jQuery Shopping Cart Plugin
     * by Dipu 
     * 
     * http://www.techlaboratory.net 
     * http://tech-laboratory.blogspot.com
     */

      $.fn.smartCart = function(options) {
          var options = $.extend({}, $.fn.smartCart.defaults, options);
                  
          return this.each(function() {
                  var obj = $(this);
                  // retrive all products
                  var products = $("input[type=hidden]",obj);
                  var resultName = options.resultName;
                  var cartItemCount = 0;
                  var cartProductCount = 0; // count of unique products added
                  var subTotal = 0; 
                  var toolMaxImageHeight = 200;
                  
                  // Attribute Settings
                  // You can assign the same you have given on the hidden elements
                  var attrProductId = "pid";  // Product Id attribute
                  var attrProductName = "pname"; // Product Name attribute   
                  var attrProductPrice = "pprice"; // Product Price attribute  
                  var attrProductImage = "pimage"; // Product Image attribute
                  var attrCategoryName = "pcategory";
                  
                  // Labels & Messages              
                  var labelCartMenuName = clstr[0] + ' (_COUNT_)';  // _COUNT_ will be replaced with cart count
                  var labelCartMenuNameTooltip = clstr[1];
                  var labelProductMenuName = clstr[2];
                  var labelSearchButton = clstr[3];
                  var labelSearchText = clstr[3];
                  var labelCategoryText = clstr[4];
                  var labelClearButton = clstr[5];
                  var labelAddToCartButton = clstr[6]; 
                  var labelQuantityText = clstr[7];
                  var labelProducts = clstr[2];
                  var labelPrice = clstr[8];
                  var labelSubtotal = clstr[9];
                  var labelTotal = clstr[10];
                  var labelRemove = clstr[11];
                  var labelCheckout = clstr[12];
                  
                  var messageConfirmRemove = clstr[13]; //  _PRODUCTNAME_ will be replaced with actula product name
                  var messageCartEmpty = clstr[14];
                  var messageProductEmpty = clstr[15];
                  var messageProductAddError = clstr[16];
                  var messageItemAdded = clstr[17];
                  var messageItemRemoved = clstr[18];
                  var messageQuantityUpdated = clstr[19];
                  var messageQuantityErrorAdd = clstr[20];
                  var messageQuantityErrorUpdate = clstr[21];
                  
                  //Create Main Menu
                  cartMenu = labelCartMenuName.replace('_COUNT_','0'); // display default
                  var btShowCart = $('<a>'+cartMenu+'</a>').attr("href","#scart");
                  var btShowProductList = $('<a>'+labelProductMenuName+'</a>').attr("href","#sproducts");
                  var btShowCheckout = $('<a>'+labelCheckout+'</a>').attr({"href":"#", "class":"scCheckoutButton", "id":""});
                  var msgBox2 = $('<div></div>').addClass("scMessageBar2").hide();
             
                  var elmProductMenu = $("<li></li>").append(btShowProductList);
                  var elmCartMenu = $("<li></li>").append(btShowCart);
                  var elmCheckoutMenu = $("<li></li>").append(btShowCheckout);
                  var elmMsgBox = $("<li></li>").append(msgBox2);
                  var elmMenuBar = $('<ul></ul>').addClass("scMenuBar").append(elmProductMenu).append(elmCartMenu).append(elmCheckoutMenu).append(elmMsgBox);
                  obj.prepend(elmMenuBar);
                  $(elmCheckoutMenu).click(function() {
                      checkoutFunction()
                      return false;
                  });
                  // Create Search Elements
                  var elmPLSearchPanel = $('<div></div>').addClass("scSearchPanel");
                  if(options.enableSearch){
                    var btSearch = $('<a>'+labelSearchButton+'</a>').attr("href","#").addClass("scSearch").attr("title",clstr[22]);                
                    $(btSearch).click(function() {
                       var searcStr = $(txtSearch).val();                      
                       populateProducts(searcStr);
                       return false;
                    }); 
                    var btClear = $('<a>'+labelClearButton+'</a>').attr("href","#").addClass("scSearch").attr("title",clstr[23]);
                    $(btClear).click(function() {
                       $(txtSearch).val('');                      
                       populateProducts('');
                       return false;
                    });
                    var txtSearch = $('<input type="text" />').attr("value","").addClass("scTxtSearch")
                    $(txtSearch).keyup(function() {
                       var searcStr = $(this).val();                      
                       populateProducts(searcStr);
                    });  
                    var lblSearch = $('<label>'+labelSearchText+':</label>').addClass("scLabelSearch");                                                 
                    elmPLSearchPanel.append(lblSearch).append(txtSearch).append(btSearch).append(btClear);                
                  }

                  // Create Category filter
                  if(options.enableCategoryFilter){
                    var lblCategory = $('<label>'+labelCategoryText+':</label>').addClass("scLabelCategory");
                    var elmCategory = $("<select></select>").addClass("scSelCategory");                
                    elmPLSearchPanel.append(lblCategory).append(elmCategory);                
                    fillCategory();
                  }

                  // Create Product List                
                  var elmPLContainer = $('<div></div>').addClass("scTabs").hide();
                  elmPLContainer.prepend(elmPLSearchPanel);
                  
                  var elmPLProducts = $('<div></div>').addClass("scProductList");
                  elmPLContainer.append(elmPLProducts);
                  
                  // Create Cart
                  var elmCartContainer = $('<div></div>').addClass("scTabs").hide();
                  var elmCartHeader = $('<div></div>').addClass("scCartHeader");
                  var elmCartHeaderTitle1 = $('<label>'+labelProducts+'</label>').addClass("scCartTitle scCartTitle1");
                  var elmCartHeaderTitle2 = $('<label>'+labelPrice+'</label>').addClass("scCartTitle scCartTitle2");
                  var elmCartHeaderTitle3 = $('<label>'+labelQuantityText+'</label>').addClass("scCartTitle scCartTitle3");
                  var elmCartHeaderTitle4 = $('<label>'+labelSubtotal+'</label>').addClass("scCartTitle scCartTitle4");
                  var elmCartHeaderTitle5 = $('<label></label>').addClass("scCartTitle scCartTitle5");
                  elmCartHeader.append(elmCartHeaderTitle1).append(elmCartHeaderTitle2).append(elmCartHeaderTitle3).append(elmCartHeaderTitle4).append(elmCartHeaderTitle5);
                  
                  var elmCartList = $('<div></div>').addClass("scCartList");
                  elmCartContainer.append(elmCartHeader).append(elmCartList);
                  
                  obj.append(elmPLContainer).append(elmCartContainer);
                  
                  // Create Bottom bar
                  var elmBottomBar = $('<div></div>').addClass("scBottomBar");
                  var elmBottomSubtotalText = $('<label>'+labelTotal+':</label>').addClass("scLabelSubtotalText");
                  var elmBottomSubtotalValue = $('<label>'+getMoneyFormatted(subTotal)+'</label>').addClass("scLabelSubtotalValue");
                  var btCheckout = $('<a>'+labelCheckout+'</a>').attr("href","#").addClass("scCheckoutButton");                
                  
                  $(btCheckout).click(function() {
                      checkoutFunction()
                      return false;
                  });

                  elmBottomBar.append(btCheckout).append(elmBottomSubtotalValue).append(elmBottomSubtotalText);
                  obj.append(elmBottomBar);

                  function checkoutFunction() {
                     if($.isFunction(options.onCheckout)) {
                        // calling onCheckout event;
                        options.onCheckout.call(this,elmProductSelected);
                     }else{
                        $(this).parents("form").submit();                   
                     }
                     return false;             
                  }

                  elmBottomBar.append(btCheckout).append(elmBottomSubtotalValue).append(elmBottomSubtotalText);
                  obj.append(elmBottomBar);
                  
                  // Create Tooltip
                  var tooltip = $('<div></div>').addClass('tooltip').hide();
                  obj.append(tooltip);
                  obj.bind("mousemove",function(){
                		tooltip.hide();                    
                    return true;
                	});                
                  
                  // Create SelectList                                
                  var elmProductSelected = $('select[name="'+resultName+'"]',obj);
                  if(elmProductSelected.length <= 0){
                     elmProductSelected = $("<select></select>").attr("name",resultName).attr("multiple","multiple").hide();
                     refreshCartValues();
                  }else{ 
                     elmProductSelected.attr("multiple","multiple").hide();
                     populateCart(); // pre-populate cart if there are selected items  
                  }                 
                  obj.append(elmProductSelected);
                  
                  // prepare the product list
                  populateProducts();
                  
                  if(options.selected == '1'){
                     showCart();
                  }else{
                     showProductList();
                  }	       

                  $(btShowProductList).bind("click", function(e){
                      showProductList();
                      return false;
                  }); 
                  $(btShowCart).bind("click", function(e){
                      showCart();
                      return false;
                  });

                  function showCart(){  
                       $(btShowProductList).removeClass("sel");
                       $(btShowCart).addClass("sel");
                       $(elmPLContainer).hide();
                       $(elmCartContainer).show();
                  }
                  function showProductList(){ 
                       $(btShowProductList).addClass("sel");
                       $(btShowCart).removeClass("sel");  
                       $(elmCartContainer).hide();
                       $(elmPLContainer).show();
                  }
                  
                  function addToCart(i,qty){
                       var addProduct = products.eq(i);
                       if(addProduct.length > 0){
                          if($.isFunction(options.onAdd)) {
                            // calling onAdd event; expecting a return value
                            // will start add if returned true and cancel add if returned false
                            if(!options.onAdd.call(this,$(addProduct),qty)){
                              return false;
                            }
                          }
                          var pId = $(addProduct).attr(attrProductId);
                          var pName = $(addProduct).attr(attrProductName);
                          var pPrice = $(addProduct).attr(attrProductPrice);

                          // Check wheater the item is already added
                          var productItem = elmProductSelected.children("option[rel=" + i + "]");
                          if(productItem.length > 0){
                              // Item already added, update the quantity and total
                              var curPValue =  productItem.attr("value");
                              var valueArray = curPValue.split('|');
                              var prdId = valueArray[0];
                              var prdQty = valueArray[1];
                              prdQty = (prdQty-0) +  (qty-0);
                              var newPValue =  prdId + '|' + prdQty;
                              productItem.attr("value",newPValue).attr('selected', true);    
                              var prdTotal = getMoneyFormatted(pPrice * prdQty);
                              // Now go for updating the design
                              var lalQuantity =  $('#lblQuantity'+i).val(prdQty);
                              var lblTotal =  $('#lblTotal'+i).html(prdTotal);
                              // show product quantity updated message
                              showHighlightMessage(messageQuantityUpdated);                                                      
                          }else{
                              // This is a new item so create the list
                              var prodStr = pId + '|' + qty;
                              productItem = $('<option></option>').attr("rel",i).attr("value",prodStr).attr('selected', true).html(pName);
                              elmProductSelected.append(productItem);
                              addCartItemDisplay(addProduct,qty);
                              // show product added message
                              showHighlightMessage(messageItemAdded);                            
                          }
                          // refresh the cart
                          refreshCartValues();
                          // calling onAdded event; not expecting a return value
                          if($.isFunction(options.onAdded)) {
                            options.onAdded.call(this,$(addProduct),qty);
                          }
                       }else{
                          showHighlightMessage(messageProductAddError);
                       }
                  }
                  
                  function addCartItemDisplay(objProd,Quantity){
                      var pId = $(objProd).attr(attrProductId);
                      var pIndex = products.index(objProd);
                      var pName = $(objProd).attr(attrProductName);
                      var pPrice = $(objProd).attr(attrProductPrice);
                      var prodImgSrc = $(objProd).attr(attrProductImage);
                      var pTotal = (pPrice - 0) * (Quantity - 0);
                      pTotal = getMoneyFormatted(pTotal);
                      // Now Go for creating the design stuff
                      
                      $('.scMessageBar',elmCartList).remove();
                      
                     var elmCPTitle1 = $('<div></div>').addClass("scCartItemTitle scCartItemTitle1");                            
                     
                     // Hover function here
                     if(prodImgSrc && options.enableImage && prodImgSrc.length>0){
                          var prodImg = $("<img></img>").attr("src",prodImgSrc).addClass("scProductImageSmall");
                          if(prodImg && options.enableImageTooltip){
                            	prodImg.bind("mouseenter mousemove",function(){
                                  showTooltip($(this));                    
                                return false;
                            	});
                            	prodImg.bind("mouseleave",function (){
                            		tooltip.hide();
                            		return true;
                            	});
                          }
                          elmCPTitle1.append(prodImg);
                      }

                      var elmCP = $('<div></div>').attr("id","divCartItem"+pIndex).addClass("scCartItem");
    
                      var pTitle =  pName;
                      var phtml = formatTemplate(options.cartItemTemplate, $(objProd));
                      var elmCPContent = $('<div></div>').html(phtml).attr("title",pTitle);                        
                      elmCPTitle1.append(elmCPContent);                        
                      var elmCPTitle2 = $('<label>'+pPrice+'</label>').addClass("scCartItemTitle scCartItemTitle2");
                      var inputQty = $('<input type="text" value="'+Quantity+'" />').attr("id","lblQuantity"+pIndex).attr("rel",pIndex).addClass("scTxtQuantity2");                    
                      $(inputQty).bind("change", function(e){
                          var newQty = $(this).val();
                          var prodIdx = $(this).attr("rel");
                          newQty = newQty - 0;
                          if(validateNumber(newQty)){
                             updateCartQuantity(prodIdx,newQty);
                          }else{
                            var productItem = elmProductSelected.children("option[rel=" + prodIdx + "]");
                            var pValue = $(productItem).attr("value");
                            var valueArray = pValue.split('|'); 
                            var pQty = valueArray[1];
                            $(this).val(pQty);                                                 
                            showHighlightMessage(messageQuantityErrorUpdate);
                          }
                          return true;
                      });
                      
                      var elmCPTitle3 = $('<div></div>').append(inputQty).addClass("scCartItemTitle scCartItemTitle3");

                      var elmCPTitle4 = $('<label>'+pTotal+'</label>').attr("id","lblTotal"+pIndex).addClass("scCartItemTitle scCartItemTitle4");
                      var btRemove = $('<a>'+labelRemove+'</a>').attr("rel",pIndex).attr("href","#").addClass("scRemove").attr("title","Remove from Cart");
                      $(btRemove).bind("click", function(e){
                          var idx = $(this).attr("rel");
                          removeFromCart(idx);
                          return false;
                      });
                      var elmCPTitle5 = $('<div></div>').addClass("scCartItemTitle scCartItemTitle5");
                      elmCPTitle5.append(btRemove);
                      
                      elmCPTitle1.append(elmCPContent);
                      elmCP.append(elmCPTitle1).append(elmCPTitle2).append(elmCPTitle3).append(elmCPTitle4).append(elmCPTitle5);
                      elmCartList.append(elmCP);
                  }
                  
                  function removeFromCart(idx){
                      var pObj = products.eq(idx);
                      var pName = $(pObj).attr(attrProductName)
                      var removeMsg = messageConfirmRemove.replace('_PRODUCTNAME_',pName); // display default
                      if(confirm(removeMsg)){
                          if($.isFunction(options.onRemove)) {
                            // calling onRemove event; expecting a return value
                            // will start remove if returned true and cancel remove if returned false
                            if(!options.onRemove.call(this,$(pObj))){
                              return false;
                            }
                          }
                          var productItem = elmProductSelected.children("option[rel=" + idx + "]");
                          var pValue = $(productItem).attr("value");
                          var valueArray = pValue.split('|');
                          var pQty = valueArray[1];
                          productItem.remove();
                          $("#divCartItem"+idx,elmCartList).slideUp("slow",function(){$(this).remove();
                          showHighlightMessage(messageItemRemoved);
                          //Refresh the cart
                          refreshCartValues();});
                          if($.isFunction(options.onRemoved)) {
                            // calling onRemoved event; not expecting a return value
                            options.onRemoved.call(this,$(pObj));
                          }
                      }
                  }
                  
                  function updateCartQuantity(idx,qty){
                      var pObj = products.eq(idx);
                      var productItem = elmProductSelected.children("option[rel=" + idx + "]");
                      var pPrice = $(pObj).attr(attrProductPrice);
                      var pValue = $(productItem).attr("value");
                      var valueArray = pValue.split('|');
                      var prdId = valueArray[0];
                      var curQty = valueArray[1];                    
                      if($.isFunction(options.onUpdate)) {
                          // calling onUpdate event; expecting a return value
                          // will start Update if returned true and cancel Update if returned false
                          if(!options.onUpdate.call(this,$(pObj),qty)){
                            $('#lblQuantity'+idx).val(curQty);
                            return false;
                          }
                      }


                      var newPValue =  prdId + '|' + qty;
                      $(productItem).attr("value",newPValue).attr('selected', true);    
                      var prdTotal = getMoneyFormatted(pPrice * qty);
                          // Now go for updating the design
                      var lblTotal =  $('#lblTotal'+idx).html(prdTotal); 
                      showHighlightMessage(messageQuantityUpdated);
                      //Refresh the cart
                      refreshCartValues();
                      if($.isFunction(options.onUpdated)){
                          // calling onUpdated event; not expecting a return value
                          options.onUpdated.call(this,$(pObj),qty);
                      }                    
                  }
                  
                  function refreshCartValues(){
                      var sTotal = 0;
                      var cProductCount = 0;
                      var cItemCount = 0;
                      elmProductSelected.children("option").each(function(n) {
                          var pIdx = $(this).attr("rel"); 
                          var pObj = products.eq(pIdx);                     
                          var pValue = $(this).attr("value");
                          var valueArray = pValue.split('|');
                          var prdId = valueArray[0];
                          var pQty = valueArray[1];
                          var pPrice =  $(pObj).attr(attrProductPrice);
                          sTotal = sTotal + ((pPrice - 0) * (pQty - 0));
                          cProductCount++;
                          cItemCount = cItemCount + (pQty-0);
                      });
                      subTotal = sTotal;
                      cartProductCount = cProductCount;
                      cartItemCount = cItemCount;
                      elmBottomSubtotalValue.html(getMoneyFormatted(subTotal));
                      cartMenu = labelCartMenuName.replace('_COUNT_',cartProductCount);  
                      cartMenuTooltip = labelCartMenuNameTooltip.replace('_PRODUCTCOUNT_',cartProductCount).replace('_ITEMCOUNT_',cartItemCount);
                      btShowCart.html(cartMenu).attr("title",cartMenuTooltip);
                      
                      if(cProductCount <= 0){
                         showMessage(messageCartEmpty,elmCartList);
                      }else{
                         $('.scMessageBar',elmCartList).remove();
                      }
                  }
                  
                  function populateCart(){
                     elmProductSelected.children("option").each(function(n) {
                          var curPValue =  $(this).attr("value");
                          var valueArray = curPValue.split('|');
                          var prdId = valueArray[0];
                          var prdQty = valueArray[1];
                          if(!prdQty){
                            prdQty = 1; // if product quantity is not present default to 1
                          }
                          var objProd = jQuery.grep(products, function(n, i){return ($(n).attr(attrProductId) == prdId);});                        
                          var prodIndex = products.index(objProd[0]);
                          var prodName = $(objProd[0]).attr(attrProductName);
                          $(this).attr('selected', true);
                          $(this).attr('rel', prodIndex);
                          $(this).html(prodName);
                          cartItemCount++; 
                          addCartItemDisplay(objProd[0],prdQty);                         
                     });
                     // Reresh the cart
                     refreshCartValues();
                  }
                  
                  function fillCategory(){
                     var catCount = 0;
                     var catItem = $('<option></option>').attr("value",'').attr('selected', true).html('All');
                     elmCategory.prepend(catItem);                   
                     $(products).each(function(i,n){
                          var pCategory = $(this).attr(attrCategoryName);
                          if(pCategory && pCategory.length>0){
                            var objProd = jQuery.grep(elmCategory.children('option'), function(n, i){return ($(n).val() == pCategory);});
                            if(objProd.length<=0){
                              catCount++;
                              var catItem = $('<option></option>').attr("value",pCategory).html(pCategory);
                              elmCategory.append(catItem);
                            }                        
                          }
                              
                     });
                     if(catCount>0){
                        $(elmCategory).bind("change", function(e){
                          $(txtSearch).val('');
                          populateProducts();
                          return true;
                      });                      
                     }else{
                        elmCategory.hide();
                        lblCategory.hide();
                     }
                  }
                  
                  
                  function populateProducts(searchString){
                     var isSearch = false;
                     var productCount = 0;
                     var selectedCategory = $(elmCategory).children(":selected").val();
                     // validate and prepare search string
                     if(searchString){
                        searchString = trim(searchString);
                       if(searchString.length>0){
                           isSearch = true;
                           searchString = searchString.toLowerCase();
                       }                      
                     }
                     // Clear the current items on product list
                     elmPLProducts.html('');
                     // Lets go for dispalying the products
                     $(products).each(function(i,n){
                        var productName = $(this).attr(attrProductName);
                        var productCategory = $(this).attr(attrCategoryName);
                        var isValid = true;
                        var isCategoryValid = true;
                        if(isSearch){
                          if(productName.toLowerCase().indexOf(searchString) == -1) {
                            isValid = false;
                          }else{
                            isValid = true;
                          }
                        }
                        // Category filter
                        if(selectedCategory && selectedCategory.length>0){
                          selectedCategory = selectedCategory.toLowerCase();
                          if(productCategory.toLowerCase().indexOf(selectedCategory) == -1) {
                            isCategoryValid = false;
                          }else{
                            isCategoryValid = true;
                          }
                        }

                        if(isValid && isCategoryValid) {
                            productCount++; 
                            var productPrice = $(this).attr(attrProductPrice); 
                            var prodImgSrc = $(this).attr(attrProductImage);
                            
                            var elmProdDiv1 = $('<div></div>').addClass("scPDiv1");
                            if(prodImgSrc && options.enableImage && prodImgSrc.length>0){
                                var prodImg = $("<img></img>").attr("src",prodImgSrc).addClass("scProductImage");    

                                // Another Hover function here
                                if(prodImg && options.enableImageTooltip){
                                	prodImg.bind("mouseenter mousemove",function(){
                                      showTooltip($(this));                    
                                    return false;
                                	});
                                	prodImg.bind("mouseleave",function (){
                                		tooltip.hide();
                                		return true;
                                	});
                                }

                                elmProdDiv1.append(prodImg);
                            }
                            var elmProdDiv2 = $('<div></div>').addClass("scPDiv2"); // for product name, desc & price
                            var productHtml = formatTemplate(options.productItemTemplate, $(this));
                            elmProdDiv2.html(productHtml);                      
                            
                            var elmProdDiv3 = $('<div></div>').addClass("scPDiv3"); // for button & qty    
                            var btAddToCart = $('<a>'+labelAddToCartButton+'</a>').attr("href","#").attr("rel",i).attr("title",labelAddToCartButton).addClass("scAddToCart");
                            $(btAddToCart).bind("click", function(e){
                                var idx = $(this).attr("rel");
                                var qty = $(this).siblings("input").val();
                                if(validateNumber(qty)){
                                   addToCart(idx,qty);
                                }else{
                                  $(this).siblings("input").val(1);                                                 
                                  showHighlightMessage(messageQuantityErrorAdd);
                                }
                                return false;
                            });
                            var inputQty = $('<input type="text" value="1" />').addClass("scTxtQuantity");  
                            var labelQty = $('<label>'+labelQuantityText+':</label>').addClass("scLabelQuantity");                    
                            elmProdDiv3.append(labelQty).append(inputQty).append(btAddToCart);                  
      
                            var elmProds = $('<div></div>').addClass("scProducts");
      
                            elmProds.append(elmProdDiv1).append(elmProdDiv2).append(elmProdDiv3);
                            elmPLProducts.append(elmProds);
                        }                                                        
                     });
                     
                     if(productCount <= 0){
                         showMessage(messageProductEmpty,elmPLProducts);
                     }
                  }
                  
                  // Display message
                  function showMessage(msg, elm){
                    var elmMessage = $('<div></div>').addClass("scMessageBar").hide();
                    elmMessage.html(msg);                  
                    if(elm){
                       elm.append(elmMessage);
                       elmMessage.show();
                    }
                  }
                  
                  function showHighlightMessage(msg){
                    msgBox2.html(msg);
            				msgBox2.fadeIn("fast", function() {
            					setTimeout(function() { msgBox2.fadeOut("fast"); }, 2000); 
            				}); 
                  }

                  // Show Image tooltip
                  function showTooltip(img) {
              		  var height = img.height();
              		  var width = img.height();
                    var imgOffsetTop = img.offset().top;
                    jQuery.log(img.offset());                
                    jQuery.log(img.position());
                    jQuery.log("--------------");
                    tooltip.html('');
                    var tImage = $("<img></img>").attr('src',$(img).attr('src')); 
                    tImage.height(toolMaxImageHeight);
                    tooltip.append(tImage);
                		var top = imgOffsetTop - height ;
                		var left = width + 10;
                    tooltip.css({'top':top, 'left':left});	
                    tooltip.show("fast");                                              
                  }
                  
                  function validateNumber(num){
                    var ret = false;
                    if(num){
                      num = num - 0;
                      if(num && num > 0){
                         ret = true;
                      }
                    }
                    return ret;
                  }
                  
                  // Get the money formatted for display
                  function getMoneyFormatted(val){
                    return val.toFixed(2);
                  }
                  // Trims the blankspace
                  function trim(s){
                  	var l=0; var r=s.length -1;
                  	while(l < s.length && s[l] == ' ')
                  	{	l++; }
                  	while(r > l && s[r] == ' ')
                  	{	r-=1;	}
                  	return s.substring(l, r+1);
                  }
                  // format the product template
                  function formatTemplate(str, objItem){
                    resStr =str.split("<%=");
                    var finalStr = '';
                    for(i=0;i<resStr.length;i++){
                      var tmpStr = resStr[i];
                      valRef = tmpStr.substring(0, tmpStr.indexOf("%>")); 
                      if(valRef!='' || valRef!=null){
                        var valRep = objItem.attr(valRef); //n[valRef]; 
                        if(valRep == null || valRep == 'undefined'){
                           valRep = '';
                        }
                        tmpStr = tmpStr.replace(valRef+'%>',valRep);
                        finalStr += tmpStr;
                      }else{
                        finalStr += tmpStr;
                      }
                    }
                    return finalStr;
                  }

          });  
      };  
   
      // Default options
      $.fn.smartCart.defaults = {
            selected: 0,  // 0 = produts list, 1 = cart   
            resultName: 'products_selected[]', 
            enableImage: true,
            enableImageTooltip: true,
            enableSearch: true,
            enableCategoryFilter: true,
            productItemTemplate:'<strong><%=pname%></strong><br />' + clstr[4] + ': <%=pcategory%><br /><small><%=pdesc%></small><br /><strong>' + clstr[8] + ': <%=pprice%> €</strong>',
            cartItemTemplate:'<strong><%=pname%></strong>',
            // Events
            onAdd: null,      // function(pObj,quantity){ return true; }
            onAdded: null,    // function(pObj,quantity){ }
            onRemove: null,   // function(pObj){return true;}
            onRemoved: null,  // function(pObj){ } 
            onUpdate: null,   // function(pObj,quantity){ return true; }
            onUpdated: null,  // function(pObj,quantity){ } 
            onCheckout: null  // function(Obj){ } 
      };
      
      jQuery.log = function(message) {
        if(window.console) {
           console.debug(message);
        }
      };
  })(jQuery);


/**********************  Cliqon Cart  *********************************************************************************/
  
  function clqCart(clqitem, m, status) {

    function tidyNum(n) {
      var q = split.n(',');
      // q[0] = significant digits, q[1] = cents
      q[0] = q[0].replace('.', '');
      var d = new String(q[0]+q[1]);
      return d.toFixed(2);
    }

    function fNum(n) {
        return formatNumber(n);
    }

    // If we have to load dependencies, load them here with loader
    // loadScript(script.js, function() {})

    $("#cart").smartCart({
      onAdd: function(pObj,quantity){ return cartAdd(pObj,quantity); }, // ;
      onAdded: function(pObj,quantity){  cartAdded(pObj,quantity); },
      onRemove: function(pObj){return cartRemove(pObj);}, 
      onRemoved: function(pObj){ cartRemoved(pObj);}, 
      onUpdate: function(pObj,quantity){ return cartUpdate(pObj,quantity); }, 
      onUpdated: function(pObj,quantity){ cartUpdated(pObj,quantity); },  //  
      onCheckout: function(Obj){ cartCheckout(Obj); }   
    });  

      // On add
      function cartAdd(obj, qty) {
        var msg = m[0] + qty + ' x ' + obj.attr("pid") + m[1] + '?';
        return confirm(msg);
      }
      
      // On added
      function cartAdded(obj,qty){
        return false;
      }
        
      // On Remove
      function cartRemove(obj){ 
        return confirm(m[2] + obj.attr("pid") + m[3] + '?');
      }
        
      // On Removed
      function cartRemoved(obj){
        return false;
      }
        
      // On Update
      function cartUpdate(obj,qty){
        return confirm(m[4] + obj.attr("pid")  + m[5] + qty + ' ?');
      }
        
      // On Updated
      function cartUpdated(obj,qty){
        return false;
      }


    // On Checkout - clqjsdb.js is loaded
    function cartCheckout(obj){
      
      // Write to Local Storage

      // Create Order and get Order Number back
      clqitem.insert({clq_recid:1, clq_type:"order"});

      var msg = "Im listing the product id, quantity of the selected products<br /> ";                
      obj.children("option").each(function(n) {                     
        var pValue = $(this).attr("value");
        var valueArray = pValue.split("|");
        var prdId = valueArray[0];
        var pQty = valueArray[1];
        msg += m[6] + ': '+prdId + m[7] + ': '+pQty+'<br />';

        // Create an order line
        clqitem.insert({clq_parent:1, clq_type:"orderline", clq_reference: prdId, clq_value: pQty});

      });
      
      $('#cart').removeClass('show').addClass('hide');
      $('#order').removeClass('hide').addClass('show');

      /**********************  Cliqon Order ***********************************************************************************/

        if(status == 'test') {
          // Test Unit
          $('#orderform').populate({
            email : 'mark_e_1281377537_per@conkas.com',
            address : 'Son Comas, 10, Esporles, 07190, Mallorca',
            telephone : '900600400',
            notes : 'Visa: 4295369404352770, 8/2015'
          }, {debug:true, phpIndices:true});  
          $('.fname').val('Mark');
          $('.mname').val('Ingram');
          $('.lname').val('Richards');
        }


        // Possibly not implemented yet - needs clq_extra on Catalog
        var statusHandler = function(value) {
          
          var result, html;
          switch (true) {
            case (value == 0):
              result = 'green';
              break;

            case ((value >= 1) && (value <= 5)):
              result = 'blue';
              break;

            case ((value >= 6) && (value <= 15)):
              result = 'yellow';
              break;

            case (value >= 16):
              result = 'red';
              break;

            default:
              result = 'orange';
              break;
          }
          html = '<img src=\"/includes/css/images/tag_' + result + '.png\" title=\"' + value + '\" alt=\"' + value + '\" style=\"height: 12px; vertical-align: top; margin-top: 2px;\" />';
          
          return html;
        }
      
        var netc = 0; var dlvc = 0; var taxc = 0; var gross = 0;
        
        // Draw a Orders Table with transparency
        // Table Header
        var tablehdr = {
          hdrcode: m[25],
          hdrtitle: m[26],
          hdrqty: m[27],
          hdrprice: m[28],
          hdrval: m[29],
          hdrsalestax: '%', 
          hdrdlv: m[30], 
          hdrtotal: m[31],
          hdrstatus: '*' // 381 Enquire about delivery time
        };
        $('#tblhdr').render(tablehdr);

        // Keep track of the order total
        var ordertotal = 0;

        // Populate the Table body with AJAX calls based on Product Code
        clqitem({clq_type:{is:'orderline'}}).each(function(record, id) {
          
          var ln = []; 
          var urlstr = '/includes/cart.php?langcd=' + store.get('lcd') + '&action=stockdetails&ref=' + record.clq_reference;
          $.getJSON(urlstr, function(obj) {
            
            var qty = record.clq_value;

            ln['ordprice'] = Number(obj.clq_value);
            var val = qty * ln['ordprice'];
            ln['ordval'] = val;
            
            var iva = val * (Number(obj.taxrate) / 100);
            ln['ordsalestax'] = fNum(iva);

            var dlv = fNum(obj.delivery) * qty;
            ln['ordlinedlv'] = dlv;
              
            if(obj.taxincl == 'y') {
              ln['ordlinetotal'] = dlv + val;
            } else {
              var tot = dlv + val;
              var tax = tot * (Number(obj.taxrate) / 100);
              ln['ordlinetotal'] = tax + tot;
            };  

            var line = {
              ordcode:record.clq_reference,
              ordtitle:obj.clq_title,
              ordqty:qty,
              ordprice:formatCurrency(ln['ordprice']),
              ordval:formatCurrency(ln['ordval']),
              ordsalestax:formatCurrency(ln['ordsalestax']),
              ordlinedlv:formatCurrency(ln['ordlinedlv']),    
              ordlinetotal:formatCurrency(ln['ordlinetotal']),
              ordlinestatus:obj.instock   
            };
      
            orderLine = '<tr>' + 
                '<td>' + line['ordcode'] + '</td>' +
                '<td>' + line['ordtitle'] + '</td>' + 
                '<td class="txtright">' + line['ordqty'] + '</td>' + 
                '<td class="txtright">' + line['ordprice'] + '</td>' + 
                '<td class="txtright">' + line['ordval'] + '</td>' + 
                '<td class="txtright">' + line['ordsalestax'] + '</td>' + 
                '<td class="txtright">' + line['ordlinedlv'] + '</td>' + 
                '<td class="txtright">' + line['ordlinetotal'] + '</td>' + 
                '<td>' + statusHandler(line['ordlinestatus']) + '</td>' + 
              '</tr>';

            $('tbody').append(orderLine); 

            netc = Number(netc) + Number(val);
            dlvc = Number(dlvc) + Number(dlv);
            taxc = Number(taxc) + Number(iva);
            gross = Number(gross) + Number(ln['ordlinetotal']); 

            // add watch and update here
            $('.ordnet').html(formatCurrency(netc)); $('.orddlv').html(formatCurrency(dlvc)); $('.ordtax').html(formatCurrency(taxc)); 
            $('.ordgross').html(formatCurrency(gross)); 

            // Create Order Form hidden fields
            var formline = '<input type=\"hidden\" name=\"orderline[]\" value=\"' + line['ordcode'] + '|' + line['ordtitle'] + '|'; 
            formline += line['ordqty'] + '|' + line['ordlinetotal'] + '\" />';
            $('#orderform').append(formline);

          });

        }); // End of Each

      
        // Render Footer
        // Watch and update variables
        var tableftr = {
          ftrtotalnet: m[32],
          ftrtotaldlv: m[33], // 423 Delivery Charge
          ftrtotaltax: m[34], // 424 Tax of {rate}% included
          ftrtotalgross: m[35],
        
          ordnet: formatCurrency(netc),
          orddlv: formatCurrency(dlvc),
          ordtax: formatCurrency(taxc),
          ordgross: formatCurrency(gross) 
        };

        ordertotal = gross;

        $('#tblftr').render(tableftr);

        // Clear all fields
        $('#resetbutton').on('click', function(e) {
          $('#orderform').reset();
        });

        // Start PayPal process
        $('#sendbutton').on('click', function(e) {

          // Get form
              var postdata = $('#orderform').serialize() + '&ordertotal=' + ordertotal + '&action=processorder';      
              sendOrder(postdata);

          /*    
          // Validate using Parsley ??
              $('#orderform').parsley('validate');
              if($('#orderform').parsley('isValid') == true) {
                sendOrder(postdata);
              }
          */
          
        });

        var sendOrder = function(postdata) {
            var alertMsg = 'Cancel or Print';
            var urlstr = '/includes/cart.php?' + postdata;
            // Open a new popup window and display PayPal
            $.popupWindow(urlstr, { // ?popwin ??
              height: 700, width: 980,
              toolbar: false, scrollbars: true, // safari always adds scrollbars
              status: false,  resizable: true, 
              // left: 100, top: 100,
              center: true, // auto-center
              createNew: true, // open a new window, or re-use existing popup
              name: 'PayPalWindow', // specify custom name for window (overrides createNew option)
              location: false, menubar: false,
              onUnload: function() { // callback when window closes
                TINY.box.hide();
              } 
            });   
        }

        var printOrder = function() {
          $('#inpopup').print();
        }
        
        var closeDialog = function() {
          $('#popup').dialog('close');
        }

        // Display order on a popup - also use for testing purposes
        $('#printbutton').on('click', function(e) {
          
          // Get form
              var postdata = $('#orderform').serialize() + '&action=printorder';      

          // Print
          var urlstr = '".$rootpath."includes/cart.php';
          var w = 600; var ww = document.body.clientWidth; var wl = ww - w; wl = (wl/2);
          $('#popup').dialog({ 
            bgiframe:true, modal:false, autoOpen:false, width:w, 
            resizeable:true, title:m[14], position: [wl,20],
            buttons: {
              'print' : printOrder,
              'cancel' : closeDialog      
            }
          }); 
          $('#inpopup').load(urlstr+'?'+postdata); 
          $('#popup').dialog('open');      
        });

      } 

  }    

  function clqmsg(param) { console.log(param) }