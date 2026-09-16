<?php
$content = file_get_contents('database/migrations/tenant/0001_01_01_000000_create_users_table.php');
$content = str_replace("Schema::create('users'", "Schema::create('tenant_users'", $content);
$content = preg_replace("/Schema::create\('password_reset_tokens'.*?\}\);/s", "", $content);
$content = preg_replace("/Schema::create\('sessions'.*?\}\);/s", "", $content);
$content = preg_replace("/Schema::dropIfExists\('sessions'\);/s", "", $content);
$content = preg_replace("/Schema::dropIfExists\('password_reset_tokens'\);/s", "", $content);
$content = str_replace("Schema::dropIfExists('users')", "Schema::dropIfExists('tenant_users')", $content);
file_put_contents('database/migrations/tenant/0001_01_01_000000_create_users_table.php', $content);
