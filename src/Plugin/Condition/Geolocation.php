<?php

namespace Drupal\edgescape\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A condition plugin based on the Akamai EdgeScape HTTP header value.
 *
 * @Condition(
 *   id = "geolocation",
 *   label = @Translation("EdgeScape geolocation")
 * )
 */
class Geolocation extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Geolocation constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(ConfigFactoryInterface $config_factory, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Get IP Address Attributes.
   *
   * @return array
   *   Returns IP attributes with their descriptions.
   */
  public function getAttributes() {
    return [
      'continent' => $this->t('Two-letter code for the continent associated with the IP address.'),
      'country_code' => $this->t('An ISO-3166, two-letter code for the country.'),
      'region_code' => $this->t('The state, province, or region code.'),
      'city' => $this->t('The city (within a 50-mile radius).'),
      'dma' => $this->t('A number representing the DMA®. DMA® is a registered service mark of The Nielsen Company, all rights reserved.'),
      'msa' => $this->t('A number representing the Metropolitan Statistical Area.'),
      'pmsa' => $this->t('A number representing the Primary Metropolitan Statistical Area.'),
      'areacode' => $this->t('The area code (multiple values possible). Note: Multiple values will be separated by the plus (+) character.'),
      'lat' => $this->t('The latitude'),
      'long' => $this->t('The longitude'),
      'country' => $this->t('The county (multiple values possible). Note: Multiple values will be separated by the plus (+) character.'),
      'timezone' => $this->t('The time zone. Daylight Saving Time is not accounted for.'),
      'zip' => $this->t('The zipcode (multiple values possible). Only available for the US and Canada.'),
      'network' => $this->t('The network that the IP address belongs to.'),
      'network_type' => $this->t('The network type.'),
      'asnum' => $this->t('The AS (autonomous system) number to which the IP belongs.'),
      'throughput' => $this->t('The actual connection speed.'),
      'bw' => $this->t('Provides additional granularity to the throughput field.'),
      'proxy' => $this->t('Indicates whether the IP address is a proxy and if it is, which type of proxy, transparent or anonymous.'),
      'company' => $this->t('The company to which the IP address belongs.'),
      'domain' => $this->t('The domain of the IP address.'),
      'tunnel' => $this->t('Indicates if the IP address is a Teredo or 6to4 address. The possible values are teredo or 6to4.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];
    foreach ($this->getAttributes() as $attr => $description) {
      $options[$attr] = $attr;
    }

    $form['info'] = [
      '#markup' => $this->t('The following attributes are available for an IP Address. Possible values for each attribute can be found on the <a href=":url">EdgeScape Data Codes</a> page (login to Akamai Control Center required).', [
        ':url' => 'https://control.akamai.com/wh/CUSTOMER/AKAMAI/en-US/WEBHELP/portal-guides/edgescape/index.html',
      ]),
    ];

    $attr = $this->configuration['attribute'];
    $description = $this->getAttributes()[$attr] ?? '';
    $form['attribute'] = [
      '#type' => 'select',
      '#title' => $this->t('IP Address attributes'),
      '#description' => $description,
      '#options' => $options,
      '#default_value' => $attr,
      '#empty_option' => $this->t('Select an IP attribute'),
      '#empty_value' => '',
    ];

    $rows = count(explode(PHP_EOL, $this->configuration['value'])) + 1;
    $form['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Value'),
      '#description' => $this->t('Enter one value per line.'),
      '#default_value' => $this->configuration['value'],
      '#rows' => $rows,
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['attribute'] = $form_state->getValue('attribute');
    $this->configuration['value'] = $form_state->getValue('value');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'attribute' => '',
      'value' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['attribute']) && !$this->isNegated()) {
      return TRUE;
    }

    $attribute = $this->configuration['attribute'];

    return in_array($this->edgescape($attribute), explode(PHP_EOL, $this->configuration['value']), TRUE);
  }

  /**
   * Get EdgeScape value.
   *
   * Returns the value for the selected IP attribute from Akamai EdgeScape
   * header.
   *
   * @param string $ip_attribute
   *   The IP attribute.
   *
   * @return string|null
   *   Attribute value, or null.
   */
  public function edgescape($ip_attribute): ?string {
    $header = $this->configFactory->get('edgescape.settings')->get('header');
    $attributes = [];
    if (!empty($_SERVER[$header])) {
      $akamai_edgescape = explode(',', $_SERVER[$header]);
      foreach ($akamai_edgescape as $attribute) {
        $attr = explode('=', $attribute);
        if (isset($attr[0], $attr[1])) {
          $attributes[$attr[0]] = $attr[1];
        }
      }
    }

    return $attributes[$ip_attribute] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if ($attribute = $this->configuration['attribute']) {
      $value = implode(', ', explode(PHP_EOL, $this->configuration['value']));

      return $this->t('@attr: @val', [
        '@attr' => $attribute,
        '@val' => $value,
      ]);
    }
    return $this->t('Not enabled.');
  }

}
