<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Specialist;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Статические страницы
        $staticUrls = [
            ['loc' => url('/'),                    'changefreq' => 'daily',   'priority' => '1.0'],
            ['loc' => url('/clinics'),             'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/doctors'),             'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/organizations'),       'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/specialists'),         'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/legal/privacy'),       'changefreq' => 'monthly', 'priority' => '0.3'],
            ['loc' => url('/legal/terms'),         'changefreq' => 'monthly', 'priority' => '0.3'],
            ['loc' => url('/legal/cookies'),       'changefreq' => 'monthly', 'priority' => '0.3'],
        ];

        foreach ($staticUrls as $u) {
            $urls[] = $u;
        }

        // Клиники
        Clinic::whereNotNull('slug')->where('slug', '!=', '')->select('slug', 'updated_at')->chunk(200, function ($items) use (&$urls) {
            foreach ($items as $item) {
                $urls[] = [
                    'loc'        => url('/clinics/' . $item->slug),
                    'lastmod'    => $item->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        });

        // Доктора
        Doctor::whereNotNull('slug')->where('slug', '!=', '')->select('slug', 'updated_at')->chunk(200, function ($items) use (&$urls) {
            foreach ($items as $item) {
                $urls[] = [
                    'loc'        => url('/doctors/' . $item->slug),
                    'lastmod'    => $item->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        });

        // Организации
        Organization::whereNotNull('slug')->where('slug', '!=', '')->select('slug', 'updated_at')->chunk(200, function ($items) use (&$urls) {
            foreach ($items as $item) {
                $urls[] = [
                    'loc'        => url('/organizations/' . $item->slug),
                    'lastmod'    => $item->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        });

        // Специалисты
        Specialist::whereNotNull('slug')->where('slug', '!=', '')->select('slug', 'updated_at')->chunk(200, function ($items) use (&$urls) {
            foreach ($items as $item) {
                $urls[] = [
                    'loc'        => url('/specialists/' . $item->slug),
                    'lastmod'    => $item->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\n\nDisallow: /admin\nDisallow: /owner\nDisallow: /account\nDisallow: /login\nDisallow: /register\nDisallow: /api\n\nSitemap: " . url('/sitemap.xml');

        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}