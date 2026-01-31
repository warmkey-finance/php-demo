<?php
// withdraw.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';

class WithdrawProcessor {
    private $config;
    private $db;
    
    public function __construct() {
        $this->config = require __DIR__ . '/config.php';
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createWithdrawalRequest($user_id, $recipient, $amount, $network = 'BNBSMARTCHAIN', $coinSymbol = 'BUSD') {
        $api_url = $this->config['warmkey']['api']['url'];
        $method = '/paymentV1/createWithdrawalRequest';
        $api_key = $this->config['warmkey']['api']['key'];
        $now = date("Y-m-d H:i:s");

		$stmt = $this->db->prepare("
            INSERT INTO wk_withdrawal (
                user_id, mw_recipient, mw_amount, mw_chain, mw_coin_symbol, 
                mw_status, mw_cdate
            ) VALUES ( ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $recipient, $amount, $network, $coinSymbol, 'pending', $now]);
		
		$unique_id = $this->db->lastInsertId();
        
        $nonce = (string)round(microtime(true) * 1000);
        
        $payload = [
            'to' => $recipient,
            'amount' => (string)$amount,
            'coin_symbol' => $coinSymbol,
            'network' => $network,
            'unique_id' => $unique_id,
        ];
		
        $headers = [
            'api_key' => $api_key,
            'nonce' => $nonce
        ];
        
        // Generate signature
        $to_sign = hash('sha256', json_encode(["header" => $headers, "payload" => $payload]), true);
		$api_secret_key = $this->config['warmkey']['api_secret_key'];
        $signature = wkSign($to_sign, $api_secret_key);
        
        if (isset($signature['error'])) {
            throw new Exception("Signature error: " . $signature['error']);
        }
        
        $headers["signature"] = $signature['signature'];
        
        // Prepare the cURL request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url . $method,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode(['header' => $headers, 'payload' => $payload]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error);
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
		
		$now 		= date("Y-m-d H:i:s");
		$stmt = $this->db->prepare("UPDATE wk_withdrawal SET mw_api_response = ?, mw_mdate = ? WHERE mw_id = ? AND user_id = ?");
        $stmt->execute([json_encode($result), $now, $unique_id, $user_id]);
        
        return $result;
    }
    	
}

    try {
        $processor = new WithdrawProcessor();
        $result = $processor->createWithdrawalRequest(
			$_GET['user_id'] ?? 0,
            $_GET['to_address'] ?? '',
            $_GET['amount'] ?? 0,
			$_GET['network'] ?? 'BNBSMARTCHAIN',
            $_GET['coin'] ?? 'BUSD'
        );
        echo json_encode(['success' => true, 'data' => $result]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;

?>