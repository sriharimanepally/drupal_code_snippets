<?php

namespace Drupal\webformnavigation\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform navigation handler.
 *
 * @WebformHandler(
 *   id = "webform_navigation",
 *   label = @Translation("Webform Navigation"),
 *   category = @Translation("Webform"),
 *   description = @Translation("A webform submission handler for the webform navigation module."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class WebformNavigationHandler extends WebformHandlerBase {

  /**
   * The token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * The Webform Navigation Helper.
   *
   * @var \Drupal\webformnavigation\WebformNavigationHelper
   */
  protected $webformNavigationHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->tokenManager = $container->get('webform.token_manager');
    $instance->webformNavigationHelper = $container->get('webformnavigation.helper');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'debug' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Development.
    $form['development'] = [
      '#type' => 'details',
      '#title' => $this->t('Development settings'),
    ];
    $form['development']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debugging'),
      '#description' => $this->t('If checked, every handler method invoked will be displayed onscreen to all users.'),
      '#return_value' => TRUE,
      '#default_value' => $this->configuration['debug'],
    ];

    return $this->setSettingsParents($form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['debug'] = (bool) $form_state->getValue('debug');
  }

  /**
   * {@inheritdoc}
   */
  public function alterElements(array &$elements, WebformInterface $webform) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function overrideSettings(array &$settings, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
    // Log the current page.
    $current_page = $this->webformNavigationHelper->getCurrentPage($webform_submission);
    $webform = $webform_submission->getWebform();
    // Get navigation webform settings.
    $forward_navigation = $webform->getThirdPartySetting('webformnavigation', 'forward_navigation');
    // Actions to perform if forward navigation is enabled and there are pages.
    if ($forward_navigation && $webform->hasWizardPages()) {
      $validations = [
        '::validateForm',
        '::draft',
      ];
      // Allow forward access to all but the confirmation page.
      $pages = $webform->getPages('edit', $webform_submission);
      if (!empty($pages)) {
        foreach ($pages as $page_key => $page) {
          // Allow user to access all but the confirmation page.
          if ($page_key != 'webform_confirmation') {
            $form['pages'][$page_key]['#access'] = TRUE;
            $form['pages'][$page_key]['#validate'] = $validations;
            $form['pages'][$page_key]['#attributes']['formnovalidate'] = 'formnovalidate';
          }
        }
      }

      // Add a logger to the prev validators.
      if (isset($form['actions']['wizard_prev'])) {
        $form['actions']['wizard_prev']['#validate'] = $validations;
        $form['actions']['wizard_prev']['#attributes']['formnovalidate'] = 'formnovalidate';
      }

      // Add a logger to the draft validators.
      if (isset($form['actions']['draft'])) {
        $form['actions']['draft']['#validate'] = $validations;
      }
      // Log the page visit.
      $visited = $this->webformNavigationHelper->hasVisitedPage($webform_submission, $current_page);
      // Log the page if it has not been visited before.
      if (!$visited) {
        $this->webformNavigationHelper->logPageVisit($webform_submission, $current_page);
      }
      if ($current_page != 'webform_confirmation') {
        // Display any errors.
        $errors = $this->webformNavigationHelper->getErrors($webform_submission, $current_page);
        // Make sure we show the errors for the page.
        if (!empty($errors)) {
          foreach ($errors as $error) {
            $this->messenger()->addError($error);
          }
        }
      }
    }
    // Bypass validation of the next click.
    $prevent_next_validation = $webform->getThirdPartySetting('webformnavigation', 'prevent_next_validation');

    // Actions to perform if prevent_next_validation is set.
    if ($prevent_next_validation && isset($form['actions']['wizard_next'])) {
      $form['actions']['wizard_next']['#validate'] = [
        '::validateForm',
        '::draft',
      ];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
    $webform = $webform_submission->getWebform();
    // Get navigation webform settings.
    $forward_navigation = $webform->getThirdPartySetting('webformnavigation', 'forward_navigation');
    // Actions to perform if forward navigation is enabled and there are pages.
    if ($forward_navigation && $webform->hasWizardPages()) {
      $triggering_element = $form_state->getTriggeringElement();
      // Log the current page errors.
      $this->webformNavigationHelper->logPageErrors($webform_submission, $form_state);
      // Validate everything on the final submit.
      if (isset($triggering_element['#validate']) && in_array('::complete', $triggering_element['#validate'])) {
        $this->webformNavigationHelper->validateAllPages($webform_submission, $form_state, $form);
        $logged_errors = $this->webformNavigationHelper->getErrors($webform_submission);
        if (!empty($logged_errors)) {
          $form_state->clearErrors();
          foreach ($logged_errors as $page_name => $errors) {
            if (!empty($errors) && $this->webformNavigationHelper->hasAccessToPage($page_name, $webform_submission)) {
              if ($page = $webform->getPage('edit', $page_name)) {
                $form_state->setErrorByName($page_name, [
                  '#theme' => 'item_list',
                  '#items' => $errors,
                  '#title' => $this->t('@title Page', [
                    '@title' => $page['#title'],
                  ]),
                ]);
              }
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
    $triggering_element = $form_state->getTriggeringElement();
    if (isset($triggering_element['#page'])) {
      $form_state->set('current_page', $triggering_element['#page']);
      $webform_submission->setCurrentPage($triggering_element['#page']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preCreate(array &$values) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postLoad(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preDelete(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postDelete(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
    // Clear the submission's logs when a submission is deleted.
    $this->webformNavigationHelper->deleteSubmissionLogs($webform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->debug(__FUNCTION__, $update ? 'update' : 'insert');
    $webform = $webform_submission->getWebform();
    // Get navigation webform settings.
    $forward_navigation = $webform->getThirdPartySetting('webformnavigation', 'forward_navigation');
    // Log the initial page if this is an insert.
    if (!$update && $forward_navigation && $webform->hasWizardPages()) {
      $pages = $webform->getPages('add', $webform_submission);
      // Log the first page.
      $this->webformNavigationHelper->logPageVisit($webform_submission, array_keys($pages)[0]);
      // Log any stashed errors.
      $this->webformNavigationHelper->logStashedPageErrors($webform_submission);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessConfirmation(array &$variables) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function createHandler() {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function updateHandler() {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteHandler() {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function createElement($key, array $element) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function updateElement($key, array $element, array $original_element) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteElement($key, array $element) {
    $this->debug(__FUNCTION__);
  }

  /**
   * Display the invoked plugin method to end user.
   *
   * @param string $method_name
   *   The invoked method name.
   * @param string $context1
   *   Additional parameter passed to the invoked method name.
   */
  protected function debug($method_name, $context1 = NULL) {
    if (!empty($this->configuration['debug'])) {
      $t_args = [
        '@id' => $this->getHandlerId(),
        '@class_name' => get_class($this),
        '@method_name' => $method_name,
        '@context1' => $context1,
      ];
      $this->messenger()->addWarning($this->t('Invoked @id: @class_name:@method_name @context1', $t_args), TRUE);
    }
  }

}
