<?php
namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\Content;
use App\Support\Turnstile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * B4: pengiriman formulir Kontak (POST, dipanggil lewat fetch). Anti-spam: honeypot + batas kirim
 * (throttle di rute) + Turnstile opsional (App\Support\Turnstile). Pesan disimpan ke tabel contact_messages, lalu email notifikasi ke alamat umum
 * (info@decaa.id, K31) dan balasan otomatis ke pengirim. Kegagalan email TIDAK menggagalkan pengiriman:
 * pesan tetap tersimpan di kotak masuk admin.
 */
class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $page = Content::contactPage();

        // Honeypot: bot mengisi kolom tersembunyi. Pura-pura sukses agar bot tidak belajar.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        // Turnstile (opsional, aktif bila kunci diisi di .env): token dari widget di form diverifikasi ke Cloudflare.
        if (Turnstile::enabled() && ! Turnstile::passes($request->input('cf-turnstile-response'), $request->ip())) {
            return response()->json(['ok' => false, 'message' => $page['err_captcha'], 'captcha' => true], 422);
        }

        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email:rfc', 'max:190'],
            'phone'   => ['nullable', 'string', 'max:40', 'regex:/^[0-9+\-\s().]*$/'],
            'subject' => ['required', 'in:' . implode(',', array_keys($page['subjects']))],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->keys() as $key) {
                $errors[$key] = in_array($key, ['email'], true) && filled($request->input('email'))
                    ? $page['err_email'] : $page['err_required'];
            }

            return response()->json(['ok' => false, 'message' => $page['err_check'], 'errors' => $errors], 422);
        }

        try {
            if (! Schema::hasTable('contact_messages')) {
                return response()->json(['ok' => false, 'message' => $page['err_send']], 503);
            }
        } catch (Throwable) {
            return response()->json(['ok' => false, 'message' => $page['err_send']], 503);
        }

        $data = $validator->validated();
        $line = fn(string $v) => trim(preg_replace('/[\r\n\t]+/', ' ', $v));

        $message = ContactMessage::create([
            'name'       => $line($data['name']),
            'email'      => $line($data['email']),
            'phone'      => filled($data['phone'] ?? null) ? $line($data['phone']) : null,
            'subject'    => $data['subject'],
            'message'    => trim($data['message']),
            'locale'     => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        $message->notified = $this->notify($message, $page['subjects'][$data['subject']] ?? $data['subject']);
        $message->save();

        $this->autoReply($message);

        return response()->json(['ok' => true]);
    }

    /** Email ke tim (satu-satunya akun email, K31). Mengembalikan true bila terkirim. */
    protected function notify(ContactMessage $m, string $subjectLabel): bool
    {
        $to = Content::company()['email']['general'];

        $body = "Pesan baru dari formulir Kontak DECAA.ID\n\n"
            . "Nama    : {$m->name}\n"
            . "Email   : {$m->email}\n"
            . 'Telepon : ' . ($m->phone ?: '-') . "\n"
            . "Subjek  : {$subjectLabel}\n"
            . 'Bahasa  : ' . strtoupper($m->locale) . "\n"
            . 'Waktu   : ' . $m->created_at->format('d-m-Y H:i') . "\n\n"
            . "Pesan:\n{$m->message}\n\n"
            . 'Balas langsung email ini (Reply-To sudah diatur ke pengirim) atau buka kotak masuk di panel admin.';

        try {
            Mail::raw($body, function ($mail) use ($to, $m, $subjectLabel) {
                $mail->to($to)
                    ->replyTo($m->email, $m->name)
                    ->subject("[Kontak DECAA.ID] {$subjectLabel} - {$m->name}");
            });

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /** Balasan otomatis ke pengirim (K31). Gagal = diabaikan. */
    protected function autoReply(ContactMessage $m): void
    {
        $id = $m->locale !== 'en';

        $subject = $id ? 'Pesan Anda sudah kami terima | DECAA.ID' : 'We have received your message | DECAA.ID';
        $body = $id
            ? "Halo {$m->name},\n\nTerima kasih sudah menghubungi DECAA.ID. Pesan Anda sudah kami terima dan tim kami akan menanggapi secepatnya.\n\nIni salinan pesan Anda:\n\n{$m->message}\n\nSalam hangat,\nDECAA.ID\n\n(Email ini dikirim otomatis. Silakan balas bila ada tambahan informasi.)"
            : "Hello {$m->name},\n\nThank you for contacting DECAA.ID. We have received your message and our team will respond as soon as possible.\n\nHere is a copy of your message:\n\n{$m->message}\n\nBest regards,\nDECAA.ID\n\n(This email was sent automatically. Feel free to reply if you have more information.)";

        try {
            Mail::raw($body, function ($mail) use ($m, $subject) {
                $mail->to($m->email, $m->name)
                    ->replyTo(Content::company()['email']['general'])
                    ->subject($subject);
            });
        } catch (Throwable $e) {
            report($e);
        }
    }
}
