<?php

$dir = 'database/migrations/tenant';
$files = scandir($dir);

foreach ($files as $file) {
    if (in_array($file, ['.', '..'])) continue;

    $path = $dir . '/' . $file;
    $content = file_get_contents($path);
    
    // Some files are just add_* modifying existing tables, so they don't have Schema::create.
    // We should only insert tenant_id in Schema::create.
    if (strpos($content, 'tenant_id') !== false) continue;

    // Pattern to match Schema::create
    $pattern = '/(Schema::create\(\'[a-z_]+\', function \(Blueprint \$table\) \{)/';
    $replacement = "$1\n            \$table->string('tenant_id')->index();\n            \$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($newContent !== $content) {
        file_put_contents($path, $newContent);
        echo "Updated $file\n";
    }
}
