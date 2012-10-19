function displayDollarsUS( number )
{
    if(number.length < 1)return number;
    var pattern= /[0-9]+(\.[0-9]+)?/;
    number = parseFloat(pattern.exec(number)).toFixed(2);
    if( isNaN(number) )number = 0;
    return "$ "+number+" US"
}

function decodeHTML( coded ) {return jQuery("<div/>").html( coded ).text();}

/* MANAGE */

jQuery(document).ready(function(){
    if( PAGE != "manage")return;

    jQuery("#search_category, #product_category").each(function(){
        for(var i=0; i<ALLCATEGORIES.length; i++)jQuery(this).append("<option value='" + ALLCATEGORIES[i] + "'>" + ALLCATEGORIES[i] + "</option>");
    });

    function hasChanged( id ) {if( ! jQuery("#"+id).data("savedAs") )return false;if( document.getElementById(id).value == jQuery("#"+id).data("savedAs") )return false; else return true;}

    function filterProducts( needle, type )
    {
        var matches = [];

        if(needle.length == 0)
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_name").value = "";
            document.getElementById("search_category").selectedIndex = 0;
            for(var i=0; i<ALLPRODUCTS.length; i++) matches.push(i);
        }

        else if(type == "search_id")
        {
            document.getElementById("search_name").value = "";
            document.getElementById("search_category").selectedIndex = 0;
            for(var i=0; i<ALLPRODUCTS.length; i++) if( ALLPRODUCTS[i].id.toLowerCase().indexOf( needle.toLowerCase() ) >= 0 ) matches.push(i);
        }

        else if(type == "search_name")
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_category").selectedIndex = 0;
            for(var i=0; i<ALLPRODUCTS.length; i++) if( ALLPRODUCTS[i].name.toLowerCase().indexOf( needle.toLowerCase() ) >= 0 ) matches.push(i);
        }

        else if(type == "search_category")
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_name").value = "";
            for(var i=0; i<ALLPRODUCTS.length; i++) if( ALLPRODUCTS[i].category == document.getElementById("search_category").value ) matches.push(i);
        }

        document.getElementById("productList").innerHTML = "";
        for( var i=0; i<matches.length; i++ )
        {
            var option = document.createElement("option");
            option.setAttribute( "value", matches[i] );
            option.text = decodeHTML( "[" + ALLPRODUCTS[ matches[i] ].id ) + "]  " + decodeHTML( ALLPRODUCTS[ matches[i] ].name );
            document.getElementById("productList").appendChild(option);
        }
        
        matches.length = 0;
    }

    function showProduct( index )
    {
        if( index == "undefined" || index < 0 || index.length < 1 || ALLPRODUCTS[index] == "undefined" )return;

        jQuery("input,textarea,select","#product").not("#product_last_modified").each(function(){
            var identifier = this.id.substr(8),
                newValue =  decodeHTML( ALLPRODUCTS[index][identifier] );

            if( jQuery(this).hasClass("formatDollars") ) newValue = displayDollarsUS(newValue);

            this.value = newValue;
            jQuery(this).data("savedAs",newValue);
        });
        
        var last_modified = new Date( parseInt(ALLPRODUCTS[index].last_modified)*1000 );
        document.getElementById("product_last_modified").value = last_modified.toLocaleDateString() + " ( " + last_modified.toLocaleTimeString() + " ) ";

        jQuery("input,select,textarea","#product").removeClass("changed");

        var imageOutput = "";
        for(var i=0; i<ALLPRODUCTS[index].images.length; i++) imageOutput += "<img src='"+ALLPRODUCTS[index].images[i]+"' />";
        jQuery("#product_images").html(imageOutput);
    }

    jQuery("#search_id").on("keyup", function(){filterProducts( this.value, this.id );});
    jQuery("#search_name").on("keyup", function(){filterProducts( this.value, this.id );});
    jQuery("#search_category").on("change", function(){filterProducts( this.value, this.id );});

    jQuery(".formatDollars").on("change", function(){this.value = displayDollarsUS( this.value )} );

    jQuery("#productList").on("change", function(){showProduct( this.value );});

    jQuery("input, select, textarea","#product").on("change",function(){if( hasChanged( this.id ) ) jQuery(this).addClass("changed"); else jQuery(this).removeClass("changed");});

    jQuery("#addCategoryButton").on("click", function(e){
        var newCategory = prompt('Please input a new category name exactly as it should appear. Note: category will not be created until the changes to this product have been saved.');
        if(!newCategory)return;
        var newIndex = ALLCATEGORIES.length;
        ALLCATEGORIES.push(newCategory);
        jQuery("#product_category").append("<option value='"+newCategory+"'>"+newCategory+"</option>");
        document.getElementById('product_category').selectedIndex = newIndex;
    });

    function prepareSubmit()
    {
        document.getElementById("manageProductForm").submit();
    }

    jQuery("#manageProductSubmit").on("click", prepareSubmit );

    filterProducts("");
});

