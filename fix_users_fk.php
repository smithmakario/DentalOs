<?php
$files = [
    'database/migrations/tenant/2026_06_11_100003_create_appointments_table.php',
    'database/migrations/tenant/2026_06_11_100004_create_treatment_plans_table.php',
    'database/migrations/tenant/2026_06_13_133837_create_treatment_plan_options_table.php',
    'database/migrations/tenant/2026_06_13_133432_create_patient_documents_table.php',
    'database/migrations/tenant/2026_08_17_000003_create_staff_hr_tables.php',
    'database/migrations/tenant/2026_09_11_071657_create_inventory_tables.php',
];
foreach(scandir('database/migrations/tenant') as $f) {
    if(in_array($f, ['.','..'])) continue;
    $path = 'database/migrations/tenant/'.$f;
    $c = file_get_contents($path);
    $new = str_replace("constrained('users')", "constrained('tenant_users')", $c);
    $new = str_replace("on('users')", "on('tenant_users')", $new);
    if($new !== $c) file_put_contents($path, $new);
}
