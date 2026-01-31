<?php
// processCron.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';


class CronProcessor {
    private $config;
    private $db;
    
    public function __construct() {
        // Load config
        $this->config = require __DIR__ . '/config.php';
        
        // Set timezone
        date_default_timezone_set($this->config['system']['timezone']);
        
        // Get database connection
        $this->db = Database::getInstance()->getConnection();
        
        // Set execution limits
        set_time_limit(0);
        ignore_user_abort(true);
    }
    
    public function processWarmKeyLogs() {
        $code = "process_warmkey";
        $logsProcessed = 0;
		
        try {
            
            // 1. Call WarmKey API
            $logs = $this->queryWarmKeyLogs();
			$logsProcessed = count($logs);
        
			if (empty($logs)) {
				return "No new logs to process.";
			}
			
			// 2. Start transaction
			try {
				$this->db->beginTransaction();
				$transactionStarted = true;
				
				// 3. Process logs
				$this->processLogs($logs);
				
				// 4. Commit
				$this->db->commit();
				
				return "Cron job completed successfully. Processed " . $logsProcessed . " logs.";
				
			} catch (Exception $e) {
				// Handle errors within transaction
				$this->db->rollBack();
				throw $e; // Re-throw to outer catch block
			}
            
		} catch (PDOException $e) {
			return "Database Error: " . $e->getMessage() . " (Processed: " . $logsProcessed . " logs)";
		} catch (Exception $e) {			 
			return "Error: " . $e->getMessage() . " (Processed: " . $logsProcessed . " logs)";
		}
    }
	 
