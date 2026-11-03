<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** B3j: isi halaman tunggal (JSON). Lihat App\Support\PageContentRepo. */
class PageContent extends Model
{
    protected $fillable = ['page', 'content', 'updated_by'];

    protected $casts = ['content' => 'array'];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
