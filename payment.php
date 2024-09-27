<?php
// Access token Production
$accessToken = '';

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

// Initialize response array
$res = [
    'status' => 'error',
    'message' => 'An unknown error occurred.',
    'data' => null
];

if(isset($data) && !empty($data['formData'])) {
    try {
        $fullName = $data['formData']['fullName'];
        $nameParts = explode(' ', $fullName);
        $firstName = isset($nameParts[0]) ? $nameParts[0] : '';
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        // Prepare customer data
        $customerData = [
            'given_name' => $firstName,
            'family_name' => $lastName,
            'email_address' => $data['formData']['email'],
            'phone_number' => $data['formData']['phoneNumber']
        ];

        $verificationToken = $data['verificationToken'];
        
        $price =  intval($data['formData']['price'] * 100);
        $body = [
            'location_id' => $data['locationId'], 
            'source_id' => $data['sourceId'], 
            'idempotency_key' => $data['idempotencyKey'],
            'verification_token' => $verificationToken,
            'amount_money' => [
                'amount' => $price, 
                'currency' => 'GBP',
            ],
        ];
        //print_r($body);
        // Create a payment
        $apiUrl = 'https://connect.squareup.com/v2/payments';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); 
        
        $response = curl_exec($ch);
        curl_close($ch);
        //echo $response;
        $paymentData = json_decode($response, true);
        
        if(isset($paymentData['payment']['status']) && $paymentData['payment']['status'] === "COMPLETED"){
            $paymentId = $paymentData['payment']['id'];
            $cardData = $paymentData['payment']['card_details'];
            $expMonth = $cardData['card']['exp_month'];
            $expYear = $cardData['card']['exp_year'];
            
            // After payment success, create customer
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://connect.squareup.com/v2/customers',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($customerData),
                CURLOPT_HTTPHEADER => array(
                    'Square-Version: 2024-08-21',
                    'Authorization: Bearer '.$accessToken,
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);
            //echo $response; 
            curl_close($curl);
            
            $customerData = json_decode($response, true);

            if(isset($customerData['customer']['id'])){
                $customerId = $customerData['customer']['id'];

                // Prepare data for attaching the card
                $cardData = [
                    "card" => [
                        "billing_address" => [
                            "first_name" => $firstName,
                            "last_name" => $lastName,
                        ],
                        "cardholder_name" => $data['formData']['fullName'],
                        "customer_id" => $customerId,
                        "exp_month" => $expMonth,  
                        "exp_year" => $expYear     
                    ],
                    "idempotency_key" => $data['idempotencyKey'],
                    "source_id" => $paymentId,
                    'verification_token' => $verificationToken 
                ];
                //print_r($cardData);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://connect.squareup.com/v2/cards',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($cardData),
                    CURLOPT_HTTPHEADER => array(
                        'Square-Version: 2024-08-21',
                        'Authorization: Bearer '.$accessToken,
                        'Content-Type: application/json',
                    ),
                ));

                $response = curl_exec($curl);
                //echo $response;
                curl_close($curl);

                $response = json_decode($response, true);
                $res['status'] = 'success';
                $res['message'] = 'Payment and customer creation succeeded.';
                $res['data'] = $paymentData;
                $res['cardData'] = $response;
                $res['customerData'] = $customerData;
            }
        } else {
            $res['status'] = 'failure';
            $res['message'] = 'Payment failed.';
            $res['data'] = $paymentData;
        }
    } catch(Exception $e) {
        $res['status'] = 'error';
        $res['message'] = 'Payment not completed';
    }
} else {
    $res['status'] = 'error';
    $res['message'] = 'Invalid input data.';
}

header('Content-Type: application/json');
echo json_encode($res);
file_put_contents('logfile.log', date('Y-m-d H:i:s') . " - " . json_encode($res) . PHP_EOL, FILE_APPEND);
exit;

?>

