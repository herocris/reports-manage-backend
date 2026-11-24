<?php

namespace App\Actions\Adjunto;

use App\Models\Adjunto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StoreAdjuntosAction
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, Adjunto>
     */
    public function execute(Model $owner, string $type, array $files): Collection
    {
        $paths = [];

        try {
            return DB::transaction(function () use ($owner, $type, $files, &$paths): Collection {
                return collect($files)->map(function (UploadedFile $file) use ($owner, $type, &$paths): Adjunto {
                    $path = $file->store('adjuntos/'.$type, 'attachments');

                    if ($path === false) {
                        throw new RuntimeException('Unable to store attachment.');
                    }

                    $paths[] = $path;

                    return $owner->adjuntos()->create([
                        'url' => $path,
                        'descripcion' => $file->getClientOriginalName(),
                    ]);
                });
            });
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                rescue(fn (): bool => Storage::disk('attachments')->delete($path));
            }

            throw $exception;
        }
    }
}
