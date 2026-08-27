<?php
echo 'PHP ' . PHP_VERSION . ' is working on Vercel!';
echo ' | Time: ' . date('H:i:s');
echo ' | APP_KEY set: ' . (getenv('APP_KEY') ? 'YES' : 'NO');
echo ' | DB_HOST: ' . (getenv('DB_HOST') ?: 'NOT SET');
