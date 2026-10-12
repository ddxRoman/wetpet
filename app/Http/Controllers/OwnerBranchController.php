<?php

namespace App\Http\Controllers;

use App\Models\BoostSubscription;
use App\Models\Chain;
use App\Models\Clinic;
use App\Models\ClinicOwner;
use App\Models\Organization;
use App\Models\OrganizationOwner;
use App\Support\OwnerAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * «Работа с филиалами» в кабинете владельца.
 *
 * Показывает все филиалы выбранной сети и позволяет массово задать логотип, график работы,
 * телефон, почту и сайт — всем филиалам сети или только выбранным. Изменять можно только
 * филиалы, которыми пользователь владеет (подтверждённое владение) — остальные показываются
 * в списке только для просмотра.
 */
class OwnerBranchController extends Controller
{
    private const GROUPS = ['logo', 'schedule', 'phone', 'email', 'website'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(?int $chain = null)
    {
        $chains = $this->userChains();

        if ($chain === null) {
            $current = $chains->first();
        } else {
            $current = Chain::findOrFail($chain);
            abort_unless(
                $chains->contains('id', $current->id) || $this->isAdmin(),
                403,
                'У вас нет прав для управления филиалами этой сети.'
            );
        }

        if (! $current) {
            return redirect()->route('owner.index')
                ->with('info', 'Работа с филиалами доступна, когда ваша карточка входит в сеть филиалов.');
        }

        $branches   = $this->branches($current);
        $manageable = $branches->where('owned', true)->values();

        // Куда вести за покупкой пакета: первая из карточек пользователя в этой сети
        $buyFor = $manageable->first();

        return view('pages.owner.branches', [
            'chains'     => $chains,
            'chain'      => $current,
            'branches'   => $branches,
            'manageable' => $manageable,
            'canManage'  => $this->canManage($manageable),
            'buyUrl'     => $buyFor
                ? route('owner.boost', ['type' => $buyFor['type'], 'id' => $buyFor['id']])
                : null,
        ]);
    }

    public function apply(Request $request, int $chain)
    {
        $chain = Chain::findOrFail($chain);

        $request->validate([
            'group'        => 'required|in:' . implode(',', self::GROUPS),
            'target'       => 'required|in:all,selected',
            'branch_ids'   => 'nullable|array',
            'branch_ids.*' => ['string', 'regex:/^(clinic|organization):\d+$/'],
        ]);

        $group = $request->input('group');

        // Филиалы сети, которыми пользователь может управлять
        $manageable = $this->branches($chain)->where('owned', true)->keyBy('key');

        if ($manageable->isEmpty()) {
            abort(403, 'У вас нет прав для управления филиалами этой сети.');
        }

        // Массовые настройки доступны только с оплаченным пакетом «Управление»
        abort_unless(
            $this->canManage($manageable),
            403,
            'Массовые настройки филиалов доступны с пакетом «Управление». Подключите его в разделе «Продвижение».'
        );

        if ($request->input('target') === 'all') {
            $targets = $manageable;
        } else {
            $selected = collect($request->input('branch_ids', []));
            $targets  = $manageable->only($selected->all());

            if ($targets->isEmpty()) {
                return back()->withInput()->with('branch_error', 'Выберите хотя бы один филиал.')
                    ->with('branch_error_group', $group);
            }
        }

        // Поля, которые применяем. Пустые поля НЕ меняются.
        $values = [];
        $logo   = null;

        switch ($group) {
            case 'logo':
                $request->validate(['logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048']);
                $logo = $request->file('logo');
                break;

            case 'schedule':
                $data = $request->validate([
                    'schedule' => 'nullable|string|max:255',
                    'workdays' => 'nullable|string|max:255',
                ]);
                $values = array_filter($data, fn ($v) => filled($v));
                break;

            case 'phone':
                $data = $request->validate([
                    'phone1' => 'nullable|string|max:30',
                    'phone2' => 'nullable|string|max:30',
                ]);
                $values = array_filter($data, fn ($v) => filled($v));
                break;

            case 'email':
                $values = $request->validate(['email' => 'required|email|max:255']);
                break;

            case 'website':
                $values = $request->validate(['website' => 'required|url|max:255']);
                break;
        }

        if ($group !== 'logo' && empty($values)) {
            return back()->withInput()
                ->with('branch_error', 'Заполните хотя бы одно поле — пустые поля не применяются.')
                ->with('branch_error_group', $group);
        }

        $count = 0;

        foreach ($targets as $branch) {
            $model = $branch['model'];

            if ($logo) {
                $dir = $branch['type'] === 'clinic' ? 'clinics/logos' : 'organizations/logos';

                if ($model->logo) {
                    Storage::disk('public')->delete($model->logo);
                }

                // Каждому филиалу — собственная копия файла, чтобы замена/удаление у одного не ломали другие
                $newPath = $logo->storeAs(
                    $dir,
                    Str::random(40) . '.' . $logo->getClientOriginalExtension(),
                    'public'
                );

                $model->update(['logo' => $newPath]);
            } else {
                $model->update($values);
            }

            $count++;
        }

        return redirect()
            ->route('owner.branches', $chain->id)
            ->with('branch_success', 'Применено к филиалам: ' . $count . '.');
    }

    // ───────────────────────── вспомогательное ─────────────────────────

    /**
     * Массовые настройки доступны при активном пакете «Управление» хотя бы у одной из карточек
     * пользователя в этой сети (администратору — всегда).
     */
    private function canManage(Collection $manageable): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $manageable->contains(
            fn ($b) => BoostSubscription::activeFor($b['type'], (int) $b['id'], 'manage')
        );
    }

