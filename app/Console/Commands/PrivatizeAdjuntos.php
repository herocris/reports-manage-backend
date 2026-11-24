<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use RuntimeException;
use Throwable;

#[Signature('adjuntos:privatize')]
#[Description('Move legacy public attachments to private storage after verifying their contents')]
class PrivatizeAdjuntos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('attachments');
        $moved = 0;

        try {
            $files = $public->getDriver()->listContents('adjuntos', true)
                ->filter(fn (StorageAttributes $entry): bool => $entry instanceof FileAttributes);

            foreach ($files as $file) {
                $path = $file->path();

                if (! $private->exists($path)) {
                    $stream = $public->readStream($path);

                    if (! is_resource($stream)) {
                        throw new RuntimeException('Unable to read '.$path);
                    }

                    try {
                        if (! $private->writeStream($path, $stream, ['visibility' => 'private'])) {
                            throw new RuntimeException('Unable to copy '.$path);
                        }
                    } finally {
                        fclose($stream);
                    }
                }

                if ($public->checksum($path) !== $private->checksum($path)) {
                    throw new RuntimeException('Contents differ; the public copy was retained: '.$path);
                }

                if (! $public->delete($path)) {
                    throw new RuntimeException('Unable to remove the verified public copy: '.$path);
                }

                $moved++;
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Moved '.$moved.' attachment(s) to private storage.');

        return self::SUCCESS;
    }
}
