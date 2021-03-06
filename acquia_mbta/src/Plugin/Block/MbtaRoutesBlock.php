<?php

namespace Drupal\acquia_mbta\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\acquia_mbta\MbtaRoutesApiService;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Provides a 'Mbta Routes' Block.
 *
 * @Block(
 *   id = "acquia_mbta_routes",
 *   admin_label = @Translation("Mbta Routes"),
 *   category = @Translation("Mbta Routes"),
 * )
 */
class MbtaRoutesBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Constructs a new MbtaRoutesBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory definition.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   ClientInterface definition.
   * @param \Drupal\acquia_mbta\MbtaRoutesApiService $mbta_route_service
   *   MbtaRoutesApiService definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerChannelFactoryInterface definition
   */

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * HTTP Service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Custom Service to get API Response.
   *
   * @var \Drupal\acquia_mbta\MbtaRoutesApiService
   */
  protected $mbtaRouteService;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, ClientInterface $httpClient, MbtaRoutesApiService $mbta_route_service, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->mbtaRouteService = $mbta_route_service;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('http_client'),
      $container->get('acquia_mbta.mbta_routes'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $api_url = $this->configFactory->get('acquia_mbta.settings')->get("mbta_api");
    $api_key = $this->configFactory->get('acquia_mbta.settings')->get("mbta_key");
    $rowcollection = [];
    if (!empty($api_url) && !empty($api_key)) {
      $table_data = $this->mbtaRouteService->getMbtaRouteApiResponse($api_url, $api_key);
      foreach ($table_data['data'] as $value) {
        $datacollection['long_name'] = $value['attributes']['long_name'];
        $datacollection['description'] = $value['attributes']['description'];
        $datacollection['color'] = $value['attributes']['color'];
        $datacollection['text_color'] = $value['attributes']['text_color'];
        $rowcollection[] = $datacollection;
      }
    }
    else {
      $this->loggerFactory->get('acquia_mbta')->notice($this->t('Empty API Url or Empty Api Key'));
    }
    return [
      '#attached' => ['library' => ['acquia_mbta/mbta_routes']],
      '#theme' => 'acquia_mbta_routes',
      '#datatabledata' => $rowcollection,
      '#cache' => [
        'context' => 'url',
        'tags' => ['config:acquia_mbta.settings'],
      ],
    ];
  }

}
