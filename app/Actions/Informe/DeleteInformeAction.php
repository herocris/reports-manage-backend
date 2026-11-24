<?php

namespace App\Actions\Informe;

use App\Models\Informe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeleteInformeAction
{
    public function execute(Informe $informe): void
    {
        DB::transaction(function () use ($informe): void {
            $informe = Informe::query()->lockForUpdate()->findOrFail($informe->id);
            $batchId = (string) Str::uuid();
            $informe->forceFill(['deletion_batch_id' => $batchId])->saveQuietly();

            foreach (Informe::CASCADED_RELATIONS as $relation) {
                $informe->{$relation}()->lockForUpdate()->chunkById(100, function (Collection $children) use ($batchId): void {
                    foreach ($children as $child) {
                        $child->forceFill(['deletion_batch_id' => $batchId])->saveQuietly();
                        $child->delete();
                    }
                });
            }

            $informe->delete();
        }, attempts: 3);
    }
}
