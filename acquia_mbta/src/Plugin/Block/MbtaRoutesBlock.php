<?php

namespace Drupal\acquia_mbta\Plugin\Block;

use Drupal\Core\Block\BlockBase;
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
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   ClientInterface definition.
   * @param \Drupal\acquia_mbta\MbtaRoutesApiService $mbta_route_service
   *   MbtaRoutesApiService definition.
   */

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $httpClient, MbtaRoutesApiService $mbta_route_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('http_client'),
      $container->get('acquia_mbta.mbta_routes'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $table_data = $this->mbtaRouteService->getMbtaRouteApiResponse();
    $rowcollection = [];
    if (!empty($table_data)) {
      foreach ($table_data['data'] as $value) {
        $datacollection['long_name'] = $value['attributes']['long_name'];
        $datacollection['description'] = $value['attributes']['description'];
        $datacollection['color'] = $value['attributes']['color'];
        $datacollection['text_color'] = $value['attributes']['text_color'];
        $rowcollection[] = $datacollection;
      }
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
