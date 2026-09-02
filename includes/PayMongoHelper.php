<?php
class PayMongoHelper {
    private $secret_key;
    private $public_key;
    private $base_url;
    private $config;
    
    public function __construct() {
        $this->config = include(__DIR__ . '/../config/paymongo.php');
        $this->secret_key = $this->config['secret_key'];
        $this->public_key = $this->config['public_key'];
        $this->base_url = $this->config['base_url'];
    }
    
    /**
     * Create Payment Intent for GCash or Card
     */
    public function createPaymentIntent($amount, $payment_method = 'gcash', $description = '', $metadata = []) {
        $url = $this->base_url . '/payment_intents';
        
        $allowed_methods = $payment_method === 'gcash' ? ['gcash'] : ['card'];
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $this->convertToCentavos($amount),
                    'payment_method_allowed' => $allowed_methods,
                    'currency' => 'PHP',
                    'description' => $description,
                    'capture_type' => 'automatic',
                    'metadata' => $metadata
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Create GCash Source
     */
    public function createGCashSource($amount, $redirect_urls) {
        $url = $this->base_url . '/sources';
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $this->convertToCentavos($amount),
                    'redirect' => $redirect_urls,
                    'type' => 'gcash',
                    'currency' => 'PHP'
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Create Payment Method (for cards)
     */
    public function createPaymentMethod($card_details) {
        $url = $this->base_url . '/payment_methods';
        
        $data = [
            'data' => [
                'attributes' => [
                    'type' => 'card',
                    'details' => $card_details
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Attach Payment Method to Payment Intent
     */
    public function attachPaymentMethod($payment_intent_id, $payment_method_id) {
        $url = $this->base_url . "/payment_intents/{$payment_intent_id}/attach";
        
        $data = [
            'data' => [
                'attributes' => [
                    'payment_method' => $payment_method_id,
                    'return_url' => $this->config['success_url']
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Retrieve Payment Intent
     */
    public function getPaymentIntent($payment_intent_id) {
        $url = $this->base_url . "/payment_intents/{$payment_intent_id}";
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Retrieve Source
     */
    public function getSource($source_id) {
        $url = $this->base_url . "/sources/{$source_id}";
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Calculate gateway fee
     */
    public function calculateGatewayFee($amount, $payment_method = 'gcash') {
        $rate = $payment_method === 'gcash' ? $this->config['gcash_fee_rate'] : $this->config['card_fee_rate'];
        $fee = ($amount * $rate) + $this->config['fixed_fee'];
        return round($fee, 2);
    }
    
    /**
     * Get total amount including fees
     */
    public function getTotalWithFees($amount, $payment_method = 'gcash') {
        return $amount + $this->calculateGatewayFee($amount, $payment_method);
    }
    
    /**
     * Convert PHP amount to centavos
     */
    private function convertToCentavos($amount) {
        return intval($amount * 100);
    }
    
    /**
     * Convert centavos to PHP amount
     */
    public function convertToPhp($centavos) {
        return $centavos / 100;
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature) {
        $computed_signature = hash_hmac('sha256', $payload, $this->config['webhook_signature_key']);
        return hash_equals($computed_signature, $signature);
    }
    
    /**
     * Make HTTP Request to PayMongo API
     */
    private function makeRequest($method, $url, $data = null) {
        $curl = curl_init();
        
        $headers = [
            'accept: application/json',
            'authorization: Basic ' . base64_encode($this->secret_key . ':'),
            'content-type: application/json'
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMessage = 'PayMongo API Error: ';
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                $errorMessage .= $decoded['errors'][0]['detail'] ?? 'Unknown error';
            } else {
                $errorMessage .= 'HTTP ' . $httpCode;
            }
            throw new Exception($errorMessage);
        }
        
        return $decoded;
    }
    
    /**
     * Log transaction for debugging
     */
    public function logTransaction($type, $data, $response = null) {
        if ($this->config['test_mode']) {
            $log = [
                'timestamp' => date('Y-m-d H:i:s'),
                'type' => $type,
                'data' => $data,
                'response' => $response
            ];
            error_log('PayMongo Transaction: ' . json_encode($log));
        }
    }
}
?>