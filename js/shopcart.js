function displayDollarsUS( number, get )
{
    if(number.length < 1)return number;
    var pattern= /[0-9]+(\.[0-9]+)?/;
    number = parseFloat(pattern.exec(number)).toFixed(2);
    if( isNaN(number) )number = 0;
    if(get)return number;
    return "$ "+number+" US";
}

var spacePattern = / /g;
function formatURL( str ){return str.replace(spacePattern,"_");}

function isUsing( attr )
{
    if( attr.length > 0 && attr != 0 ) return true;
    else return false;
}

/* COLLECTIONS MENU */

jQuery(document).ready(function(){
    if( typeof ALLCATEGORIES == "undefined" || ! jQuery("#categoryMenu").length )return;

    var categoryMenu = jQuery("#categoryMenu"),
        template_category = jQuery(".template",categoryMenu).removeClass("template").detach();

    for( var i=0; i<ALLCATEGORIES.length; i++ )
    {
        var newCategory = template_category.clone();

        newCategory.attr( "href", "/shop/"+formatURL( ALLCATEGORIES[i].toLowerCase() ) ).text( ALLCATEGORIES[i] );

        categoryMenu.append(newCategory);
        if( i < ALLCATEGORIES.length-1 )categoryMenu.append("<span class='divider'>|</span>");
    }

    if( typeof(CATEGORY) != "undefined" ){
        jQuery(".category","#categoryMenu").each(function(){
            if( jQuery(this).text().toLowerCase() == CATEGORY )jQuery(this).addClass("active");
        });
    }
});

/* CATEGORY */

jQuery(document).ready(function()
{
    if( !PAGE || PAGE != "category" )return;

    var productList = jQuery("#productList"),
        template_product = jQuery(".template",productList).removeClass("template").detach();

    jQuery("#collectionHeader").attr("src","/images/collection_headers/"+formatURL(CATEGORY.toLowerCase())+".jpg");
    
    for( var i=0; i<ALLPRODUCTS.length; i++ )
    {
        var newProduct = template_product.clone();
        
        newProduct.attr( "href", "/shop/"+formatURL( CATEGORY )+"/"+ALLPRODUCTS[i].id );
        newProduct.find(".thumb img").attr("src",ALLPRODUCTS[i].images[0]);

        var priceTag = "$" + parseInt( ALLPRODUCTS[i].unit_price );
        if( isUsing( ALLPRODUCTS[i].set_price ) && ALLPRODUCTS[i].is_full_set != "yes" ) priceTag += "-" + parseInt( ALLPRODUCTS[i].set_price );

        newProduct.children(".priceTag").text(  priceTag  );
        newProduct.children(".name").html(ALLPRODUCTS[i].name);

        if( ALLPRODUCTS[i].is_full_set == "yes" )newProduct.addClass("fullSet");

        productList.append(newProduct);
    }
});


/* PRODUCT */

