<?php
// public/index.php

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$app = require __DIR__ . '/../bootstrap/app.php';

// Handle the request and send response
$app->handleRequest();