/* ADD */

jQuery(document).ready(function(){
    if( PAGE != "add")return;

    jQuery("#search_category, #product_category").each(function(){
        for(var i=0; i<ALLCATEGORIES.length; i++)jQuery(this).append("<option value='" + ALLCATEGORIES[i] + "'>" + ALLCATEGORIES[i] + "</option>");
    });

    jQuery("#addCategoryButton").on("click", function(e){
        var newCategory = prompt('Please input a new category name exactly as it should appear. Note: category will not be created until the changes to this product have been saved.');
        if(!newCategory)return;
        var newIndex = ALLCATEGORIES.length;
        ALLCATEGORIES.push(newCategory);
        jQuery("#product_category").append("<option value='"+newCategory+"'>"+newCategory+"</option>");
        document.getElementById('product_category').selectedIndex = newIndex;
    });

    function prepareSubmit()
    {
        document.getElementById("addProductForm").submit();
    }

    jQuery(".formatDollars").on("change", function(){this.value = displayDollarsUS( this.value )} );

    jQuery("#addProductSubmit").on("click", prepareSubmit );
});

/* SETTINGS */

jQuery(document).ready(function(){
    if( PAGE != "settings" )return;

    var category_template = jQuery(".template.categoryListItem").removeClass("template").detach();

    for(var i=0; i<ALLCATEGORIES.length; i++)
    {
        var newCat = category_template.clone(),
            numProducts = 0,
            isActive = false;

        for( var j=0; j<ALLPRODUCTS.length; j++ )
        {
            if( ALLPRODUCTS[j].category == ALLCATEGORIES[i] )numProducts++;
        }

        newCat.children(".categoryName").html( ALLCATEGORIES[i] );
        newCat.children(".productsIn").html( numProducts + " products" );
        newCat.children(".isActive").children("input").data("category",ALLCATEGORIES[i]);
        newCat.children(".rename, .delete").data("category",ALLCATEGORIES[i]);

        for( var j=0; j<ACTIVECATEGORIES.length; j++ ) if( ACTIVECATEGORIES[j] == ALLCATEGORIES[i] )isActive = true;
        
        if( isActive ) newCat.children(".isActive").children("input")[0].checked = true;
        else newCat.addClass("inactive");

        jQuery("#categoriesList").append(newCat);
    }

    jQuery("#categoriesList").dragOrder();

    function prepareCategoriesSubmit()
    {
        var categories = jQuery(".categoryListItem"),
            reorder = [];

        for( var i=0; i<categories.length; i++ ) reorder.push( categories.eq(i).children(".categoryName").text() );

        jQuery("#categorySettingsForm").append('<input id="categories" type="hidden" name="categories" value="'+reorder.join("#")+'" />');
        console.log( reorder.join() );

        document.getElementById("categorySettingsForm").submit();
    }

    function setActiveSubmit( object )
    {
        var category = jQuery(object).data("category"),
            checkText = object.checked ? "active / visible" : "inactive / hidden",
            confirmed = confirm("Confirm setting category '"+category+"' to "+checkText),
            setTo = object.checked ? "active" : "inactive";

        if( ! confirmed ) object.checked = ! object.checked;
        else{
            jQuery("#categorySettingsForm").append('<input type="hidden" name="'+setTo+'" value="'+category+'" />');
            document.getElementById("categorySettingsForm").submit();
        }
    }

    function renameSubmit( object )
    {
        var category = jQuery(object).data("category"),
            renamedTo = prompt("Rename category to:",category)

        if( renamedTo == null ) return;
        else{
            jQuery("#categorySettingsForm").append('<input type="hidden" name="rename" value="'+category+"#"+renamedTo+'" />');
            document.getElementById("categorySettingsForm").submit();
        }
    }

    function deleteSubmit( object )
    {
        var category = jQuery(object).data("category"),
            deleteConfirm = confirm("Are you sure you want to delete category '"+category+"'?");

        if( ! deleteConfirm ) return;
        else{
            jQuery("#categorySettingsForm").append('<input type="hidden" name="delete" value="'+category+'" />');
            document.getElementById("categorySettingsForm").submit();
        }
    }

    jQuery("input.rename",".categoryListItem").on("click",function(){ renameSubmit(this); });
    jQuery("input.delete",".categoryListItem").on("click",function(){ deleteSubmit(this); });
    jQuery(".isActive input",".categoryListItem").on("change",function(){ setActiveSubmit(this); });
    jQuery("#categorySettingsSubmit").on("click",prepareCategoriesSubmit);
});

