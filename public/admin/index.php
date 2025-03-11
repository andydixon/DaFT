<?php

use DaFT\Config\ConfigModel;
use DaFT\Controller\ConfigController;

require __DIR__ . '/../../vendor/autoload.php';

// If "public" is your document root and config.ini is next to "public/":
$configFilePath = __DIR__ . '/../../config.ini';

try {
    $model = new ConfigModel($configFilePath);
    $controller = new ConfigController($model);
    $controller->handleRequest();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
