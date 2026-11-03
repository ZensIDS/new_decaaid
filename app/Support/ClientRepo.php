<?php
namespace App\Support;

use App\Models\Client;
use App\Models\ClientCategory;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3h: sumber data Klien & Mitra untuk halaman publik. Mengembalikan array dengan BENTUK YANG SAMA
 * seperti data lama di Content (name, category, logo), sehingga view tidak ditulis ulang.
 *
 * Dipakai hanya bila tabel ada DAN sudah berisi klien (available()); bila belum, Content memakai
 * logo bawaan di public/img/clients agar situs tidak kosong sebelum `content:import-clients` dijalankan.
 */
class ClientRepo
{
    protected static ?bool $available = null;

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('clients') && Schema::hasTable('client_categories') && Client::query()->exists();
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Kategori yang punya klien tampil: slug => nama (urutan admin). */
    public static function categories(): array
    {
        $used = Client::query()->visible()->pluck('client_category_id')->unique();

        return ClientCategory::query()->whereIn('id', $used)->orderBy('sort_order')->orderBy('id')->get()
            ->mapWithKeys(fn(ClientCategory $c) => [$c->slug => $c->label()])
            ->all();
    }

    /**
     * Semua klien tampil. Urutan selang-seling antar kategori (agar strip beranda beragam);
     * urutan relatif di dalam tiap kategori tetap sesuai urutan admin.
     */
    public static function all(?int $limit = null): array
    {
        $items = self::interleave(Client::query()->visible()->with('category')->get());

        return $limit ? array_slice($items, 0, $limit) : $items;
    }

    /**
     * Strip logo beranda: klien yang dicentang "Tampil di beranda". Bila tidak ada yang dicentang,
     * dipakai logo terdepan (selang-seling) agar bagian ini tidak kosong.
     */
    public static function home(int $limit = 24): array
    {
        $picked = Client::query()->visible()->where('show_on_home', true)->with('category')->get();

        return $picked->isEmpty() ? self::all($limit) : array_slice(self::interleave($picked), 0, $limit);
    }

    /** @param \Illuminate\Support\Collection<int, Client> $clients */
    protected static function interleave($clients): array
    {
        $order = ClientCategory::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->flip();

        // Posisi dalam kategori (1, 2, 3, ...) dipakai untuk nama cadangan dan untuk selang-seling.
        $groups = $clients->sortBy(fn(Client $c) => [$order[$c->client_category_id] ?? 999, $c->sort_order, $c->id])
            ->groupBy('client_category_id');

        $rows = [];
        foreach ($groups as $group) {
            foreach ($group->values() as $i => $client) {
                $rows[] = [$i, $order[$client->client_category_id] ?? 999, $client];
            }
        }

        usort($rows, fn($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        return array_map(function (array $row) {
            [$i, , $client] = $row;
            $label = $client->category?->label() ?? '';

            return [
                'name'     => filled($client->name) ? $client->name : trim($label . ' ' . ($i + 1)),
                'category' => $client->category?->slug ?? '',
                'logo'     => $client->logo,
            ];
        }, $rows);
    }
}
