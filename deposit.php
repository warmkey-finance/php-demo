<?php
// deposit.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';

class DepositProcessor {
    private $config;
    private $db;
    
    public function __construct() {
        $this->config = require __DIR__ . '/config.php';
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createPaymentInterface($user_id) {
        $api_key = $this->config['warmkey']['api']['key'];
        $account = md5($api_key);
        $api_secret_key = $this->config['warmkey']['api_secret_key'];
        
        $html = '';
        
		$path = "0/{$user_id}";
		
		$payload = [
			"account" => $account,
			"path" => $path
		];
		
		$to_sign = hash('sha256', json_encode($payload), true);
		$signature = wkSign($to_sign, $api_secret_key)['signature'];
		
		$url = $this->config['warmkey']['api']['url'] . "/paymentV1/interface/?";
		$params = [
			"account" => $account,
			"path" => $path,
			"signature" => $signature
		];
		
		$link = $url . http_build_query($params);
		
		$html .= "<iframe src='{$link}' style='width:500px;height:500px;float:left'></iframe>";
        
        return $html;
    }
    
}

$processor = new DepositProcessor();
$user_id = $_GET['user_id'] ?? 1;
echo $processor->createPaymentInterface($user_id);

?>