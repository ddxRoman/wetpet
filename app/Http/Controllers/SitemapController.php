<?php

namespace App\Http\Controllers;

use App\Models\Ad;
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
        // ВНИМАНИЕ: страницы /legal/privacy, /legal/terms, /legal/cookies и другие
        // юридические страницы намеренно НЕ включаются — они закрыты от индексации
        // в robots.txt (см. SitemapController::robots()).
        $staticUrls = [
            ['loc' => url('/'),                    'changefreq' => 'daily',   'priority' => '1.0'],
            ['loc' => url('/clinics'),             'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/doctors'),             'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/organizations'),       'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/specialists'),         'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => url('/ads'),                 'changefreq' => 'daily',   'priority' => '0.8'],
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

        // Объявления
        Ad::where('is_active', true)->select('id', 'updated_at')->chunk(200, function ($items) use (&$urls) {
            foreach ($items as $item) {
                $urls[] = [
                    'loc'        => url('/ads/' . $item->id),
                    'lastmod'    => $item->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.6',
                ];
            }
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        // ВНИМАНИЕ: физический файл public/robots.txt обслуживается веб-сервером
        // напрямую и имеет приоритет над этим роутом — держите их синхронными.
        $content = "User-agent: *\n"
            . "Allow: /\n\n"
            . "Disallow: /admin\n"
            . "Disallow: /admin/\n"
            . "Disallow: /owner\n"
            . "Disallow: /account\n"
            . "Disallow: /login\n"
            . "Disallow: /register\n"
            . "Disallow: /forgot-password\n"
            . "Disallow: /reset-password\n"
            . "Disallow: /api/\n"
            . "Disallow: /cabinet\n\n"
            . "Disallow: /legal/privacy\n"
            . "Disallow: /legal/terms\n"
            . "Disallow: /legal/personal-data-agreement\n"
            . "Disallow: /legal/partner-offer\n"
            . "Disallow: /legal/cookies\n"
            . "Disallow: /legal/content-rules\n"
            . "Disallow: /legal/glossary\n"
            . "Disallow: /legal/contacts\n\n"
            . "User-agent: TelegramBot\n"
            . "Allow: /\n\n"
            . "Sitemap: " . url('/sitemap.xml');

        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}