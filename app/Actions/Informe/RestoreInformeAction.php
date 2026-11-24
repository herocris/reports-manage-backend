<?php

namespace App\Actions\Informe;

use App\Models\Informe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestoreInformeAction
{
    public function execute(Informe $informe): void
    {
        DB::transaction(function () use ($informe): void {
            $informe = Informe::query()->withTrashed()->lockForUpdate()->findOrFail($informe->id);

            if (! $informe->trashed()) {
                return;
            }

            $batchId = $informe->deletion_batch_id;
            $informe->restore();

            if ($batchId === null) {
                return;
            }

            foreach (Informe::CASCADED_RELATIONS as $relation) {
                $informe->{$relation}()
                    ->onlyTrashed()
                    ->where('deletion_batch_id', $batchId)
                    ->lockForUpdate()
                    ->chunkById(100, function (Collection $children): void {
                        foreach ($children as $child) {
                            $child->restore();
                        }
                    });
            }
        }, attempts: 3);
    }
}
