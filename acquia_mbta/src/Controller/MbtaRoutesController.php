<?php

namespace Drupal\acquia_mbta\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockManagerInterface;

/**
 * MbtaRoutesController controller class.
 *
 * @package Drupal\acquia_mbta\Controller
 */
class MbtaRoutesController extends ControllerBase {


  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs a new Mbta Routes object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   BlockManagerInterface definition.
   * @param \Drupal\acquia_mbta\MbtaRoutesApiService $mbta_route_service
   *   MbtaRoutesApiService definition.
   */

  /**
   * {@inheritDoc}
   */
  public function __construct(ConfigFactory $configFactory, BlockManagerInterface $block_manager) {
    $this->configFactory = $configFactory;
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Returns the search results.
   */
  public function getMbtaRoutes() {
    $build = [
      '#cache' => [
        'contexts' => ['url'],
        'tags' => [
          'config:acquia_mbta.settings',
        ],
      ],
    ];
    $config = [];
    $mbta_routes_block = $this->blockManager->createInstance('acquia_mbta_routes', $config);
    $build['mbta_routes'] = $mbta_routes_block->build();
    return $build;

  }

  /**
   * Returns the page title.
   */
  public function getTitle(string $mbtaroutestitle = NULL) {
    if (!$mbtaroutestitle) {
      return $this->t('Mbta Routes Data');
    }
    else {
      return $mbtaroutestitle;
    }

  }

}
