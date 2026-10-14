<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Formats loyalty points balance for customer admin grid column.
 */
class LoyaltyPoints extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $points = isset($item[$fieldName]) ? (float)$item[$fieldName] : 0.0;
                $item[$fieldName] = number_format($points, 2);
            }
        }

        return $dataSource;
    }
}
