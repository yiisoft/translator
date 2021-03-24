<?php

declare(strict_types=1);

namespace Yii\Extension\User\Helper;

use DateTime;
use DateTimeZone;
use Yiisoft\Arrays\ArraySorter;

final class TimeZone
{
    public function getAll(): array
    {
        $timeZones = [];
        $listlistIdentifiers = DateTimeZone::listIdentifiers();
        $timeZoneIdentifiers = is_array($listlistIdentifiers) ? $listlistIdentifiers : [];

        foreach ($timeZoneIdentifiers as $timeZone) {
            $name = str_replace('_', ' ', $timeZone);
            $date = new DateTime('now', new DateTimeZone($timeZone));

            array_push(
                $timeZones,
                [
                    'timezone' => $timeZone,
                    'name' => "{$name} (UTC {$date->format('P')})",
                    'offset' => $date->getOffset(),
                ]
            );
        }

        ArraySorter::multisort($timeZones, 'offset', SORT_ASC, SORT_NUMERIC);

        return $timeZones;
    }
}
