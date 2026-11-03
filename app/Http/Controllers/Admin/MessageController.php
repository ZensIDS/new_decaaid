<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Support\Content;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * B3l: Kotak Masuk pesan dari formulir Kontak. Super Admin dan Admin.
 * Daftar + cari/filter, detail (otomatis ditandai dibaca), tandai belum dibaca, hapus, ekspor CSV.
 * Balas lewat email (tautan mailto ke pengirim). Subjek tetap di kode (Content::contactPage()).
 */
class MessageController extends Controller
{
    protected const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $items = null;
        if ($this->available()) {
            $items = $this->query($filters)->latest('id')->paginate(self::PER_PAGE)->withQueryString();
        }

        return view('pages.admin.messages.index', $filters + [
            'items'     => $items,
            'available' => $this->available(),
            'subjects'  => $this->subjects(),
            'unread'    => $this->available() ? ContactMessage::unread()->count() : 0,
            'filtered'  => $filters['q'] !== '' || $filters['status'] || $filters['subject'],
        ]);
    }

    public function show(Request $request, ContactMessage $message): View
    {
        if (! $message->is_read) {
            $message->forceFill(['is_read' => true, 'read_at' => now(), 'read_by' => $request->user()->id])->save();
        }

        return view('pages.admin.messages.show', [
            'message'  => $message->load('reader'),
            'subjects' => $this->subjects(),
        ]);
    }

    /** Tandai belum dibaca (atau sebaliknya). */
    public function toggle(Request $request, ContactMessage $message): RedirectResponse
    {
        $read = ! $message->is_read;

        $message->forceFill([
            'is_read' => $read,
            'read_at' => $read ? now() : null,
            'read_by' => $read ? $request->user()->id : null,
        ])->save();

        return $read
            ? back()->with('status', 'Pesan ditandai sudah dibaca.')
            : redirect()->route('admin.messages.index')->with('status', 'Pesan ditandai belum dibaca.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $from = $message->name;
        $message->delete();

        ActivityLog::record('message.deleted', "menghapus pesan Kontak dari \"{$from}\"");

        return redirect()->route('admin.messages.index')->with('status', "Pesan dari \"{$from}\" dihapus.");
    }

    /** Ekspor CSV (mengikuti cari/filter yang aktif). */
    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        if (! $this->available()) {
            return redirect()->route('admin.messages.index');
        }

        $filters  = $this->filters($request);
        $subjects = $this->subjects();
        $name     = 'pesan-kontak-' . now()->format('Ymd-His') . '.csv';

        ActivityLog::record('message.exported', 'mengekspor pesan Kontak ke CSV');

        return response()->streamDownload(function () use ($filters, $subjects) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Diterima', 'Nama', 'Email', 'Telepon', 'Subjek', 'Bahasa', 'Status', 'Pesan']);

            // Cegah injeksi rumus di Excel: sel berawalan = + - @ diberi tanda kutip.
            $safe = function ($v) {
                $v = (string) $v;

                return $v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $v : $v;
            };

            $this->query($filters)->orderBy('id')->chunk(200, function ($rows) use ($out, $subjects, $safe) {
                foreach ($rows as $m) {
                    fputcsv($out, array_map($safe, [
                        $m->created_at->format('Y-m-d H:i'), $m->name, $m->email, $m->phone,
                        $subjects[$m->subject] ?? $m->subject, strtoupper($m->locale),
                        $m->is_read ? 'Dibaca' : 'Baru', $m->message,
                    ]));
                }
            });

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ------------------------------------------------------------------ */

    protected function available(): bool
    {
        try {
            return Schema::hasTable('contact_messages');
        } catch (Throwable) {
            return false;
        }
    }

    /** Label subjek (Bahasa Indonesia, kunci => label). */
    protected function subjects(): array
    {
        return Content::contactPage()['subjects'];
    }

    protected function filters(Request $request): array
    {
        $status  = $request->query('status');
        $subject = $request->query('subject');

        return [
            'q'       => trim((string) $request->query('q')),
            'status'  => in_array($status, ['new', 'read'], true) ? $status : null,
            'subject' => array_key_exists((string) $subject, $this->subjects()) ? $subject : null,
        ];
    }

    protected function query(array $f): Builder
    {
        return ContactMessage::query()
            ->when($f['q'] !== '', function (Builder $query) use ($f) {
                $like = '%' . addcslashes($f['q'], '%_\\') . '%';
                $query->where(fn(Builder $w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('message', 'like', $like));
            })
            ->when($f['status'], fn(Builder $query) => $query->where('is_read', $f['status'] === 'read'))
            ->when($f['subject'], fn(Builder $query) => $query->where('subject', $f['subject']));
    }
}
