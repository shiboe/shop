<?php

    $allProducts = new shopcart_products("all");

    $nonce_action = "shopcart_checkout";
    $nonce_name = "fauncy_nauncy";

    $failed;

    
    $existingID = isset( $_COOKIE["shopcart_preorder"] ) ? $_COOKIE["shopcart_preorder"] : false;
    $pre_order = new shopcart_order( $existingID );

    try
    {
        if( isset( $_COOKIE["shopcart_order"] ) )
        {
            if ( ! empty( $_POST ) && !wp_verify_nonce($_POST[$nonce_name],$nonce_action) )throw new Exception("Failed to verify request authenticity.");

            if( $pre_order->check_for_pre_order_updates() )
            {
                if( $existingID )$pre_order->update_pre_order();
                else $pre_order->create_pre_order();

                $existingID = $pre_order->get_id();
            }

            if( $existingID ) $pre_order->calculate_costs( $allProducts->get_raw_list() );
        }
    }
    catch(Exception $e)
    {
        $failed = $e->getMessage();
    }

ob_start(); ?>

<div id="shopcart_checkout">

    <script type="text/javascript">
        var PAGE = "checkout",
            ALLPRODUCTS = <?php echo $allProducts; ?>,
            SHOPCART_PREORDER = '<?php echo $existingID; ?>',
            CLEAR_COOKIES = <?php if( $failed ) echo "true"; else echo "false"; ?>;
    </script>

    <hr class="bigBar" style="margin-top:0px; margin-bottom:4px;">

    <p style="text-align:center; margin:6px 0;">
        <span>Shopping Cart |</span>
        <a href="/terms-privacy/">Terms of Service</a> |
        <a href="/terms-privacy/">Privacy Policy</a> |
        <a href="/faq/">FAQ</a>
    </p>

    <hr class="bigBar" style="margin-top:4px;">

    <?php if( $failed ) echo "<p class='failText'>$failed</p>"; ?>

    <h2 style="text-align:center; margin:20px 0;"><img src="/images/template/checkout-header.png" alt="Please review the contents of your shopping cart below!" /></h2>

        <div id="checkoutSidebar">

            <form action="/cart/" method="post" name="generateOrder">
                <?php
                    wp_nonce_field($nonce_action,$nonce_name);
                ?>
                <div class="checkoutContainer">
                    <p style="width:200px;">Your postal ZIP code is required<br> to complete this order.</p>
                    <label for="zipInput">Postal ZIP Code:</label>
                    <input type="text" name="ZIP" id="zipInput" onchange="document.forms['generateOrder'].submit()" class="styledInput" style="width:40px; text-align:center; margin-top:-6px;" maxlength="5" value="<?php if($existingID)echo $pre_order->get_zip(); ?>" />
                </div>

                <hr class="rightBar" />

                <div class="checkoutContainer" style="opacity:.5;">
                    <p>Have a promo code? Enter it here to apply your special savings!</p>
                    <label>Promo Code:</label>
                    <input type="text" id="promoInput" class="styledInput" style="width:90px; margin-top:-6px;" maxlength="30" readonly="readonly" value="<?php echo $pre_order->get_promo(); ?>" />
                </div>
            </form>

            <hr class="rightBar" />

            <div class="checkoutContainer">
                <div id="subTotalCount" class="checkoutRow">
                    <label>Subtotal:</label>
                    <span class="value">$ <?php echo money_format( "%.2n", $pre_order->get_subtotal() ); ?></span>
                </div>
                
                <div id="taxCount" class="checkoutRow" style="background-color:#FBF9F0;">
                    <label>Tax:</label>
                    <span class="value">$ <?php echo money_format( "%.2n", $pre_order->get_sales_tax() ); ?></span>
                </div>

                <div id="promoCount" class="checkoutRow">
                    <label>Promo:</label>
                    <span class="value">$ 0.00</span>
                </div>

                <div id="shippingCount" class="checkoutRow" style="background-color:#FBF9F0;">
                    <label>Shipping:</label>
                    <span class="value">$ <?php echo money_format( "%.2n", $pre_order->get_shipping() ); ?></span>
                </div>

                <hr class="rightSideDots" style="margin:10px 0 0 0;">

                <div id="totalCount" class="checkoutRow">
                    <label>Total:</label>
                    <span class="value">$ <?php echo money_format( "%.2n", $pre_order->get_total() ); ?> US</span>
                </div>
            </div>

            <?php if( $existingID ){ ?>

            <hr class="rightBar" />

            <div id="checkoutBox">
                <p style="text-align:justify;">Everything look good? Click the checkout button below to complete your order through Amazon Checkout.</p>

                <?php StandardButton::formIt( "USD ".$pre_order->get_total(), $existingID, "prod" ); ?>
                 
            </div>

            <?php } ?>

        </div>

        <div id="orders">
            <div class="order template">
                <div class="thumb"><img /></div>
                <div class="productInfo">
                    <span class="name">Item Name</span>
                    <span class="price"></span>
                </div>
                <hr class="bar-checkout">
                <div class="options">
                    <div class="custom">
                        <div class="title">Your Custom Print:</div>
                        <div class="names">Names | <span></span></div>
                        <div class="date">Date | <span></span></div>
                        <div class="location">Location | <span></span></div>
                    </div>
                    <div class="monogram">
                        <div class="title">Your Monogram: <span class="initials"></span> <span class="price" style="font-size:.8em; font-style:italic;">( + <span class="value">$0.00 US</span> )</span></div>
                    </div>
                    <div class="modifyButtons">
                        <a class="remove">Remove</a><span class="div">|</span><a class="edit">Edit</a>
                    </div>
                </div>
                
                <div class="purchaseInfo">
                    <div class="quantityBox">
                        <input class="styledInput quantity" type="text" name="quantity" value="0" /><label>Quantity</label>
                    </div>

                    <p class="styledInput subtotal">Subtotal &nbsp;| <span class="price">$ 0.00 US</span></p>
                </div>
            </div>
        </div>

        
        
    </form>

</div>

<?php echo ob_get_clean();