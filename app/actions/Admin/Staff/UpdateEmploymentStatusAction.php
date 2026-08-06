<?php

namespace App\Actions\Admin\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateEmploymentStatusAction
{
    public function execute(User $staff, array $data): User
    {
        return DB::transaction(function () use ($staff, $data) {

            $staff->staffProfile()->updateOrCreate(
                ['user_id' => $staff->id],
                [
                    'employment_status' => $data['employment_status'],
                ]
            );

            return $staff->fresh();
        });
    }
}
