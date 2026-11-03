<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** B3b: satu berkas gambar di Media Library. */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['path', 'name', 'mime', 'size', 'width', 'height', 'source', 'folder', 'alt', 'thumb', 'created_by'];

    protected $casts = [
        'alt'    => 'array',
        'size'   => 'integer',
        'width'  => 'integer',
        'height' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isBundled(): bool
    {
        return $this->source === 'bundled';
    }

    public function url(): string
    {
        return asset($this->path);
    }

    /** Thumbnail bila ada, kalau tidak berkas aslinya. */
    public function thumbUrl(): string
    {
        return asset($this->thumb ?: $this->path);
    }

    /** Berkas fisik masih ada? */
    public function exists(): bool
    {
        return is_file(self::absolute($this->path));
    }

    public function altText(string $locale = 'id'): string
    {
        return News::pick($this->alt ?? [], $locale);
    }

    public function hasAlt(): bool
    {
        return trim((string) ($this->alt['id'] ?? '')) !== '';
    }

    /** Path absolut di disk dari path relatif-public. */
    public static function absolute(string $path): string
    {
        return str_starts_with($path, 'storage/')
            ? Storage::disk('public')->path(substr($path, 8))
            : public_path($path);
    }

    public function sizeLabel(): string
    {
        $kb = $this->size / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1, ',', '.') . ' MB' : number_format(max(1, $kb), 0, ',', '.') . ' KB';
    }

    /** Bentuk ringkas untuk pemilih media (JSON). */
    public function toPicker(): array
    {
        return [
            'path'   => $this->path,
            'url'    => $this->url(),
            'thumb'  => $this->thumbUrl(),
            'name'   => $this->name,
            'alt'    => $this->altText('id'),
            'width'  => $this->width,
            'height' => $this->height,
        ];
    }
}
