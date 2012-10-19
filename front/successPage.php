<?php

    $failed;

    try{
        // check request is legit from amazon
        $utils = new Amazon_FPS_SignatureUtilsForOutbound();
        $urlEndPoint = "http://www.paperloveanddreams.com/success/";
        if( $utils->validateRequest($getvars, $urlEndPoint, "GET") != 1)error_log("Could not verify Amazon response. Referrer: ".$_SERVER["HTTP_REFERER"]." sent = ".$getvars);

        $order = new shopcart_order( $getvars["referenceId"] );

        $order->complete($getvars);

        $order->emailSellerReciept("billing@paperloveanddreams.com");
    }catch(Exception $e){
        $failed .= $e->getMessage();
        error_log("Exception during success page order production: ".$failed);
    }
?>

    <script type="text/javascript">
        var PAGE = 'success';

        var exp = -1000 * 60 * 60,
            date = new Date();
        date.setTime( date.getTime() + exp );
        document.cookie = "shopcart_preorder=''; expires=" + date.toGMTString() + "; path=/";
        document.cookie = "shopcart_order=''; expires=" + date.toGMTString() + "; path=/";
    </script>


<div style="margin:20px auto; text-align:center;">
<h1 style="font-size:1.4em; font-style:italic;">Thank you for making a purchase with us at Paper, Love & Dreams!</h1>
<p>A confirmation email has been sent to your supplied email address.<br>Please <a href="/contact/">contact us</a> with any questions, comments, or concerns regarding your order.</p>
<!-- <?php echo $failed; ?> -->


</div>