<?php

namespace App\Enums;

enum PatientDocumentCategory: string
{
    case LabResult = 'lab_result';
    case Xray = 'xray';
    case Imaging = 'imaging';
    case Report = 'report';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LabResult => __('Lab Result'),
            self::Xray => __('X-Ray'),
            self::Imaging => __('Imaging'),
            self::Report => __('Clinical Report'),
            self::Other => __('Other'),
        };
    }
}
