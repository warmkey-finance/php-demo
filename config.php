<?php
// config.php - Central configuration file
return [
    // Database Configuration
    'database' => [
        'host' => '127.0.0.1:3306',
        'dbname' => 'warmkey-demo', // === CHANGE TO YOUR DB NAME ===
        'username' => 'root', // === CHANGE TO YOUR MYSQL USER ===
        'password' => '', // === CHANGE TO YOUR PASSWORD ===
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ],
    
    // WarmKey API Configuration
    'warmkey' => [
        'api' => [
            'url' => 'https://api.warmkey.finance',
            
            /* === CHANGE YOUR_API_KEY_HERE === */
            'key' => '43n6qpReBN3jZFpNdXSaOX9f0kdSA5'
        ],
        
        /* === CHANGE YOUR_API_SECRET_KEY_HERE === */
		'api_secret_key' => 
<<<EOD
-----BEGIN PRIVATE KEY-----
MIIBVAIBADANBgkqhkiG9w0BAQEFAASCAT4wggE6AgEAAkEAtRWkCmRjWEFh/VJR
/490EuDM5ZMSpN/f5JcT+8yHTgD5UCc4t8n0y2n2ZuI6xjFst4eAb3ygzob+U9QX
V+qgOQIDAQABAkA2bWS6wuWhNzWuoDmJKKLosaykLApkh+2RlV8qRZU9ekfmdlZp
lZxf1qaj/YcVBiIf4xkedb/OY8LeFKOWLroJAiEA4RsJSb/piTOnVB0Sm4QzPYSk
BFnWId+l5m7n/MuF2MMCIQDN7/KlBUnYqhkAMNsmGYG+UCqPovx5u5oKHsm4T+ez
UwIgZczaSHX34Upw08NKFPaWTa3clvMhubPwzOM/Gr3XzA0CIHMsyIsMayGW+EaI
DHjBeTOkCDmvEP9QMbWJRI4lelNrAiEAublObgm4JTOl14n6NJlaqGlDv6qFryEa
1pdKPf6pxYg=
-----END PRIVATE KEY-----
EOD
    ],
   
    // System Configuration
    'system' => [
        'timezone' => 'Asia/Kuala_Lumpur'
    ],

];