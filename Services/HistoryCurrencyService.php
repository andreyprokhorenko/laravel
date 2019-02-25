<?php

namespace App\Services;

use App\DataProviders\CurrencyAPIConfigException;
use App\DataProviders\CurrencyAPICurlException;
use App\DataProviders\DataProviderNotFoundException;
use App\DataProviders\HistoryCryptocompareFormatter;
use App\DataProviders\HistoryDayCryptocompareProvider;
use App\DataProviders\HistoryHourCryptocompareProvider;
use App\DataProviders\HistoryMinuteCryptocompareProvider;
use App\DataProviders\IHistoryCurrencyAPI;
use App\DataProviders\IHistoryCurrencyFormatter;
use App\Repositories\Eloquent\Models\CryptoAPI\CryptocomparePrice;
use App\Repositories\Eloquent\Models\Currency;

class HistoryCurrencyService
{

    public function getChartData(
        IHistoryCurrencyAPI $historyDataProvider,
        IHistoryCurrencyFormatter $historyFormatter,
        string $periodType
    ) {
        $columnsCount = (array) $historyDataProvider->getConfigValue('columns_count', []);
        $columnCount = $columnsCount[$periodType] ?? 12;

        $historyData = $historyDataProvider->getData();

        $aggregate = (int) ($historyDataProvider->getAPIParam('limit') / $columnCount);
        $chartData = $historyFormatter->format($historyData, $aggregate);

        return $chartData;
    }

    /**
     * @param Currency $fromCurrency
     * @param Currency $toCurrency
     * @param string $periodType
     * @return HistoryDayCryptocompareProvider|HistoryHourCryptocompareProvider|HistoryMinuteCryptocompareProvider
     * @throws DataProviderNotFoundException
     */
    public function getHistoryDataProviderByPeriodType(Currency $fromCurrency, Currency $toCurrency, string $periodType)
    {
        switch ($periodType) {
            case CryptocomparePrice::PERIOD_TYPE_HOUR:
                return new HistoryMinuteCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_HOUR);

            case CryptocomparePrice::PERIOD_TYPE_DAY:
                return new HistoryHourCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_DAY);

            case CryptocomparePrice::PERIOD_TYPE_WEEK:
                return new HistoryDayCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_WEEK);

            case CryptocomparePrice::PERIOD_TYPE_MONTH:
                return new HistoryDayCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_MONTH);

            case CryptocomparePrice::PERIOD_TYPE_3MONTH:
                return new HistoryDayCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_3MONTH);

            case CryptocomparePrice::PERIOD_TYPE_6MONTH:
                return new HistoryDayCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_6MONTH);

            case CryptocomparePrice::PERIOD_TYPE_YEAR:
                return new HistoryDayCryptocompareProvider($fromCurrency, $toCurrency, CryptocomparePrice::PERIOD_TIME_YEAR);
        }

        throw new DataProviderNotFoundException('Can\'t found history data provider for this period type: [' . $periodType . ']');
    }

    /**
     * @param Currency $fromCurrency
     * @param Currency $toCurrency
     * @param string $periodType
     * @throws DataProviderNotFoundException
     * @throws CurrencyAPICurlException
     * @throws CurrencyAPIConfigException
     */
    public function getCryptocompareChartData(Currency $fromCurrency, Currency $toCurrency, string $periodType)
    {
        $historyDataProvider = $this->getHistoryDataProviderByPeriodType($fromCurrency, $toCurrency, $periodType);
        $formatter = new HistoryCryptocompareFormatter();

        return $this->getChartData($historyDataProvider, $formatter, $periodType);
    }

}