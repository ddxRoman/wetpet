<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Clinic;
use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * Поиск мест работы (клиник / организаций) для выбора нескольких мест у врача
 * или специалиста. Работает по всем городам, чтобы можно было указать место
 * работы в другом городе; клиники города врача выводятся первыми.
 *
 * Ответ в формате select2 (ajax): {"results": [{"id": 1, "text": "..."}]}.
 */
class WorkplaceSearchController extends Controller
{
    public function search(Request $request, string $type)
    {
        $model = match ($type) {
            'clinics'       => Clinic::class,
            'organizations' => Organization::class,
            default         => abort(404),
        };

        $term = trim((string) $request->query('q', ''));

        $query = $model::query()->select(['id', 'name', 'city', 'street', 'house']);

        if ($term !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'LIKE', $like)
                  ->orWhere('street', 'LIKE', $like);
            });
        }

        // Места работы в городе врача/специалиста показываем первыми
        $cityName = $request->filled('city_id') ? City::whereKey($request->query('city_id'))->value('name') : null;
        if ($cityName) {
            $query->orderByRaw('city = ? DESC', [$cityName]);
        }

        $results = $query->orderBy('name')->limit(30)->get()->map(function ($item) {
            $address = implode(', ', array_filter([$item->city, trim($item->street . ' ' . $item->house)]));

            return [
                'id'   => $item->id,
                'text' => $item->name . ($address !== '' ? ' — ' . $address : ''),
            ];
        })->values();

        return response()->json(['results' => $results]);
    }
}
