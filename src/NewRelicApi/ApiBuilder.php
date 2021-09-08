<?php

declare(strict_types=1);

namespace RunAsRoot\PrometheusExporter\NewRelicApi;

use RunAsRoot\NewRelicApi\Api\AbstractV1Api;
use RunAsRoot\NewRelicApi\Config\ClientConfig as NewRelicClientConfiguration;
use Magento\Framework\ObjectManagerInterface;
use RunAsRoot\PrometheusExporter\Logger\MetricLogger;
use RunAsRoot\PrometheusExporter\Data\NewRelicConfig;

class ApiBuilder
{
    private ObjectManagerInterface $objectManager;
    private NewRelicConfig $newRelicConfig;

    public function __construct(ObjectManagerInterface $objectManager, NewRelicConfig $newRelicConfig)
    {
        $this->objectManager = $objectManager;
        $this->newRelicConfig = $newRelicConfig;
    }

    /**
     * @return AbstractV1Api
     *
     * @throws \RuntimeException
     */
    public function build(string $class, ?int $storeId): AbstractV1Api
    {
        $host = $this->newRelicConfig->getApiUrl($storeId);
        $authKey = $this->newRelicConfig->getApiKey($storeId);

        $config = new NewRelicClientConfiguration();
        $config->setServiceUrl($host);
        $config->setApiKey($authKey);

        $logger = $this->newRelicConfig->isDebugEnabled() ?
            $this->objectManager->get(MetricLogger::class)
            : null;

        $client = $this->objectManager->create($class, ['clientConfig' => $config, 'logger' => $logger]);

        if (!($client instanceof $class)) {
            throw new \RuntimeException("Api client for $class could not be created");
        }

        return $client;
    }
}
