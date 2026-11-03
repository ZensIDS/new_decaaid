<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\UrlRedirect;
use App\Support\RedirectRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3n: Redirect 301/302. Daftar + form + impor banyak. Super Admin dan Admin.
 * Aturan hanya berlaku bila alamat asal tidak ditemukan (404), jadi tidak menimpa halaman yang masih ada.
 */
class RedirectController extends Controller
{
    protected const PER_PAGE = 20;
    protected const IMPORT_MAX = 500;

    public function index(Request $request): View
    {
        $q      = trim((string) $request->query('q'));
        $status = $request->query('status');
        $items  = null;

        if (RedirectRepo::available()) {
            $items = UrlRedirect::query()
                ->when($q !== '', function ($query) use ($q) {
                    $like = '%' . addcslashes($q, '%_\\') . '%';
                    $query->where(fn($w) => $w->where('from_path', 'like', $like)->orWhere('to_path', 'like', $like)->orWhere('note', 'like', $like));
                })
                ->when(in_array($status, ['active', 'off'], true), fn($query) => $query->where('is_active', $status === 'active'))
                ->orderByDesc('updated_at')->orderByDesc('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        return view('pages.admin.redirects.index', [
            'items'     => $items,
            'available' => RedirectRepo::available(),
            'q'         => $q,
            'status'    => $status,
            'filtered'  => $q !== '' || in_array($status, ['active', 'off'], true),
            'noindex'   => (bool) config('site.noindex'),
        ]);
    }

