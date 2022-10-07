<?php
require 'vendor/autoload.php';

use Src\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$conn = (new Database())->db_connect();
