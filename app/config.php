<?php
declare(strict_types=1);

const APP_ENV = 'dev'; // change to 'prod' after verifying
const APP_URL = 'http://tpaa23-001-site1.ntempurl.com/'; // include trailing slash, use http for ntmp URLs

// MySQL (Site4Now)
const DB_HOST = 'MYSQL9001.site4now.net';
const DB_NAME = 'db_abfaa3_tpaa23';
const DB_USER = 'abfaa3_tpaa23';
const DB_PASS = 'Pjant9Ys';
const DB_CHARSET = 'utf8mb4';

date_default_timezone_set('Asia/Manila');

// Absolute filesystem base (wwwroot)
define('BASE_PATH', realpath(__DIR__ . '/..')); // app -> wwwroot

// Centralized uploads dir (filesystem path)
define('UPLOADS_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');