    private function queryWarmKeyLogs() {
        $api_url = $this->config['warmkey']['api']['url'];
        $method = '/paymentV1/queryLog';
        $api_key = $this->config['warmkey']['api']['key'];
        $nonce = (string)round(microtime(true) * 1000);
        
        // Get last log ID
        $stmt = $this->db->query("SELECT log_id FROM wk_query_log ORDER BY log_id DESC LIMIT 1");
        $log_id = $stmt->fetchColumn() ?: 1;
        $log_id++;
        
        $payload = ['log_id' => $log_id];
        $headers = ['api_key' => $api_key, 'nonce' => $nonce];
        
        // Generate signature
        $to_sign = hash('sha256', json_encode(['header' => $headers, 'payload' => $payload]), true);
		$api_secret_key = $this->config['warmkey']['api_secret_key'];
		
        $signature = wkSign($to_sign, $api_secret_key)['signature'];
        $headers["signature"] = $signature;
        
        // Make API call
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
            throw new Exception("API Error: " . curl_error($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
		
		//print_r($result);
        
        if (!isset($result['code']) || $result['code'] != 100) {
            throw new Exception("API returned error: " . ($result['message'] ?? 'Unknown error'));
        }
        
        return $result['result'] ?? [];
    }
    
    private function processLogs($logs) {
        foreach ($logs as $log) {
			
			// Log the processed record
            $this->logQuery($log);
			
            // Process different log types
            switch ($log['log_type']) {

				case 'withdrawal_req_created':
					$this->processWithdrawalCreated($log);
					break;
                case 'deposit_link_created':
                    $this->processDepositLink($log);
                    break;
                    
                case 'deposit_tx':
                    $this->processDepositTransaction($log);
                    break;
                    
                case 'deposit_fundout':
                    $this->processFundOut($log);
                    break;
                    
                case 'withdrawal_status_changed':
                    $this->processWithdrawalStatus($log);
                    break;
            }
            
        }
    }
    
    private function processDepositLink($log) {
		
        $path 	= str_replace('\\','',$log['deposit']['path']);
		$path_arr = explode('/',$path);
		$user_id = (int) $path_arr[1];
		
		$addresses = $log['deposit']['address'] ?? [];
    
		$updateData = [];
		
		foreach ($addresses as $network => $address) {
			$fieldName = 'addr_'.strtolower($network).'_address';
			$updateData[$fieldName] = $address;
		}
		
		// Check if record exists
		$stmt = $this->db->prepare("SELECT addr_id FROM wk_address WHERE addr_relative_path = ? LIMIT 1");
		$stmt->execute([$path]);
		$exists = $stmt->fetchColumn();
		
		if ($exists) {
			// UPDATE
			$this->updateAddressRecord($path, $user_id, $updateData);
		} else {
			// INSERT
			$this->insertAddressRecord($path, $user_id, $updateData);
		}
			
    }
    
	private function updateAddressRecord($path, $user_id, $updateData) {
		$setClauses = [];
		$params = [];
		
		foreach ($updateData as $field => $value) {
			$setClauses[] = "{$field} = IF({$field} = '' OR {$field} IS NULL, ?, {$field})";
			$params[] = $value;
		}
		
		$params[] = date("Y-m-d H:i:s");
		$params[] = $path;
		$params[] = $user_id;
		
		$sql = "UPDATE wk_address 
				SET " . implode(', ', $setClauses) . ", addr_mdate = ?
				WHERE addr_relative_path = ? AND user_id = ?";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	}

	private function insertAddressRecord($path, $user_id, $insertData) {
		
		$insertData['addr_relative_path'] = $path;
		$insertData['user_id'] = $user_id;
		$insertData['addr_cdate'] = date('Y-m-d H:i:s');
		
		$columns = implode(', ', array_keys($insertData));
		$placeholders = ':' . implode(', :', array_keys($insertData));
		
		$sql = "INSERT INTO wk_address ({$columns}) VALUES ({$placeholders})";
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($insertData);
	}

    private function processDepositTransaction($log) {
		
		$path 	= str_replace('\\','',$log['deposit']['path']);
		$path_arr = explode('/',$path);
		$user_id = (int) $path_arr[1];

		$chain		= $log['deposit_tx']['network'];
		$from		= $log['deposit_tx']['sender'];
		$to			= $log['deposit_tx']['recipient'];
		$position	= $log['deposit_tx']['position'];	
		$tx_hash	= $log['deposit_tx']['transaction_hash'];
		$coin		= $log['deposit_tx']['coin_symbol'];
		$amount 	= $log['deposit_tx']['amount'];
		$now 		= date("Y-m-d H:i:s");
		
		$stmt = $this->db->prepare("
			INSERT INTO wk_deposit (bcd_chain, user_id, bcd_sender, bcd_receiver, bcd_amount, bcd_position, bcd_txhash, bcd_cdate, bcd_token)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
		");
		$stmt->execute([$chain, $user_id, $from, $to, $amount, $position, $tx_hash, $now, $coin]);
		
		//Update wk_user coin balance
		if ($user_id) {
			$coin_name = strtolower($coin);
			
			$stmt = $this->db->prepare("UPDATE wk_user SET user_{$coin_name}_balance = user_{$coin_name}_balance + ?, user_mdate = ? WHERE user_id = ?"); 
			
			$stmt->execute([
				$amount,
				$now ,
				$user_id
			]);
		}
    }
    
    private function processFundOut($log) {
		// insert into table if necessary
    }
	
	private function processWithdrawalCreated($log) {
		// process withdrawal request is received by WarmKey
	}
    
    private function processWithdrawalStatus($log) {
        
		$status 	= $log['withdrawal']['status'];
		$mw_id 		= $log['withdrawal']['unique_id'];
		$hash		= $log['withdrawal']['transaction_hash'];
		$position   = $log['withdrawal']['position'];
		$now 		= date("Y-m-d H:i:s");
	
		if($status == 'success') {
			
			$stmt = $this->db->prepare("
				UPDATE wk_withdrawal 
				SET mw_status = ?, mw_txhash = ?, mw_pddate = ?, mw_position = ? 
				WHERE mw_id = ? 
			");
			$stmt->execute([$status, $hash, $now, $position, $mw_id]);
			
		} else {

			$stmt = $this->db->prepare("
				UPDATE wk_withdrawal 
				SET mw_status = ?, mw_remark = ? 
				WHERE mw_id = ? 
			");
			$stmt->execute([$status, $log['withdrawal']['status_message'], $mw_id]);
			
		}
		
    }
    
    private function logQuery($log) {
		
		$now = date("Y-m-d H:i:s");
        $stmt = $this->db->prepare("
            INSERT INTO wk_query_log (log_id, log_type, log_response, log_cdate)
            VALUES (?, ?, ?,  ?)
        ");
        $stmt->execute([
            $log['log_id'],
            $log['log_type'],
            json_encode($log),
			$now
        ]);
    }
}

// Run cron job

$cron = new CronProcessor();
echo $cron->processWarmKeyLogs();

?>