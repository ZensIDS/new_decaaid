<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Support\Content;
use App\Support\PageContentRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3o (QA2): Menu utama. Admin hanya mengubah label (ID/EN), menyembunyikan item, dan mengurutkan menu tingkat atas.
 * Anak menu (Tentang Kami, Pilar Bisnis, Portofolio) tetap otomatis. Label dikosongkan = label bawaan.
 * Super Admin dan Admin.
 */
class MenuController extends Controller
{
    protected const PAGE = 'menu';

    public function edit(): View
    {
        $row     = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;
        $default = collect(Content::navRaw())->keyBy('route');
        $stored  = $row && is_array($row->content['items'] ?? null) ? $row->content['items'] : [];

        $items = [];

        foreach ($stored as $s) {
            $key = $s['key'] ?? null;

            if ($key && $default->has($key) && ! isset($items[$key])) {
                $items[$key] = ['key' => $key, 'label_id' => (string) ($s['label_id'] ?? ''), 'label_en' => (string) ($s['label_en'] ?? ''), 'visible' => empty($s['hidden'])];
            }
        }

        foreach ($default as $key => $d) {
            $items[$key] ??= ['key' => $key, 'label_id' => '', 'label_en' => '', 'visible' => true];
        }

        $items = array_values(array_map(fn($i) => $i + [
            'default_id' => $default[$i['key']]['label']['id'],
            'default_en' => $default[$i['key']]['label']['en'],
            'children'   => count($default[$i['key']]['children'] ?? []),
        ], $items));

        return view('pages.admin.menus.form', [
            'items'     => $items,
            'row'       => $row,
            'available' => PageContentRepo::available(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['items' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        $keys = collect(Content::navRaw())->pluck('route')->all();

        $d = $request->validate([
            'items'            => ['required', 'array', 'size:'.count($keys)],
            'items.*.key'      => ['required', 'string', 'in:'.implode(',', $keys), 'distinct'],
            'items.*.label_id' => ['nullable', 'string', 'max:40'],
            'items.*.label_en' => ['nullable', 'string', 'max:40'],
            'items.*.visible'  => ['required', 'in:0,1'],
        ], [
            'items.size' => 'Daftar menu tidak lengkap. Muat ulang halaman lalu coba lagi.',
        ], [
            'items.*.label_id' => 'label (ID)',
            'items.*.label_en' => 'label (EN)',
        ]);

        if (collect($d['items'])->where('visible', '1')->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Minimal satu item menu harus tampil.']);
        }

        $content = ['items' => array_values(array_map(fn($i) => [
            'key'      => $i['key'],
            'label_id' => trim((string) ($i['label_id'] ?? '')),
            'label_en' => trim((string) ($i['label_en'] ?? '')),
            'hidden'   => $i['visible'] !== '1',
        ], $d['items']))];

        PageContent::updateOrCreate(
            ['page' => self::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('menus.updated', 'mengubah menu utama');

        return redirect()->route('admin.menus.edit')->with('status', 'Menu disimpan.');
    }

    /** Kembali ke menu bawaan kode. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('menus.reset', 'mengembalikan menu ke bawaan');

        return redirect()->route('admin.menus.edit')->with('status', 'Menu dikembalikan ke bawaan.');
    }
}
