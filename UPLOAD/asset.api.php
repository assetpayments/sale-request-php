<?php 

	header("Content-Type: text/html; charset=utf-8");

	//Processing Details
	$asset_key = '03e5515f-7cd8-49ce-9284-5d78ff1390d9';
	$asset_secret = 'b6e6b617-88b0-4102-8f8f-704327fa6d9f';
	$processing_method = 'redirect'; //Allowed values iframe, redirect or invoice
	$processing_id = 1;
	$allowed_processings = [1];
	$template_id = 0;
	
	//HTML form details
	$form_customer_name = $_POST['Name_Surname'];
	$form_phone = '+'.$_POST['form_phone'];
	$form_email = $_POST['form_email'];
	$form_address = $_POST['form_address'];
	$form_country = 'UKR';
	$form_order_id = '12345';	
	$form_description = $_POST['form_description'];
	$form_sum = number_format($_POST['form_sum'], 2, '.', '');	
	$form_currency = $_POST['form_currency'];	

	$get_source = parse_url(getenv("HTTP_REFERER"));
	$source_domain = $get_source['host'];
				
	$ip = getenv('HTTP_CLIENT_IP')?:
		  getenv('HTTP_X_FORWARDED_FOR')?:
		  getenv('HTTP_X_FORWARDED')?:
		  getenv('HTTP_FORWARDED_FOR')?:
		  getenv('HTTP_FORWARDED')?:
		  getenv('REMOTE_ADDR');

	//Required variables	
	$option['TemplateId'] = $template_id;
	$option['ProcessingId'] = $processing_id;
	$option['AllowedProcessings'] = $allowed_processings;
	$option['CustomMerchantInfo'] = $form_description;
	$option['MerchantInternalOrderId'] = $form_order_id;
	$option['StatusURL'] = 'status';	
	$option['ReturnURL'] = 'return';
	$option['AssetPaymentsKey'] = $asset_key;
	$option['Amount'] = $form_sum;	
	$option['Currency'] = $form_currency;
	$option['IpAddress'] = $ip;
	$option['CountryISO'] = $form_country;	
		
	//****Customer details and address****//
	$option['FirstName'] = $form_customer_name;
        $option['Email'] = $form_email;
        $option['Phone'] = $form_phone;
        $option['Address'] = $form_address;
		
	//****Cart details****//
   	$option['Products'][0]['ProductId'] = $form_order_id;
	$option['Products'][0]['ProductName'] = 'Order #' + $form_order_id;
	$option['Products'][0]['ProductPrice'] = $form_sum;
	$option['Products'][0]['ProductItemsNum'] = 1;
	$option['Products'][0]['ImageUrl'] = 'https://assetpayments.com/dist/css/images/product.png';   

	if ($processing_method == 'iframe'){		
		$option['OperationMode'] = 'Iframe';	
		$option['TransactionType'] = 'Sale';		
		$data = json_encode($option);
		$url = 'https://api.assetpayments.us/api/payment/create';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status == 201 ) {
			die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}
		curl_close($curl);
		
		$response = json_decode($json_response, true);
		$externalForm  = $response['htmlIframeForm']; 
		$OrderId = $response['transactionId']; 
		echo $externalForm;		
	} 
	
	if ($processing_method == 'redirect') {		
	 	$data = base64_encode( json_encode($option) );
		echo sprintf('
            	<form method="POST" id="checkout" action="https://assetpayments.us/checkout/pay" accept-charset="utf-8">
                <input type="hidden" name="data" value='.$data.' />                
            	</form>');	
		echo "<script type=\"text/javascript\"> 
                window.onload=function(){
                    document.forms['checkout'].submit();
                }
		</script>";		
	}	
	
	if ($processing_method == 'invoice'){		
		$option['CustomerNotification'] = 'false';	
		$option['AllowedProcessings'] = [];	
		$option['CustomerEmail'] = $form_email;		
		$data = json_encode($option);
		$url = 'https://api.assetpayments.us/api/merchant/invoice/create';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status == 201 ) {
			die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}
		curl_close($curl);
		
		$response = json_decode($json_response, true);
		$invoiceLink  = $response['invoicePaymentLink']; 
		header('Location:'.$invoiceLink); 
		echo $invoiceLink;		
	} 

	if ($processing_method == 'token'){

		$option['OperationMode'] = 'Iframe';
		$option['TransactionType'] = 'Token';
		$data = json_encode($option);
		$url = 'https://api.assetpayments.us/api/payment/create';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status == 201 ) {
			die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}
		curl_close($curl);

		$response = json_decode($json_response, true);
		print_r ($response);
		echo '<br/><br/>';
		$transactionId = $response[transactionId];

		if (!empty($transactionId) || $transactionId != 0){
			$requestSign =$asset_key.':'.$transactionId.':'.strtoupper($secret);
			$sign = hash_hmac('md5',$requestSign,$secret);

			$option['AssetPaymentsKey'] = $asset_key;
			$option['TransactionId'] = $transactionId;
			$option['CardToken'] = '3f247fb3-e78f-4b6e-98c1-8aaea4694d';
			$option['Signature'] = $sign;

			$data = json_encode($option);
			$url = 'https://api.assetpayments.us/api/payment/token';
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

			$json_response = curl_exec($curl);
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ( $status == 201 ) {
				die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
			}
			curl_close($curl);

			$response = json_decode($json_response, true);
			print_r ('transactionState = ' . $response[transactionState] . '<br/>');
			print_r ('transactionStateCode = ' . $response[transactionStateCode] . '<br/>');
			print_r ('errorCode = ' . $response[errorCode] . '<br/>');
			print_r ('responseMessage = ' . $response[responseMessage] . '<br/>');
			print_r ('transactionId = ' . $response[transactionId] . '<br/>');
		}

	}

	if ($processing_method == 'applepay'){

		$option['OperationMode'] = 'Iframe';
		$option['TransactionType'] = 'ApplePay';

		$data = json_encode($option);
		$url = 'https://api.assetpayments.us/api/payment/create';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status == 201 ) {
			die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}
		curl_close($curl);

		$response = json_decode($json_response, true);
		$externalForm  = $response['htmlIframeForm'];
		$OrderId = $response['transactionId'];
		echo $externalForm;

	}

	public function callback() {
		$json = json_decode(file_get_contents('php://input'), true);

		$key = $asset_key;
		$secret = $asset_secret;
		$transactionId = $json['Payment']['TransactionId'];
		$signature = $json['Payment']['Signature'];
		$order_id = $json['Order']['OrderId'];
		$status = $json['Payment']['StatusCode'];

		$requestSign =$key.':'.$transactionId.':'.strtoupper($secret);
		$sign = hash_hmac('md5',$requestSign,$secret);
		//Successful payment
		if ($status == 1 && $sign == $signature) {			
		} 
		//Failed payment
		if ($status == 2 && $sign == $signature) {			
		}
		//Authorized (holded) payment
		if ($status == 4 && $sign == $signature) {			
		} 
	}
?>
