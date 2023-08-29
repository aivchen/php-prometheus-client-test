<?php

declare(strict_types=1);

namespace Andrew\PhpPrometheusClient;

use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\CollectorFactory;
use Zlodes\PrometheusClient\Exporter\StoredMetricsExporter;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Registry\ArrayRegistry;
use Zlodes\PrometheusClient\Storage\InMemoryStorage;

/**
 * @api
 */
final class Test
{
    public function __invoke()
    {
        $registry = new ArrayRegistry();
        $storage = new InMemoryStorage();

        // Register your metrics
        $registry
            ->registerMetric(
                new Gauge('body_temperature', 'Body temperature in Celsius')
            )
            ->registerMetric(
                new Counter('steps', 'Steps count')
            )
            ->registerMetric(
                new Histogram('request_duration', 'Request duration in seconds'),
            );

        // Create a Collector factory
        $collectorFactory = new CollectorFactory(
            $registry,
            $storage,
            new NullLogger(),
        );

        // Collect metrics
        $bodyTemperatureGauge = $collectorFactory->gauge('body_temperature');

        $bodyTemperatureGauge
            ->withLabels(['source' => 'armpit'])
            ->update(36.6);

        $bodyTemperatureGauge
            ->withLabels(['source' => 'ass'])
            ->update(37.2);

        $collectorFactory
            ->counter('steps')
            ->increment();

        $requestTimer = $collectorFactory
            ->histogram('request_duration')
            ->startTimer();

        usleep(50_000);

        $requestTimer->stop();

        // Export metrics
        $exporter = new StoredMetricsExporter(
            $registry,
            $storage,
        );

        foreach ($exporter->export() as $metricOutput) {
            echo $metricOutput . "\n\n";
        }
    }

}
