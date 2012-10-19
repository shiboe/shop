<?php

function shopcart_viewOrdersPage()
{

    $allOrders = shopcart_order::exportJavascriptArray();
    $allProducts = new shopcart_products("all");

    ob_start(); ?>

    <script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>../js/shopcart-admin.js"></script>
    <script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>../js/shopcart_order.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__); ?>../css/shopcart-admin.css" />

    <script type="text/javascript">
        var PAGE = "viewOrders",
            ALLPRODUCTS = <?php echo $allProducts; ?>,
            ALLORDERS = <?php echo $allOrders; ?>;
    </script>

    <div class="wrap adminContainer">
        <h2>View Orders <span style="font-size:.5em;">Shopcart</span></h2>


        <form>

            <h3>Find an order by:</h3>

            <div id="searchOptions">
                <label for="search_id">transaction ID</label>
                <input type="text" id="search_id" maxlength="20" />

                <label for="search_name">Buyer Name</label>
                <input type="text" id="search_name" maxlength="30" />

                <label for="search_email">Buyer Email</label>
                <input type="text" id="search_email" maxlength="60" />

                <label for="search_date">Date</label>
                <select id="search_date">
                    <option>select a month...</option>
                </select>
            </div>

            <div class="newMessage">newly created within last 7 days</div>
            <select id="orderList" size="10"></select>

            <div id="order">
                <h3 style="padding-top:30px; clear:both;">Order:</h3>

                <table cellpadding="4" cellspacing="2" border="1">
                    <tr>
                        <td class="label">transaction ID</td><td id="order_transaction_id"></td>
                        <td class="label">date</td><td id="order_date"></td>
                    </tr>
                </table>

                <table cellpadding="4" cellspacing="2" border="1">
                    <tr>
                        <td class="label">Buyer</td><td id="order_name" class="fill"></td>
                        <td id="order_address" rowspan="3" style="text-align:center;"></td>
                    </tr>
                    <tr>
                        <td class="label">Email</td><td id="order_email" class="fill"></td>
                    </tr>
                    <tr>
                        <td class="label">Phone</td><td id="order_phone" class="fill"></td>
                    </tr>
                </table>

                <table cellpadding="4" cellspacing="2" id="order_items" border="1">
                    <tr class="heading">
                        <td class="label" width="160"></td><td class="label">Name</td><td class="label" width="30">#</td><td class="label">Type</td><td class="label">Details</td>
                    </tr>
                    <tr class="template anItem">
                        <td class="thumb"></td><td class="id"></td><td class="quantity"></td><td class="type"></td><td class="details"></td>
                    </tr>
                </table>

                <table cellpadding="4" cellspacing="2" border="1">
                    <tr>
                        <td class="label">Charged</td><td id="order_charged" class="fill"></td>
                        <td class="label">Promo Code</td><td id="order_promo" class="fill"></td>
                        <td class="label">State taxed correctly</td><td id="order_taxedCorrectly"></td>
                    </tr>
                </table>
            </div>

            <footer><?php include('_footerContent.php'); ?></footer>
        </form>
    </div>

    <?php echo ob_get_clean();
}