    public function create(): View
    {
        $this->ensureTable();

        return view('pages.admin.redirects.form', ['rule' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureTable();

        $data = $this->validated($request, null);

        $rule = new UrlRedirect($data);
        $rule->created_by = $request->user()->id;
        $rule->updated_by = $request->user()->id;
        $rule->save();

        ActivityLog::record('redirect.created', "menambah redirect {$rule->from_path} ke {$rule->to_path}", $rule);

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect ditambahkan.');
    }

    public function edit(UrlRedirect $redirect): View
    {
        $this->ensureTable();

        return view('pages.admin.redirects.form', ['rule' => $redirect]);
    }

    public function update(Request $request, UrlRedirect $redirect): RedirectResponse
    {
        $data = $this->validated($request, $redirect);

        $redirect->fill($data);
        $redirect->updated_by = $request->user()->id;
        $redirect->save();

        ActivityLog::record('redirect.updated', "mengubah redirect {$redirect->from_path}", $redirect);

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect disimpan.');
    }

    public function toggle(UrlRedirect $redirect): RedirectResponse
    {
        $redirect->is_active = ! $redirect->is_active;
        $redirect->updated_by = auth()->id();
        $redirect->save();

        ActivityLog::record('redirect.toggled', ($redirect->is_active ? 'mengaktifkan' : 'menonaktifkan') . " redirect {$redirect->from_path}", $redirect);

        return back()->with('status', $redirect->is_active ? 'Redirect diaktifkan.' : 'Redirect dinonaktifkan.');
    }

    public function destroy(UrlRedirect $redirect): RedirectResponse
    {
        $from = $redirect->from_path;
        $redirect->delete();

        ActivityLog::record('redirect.deleted', "menghapus redirect {$from}");

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect dihapus.');
    }

    public function importForm(): View
    {
        $this->ensureTable();

        return view('pages.admin.redirects.import', ['max' => self::IMPORT_MAX]);
    }

    /**
     * Impor banyak: satu baris satu aturan, "alamat-lama alamat-baru" dipisah spasi, tab, koma, atau "->".
     * Aturan yang asalnya sudah ada diperbarui; baris tidak sah dilewati dan dilaporkan.
     */
    public function importStore(Request $request): RedirectResponse
    {
        $this->ensureTable();

        $request->validate([
            'lines'       => ['required', 'string', 'max:200000'],
            'status_code' => ['required', 'in:301,302'],
        ], [], ['lines' => 'daftar redirect', 'status_code' => 'jenis redirect']);

        $rows = preg_split('/\R/u', (string) $request->input('lines')) ?: [];
        $rows = array_values(array_filter(array_map('trim', $rows), fn($r) => $r !== '' && ! str_starts_with($r, '#')));

        if (count($rows) > self::IMPORT_MAX) {
            throw ValidationException::withMessages(['lines' => 'Maksimal ' . self::IMPORT_MAX . ' baris per impor (ada ' . count($rows) . ').']);
        }

        $created = $updated = 0;
        $skipped = [];
        $userId  = $request->user()->id;

        foreach ($rows as $i => $row) {
            $n     = $i + 1;
            $parts = preg_split('/\s*(?:->|=>|,|\t)\s*|\s+/u', $row, 2);

            if (count($parts) < 2) {
                $skipped[] = "baris $n: harus berisi alamat lama dan alamat baru";
                continue;
            }

            $from = UrlRedirect::normalizeFrom($parts[0]);
            $to   = UrlRedirect::normalizeTo($parts[1]);
            $why  = $this->problem($from, $to);

            if ($why) {
                $skipped[] = "baris $n: $why";
                continue;
            }

            $rule = UrlRedirect::where('from_path', $from)->first();

            if ($rule) {
                $rule->fill(['to_path' => $to, 'status_code' => (int) $request->input('status_code'), 'is_active' => true]);
                $rule->updated_by = $userId;
                $rule->save();
                $updated++;
            } else {
                $rule = new UrlRedirect(['from_path' => $from, 'to_path' => $to, 'status_code' => (int) $request->input('status_code'), 'is_active' => true]);
                $rule->created_by = $rule->updated_by = $userId;
                $rule->save();
                $created++;
            }
        }

        ActivityLog::record('redirect.imported', "mengimpor redirect: $created baru, $updated diperbarui, " . count($skipped) . ' dilewati');

        $redirect = redirect()->route('admin.redirects.index')
            ->with('status', "Impor selesai: $created baru, $updated diperbarui, " . count($skipped) . ' dilewati.');

        return $skipped ? $redirect->with('import_skipped', array_slice($skipped, 0, 30)) : $redirect;
    }

    protected function validated(Request $request, ?UrlRedirect $current): array
    {
        $d = $request->validate([
            'from_path'   => ['required', 'string', 'max:300'],
            'to_path'     => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'note'        => ['nullable', 'string', 'max:200'],
        ], [
            'from_path.required' => 'Alamat lama wajib diisi.',
            'to_path.required'   => 'Alamat tujuan wajib diisi.',
        ], ['from_path' => 'alamat lama', 'to_path' => 'alamat tujuan', 'status_code' => 'jenis redirect', 'note' => 'catatan']);

        $from = UrlRedirect::normalizeFrom($d['from_path']);
        $to   = UrlRedirect::normalizeTo($d['to_path']);

        if ($why = $this->problem($from, $to, $current?->id, $d['from_path'], $d['to_path'])) {
            throw ValidationException::withMessages([$why[0] === 'from' ? 'from_path' : 'to_path' => $why[1]]);
        }

        return [
            'from_path'   => $from,
            'to_path'     => $to,
            'status_code' => (int) $d['status_code'],
            'is_active'   => $request->boolean('is_active'),
            'note'        => trim((string) ($d['note'] ?? '')) ?: null,
        ];
    }

    /**
     * Pemeriksaan satu pasangan. Mengembalikan null bila sah. Untuk impor (tanpa argumen mentah) berupa teks;
     * untuk form berupa [kolom, pesan].
     */
    protected function problem(string $from, string $to, ?int $ignoreId = null, ?string $rawFrom = null, ?string $rawTo = null): string|array|null
    {
        $form = $rawFrom !== null;
        $err  = fn(string $col, string $msg) => $form ? [$col, $msg] : $msg;

        if ($from === '') {
            return $err('from', 'Alamat lama tidak sah (beranda tidak bisa dialihkan). Contoh: /2024/05/judul-lama atau https://decaa.id/judul-lama/');
        }

        if (UrlRedirect::isBlocked($from)) {
            return $err('from', 'Alamat lama termasuk bagian sistem (admin, storage, sitemap, dst.) dan tidak bisa dialihkan.');
        }

        if ($to === '') {
            return $err('to', 'Alamat tujuan harus diawali "/" (halaman di situs ini, mis. /id/berita/judul-baru) atau https:// (situs lain).');
        }

        if (str_starts_with($to, '/') && UrlRedirect::normalizeFrom($to) === $from) {
            return $err('to', 'Alamat tujuan sama dengan alamat lama (akan berputar).');
        }

        if ($ignoreId !== null || $form) {
            $dup = UrlRedirect::where('from_path', $from)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists();

            if ($dup) {
                return $err('from', 'Alamat lama ini sudah punya aturan redirect. Ubah aturan yang ada.');
            }
        }

        return null;
    }

    protected function ensureTable(): void
    {
        if (! RedirectRepo::available()) {
            throw ValidationException::withMessages(['from_path' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }
    }
}
