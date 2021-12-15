<?php

namespace Drupal\acquia_mbta;

use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;

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
   */
  public function __construct(
    ClientInterface $http_client,
    CacheFactoryInterface $cache_factory,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactory $configFactory) {
    $this->httpClient = $http_client;
    $this->cacheFactory = $cache_factory;
    $this->loggerFactory = $logger_factory;
    $this->configFactory = $configFactory;
    $this->cacheBin = $this->cacheFactory->get('default');
  }

  /**
   * Returns the Json response for the given API path.
   */
  public function getMbtaRouteAPIResponse() {
    $api_url = $this->configFactory->get('acquia_mbta.settings')->get("mbta_api");
    $api_key = $this->configFactory->get('acquia_mbta.settings')->get("mbta_key");
    $response_data = NULL;
    if (empty($api_url) || empty($api_key)) {
      $this->loggerFactory->get('acquia_mbta')->notice($this->t('Empty API Url or Empty Api Key'));
    }
    else {
      try {
        $response = $this->httpClient->request('GET', $api_url . '?api_key=' . $api_key);
        $response_data = json_decode($response->getBody()->getContents(), TRUE);
      }
      catch (\Exception $e) {
        $this->loggerFactory->get('acquia_mbta')->emergency('Retrieving data from the following @url failed with error for @url with @error', [
          '@url' => $api_url,
          '@error' => $e->getMessage(),
        ]
        );
      }
      return new JsonResponse([
        'message' => $this->t("Download encountered an error"),
      ], 400);
    }
    return $response_data;
  }

}
