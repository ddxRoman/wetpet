<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Только диагностика, ничего не меняет. Находит пользователей, у которых
 * в колонке password лежит не настоящий bcrypt-хеш (пусто, plain-текст,
 * хеш от другого алгоритма и т.п.) — именно из-за такой записи
 * Hash::check() бросает RuntimeException "This password does not use
 * the Bcrypt algorithm." при попытке входа, и Auth::attempt() валится
 * в 500 вместо того, чтобы просто сказать "неверный пароль".
 *
 * Пароли этой командой не трогаются и не пересоздаются — переустановить
 * чужой пароль, не зная его, нельзя. Для найденных аккаунтов нужно либо
 * попросить владельца пройти "Забыли пароль" (сам перезапишет хеш
 * правильно), либо вручную задать новый пароль через Hash::make().
 */
class FindBrokenPasswordHashes extends Command
{
    protected $signature = 'app:find-broken-password-hashes';

    protected $description = 'Показывает пользователей, у которых пароль в БД не является валидным bcrypt-хешем';

    public function handle(): int
    {
        $broken = User::query()
            ->get(['id', 'email', 'name', 'password'])
            ->filter(function (User $user) {
                $hash = $user->password;
                // Настоящий bcrypt-хеш Laravel всегда начинается с $2y$ (реже $2a$/$2b$)
                // и имеет длину 60 символов.
                return empty($hash) || !preg_match('/^\$2[aby]\$/', $hash) || strlen($hash) !== 60;
            });

        if ($broken->isEmpty()) {
            $this->info('Все пароли в users — валидные bcrypt-хеши.');
            return self::SUCCESS;
        }

        $this->warn("Найдено аккаунтов с некорректным хешем пароля: {$broken->count()}");
        $this->table(
            ['ID', 'Email', 'Имя', 'Чем выглядит password'],
            $broken->map(fn (User $u) => [
                $u->id,
                $u->email,
                $u->name,
                $u->password === null
                    ? 'NULL'
                    : ($u->password === ''
                        ? '(пустая строка)'
                        : mb_substr($u->password, 0, 20) . '… (длина ' . strlen($u->password) . ')'),
            ])
        );

        $this->newLine();
        $this->line('Исправить: либо пусть пользователь пройдёт "Забыли пароль" (сам '
            . 'перезапишет хеш правильно), либо через tinker: '
            . "User::find(ID)->update(['password' => Hash::make('новый-пароль')]);");

        return self::SUCCESS;
    }
}