jQuery(document).ready(function()
{
    if( !PAGE || PAGE != "product" )return;

    var isFullSet = (PRODUCT.is_full_set == "yes"),
        isCustom = (PRODUCT.category == "Custom Prints");

    var newlinePattern = /\r\n/g;
    function handleNewlines( str ){return str.replace(newlinePattern,"<br>");}

    function addToCart()
    {console.log("lets add to cart...");
        var form = jQuery("#addToCartForm");
        //if( ! form.attr("action") )form.attr("action", "/cart/");

        var subtotal = displayDollarsUS( jQuery(".price", "#subtotal").text(), true );
        if( ! ( subtotal > 0 ) )return false;

        if(isCustom)
        {
            jQuery("input.required","#productCustomOptions").each(function(){
                if( this.value == jQuery(this).data("example") || this.value.length < 1 )jQuery(this).parent(".lineInput").addClass("invalid");
                else jQuery(this).parent(".lineInput").removeClass("invalid");
            });

            var datePattern = /^\d+\/\d+\/\d+$/;
            if( datePattern.exec( document.getElementById("customDate").value ) ) jQuery("#customDate").removeClass("invalid");
            else jQuery("#customDate").parent(".lineInput").addClass("invalid");
        }
        else
        {
            if( document.getElementById("isMonogramed").value == "1" && document.getElementById("initial").value.length < 1 ) jQuery("#initial").addClass("invalid");
            else jQuery("#initial").removeClass("invalid");
        }
        console.log("the mighty validation check...");
        if( jQuery(".invalid",form).length > 0 )return false;

        console.log("valid... lets build that order...");
        shopcart_order.addNew();
    }
    //jQuery("#testBuy").on("click",addToCart);
    jQuery("#addToCart").on("click",addToCart);

    function defaultInfoInit()
    {
        jQuery(".name","#productInfo").html(PRODUCT.name);
        jQuery(".headerPrice .unit","#productInfo").html( parseFloat( PRODUCT.unit_price ).toFixed(2) );
        jQuery(".description","#productInfo").html( handleNewlines(PRODUCT.description) );
        if( isUsing( PRODUCT.set_price ) && ! isCustom && ! isFullSet ) jQuery(".headerPrice .set","#productInfo").css("visibility","visible").append( parseFloat( PRODUCT.set_price ).toFixed(2) );
        else jQuery(".headerPrice .set","#productInfo").remove();
    }

    function imagesInit()
    {
        jQuery("#productImage img").attr("src",PRODUCT.images[1]);

        //load white clickboxes for each image
        var showLink_template = jQuery(".template.showLink","#productImage").removeClass("template").detach();
        for( var i=1; i<PRODUCT.images.length; i++ )
        {
            var newShowLink = showLink_template.clone();

            newShowLink.data("index",i).on("click", function(){jQuery("#productImage img").attr("src", PRODUCT.images[ jQuery(this).data("index") ] );jQuery(this).addClass("active").siblings().removeClass("active");}).css("marginRight", (i-1)*34 );
            if(i == 1)newShowLink.addClass("active");

            jQuery("#imageSelectors").append(newShowLink);
        }

        //preload product images
        jQuery("body").append("<div id='productPreload' style='position:absolute; top:-1000px; left:0px; width:1px; height:1px; overflow:hidden;'></div>");
        for( var i=0; i<PRODUCT.images.length; i++ )
        {
            jQuery("#productPreload").append("<img src='"+PRODUCT.images[i]+"' />");
        }

        //click to modal view
        jQuery("img","#productImage").on("click", function(){jQuery(this).clone().modal( {"containerCss":{"border":"10px solid white","boxShadow":"0 0 12px black"}, "overlayCss":{"backgroundColor":"black","opacity":".3"}, "overlayClose":true, "escClose":true} );});
    }

    function customPrintsInit()
    {
        jQuery("#productCheckOptions").remove();
        jQuery("#productCustomOptions").css("display","block");

        jQuery("input","#productCustomOptions")
            .on("focus",function(){if( this.value == jQuery(this).data("example") )this.value = "";})
            .on("blur",function(){if(this.value == "")this.value = jQuery(this).data("example");})
            .each(function(){jQuery(this).attr("value", jQuery(this).data("example") );});
    }

    function defaultOptionsInit()
    {
        if( ! isFullSet ) jQuery("#productCheckOptions .single").data("price",PRODUCT.unit_price).children(".price").html( parseFloat( PRODUCT.unit_price ).toFixed(2) );
        else jQuery("#productCheckOptions .single").remove();

        if( isUsing( PRODUCT.monogram ) ) jQuery("a.monogram","#productCheckOptions").data("price", PRODUCT.monogram ).children(".price").html( parseFloat( PRODUCT.monogram ).toFixed(2) );
        else jQuery("a.monogram","#productCheckOptions").remove();

        if( isUsing( PRODUCT.set_price ) && PRODUCT.is_full_set != 'yes' )
        {
            jQuery("#productCheckOptions .set").data({"price":PRODUCT.set_price,"amount":PRODUCT.set_amount}).each(function(){
                jQuery(this).children(".amount").html( parseFloat( PRODUCT.set_amount ).toFixed(0) );
                jQuery(this).children(".price").html( parseFloat( PRODUCT.set_price ).toFixed(2) );
            });
        }
        else jQuery("a.set","#productCheckOptions").remove();

        jQuery("#productCustomOptions").remove();

        jQuery("a","#productCheckOptions").on("click", function(){

            var wasOn = jQuery(this).hasClass("active");

            if( wasOn )jQuery(this).removeClass("active").parent("div").removeClass("active");
            else jQuery(this).addClass("active").parent("div").addClass("active");

            if( jQuery(this).hasClass("option") )
            {
                if( ! parseInt( jQuery("#quantity")[0].value ) ) jQuery("#quantity")[0].value = "1";
                jQuery(this).siblings(".option").removeClass("active");

                if( wasOn ) document.getElementById("quantity").value = "0";
            }

            document.getElementById("isSet").value = jQuery(".set.active","#productCheckOptions").length ? "1" : "0";
            document.getElementById("isMonogramed").value = jQuery(".monogram.active","#productCheckOptions").length ? "1" : "0";

            calcSubtotal();
        });
    }

    function calcSubtotal( isCustom )
    {
        var subtotal = 0.0,
            quantity = document.getElementById("quantity").value ? parseFloat( document.getElementById("quantity").value ) : 0;

        if( isCustom ) subtotal += ( quantity * parseFloat( PRODUCT.unit_price ) );
        else
        {
            var price =  0.0;
            if( jQuery(".set.active","#productCheckOptions").length ) price = parseFloat( PRODUCT.set_price );
            else if( jQuery(".single.active","#productCheckOptions").length || isFullSet )price = parseFloat( PRODUCT.unit_price );
            subtotal += ( quantity * price );
        }

        if( subtotal > 0 && jQuery(".monogram.active","#productCheckOptions").length ) subtotal += parseFloat( PRODUCT.monogram );

        jQuery(".price","#subtotal").html( displayDollarsUS( subtotal ) );
    }

    /* OK LETS GO! */

    defaultInfoInit();
    
    imagesInit();

    if( isCustom )customPrintsInit();
    else defaultOptionsInit();

    jQuery("#quantity").keyup(function(){calcSubtotal( isCustom );});
    
    jQuery(".template").remove(); //removed unused templates after choosing a template to use
});

