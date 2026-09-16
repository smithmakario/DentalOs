<?php
$models = [
    'Appointment', 'BranchProfile', 'ClinicService', 'DentalProcedure',
    'InventoryBatch', 'InventoryCategory', 'InventoryItem', 'InventoryTransaction',
    'InvoiceItem', 'Invoice', 'PatientAllergy', 'PatientDocument', 'PatientLabResult',
    'Patient', 'PatientVital', 'Payment', 'StaffAttendance', 'StaffLeaveRequest',
    'StaffPerformanceReview', 'Treatment', 'TreatmentPlanItem', 'TreatmentPlanOption',
    'TreatmentPlan', 'Staff'
];

foreach ($models as $model) {
    $path = "app/Models/{$model}.php";
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    if (strpos($content, 'use Stancl\Tenancy\Database\Concerns\BelongsToTenant;') !== false) {
        continue;
    }

    // Add import statement
    $content = preg_replace('/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)/', "$1\nuse Stancl\Tenancy\Database\Concerns\BelongsToTenant;", $content);
    // Or if not found, put it after namespace
    if (strpos($content, 'Stancl\Tenancy\Database\Concerns\BelongsToTenant;') === false) {
        $content = preg_replace('/(namespace App\\\\Models;)/', "$1\n\nuse Stancl\Tenancy\Database\Concerns\BelongsToTenant;", $content);
    }
    
    // Add use BelongsToTenant; inside class
    $content = preg_replace('/(class [a-zA-Z_]+ extends [a-zA-Z_]+(?: implements [a-zA-Z_]+)?\n\{)/', "$1\n    use BelongsToTenant;", $content);
    
    file_put_contents($path, $content);
    echo "Updated $model\n";
}