    private function isAdmin(): bool
    {
        return (Auth::user()->is_admin ?? false) === true;
    }

    /** Сети, в которых у пользователя есть хотя бы один подтверждённый филиал. */
    private function userChains(): Collection
    {
        $userId = Auth::id();

        $clinicIds = ClinicOwner::where('user_id', $userId)->where('is_confirmed', true)->pluck('clinic_id');
        $orgIds    = OrganizationOwner::where('user_id', $userId)->where('is_confirmed', true)->pluck('organization_id');

        $chainIds = Clinic::whereIn('id', $clinicIds)->whereNotNull('chain_id')->pluck('chain_id')
            ->merge(Organization::whereIn('id', $orgIds)->whereNotNull('chain_id')->pluck('chain_id'))
            ->unique()
            ->values();

        return Chain::whereIn('id', $chainIds)->orderBy('name')->get();
    }

    /**
     * Все филиалы сети (клиники и организации), владельцем которых может быть пользователь — owned = true.
     */
    private function branches(Chain $chain): Collection
    {
        $user = Auth::user();

        $map = function ($model, string $type) use ($user) {
            return [
                'key'      => $type . ':' . $model->id,
                'type'     => $type,
                'label'    => $type === 'clinic' ? 'Клиника' : 'Организация',
                'id'       => $model->id,
                'model'    => $model,
                'name'     => $model->name,
                'city'     => $model->city,
                'address'  => trim(trim((string) $model->street) . ' ' . trim((string) $model->house)),
                'logo'     => $model->logo,
                'schedule' => $model->schedule,
                'workdays' => $model->workdays,
                'phone1'   => $model->phone1,
                'phone2'   => $model->phone2,
                'email'    => $model->email,
                'website'  => $model->website,
                'owned'    => OwnerAccess::isConfirmedOwner($user, $type, $model->id),
                'url'      => route($type === 'clinic' ? 'owner.clinic' : 'owner.organization', $model->id),
            ];
        };

        return $chain->clinics()->get()->map(fn ($c) => $map($c, 'clinic'))
            ->concat($chain->organizations()->get()->map(fn ($o) => $map($o, 'organization')))
            ->sortBy([['city', 'asc'], ['name', 'asc']])
            ->values();
    }
}
