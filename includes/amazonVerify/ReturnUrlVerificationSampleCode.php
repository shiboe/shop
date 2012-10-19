<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     Amazon_FPS
 *  @copyright   Copyright 2008-2011 Amazon Technologies, Inc.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2010-08-28
 */
/******************************************************************************* 
 *    __  _    _  ___ 
 *   (  )( \/\/ )/ __)
 *   /__\ \    / \__ \
 *  (_)(_) \/\/  (___/
 * 
 *  Amazon FPS PHP5 Library
 *  Generated: Wed Sep 23 03:35:04 PDT 2009
 * 
 */

  
class Amazon_FPS_ReturnUrlVerificationSampleCode {

	public static function test( $v ) {
        $utils = new Amazon_FPS_SignatureUtilsForOutbound();
        
        //Parameters present in return url.
        $params["signature"] = $v["signature"];
	//$params["expiry"] = "10/2016";
	$params["signatureVersion"] = $v["signatureVersion"];
	$params["signatureMethod"] = $v["signatureMethod"];
	$params["certificateUrl"] = $v["certificateUrl"];
	//$params["tokenID"] = "77H84MAUCME17HP5VVIC61KGHXSAX6KS7DJ6PXI5MC5C3LZ8X8RPRKQIAAE3TRP8";
	$params["status"] = $v["status"];
	$params["callerReference"] = $v["referenceId"];
 
        $urlEndPoint = "http://www.paperloveanddreams.com/success/"; //Your return url end point.
        print "Verifying return url signed using signature v2 ....\n";
        //return url is sent as a http GET request and hence we specify GET as the http method.
        //Signature verification does not require your secret key
        print "Is signature correct: " . $utils->validateRequest($v, $urlEndPoint, "GET") . "\n";
	}
}


?>
