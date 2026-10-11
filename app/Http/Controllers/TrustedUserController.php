<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Specialist;
use App\Models\User;
use Illuminate\Http\Request;

class TrustedUserController extends Controller
{
    /**
     * Добавить пользователя в доверенные (только с премиум-подпиской).
     */
    public function store(Request $request, User $user)
    {
        $me = $request->user();

        if (! $me->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Доверенные пользователи доступны с Премиум-подпиской.',
            ], 403);
        }

        if ($user->id === $me->id) {
            return response()->json(['success' => false, 'message' => 'Нельзя добавить самого себя.'], 422);
        }

        if ($user->status === 'ban') {
            return response()->json(['success' => false, 'message' => 'Пользователь не найден.'], 404);
        }

        $me->trustedUsers()->syncWithoutDetaching([$user->id]);

        return response()->json(['success' => true, 'trusted' => true]);
    }

    /**
     * Убрать из доверенных (доступно всегда, даже если подписка закончилась).
     */
    public function destroy(Request $request, User $user)
    {
        $request->user()->trustedUsers()->detach($user->id);

        return response()->json(['success' => true, 'trusted' => false]);
    }

    /**
     * Лента отзывов доверенных пользователей — HTML-фрагмент с пагинацией.
     */
    public function feed(Request $request)
    {
        $me = $request->user();

        if (! $me->isPremium()) {
            return response()->json(['success' => false, 'message' => 'Лента доступна с Премиум-подпиской.'], 403);
        }

        $trustedIds = $me->trustedUsers()
            ->where('users.status', '!=', 'ban')
            ->pluck('users.id');

        $reviews = Review::whereIn('user_id', $trustedIds)
            // Отзывы на удалённые объекты в ленте не нужны
            ->whereHasMorph('reviewable', [
                Clinic::class,
                Doctor::class,
                Specialist::class,
                Organization::class,
            ])
            ->with(['user:id,name,nickname,avatar', 'reviewable', 'photos', 'pet.animal'])
            ->orderByDesc('review_date')
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json([
            'success'  => true,
            'html'     => view('account.tabs.partials.trusted-feed', ['reviews' => $reviews])->render(),
            'has_more' => $reviews->hasMorePages(),
            'total'    => $reviews->total(),
        ]);
    }
}
