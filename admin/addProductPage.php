<?php

function shopcart_addProductPage()
{
    $nonce_action = "shopcart_add_product";
    $nonce_name = "fauncy_nauncy";

    $failed = false;
    $success = false;

    try
    {
        if( isset($_POST["id"]) )
        {
            check_admin_referer($nonce_action,$nonce_name);

            $product = new shopcart_product("create", $_POST);
            $success = "Product '$product->name' created successfully!";
        }
    }
    catch(Exception $e)
    {
        $failed = $e->getMessage();
    }

    $allCategories = categories::get_js_array();

    ob_start(); ?>

    <script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>../js/shopcart-admin.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__); ?>../css/shopcart-admin.css" />

    <script type="text/javascript">
        var PAGE = "add",
            ALLCATEGORIES = <?php echo $allCategories; ?>;
    </script>

    <div class="wrap adminContainer">
        <h2>Add Products <span style="font-size:.5em;">Shopcart</span></h2>

        <?php if( $failed ) echo "<p class='failText'>$failed</p>"; ?>
        <?php if( $success ) echo "<p class='successText'>$success</p>"; ?>

        <form id="addProductForm" method="post" action="/wp-admin/admin.php?page=shopcart_add">

            <?php wp_nonce_field($nonce_action,$nonce_name); ?>

            <div id="product">
                <h3 style="margin-left:0px;">Product:</h3>
                <p>Optional fields are colored <span style="background-color:#E8F6FA; border-radius:10px 10px 0 0; padding:2px 10px;">blue.</span> Modified fields are colored in <span style="padding:2px 10px; background-color:#FCF3C7; border:1px solid gold; border-radius:2px;">gold.</span>

                <div class="inputBox">
                    <label for="product_id">ID</label>
                    <input type="text" id="product_id" name="id" />
                </div>
                <div class="inputBox">
                    <label for="product_name">Name</label>
                    <input type="text" id="product_name" name="name" />
                </div>
                <div class="inputBox clear">
                    <label for="product_last_modified">Valid?</label>
                    <input type="text" id="product_valid" readonly="readonly" />
                </div>
                <div class="inputBox" style="position:relative;">
                    <label for="product_category">Category</label>
                    <select id="product_category" name="category"></select>
                    <button id="addCategoryButton" type="button" class="button-primary" style="position:absolute;bottom:0px;right:-40px; padding:4px 12px;">add</button>
                </div>



                <div class="inputBox clear">
                    <label for="product_description">Description</label>
                    <textarea id="product_description" maxlength="1024" name="description"></textarea>
                </div>

                <div style="float:left;">
                    <div class="inputBox small" style="padding-top:14px;">
                        <label for="product_unit_price">Unit price</label>
                        <input type="text" id="product_unit_price" class="formatDollars" name="unit_price" />
                    </div>
                    <div class="inputBox small optional" style="padding-top:14px;">
                        <label for="product_monogram">Monogram</label>
                        <input type="text" id="product_monogram" class="formatDollars" name="monogram" />
                    </div>
                    <div class="inputBox small clear optional" style="padding-top:10px;">
                        <label for="product_set_amount"># in a set</label>
                        <input type="text" id="product_set_amount" name="set_amount" />
                    </div>
                    <div class="inputBox small optional" style="padding-top:10px;">
                        <label for="product_set_price">Set price</label>
                        <input type="text" id="product_set_price" class="formatDollars" name="set_price" />
                    </div>
                    <div class="inputBox small clear" style="padding-top:10px;">
                        <label for="product_shipping_base">Base shipping cost</label>
                        <input type="text" id="product_shipping_base" class="formatDollars" name="shipping_base" />
                    </div>
                    <div class="inputBox small" style="padding-top:10px;">
                        <label for="product_shipping_add">Additional shipping per</label>
                        <input type="text" id="product_shipping_add" class="formatDollars" name="shipping_add" />
                    </div>
                    <div class="inputBox small clear" style="padding-top:16px;">
                    <label for="product_is_full_set">is a full set?</label>
                    <select id="product_is_full_set" name="is_full_set"><option value="no">No</option><option value="yes">Yes</option></select>
                </div>
                </div>


            </div>

            <input id="addProductSubmit" class="button-primary" style="clear:both; margin:40px; width:auto;" type="button" value="Add this product" />

            <footer><?php include('_footerContent.php'); ?></footer>
        </form>
    </div>

    <?php echo ob_get_clean();
}


