<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Link' block
 *
 * @Block(
 *     id = "aluminum_link",
 *     admin_label = @Translation("Link"),
 * )
 */
class AluminumLinkBlock extends AluminumBlockBase {

  /**
   * PathValidatorInterface definition.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * PathAliasInterface definition.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * RouteMatchInterface definition.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new AluminumBlockBase object.
   *
   * @param array $configuration
   *   The block plugin configuration.
   * @param string $plugin_id
   *   The block plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The alias manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, PathValidatorInterface $path_validator, AliasManagerInterface $alias_manager, RouteMatchInterface $route_match, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->pathValidator = $path_validator;
    $this->aliasManager = $alias_manager;
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Path\PathValidatorInterface $path_validator */
    $path_validator = $container->get('path.validator');
    /** @var \Drupal\path_alias\AliasManagerInterface $alias_manager */
    $alias_manager = $container->get('path_alias.manager');
    /** @var \Drupal\Core\Routing\RouteMatchInterface $route_match */
    $route_match = $container->get('current_route_match');
    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $container->get('request_stack');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory,
      $path_validator,
      $alias_manager,
      $route_match,
      $request_stack
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#description' => $this->t('Enter the text to use for the link.'),
      '#default_value' => '',
    ];

    $options['link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link URL'),
      '#description' => $this->t('Enter the URL, path, or route to use for the link. Use the token [back] to link to the previous page, or [current] to append the current URL to your link.'),
      '#default_value' => '',
    ];

    return $options;
  }

  /**
   * Is active trail.
   *
   * @return bool
   *   True if active trail.
   */
  protected function isActiveTrail(): bool {
    $linkUrl = $this->getOptionValue('link_url');

    if (empty($linkUrl) || $linkUrl == '#') {
      return FALSE;
    }

    $url = Url::fromUserInput($linkUrl);

    if ($url->isExternal() || !$url->isRouted()) {
      return FALSE;
    }

    $currentUrl = Url::fromRouteMatch($this->routeMatch)->getInternalPath();
    return (str_starts_with($currentUrl, $url->getInternalPath()));
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $classes = ['aluminum-link'];

    if ($this->getOptionValue('link_url') != '[back]'
      && $this->isActiveTrail()) {
      $classes[] = 'is-activeTrail';
    }

    $build = [
      'content' => [
        '#theme' => 'aluminum_link',
        '#title' => $this->t($this->getOptionValue('link_text')),
        '#url' => $this->getUrl(),
        '#classes' => $classes,
      ]
    ];

    if ($this->getOptionValue('link_url') == '[back]') {
      $build['#cache']['max-age'] = 0;
    }

    return $build;
  }

  /**
   * Get url.
   *
   * @return array|\Drupal\Core\GeneratedUrl|string|string[]
   */
  protected function getUrl(): array|GeneratedUrl|string {
    $url = $this->getOptionValue('link_url');

    if (str_contains($url, '[back]')) {
      $back_url = '/';
      $previousUrl = $this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER');
      
      if ($previousUrl) {
        $fake_request = Request::create($previousUrl);
        /** @var \Drupal\Core\Url $url_object */
        $url_object = $this->pathValidator->getUrlIfValid($fake_request->getRequestUri());

        if ($url_object) {
          $back_url = $this->aliasManager->getAliasByPath('/' . $url_object->getInternalPath());
        }
      }
      $url = str_replace('[back]', $back_url, $url);
    }

    if (str_contains($url, '[current]')) {
      $current = $this->requestStack->getCurrentRequest()->getRequestUri();
      $url = str_replace('[current]', $current, $url);
    }

    if ((str_starts_with($url, '/'))
      || (str_starts_with($url, '#'))
      || (str_starts_with($url, '?'))) {
      $urlObject = Url::fromUserInput($url);
      $url = $urlObject->toString();
    }

    return $url;
  }

  /**
   * Get cache contexts.
   *
   * @return array|string[]
   *   An array of cache contexts.
   */
  public function getCacheContexts(): array {
    // If you depend on \Drupal::routeMatch()
    // you must set context of this block with 'route' context tag.
    // Every new route this block will rebuild.
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

}
