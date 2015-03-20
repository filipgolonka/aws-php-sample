<?php

use Aws\ElasticBeanstalk\ElasticBeanstalkClient;

final class VersionsRemover
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var ElasticBeanstalkClient
     */
    protected $client;

    public function __construct($config)
    {
        $requiredKeys = [
            'client',
            'labelPatterns',
            'leaveLastNVersions'
        ];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                throw new \Exception(sprintf('Missing "%s" configuration', $key));
            }
        }

        $this->config = $config;
    }

    /**
     * return int
     */
    public function perform()
    {
        $applicationVersions = $this->getApplicationVersions();
        $applicationVersions = $this->removeCurrentUsedApplicationVersions($applicationVersions);
        $applicationVersions = $this->removeRecentVersions($applicationVersions);

        foreach ($applicationVersions as $version) {
            $this->getClient()->deleteApplicationVersion([
                'ApplicationName' => $version['ApplicationName'],
                'VersionLabel' => $version['VersionLabel'],
            ]);
        }

        return 0;
    }

    /**
     * @return array
     */
    protected function getApplicationVersions()
    {
        $response = $this->getClient()->describeApplicationVersions();
        $response = $response->getAll();

        return $response['ApplicationVersions'];
    }

    /**
     * @param array $applicationVersions
     *
     * @return array
     */
    protected function removeCurrentUsedApplicationVersions($applicationVersions)
    {
        $environments = $this->getEnvironments();

        foreach($environments as $environment) {
            foreach ($applicationVersions as $key => $version) {
                if($version['VersionLabel'] == $environment['VersionLabel']) {
                    unset($applicationVersions[$key]);
                    break;
                }
            }
        }

        return $applicationVersions;
    }

    /**
     * @return array
     */
    protected function getEnvironments()
    {
        $response = $this->getClient()->describeEnvironments();
        $response = $response->getAll();
        return $response['Environments'];
    }

    /**
     * @param array $applicationVersions
     *
     * @return array
     */
    protected function removeRecentVersions($applicationVersions)
    {
        foreach($this->config['labelPatterns'] as $pattern) {
            $counter = 0;
            foreach ($applicationVersions as $key => $version) {
                if (preg_match($pattern, $version['VersionLabel'])) {
                    if ($counter++ < $this->config['leaveLastNVersions']) {
                        unset($applicationVersions[$key]);
                    }
                }
            }
        }

        return $applicationVersions;
    }

    /**
     * @return ElasticBeanstalkClient
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = ElasticBeanstalkClient::factory($this->config['client']);
        }

        return $this->client;
    }
}