/* SHOP */

jQuery(document).ready(function()
{
    if( !PAGE || PAGE != "shop" )return;

    
});


/* CHECKOUT */

jQuery(document).ready(function()
{
    if( !PAGE || PAGE != "checkout" )return;

    shopcart_order.load();

    var orderTemplate = jQuery(".order.template").removeClass("template").detach();

    if( ! shopcart_order.list || shopcart_order.list.length == 0 ){
        jQuery("#orders").append("<h3 style='margin-left:40px;'>There are currently no items in your cart.</h3>");
        return;
    }
/*
    var SUBTOTAL = 0,
        SHIPPING_BASE = 0,
        SHIPPING_ADD = 0;
*/
    for( var i=0; i< shopcart_order.list.length; i++ )
    {
        var anOrderInfo = shopcart_order.list[i].split( shopcart_order.itemDiv ),
            product_id = anOrderInfo[0],
            type = anOrderInfo[1],
            quantity = anOrderInfo[2],
            order = orderTemplate.clone(),
            product_info;

        for( var j=0; j<ALLPRODUCTS.length; j++ )
        {
            if( ALLPRODUCTS[j].id == product_id ){
                product_info = ALLPRODUCTS[j];
                break;
            }
        }

        if( i%2 )order.addClass("odd");

        var price = type == 1 || type == 4 ? product_info.set_price : product_info.unit_price;

        order.children(".thumb").children("img").attr("src",product_info.images[0]);
        var nameString = type == 1 || type == 4 ? product_info.name + " <span>( Set of "+product_info.set_amount+" )</span>" : product_info.name;
        order.children(".productInfo").children(".name").html(nameString);
        order.children(".productInfo").children(".price").html( displayDollarsUS( price ) );
        order.children(".purchaseInfo").children(".quantityBox").children(".quantity").attr("value", quantity ).data("price", price )
            .on("change", function(){shopcart_order.changeQuantity(this.value, jQuery(this).parents(".order").index() );} );

        var subtotal = parseFloat(price) * parseFloat(quantity);

        if( type == 3 || type == 4 ){
            order.children(".options").children(".monogram").children(".title").children(".initials").html( anOrderInfo[3],true );
            order.children(".options").children(".monogram").children(".title").children(".price").children(".value").html( displayDollarsUS( product_info.monogram ) );
            order.children(".options").children(".custom").remove();
            subtotal += parseFloat( product_info.monogram );
        }
        else if( type == 2 ){
            order.children(".options").children(".custom").children(".names").children("span").html( anOrderInfo[3] );
            order.children(".options").children(".custom").children(".date").children("span").html( anOrderInfo[4] );
            order.children(".options").children(".custom").children(".location").children("span").html( anOrderInfo[5] );
            order.children(".options").children(".monogram").remove();
        }
        else{
            order.children(".options").children(".custom,.monogram").remove();
            order.children(".options").children(".modifyButtons").children(".div,.edit").remove();
        }

        order.children(".options").children(".modifyButtons").children(".remove").on("click",function(){shopcart_order.deleteOrder(jQuery(this).parents(".order").index());});

        order.children(".purchaseInfo").children(".subtotal").children(".price").html( displayDollarsUS( subtotal ) );

        jQuery("#orders").append(order);

        //SUBTOTAL += subtotal;

        //if( parseFloat(product_info.shipping_base) > SHIPPING_BASE ) SHIPPING_BASE = parseFloat(product_info.shipping_base);
        //SHIPPING_ADD += parseFloat(product_info.shipping_add) * parseFloat(quantity);
    }

    //jQuery(".value","#subTotalCount").html( "$ " + displayDollarsUS( SUBTOTAL, true ) );
    //jQuery(".value","#taxCount").html( "$ " + displayDollarsUS( TAX * SUBTOTAL, true ) );
    //jQuery(".value","#shippingCount").html( "$ " + displayDollarsUS( SHIPPING_ADD + SHIPPING_BASE, true ) );

    //var total = (TAX * SUBTOTAL) + SHIPPING_ADD + SHIPPING_BASE + SUBTOTAL;
    //if( document.getElementById("checkoutTotalValue") ) document.getElementById("checkoutTotalValue").value = "USD "+total.toFixed(2);
    //jQuery(".value","#totalCount").html( displayDollarsUS( total ) );

});