/* VIEW ORDER */

jQuery(document).ready(function(){
    if( PAGE != "viewOrders" )return;

    var order_template = jQuery(".template.anItem").removeClass("template").detach(),
        order_heading = jQuery(".heading","#order_items"),
        now = new Date(),
        oldestOrder = Math.floor( now.valueOf() / 1000),
        textMonths = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

    // build list select of dates for date filtering
    for( var i = 0; i < ALLORDERS.length; i++ )
    {
        if( ALLORDERS[i].created_on < oldestOrder )oldestOrder = ALLORDERS[i].created_on;
    }
    oldestOrder = new Date( oldestOrder * 1000 );

    while( oldestOrder.getMonth() <= now.getMonth() || oldestOrder.getFullYear() <= now.getYear() )
    {
        var option = document.createElement("option");
        option.setAttribute( "value", oldestOrder.getFullYear() + "." + oldestOrder.getMonth() );
        option.text = oldestOrder.getFullYear() + " - " + textMonths[ oldestOrder.getMonth() ];
        document.getElementById("search_date").appendChild( option );
        oldestOrder.setMonth( oldestOrder.getMonth() + 1 );
    }

    function filterOrders( needle, type )
    {
        var matches = [];
        
        if(needle.length == 0 || needle == "select a month...")
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_name").value = "";
            document.getElementById("search_email").value = "";
            document.getElementById("search_date").selectedIndex = 0;
            for(var i=0; i<ALLORDERS.length; i++) matches.push(i);
        }

        else if(type == "search_id")
        {
            document.getElementById("search_name").value = "";
            document.getElementById("search_email").value = "";
            document.getElementById("search_date").selectedIndex = 0;
            for(var i=0; i<ALLORDERS.length; i++) if( ALLORDERS[i].transaction_id.toLowerCase().indexOf( needle.toLowerCase() ) >= 0 ) matches.push(i);
        }

        else if(type == "search_name")
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_email").value = "";
            document.getElementById("search_date").selectedIndex = 0;
            for(var i=0; i<ALLORDERS.length; i++) if( ALLORDERS[i].name.toLowerCase().indexOf( needle.toLowerCase() ) >= 0 ) matches.push(i);
        }

        else if(type == "search_email")
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_name").value = "";
            document.getElementById("search_date").selectedIndex = 0;
            for(var i=0; i<ALLORDERS.length; i++) if( ALLORDERS[i].email.toLowerCase().indexOf( needle.toLowerCase() ) >= 0 ) matches.push(i);
        }

        else if(type == "search_date")
        {
            document.getElementById("search_id").value = "";
            document.getElementById("search_name").value = "";
            document.getElementById("search_email").value = "";
            for(var i=0; i<ALLORDERS.length; i++)
            {
                var thisDate = new Date( ALLORDERS[i].created_on * 1000 ),
                    filteredDate = document.getElementById("search_date").value.split(".");
                if( thisDate.getMonth() == filteredDate[1] && thisDate.getFullYear() == filteredDate[0] ) matches.push(i);
            }
        }

        document.getElementById("orderList").innerHTML = "";
        for( var i=0; i<matches.length; i++ )
        {
            var option = document.createElement("option"),
                today = new Date(),
                matchDate = new Date( ALLORDERS[ matches[i] ].created_on * 1000 );
            option.setAttribute( "value", matches[i] );
            if( today.valueOf() - matchDate.valueOf() < 604800000 ) option.className = "new"; // if less than a week old, mark as new
            option.text = decodeHTML( "[" + ALLORDERS[ matches[i] ].email ) + "]  " + decodeHTML( ALLORDERS[ matches[i] ].name );
            document.getElementById("orderList").appendChild(option);
        }

        matches.length = 0;
    }

    function showOrder( index )
    {console.log("show: "+index);
        if( index == "undefined" || index < 0 || index.length < 1 || ALLORDERS[index] == "undefined" )return;

        jQuery(".fill","#order").each(function(){
            var identifier = this.id.substr(6),
                newValue =  decodeHTML( ALLORDERS[index][identifier] );

            this.innerHTML = newValue;
        });

        jQuery("#order_transaction_id").html( "<a target='_blank' href='https://payments.amazon.com/sdui/sdui/txndetail?transactionId="+ALLORDERS[index].transaction_id+"'>" + ALLORDERS[index].transaction_id + "</a>" )

        var SUBTOTAL = 0,
        SHIPPING_BASE = 0,
        SHIPPING_ADD = 0;

        var ordered_date = new Date( parseInt(ALLORDERS[index].created_on)*1000 );
        document.getElementById("order_date").innerHTML = ordered_date.toLocaleDateString() + " ( " + ordered_date.toLocaleTimeString() + " ) ";

        var address_string = decodeHTML( ALLORDERS[index].address ) + "<br>" + ALLORDERS[index].city + ", " + ALLORDERS[index].state + ", " + ALLORDERS[index].zip + "<br>" + ALLORDERS[index].country;
        document.getElementById("order_address").innerHTML = address_string;

        shopcart_order.load( ALLORDERS[index].orderString );

        jQuery("#order_items").html( order_heading );

        for( var i=0; i<shopcart_order.list.length; i++ )
        {
            var thisOrder = order_template.clone(),
                thisOrderString = shopcart_order.list[i].split( shopcart_order.itemDiv ),
                image,
                quantity = thisOrderString[2],
                product_info,
                type = thisOrderString[1],
                typeString = "",
                details = "";

                for( var j=0; j<ALLPRODUCTS.length; j++ ){
                    if(ALLPRODUCTS[j].id == thisOrderString[0]){
                        product_info = ALLPRODUCTS[j];
                        image = ALLPRODUCTS[j].images[0];
                        break;
                    }
                }

                var price = type == "1" || type == "4" ? product_info.set_price : product_info.unit_price;
                if( type == "3" || type == "4" ) SUBTOTAL += parseFloat(product_info.monogram);

                SUBTOTAL += parseFloat(price) * parseFloat(quantity);

                if( parseFloat(product_info.shipping_base) > SHIPPING_BASE ) SHIPPING_BASE = parseFloat(product_info.shipping_base);
                SHIPPING_ADD += parseFloat(product_info.shipping_add) * parseFloat(quantity);

                thisOrder.children(".thumb").html( "<img src='"+image+"' />" );
                thisOrder.children(".id").html( thisOrderString[0] );
                thisOrder.children(".quantity").html( quantity );

                switch( type ){
                    case "0":
                        typeString = "a single";
                        break;
                    case "1":
                        typeString = "a set";
                        break;
                    case "2":
                        typeString = "custom print";
                        details = thisOrderString[3] + "<br>" + thisOrderString[4] + "<br>" + thisOrderString[5];
                        break;
                    case "3":
                        typeString = "a single w/ monogram";
                        details = thisOrderString[3];
                        break;
                    case "4":
                        typeString = "a set w/ monogram";
                        details = thisOrderString[3];
                        break;
                }
                
                thisOrder.children(".type").html(typeString);

                thisOrder.children(".details").html( details );

            jQuery("#order_items").append(thisOrder);
        }

        if( ALLORDERS[index].state == "CA" )SUBTOTAL += SUBTOTAL *.085;
        SUBTOTAL += SHIPPING_BASE + SHIPPING_ADD;

        jQuery("#order_charged").append( " ( calculated: "+displayDollarsUS(SUBTOTAL)+" )" );

        var stateCheck = "Yes";
        if( ! ALLORDERS[index].is_california && ALLORDERS[index].state == "CA" )stateCheck = "<b title='User inputed an out of CA zip to calculate tax, resulting in no tax, however their billing/shipping address is in CA. Tax needs to be billed.' style='color:red;'>NO</b>";
        jQuery("#order_taxedCorrectly").html(stateCheck);

    }

    jQuery("#search_id").on("keyup", function(){filterOrders( this.value, this.id );});
    jQuery("#search_name").on("keyup", function(){filterOrders( this.value, this.id );});
    jQuery("#search_email").on("keyup", function(){filterOrders( this.value, this.id );});
    jQuery("#search_date").on("change", function(){filterOrders( this.value, this.id );});

    jQuery("#orderList").on("change", function(){showOrder( this.value );});

    filterOrders("");
});