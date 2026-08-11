<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

trait ValidatesStaffBranchAccess
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->boolean('has_global_branch_access')) {
                return;
            }

            $branchIds = $this->input('branch_ids', []);

            if (! is_array($branchIds) || $branchIds === []) {
                $validator->errors()->add(
                    'branch_ids',
                    __('Select at least one branch or enable global access.'),
                );
            }
        });
    }
}
