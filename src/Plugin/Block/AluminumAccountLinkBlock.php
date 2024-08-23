<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Account link' block
 *
 * @Block(
 *     id = "aluminum_account_link",
 *     admin_label = @Translation("Account link"),
 * )
 */
class AluminumAccountLinkBlock extends AluminumBlockBase {

  /**
   * AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * RouteMatchInterface definition.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

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
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account proxy.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *    The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, AccountProxyInterface $account, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->account = $account;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\Core\Session\AccountProxyInterface $account */
    $account = $container->get('current_user');
    /** @var \Drupal\Core\Routing\RouteMatchInterface $route_match */
    $route_match = $container->get('current_route_match');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory,
      $account,
      $route_match
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['login_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login text'),
      '#description' => $this->t('Enter the text to use for the login link.'),
      '#default_value' => 'Log In',
    ];

    $options['account_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('My Account'),
      '#description' => $this->t('Enter the text to use for the account link.'),
      '#default_value' => 'My Account',
    ];

    return $options;
  }

  /**
   * Get link title.
   *
   * @return array|string
   *   The link title.
   */
  protected function getLinkTitle(): array|string {
    return $this->getOptionValue(($this->isLoggedIn() ? 'account_text' : 'login_text'));
  }

  /**
   * Get link url.
   *
   * @return \Drupal\Core\Url
   *   The url.
   */
  protected function getLinkUrl(): Url {
    return Url::fromRoute(($this->isLoggedIn() ? 'user.page' : 'user.login'));
  }

  /**
   * Get link fragment.
   *
   * @return string
   *   Link class fragment.
   */
  protected function getLinkClassFragment(): string {
    return $this->isLoggedIn() ? 'account' : 'login';
  }

  /**
   * Is user logged in.
   *
   * @return bool
   *   Whether the current user is authenticated.
   */
  protected function isLoggedIn(): bool {
    return $this->account->isAuthenticated();
  }

  /**
   * Is active trail.
   *
   * @return bool
   *   Whether there is an active trail.
   */
  protected function isActiveTrail(): bool {
    $currentUrl = Url::fromRouteMatch($this->routeMatch)->getInternalPath();
    $url = $this->getLinkUrl()->getInternalPath();

    return (str_starts_with($currentUrl, $url));
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $classes = [
      'aluminum-account-link',
      'aluminum-account-link--' . $this->getLinkClassFragment(),
    ];

    if ($this->isActiveTrail()) {
      $classes[] = 'is-activeTrail';
    }

    return [
      '#type' => 'link',
      '#title' => $this->getLinkTitle(),
      '#url' => $this->getLinkUrl(),
      '#attributes' => ['class' => $classes],
    ];
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
    // Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

}
