<?php

namespace Tests\Feature\Console;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PrivatizeAdjuntosTest extends TestCase
{
    public function test_legacy_attachments_are_moved_without_changing_paths_or_contents(): void
    {
        Storage::fake('public');
        Storage::fake('attachments');
        Storage::disk('public')->put('adjuntos/informe/evidence.jpg', 'evidence');
        Storage::disk('public')->put('avatars/photo.jpg', 'avatar');

        $this->artisan('adjuntos:privatize')->assertSuccessful();

        Storage::disk('public')->assertMissing('adjuntos/informe/evidence.jpg');
        Storage::disk('public')->assertExists('avatars/photo.jpg');
        Storage::disk('attachments')->assertExists('adjuntos/informe/evidence.jpg');
        $this->assertSame('evidence', Storage::disk('attachments')->get('adjuntos/informe/evidence.jpg'));

        $this->artisan('adjuntos:privatize')->expectsOutput('Moved 0 attachment(s) to private storage.')
            ->assertSuccessful();
    }

    public function test_matching_private_copies_allow_a_safe_restart(): void
    {
        Storage::fake('public');
        Storage::fake('attachments');
        $path = 'adjuntos/persona/photo.jpg';
        Storage::disk('public')->put($path, 'same contents');
        Storage::disk('attachments')->put($path, 'same contents');

        $this->artisan('adjuntos:privatize')->assertSuccessful();

        Storage::disk('public')->assertMissing($path);
        $this->assertSame('same contents', Storage::disk('attachments')->get($path));
    }

    public function test_conflicting_copies_are_both_preserved(): void
    {
        Storage::fake('public');
        Storage::fake('attachments');
        $path = 'adjuntos/bien/photo.jpg';
        Storage::disk('public')->put($path, 'original');
        Storage::disk('attachments')->put($path, 'different');

        $this->artisan('adjuntos:privatize')->assertFailed();

        $this->assertSame('original', Storage::disk('public')->get($path));
        $this->assertSame('different', Storage::disk('attachments')->get($path));
    }

    public function test_failed_writes_do_not_remove_public_files(): void
    {
        Storage::fake('public');
        $path = 'adjuntos/informe/photo.jpg';
        Storage::disk('public')->put($path, 'original');
        $private = Mockery::mock(FilesystemAdapter::class);
        $private->shouldReceive('exists')->with($path)->once()->andReturnFalse();
        $private->shouldReceive('writeStream')->once()->andReturnFalse();
        Storage::set('attachments', $private);

        $this->artisan('adjuntos:privatize')->assertFailed();

        $this->assertSame('original', Storage::disk('public')->get($path));
    }
}
