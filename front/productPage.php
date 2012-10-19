<?php

    $allCategories = categories::get_js_array( true );
    $thisProduct = new shopcart_product("retrieve", $product);

    $nonce_action = "shopcart_add_to_cart";
    $nonce_name = "fauncy_nauncy";

ob_start(); ?>

<div id="shopCart" class="product">

    <script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>../js/jquery.simplemodal.1.4.2.min.js"></script>
    
    <script type="text/javascript">
        var PAGE = "product",
            ALLCATEGORIES = <?php echo $allCategories; ?>,
            PRODUCT = <?php echo $thisProduct; ?>,
            CATEGORY = '<?php echo $category; ?>';
    </script>

    <hr class="bigBar" style="margin-top:0px; margin-bottom:4px;">

    <div id="categoryMenu">
        <a class="template category" href="/shop/category/">category</a>
    </div>

    <hr class="bigBar" style="margin-top:4px;">

<!-- PRODUCT -->

    <div id="productInfo">
        <h2 class="name">Item Name</h2>
        <p class="headerPrice">$<span class="unit"></span><span class="set"> - $</span> <span style="font-size:.5em;">US</span></p>
        <hr class="productBar" style="margin-top:0px;" />
        <p class="description">Description</p>

        <h2>Options</h2>
        <hr class="productBar" style="margin-top:4px;" />

        <div class="productOptions">
            <form id="addToCartForm">

                <div id="productCheckOptions">
                    <a class="single option"><span class="box"><img src="/images/template/checkbox.png" /></span>Single Card &nbsp;&nbsp;$<span class="price"></span></a>
                    <a class="set option" style="margin-bottom:8px;"><span class="box"><img src="/images/template/checkbox.png" /></span>Set of <span class="amount">#</span> &nbsp;&nbsp;$<span class="price"></span></a>
                    <input type="hidden" name="isSet" id="isSet" value="0" />

                    <div>
                        <a class="monogram" style="display:inline; padding-top:8px;"><span class="box" style="top:5px;"><img src="/images/template/checkbox.png" /></span>Monogram &nbsp;&nbsp;+$<span class="price"></span></a>
                        <span id="initialBox">
                            <label for="initial">| Initial: </label>
                            <input class="styledInput" type="text" id="initial" name="initial" maxlength="3" />
                            <input type="hidden" name="isMonogramed" id="isMonogramed" value="0" />
                        </span>
                    </div>                    
                </div>

                <div id="productCustomOptions">

                    <input type="hidden" name="isCustom" id="isCustom" value="1" />

                    <div class="lineInput">
                        <label for="customNames">Names |</label>
                        <input type="text" name="names" id="customNames" class="required" maxlength="60" data-example="John & Jane" />
                    </div>
                    <div class="lineInput" title="MM/DD/YY">
                        <label for="customDate">Date |</label>
                        <input type="text" name="date" id="customDate" class="required" maxlength="8" data-example="MM/DD/YY" />
                    </div>
                    <div class="lineInput">
                        <label for="customLocation">Location |</label>
                        <input type="text" name="location" id="customLocation" class="required" maxlength="60" data-example="City, State" />
                    </div>
                </div>

                <div>
                    <div id="quantityBox">
                        <input class="styledInput" type="text" id="quantity" name="quantity" value="0" /><label for="quantity">Quantity</label>
                    </div>

                    <p id="subtotal" class="styledInput">Subtotal &nbsp;| <span class="price">$ 0.00 US</span></p>
                </div>

                <a id="addToCart" class="styledInput"><span class="divider">|</span>Add to Cart <img src="/images/template/cart.png" /></a>
                <a id="testBuy" style="display:none;">testBuy</a>
                <!-- submit button! -->
                <div id="tempMessage" style="padding-top:20px; clear:both; padding-bottom:10px; width:102%;">
                    <!-- temp message location -->
                </div>

                <input type="hidden" name="shopcart_add_to_order" value="1" />
                <input type="hidden" name="product_id" value="<?php echo $thisProduct->getId(); ?>" />
            </form>
        </div>

    </div>

    <div id="productImage">
        <img />
        <div id="imageSelectors">
            <a class="template showLink"></a>
        </div>
    </div>

    <div id="shareBox">
        <div style="float:left;">Share:</div>
        <div id="socialBar">
			<div class="facebook" style="margin-right:0px; margin-left:10px;"><div class="fb-like" data-href="<?php echo get_permalink() . get_query_var("shopcart_category") . "/" . get_query_var("shopcart_product") . "/"; ?>" data-send="false" data-layout="button_count" data-show-faces="false"></div></div>
                        <div class="twitter" style="margin-right:0px; margin-left:0px;"><a href="https://twitter.com/share?url=<?php echo urlencode(get_permalink()); ?>" class="twitter-share-button" data-lang="en">Tweet</a></div>
                        <?php
                        $src = "http://www.paperloveanddreams.com" . $thisProduct->main_image;
                        printf( '<div class="pinterest" style="margin-right:0px; margin-left:0px;"><a href="http://pinterest.com/pin/create/button/?url=%1$s&media=%2$s" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a></div>', urlencode(get_permalink()), urlencode($src) );
                        echo "<!-- $src -->";
                        ?>
		</div>
    </div>
</div>

<?php echo ob_get_clean();