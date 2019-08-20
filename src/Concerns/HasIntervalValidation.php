<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Interval\IntervalInterface;

trait HasIntervalValidation
{
    /**
     * @var DruidClient
     */
    protected $client;

    /**
     * Check if the given interval is valid for the given dataSource.
     *
     * @param string                                    $dataSource
     * @param \Level23\Druid\Interval\IntervalInterface $interval
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    protected function validateInterval(string $dataSource, IntervalInterface $interval): void
    {
        $fromStr = $interval->getStart()->format('Y-m-d\TH:i:s.000\Z');
        $toStr   = $interval->getStop()->format('Y-m-d\TH:i:s.000\Z');

        $foundFrom = false;
        $foundTo   = false;

        // Get all intervals and check if our interval is among them.
        $intervals = $this->client->intervals($dataSource);

        foreach ($intervals as $dateStr => $info) {

            if (!$foundFrom) {
                if (substr($dateStr, 0, strlen($fromStr)) === $fromStr) {
                    $foundFrom = true;
                }
            }

            if (!$foundTo) {
                if (substr($dateStr, -strlen($toStr)) === $toStr) {
                    $foundTo = true;
                }
            }

            if ($foundFrom && $foundTo) {
                return;
            }
        }

        throw new InvalidArgumentException(
            'Error, invalid interval given. The given dates do not match a complete interval!'
        );
    }
}