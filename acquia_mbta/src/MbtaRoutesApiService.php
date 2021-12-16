<?php

namespace Drupal\acquia_mbta;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Datetime\Time;

/**
 * Service for returing data from Mbta Routes API.
 *
 * @package Drupal\acquia_mbta
 */
class MbtaRoutesApiService {

  use StringTranslationTrait;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Core\Cache\CacheFactoryInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheFactoryInterface
   */
  protected $cacheFactory;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cachebin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBin;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $timeService;

  /**
   * Constructs a new Mbta Routes object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   GuzzleHttp\ClientInterface definition.
   * @param \Drupal\Core\Cache\CacheFactoryInterface $cache_factory
   *   CacheFactoryInterface definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerChannelFactoryInterface definition.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory definition.
   * @param \Drupal\Component\Datetime\Time $timeService
   *   Time definition.
   */
  public function __construct(
    ClientInterface $http_client,
    CacheFactoryInterface $cache_factory,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactory $configFactory,
    Time $timeService) {
    $this->httpClient = $http_client;
    $this->cacheFactory = $cache_factory;
    $this->loggerFactory = $logger_factory;
    $this->configFactory = $configFactory;
    $this->timeService = $timeService;
    $this->cacheBin = $this->cacheFactory->get('default');
  }

  /**
   * Get the data from API.
   *
   * @return array
   *   Array is dervied from Json response.
   */
  public function getMbtaRouteApiResponse(string $api_url, string $api_key = NULL) {
    $cached_response = $this->cacheBin->get('mbta_routes_data');
    $response_data = NULL;
    if ($cached_response) {
      return $cached_response->data;
    }
    $expire_cache = Cache::PERMANENT;
    try {
      if ($api_key) {
        $response = $this->httpClient->request('GET', $api_url, ['query' => ['api_key' => $api_key]]);
      }
      else {
        $response = $this->httpClient->request('GET', $api_url);
      }
      $response_data = json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('acquia_mbta')->emergency('Retrieving data from the following @url failed with error for @url with @error', [
        '@url' => $api_url,
        '@error' => $e->getMessage(),
      ]
      );
    }
    $expire_cache = $this->timeService->getRequestTime() + 60 * 1;

    $tags = ['config:acquia_mbta.settings'];
    $this->cacheBin->set('mbta_routes_data', $response_data, $expire_cache, $tags);
    return $response_data;
  }

}
