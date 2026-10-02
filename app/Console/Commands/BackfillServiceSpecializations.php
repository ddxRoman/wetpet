<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\FieldOfActivity;
use App\Models\Organization;
use App\Models\Price;
use App\Models\Service;
use App\Models\Specialist;
use Illuminate\Console\Command;

/**
 * Чинит уже созданные услуги, у которых specialization И specialization_doctor
 * одновременно пустые (появлялось при добавлении «+ Новая услуга» из кабинета
 * владельца до того, как OwnerCabinetController::savePrice() начал проставлять
 * специализацию сразу при создании — см. serviceSpecializationFieldsFor()).
 *
 * Специализацию определяем по тому, у кого эта услуга реально указана в ценах
 * (таблица prices): берём сферу деятельности/специализацию карточек, которые
 * ею пользуются. Если среди них только один вариант — проставляем его.
 * Если варианты расходятся (услугу завели в разных по смыслу карточках) —
 * трогать не будем и выведем в консоль для ручной проверки, чтобы не
 * присвоить услуге специализацию наугад.
 */
class BackfillServiceSpecializations extends Command
{
    protected $signature = 'app:backfill-service-specializations {--dry-run : Показать, что будет исправлено, но не сохранять}';

    protected $description = 'Проставляет specialization/specialization_doctor у услуг, созданных без неё, по карточкам, которые ею реально пользуются';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $services = Service::whereNull('specialization')->whereNull('specialization_doctor')->get();

        if ($services->isEmpty()) {
            $this->info('Услуг без специализации не найдено.');
            return self::SUCCESS;
        }

        $fixed = 0;
        $ambiguous = 0;

        foreach ($services as $service) {
            $guesses = [];

            foreach (Price::where('service_id', $service->id)->get() as $price) {
                $guess = $this->guessSpecialization($price->priceable_type, $price->priceable_id);
                if ($guess) {
                    $guesses[$guess['field'] . ':' . $guess['value']] = $guess;
                }
            }

            if (empty($guesses)) {
                continue; // некому подсказать специализацию — не трогаем
            }

            if (count($guesses) > 1) {
                $ambiguous++;
                $this->line(sprintf(
                    '[пропущено, разные варианты] Service #%d "%s": %s',
                    $service->id,
                    $service->name,
                    implode(', ', array_map(fn ($g) => $g['value'], $guesses))
                ));
                continue;
            }

            $guess = array_values($guesses)[0];
            $this->line(sprintf(
                'Service #%d "%s": %s = "%s"',
                $service->id,
                $service->name,
                $guess['field'],
                $guess['value']
            ));

            if (!$dryRun) {
                $service->{$guess['field']} = $guess['value'];
                $service->saveQuietly();
            }

            $fixed++;
        }

        $this->info($dryRun
            ? "Можно исправить: {$fixed} (ничего не сохранено — это --dry-run). Неоднозначных: {$ambiguous}."
            : "Исправлено: {$fixed}. Пропущено как неоднозначные: {$ambiguous}.");

        return self::SUCCESS;
    }

    /**
     * @return array{field: string, value: string}|null
     */
    private function guessSpecialization(string $priceableType, int $priceableId): ?array
    {
        return match ($priceableType) {
            Organization::class => ($name = Organization::with('activityType')->find($priceableId)?->activityType?->name)
                ? ['field' => 'specialization', 'value' => $name]
                : null,

            Clinic::class => ['field' => 'specialization', 'value' => FieldOfActivity::VET_CLINIC_NAME],

            Doctor::class => ($spec = Doctor::find($priceableId)?->specialization)
                ? ['field' => 'specialization_doctor', 'value' => $spec]
                : null,

            Specialist::class => ($spec = Specialist::find($priceableId)?->specialization)
                ? ['field' => 'specialization_doctor', 'value' => $spec]
                : null,

            default => null,
        };
    }
}
