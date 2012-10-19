
var shopcart_order = { list:[], itemDiv:"||", orderDiv:"##" };

shopcart_order.load = function( fromString )
{
    if( typeof CLEAR_COOKIES != "undefined" && CLEAR_COOKIES ){
        var exp = -1000 * 60 * 60,
            date = new Date();
        date.setTime( date.getTime() + exp );
        document.cookie = "shopcart_preorder=''; expires="+date.toGMTString() + "; path=/";
    }

    if( fromString ){

        shopcart_order.list = fromString.split(shopcart_order.orderDiv);

        return;
    }

    var cookies = document.cookie.split(";"),
        orderName = "shopcart_order",
        preorderName = "shopcart_preorder",
        orderString = "",
        preorderString = "";

    // check for existing preorder
    for( var i=0; i<cookies.length; i++ )
    {
        if( cookies[i].substr(0,orderName.length) == orderName || cookies[i].substr(1,orderName.length ) == orderName )
        {
            orderString = cookies[i].substr( cookies[i].indexOf("=")+1 );
            break;
        }
    }
    // if found, load the preorder
    if( orderString.length > 0 ) shopcart_order.list = orderString.split(shopcart_order.orderDiv);

    // check for a server saved preorder
    if( typeof SHOPCART_PREORDER != "undefined" && SHOPCART_PREORDER )
    {
        for( var i=0; i<cookies.length; i++ )
        {
            if( cookies[i].substr(0,preorderName.length) == preorderName || cookies[i].substr(1,preorderName.length ) == preorderName )
            {
                preorderString = cookies[i].substr( cookies[i].indexOf("=")+1 );
                break;
            }
        }
        // if found but not cookied, save a preorder cookie
        var exp = 1000 * 60 * 60,
            date = new Date();
        date.setTime( date.getTime() + exp );
        if( SHOPCART_PREORDER && preorderString != SHOPCART_PREORDER ) document.cookie = "shopcart_preorder="+SHOPCART_PREORDER+"; expires=" + date.toGMTString() + "; path=/";
    }
}

shopcart_order.save = function( callback )
{
    var exp = 24 * 60 * 60 * 1000 * 7; //1 week
    var date = new Date();
    date.setTime( date.getTime() + exp );

    var order = shopcart_order.list.join(shopcart_order.orderDiv);
    
    document.cookie = "shopcart_order=" + order + "; expires=" + date.toGMTString() + "; path=/";

    if( ! callback ) window.location = "/cart/";
    else callback();
}

shopcart_order.erase = function() { document.cookie = "shopcart_order=; expires=0"; return true; }

shopcart_order.addNew = function()
{
    shopcart_order.load();

    var id = PRODUCT.id,
        type = 0,
        quantity = cleanInput( document.getElementById("quantity").value ),
        additional = "";

    function cleanInput( input ){return input.replace(";","").replace(shopcart_order.itemDiv,"").replace(shopcart_order.orderDiv,"").substr(0,60);}

    // determin type and additional values as necessary
    if( PRODUCT.category == "Custom Prints" )
    {
        type = 2;
        additional = shopcart_order.itemDiv + cleanInput( document.getElementById("customNames").value ) +
                     shopcart_order.itemDiv + cleanInput( document.getElementById("customDate").value ) +
                     shopcart_order.itemDiv + cleanInput( document.getElementById("customLocation").value );
    }
    else{
        if( document.getElementById("isSet").value == "1" )type++;
        if( document.getElementById("isMonogramed").value == "1" ){
            type += 3;
            additional = shopcart_order.itemDiv + cleanInput( document.getElementById("initial").value );
        }
    }
    // combine to form one order
    var orderString = id + shopcart_order.itemDiv + type + shopcart_order.itemDiv + quantity + additional;

    // check for duplicate orders, and add quantity to them if found, otherwise prepend to complete order
    if( shopcart_order.list.length > 0 )
    {
        for( var i=0; i<shopcart_order.list.length; i++ )
        {
            var anOrder = shopcart_order.list[i].split(shopcart_order.itemDiv);
            
            if( anOrder[0] == id && anOrder[1] == type )
            {   
                additionalChecks = additional.split(shopcart_order.itemDiv);
                if( type == 2 && ( anOrder[3] != additionalChecks[0] || anOrder[4] != additionalChecks[1] || anOrder[5] != additionalChecks[2] ) )break; //similar custom order with different options
                if( ( type == 3 || type == 4 ) && anOrder[3] != additionalChecks[0] )break; //similar monogramed order with different options
                
                anOrder[2] = parseInt(anOrder[2]) + parseInt(quantity);
                shopcart_order.list[i] = anOrder.join(shopcart_order.itemDiv);
                shopcart_order.save();
                return;
            }
        }
        shopcart_order.list.unshift( orderString );
    }
    else shopcart_order.list[0] = orderString;

    shopcart_order.save();

    // prevent multiple calls (accidental multi-clicks)
    shopcart_order.addNew = function(){return false;};
}

shopcart_order.changeQuantity = function( newQuantity, index )
{
    if( isNaN( parseInt(newQuantity) ) || newQuantity == 0 ) return shopcart_order.deleteOrder(index);

    var thisOrder = shopcart_order.list[index].split( shopcart_order.itemDiv );

    thisOrder[2] = newQuantity;

    shopcart_order.list[index] = thisOrder.join(shopcart_order.itemDiv);

    shopcart_order.save();
}

shopcart_order.deleteOrder = function( index )
{
    shopcart_order.list.splice( index, 1 );
    shopcart_order.save();
}