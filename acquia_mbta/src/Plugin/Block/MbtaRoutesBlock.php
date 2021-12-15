<?php

namespace Drupal\acquia_mbta\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\acquia_mbta\MbtaRoutesApiService;

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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, ClientInterface $httpClient, MbtaRoutesApiService $mbta_route_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->mbtaRouteService = $mbta_route_service;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('acquia_mbta.settings');
    // print_r($this->mbtaRouteService->getMbtaRouteAPIResponse());
    // die;
    return [
      '#theme' => 'acquia_mbta_routes',
      '#context' => 'homepage',
      '#cache' => [
        'context' => 'url',
        'tags' => $config->getCacheTags(),
      ],
    ];
  }

}