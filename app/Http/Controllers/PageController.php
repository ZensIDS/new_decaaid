<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return $this->render('home');
    }

    /** Halaman statis biasa (tanpa parameter). */
    public function page(Request $request): View
    {
        return $this->render($this->key($request));
    }

    public function unit(Request $request, string $slug): View
    {
        $unit = Content::unit($slug) ?? abort(404);

        return $this->render($this->key($request), compact('unit'));
    }

    public function project(Request $request, string $slug): View
    {
        // A6: ganti dengan data dummy portofolio
        $project = Content::project($slug);

        return $this->render($this->key($request), compact('project'));
    }

    public function post(Request $request, string $slug): View
    {
        // A7: ganti dengan data dummy berita
        $post = Content::post($slug);

        return $this->render($this->key($request), compact('post'));
    }

    public function category(Request $request, string $slug): View
    {
        // A7: ganti dengan data dummy kategori berita
        $category = Content::category($slug);

        return $this->render($this->key($request), compact('category'));
    }

    public function search(Request $request): View
    {
        $q = Str::limit(trim((string) $request->query('q', '')), 100, '');

        return $this->render($this->key($request), compact('q'));
    }

    /**
     * Key halaman diambil dari nama route: "id.about.profile" => "about.profile".
     */
    protected function key(Request $request): string
    {
        return Str::after($request->route()->getName(), '.');
    }

    /**
     * Render resources/views/pages/{key}.blade.php.
     * Bila belum dibuat, tampilkan halaman placeholder agar routing tetap bisa diuji.
     */
    protected function render(string $key, array $data = []): View
    {
        $data['pageKey'] = $key;
        $view = 'pages.'.$key;

        return view()->exists($view)
            ? view($view, $data)
            : view('pages.placeholder', $data);
    }
}