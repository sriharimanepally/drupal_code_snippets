<?php

namespace Drupal\webformnavigation;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform_submission_log\WebformSubmissionLogManager;

/**
 * Defines a helper class for the webform navigation module.
 */
class WebformNavigationHelper {

  /**
   * Name of the table where log entries are stored.
   */
  const TABLE = 'webformnavigation_log';

  /**
   * Name of the error operation.
   */
  const ERROR_OPERATION = 'errors';

  /**
   * Name of the page visited operation.
   */
  const PAGE_VISITED_OPERATION = 'page visited';

  /**
   * Name of the navigation handler.
   */
  const HANDLER_ID = 'webform_navigation';

  /**
   * The temp_store key.
   */
  const TEMP_STORE_KEY = 'webformnavigation_errors';

  /**
   * The webform submission log manager.
   *
   * @var \Drupal\webform_submission_log\WebformSubmissionLogManager
   */
  protected WebformSubmissionLogManager $webformSubmissionLogManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * The private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected PrivateTempStoreFactory $privateTempStoreFactory;

  /**
   * AutosaveHelper constructor.
   *
   * @param \Drupal\webform_submission_log\WebformSubmissionLogManager $webform_submission_log_manager
   *   The webform submission logger service.
   * @param \Drupal\Core\Database\Connection $datababse
   *   The database service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   The private temp store.
   */
  public function __construct(WebformSubmissionLogManager $webform_submission_log_manager, Connection $datababse, MessengerInterface $messenger, EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder, AccountInterface $current_user, PrivateTempStoreFactory $private_temp_store) {
    $this->webformSubmissionLogManager = $webform_submission_log_manager;
    $this->database = $datababse;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->currentUser = $current_user;
    $this->privateTempStoreFactory = $private_temp_store;
  }

  /**
   * Gets the current submission page.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   *
   * @return string
   *   The current submission page ID.
   */
  public function getCurrentPage(WebformSubmissionInterface $webform_submission) {
    $pages = $webform_submission->getWebform()->getPages('edit', $webform_submission);
    return empty($webform_submission->getCurrentPage()) ? array_keys($pages)[0] : $webform_submission->getCurrentPage();
  }

  /**
   * Has visited page.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   * @param string $page
   *   The page we're checking.
   *
   * @return bool
   *   TRUE if the user has previously visited the page.
   */
  public function hasVisitedPage(WebformSubmissionInterface $webform_submission, $page) {
    // Get outta here if the submission hasn't been saved yet.
    if (empty($webform_submission->id()) || empty($page)) {
      return FALSE;
    }
    $query = $this->database->select(self::TABLE, 'l');
    $query->condition('webform_id', $webform_submission->getWebform()->id());
    $query->condition('sid', $webform_submission->id());
    $query->condition('operation', self::PAGE_VISITED_OPERATION);
    $query->condition('data', $page);
    $query->fields('l', [
      'lid',
      'sid',
      'data',
    ]);
    $submission_log = $query->execute()->fetch();
    return !empty($submission_log);
  }

  /**
   * Gets either all errors or errors for a specific page.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   * @param string|null $page
   *   Set to page name if you only want the data for a particular page.
   *
   * @return array
   *   An array of errors.
   */
  public function getErrors(WebformSubmissionInterface $webform_submission, string $page = NULL) {
    // Get outta here if the submission hasn't been saved yet.
    if (empty($webform_submission->id())) {
      return [];
    }
    $query = $this->database->select(self::TABLE, 'l');
    $query->condition('webform_id', $webform_submission->getWebform()->id());
    $query->condition('sid', $webform_submission->id());
    $query->condition('operation', self::ERROR_OPERATION);
    $query->fields('l', [
      'lid',
      'sid',
      'data',
    ]);
    $query->orderBy('l.lid', 'DESC');
    $query->range(0, 1);
    $submission_log = $query->execute()->fetch();
    $data = !empty($submission_log->data) ? unserialize($submission_log->data) : [];
    // Return the data just for the page if it is requested.
    if (!empty($page)) {
      return !empty($data[$page]) ? $data[$page] : [];
    }
    // Return everything.
    return $data;
  }

  /**
   * Logs the current submission page.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   * @param string $page
   *   The page to log.
   *
   * @throws \Exception
   */
  public function logPageVisit(WebformSubmissionInterface $webform_submission, $page) {
    // Get outta here if the submission hasn't been saved yet.
    if (empty($webform_submission->id())) {
      return;
    }
    // Set the page to the current page if it is empty.
    if (empty($page)) {
      $page = $this->getCurrentPage($webform_submission);
    }
    // Only log the page if they haven't already visited it.
    if (!$this->hasVisitedPage($webform_submission, $page)) {
      $fields = [
        'webform_id' => $webform_submission->getWebform()->id(),
        'sid' => $webform_submission->id(),
        'operation' => self::PAGE_VISITED_OPERATION,
        'handler_id' => self::HANDLER_ID,
        'uid' => $this->currentUser->id(),
        'data' => $page,
        'timestamp' => (string) \Drupal::time()->getRequestTime(),
      ];
      $query = $this->database->insert(self::TABLE, $fields);
      $query->fields($fields)->execute();
    }
  }

  /**
   * Logs the stashed submission errors.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   *
   * @throws \Exception
   */
  public function logStashedPageErrors(WebformSubmissionInterface $webform_submission) {
    $store = $this->privateTempStoreFactory->get('webformnavigation');
    $errors = $store->get(self::TEMP_STORE_KEY);
    // Get outta here if there are not any stashed errors.
    if (empty($errors)) {
      return;
    }
    $prev_errors = $this->getErrors($webform_submission);
    $new_errors = array_merge($prev_errors, $errors);
    // Log the stashed errors.
    $this->logErrors($webform_submission, $new_errors);
    // Clear the stashed errors now that they are logged.
    $store->delete(self::TEMP_STORE_KEY);
  }

  /**
   * Logs the current submission errors.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form's form_state.
   *
   * @throws \Exception
   */
  public function logPageErrors(WebformSubmissionInterface $webform_submission, FormStateInterface $form_state) {
    $form_errors = $form_state->getErrors();
    $current_errors = $this->getErrors($webform_submission);
    $paged_errors = empty($current_errors) ? [] : $current_errors;
    $current_page = $this->getCurrentPage($webform_submission);
    // Let's not create too many logs.
    $this->deleteSubmissionLogs($webform_submission, TRUE);
    // Reset the current page's errors with those set in the form state.
    $paged_errors[$current_page] = [];
    foreach ($form_errors as $element => $error) {
      $base_element = explode('][', $element)[0];
      $page = $this->getElementPage($webform_submission->getWebform(), $base_element);
      // Place error on current page if the page is empty.
      if (!empty($page) && is_string($page)) {
        $paged_errors[$page][$element] = $error;
        // Log the page visit if needed.
        $this->logPageVisit($webform_submission, $current_page);
      }
      else {
        $paged_errors[$current_page][$element] = $error;
      }
    }
    // Stash the errors and return if the submission hasn't been created yet.
    if (empty($webform_submission->id())) {
      $store = $this->privateTempStoreFactory->get('webformnavigation');
      $store->set(self::TEMP_STORE_KEY, $paged_errors);
      return;
    }
    $this->logErrors($webform_submission, $paged_errors);
  }

  /**
   * Logs errors.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   * @param array $errors
   *   Array of errors to log.
   *
   * @throws \Exception
   */
  public function logErrors(WebformSubmissionInterface $webform_submission, array $errors) {
    // Get outta here if the submission hasn't been saved yet.
    if (empty($webform_submission->id())) {
      return;
    }
    if (!empty($errors)) {
      $fields = [
        'webform_id' => $webform_submission->getWebform()->id(),
        'sid' => $webform_submission->id(),
        'operation' => self::ERROR_OPERATION,
        'handler_id' => self::HANDLER_ID,
        'uid' => $this->currentUser->id(),
        'data' => serialize($errors),
        'timestamp' => (string) \Drupal::time()->getRequestTime(),
      ];
      $this->database->insert(self::TABLE)->fields($fields)->execute();
    }
  }

  /**
   * Delete submission logs.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission entity.
   * @param bool $keep_visited
   *   Set to TRUE if you would like to keep the page visited logs.
   */
  public function deleteSubmissionLogs(WebformSubmissionInterface $webform_submission, bool $keep_visited = FALSE) {
    // Get outta here if the submission hasn't been saved yet.
    if (empty($webform_submission->id())) {
      return;
    }
    $query = $this->database->delete(self::TABLE);
    $query->condition('webform_id', $webform_submission->getWebform()->id());
    $query->condition('sid', $webform_submission->id());
    if ($keep_visited) {
      $query->condition('operation', self::PAGE_VISITED_OPERATION, '!=');
    }
    $query->execute();
  }

  /**
   * Gets a page an element is located at.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   A webform entity.
   * @param string $element
   *   The element's key.
   *
   * @return mixed
   *   A page an element belongs to.
   */
  public function getElementPage(WebformInterface $webform, string $element) {
    $element = $webform->getElement($element);
    return !empty($element) && array_key_exists('#webform_parents', $element) ? $element['#webform_parents'][0] : NULL;
  }

  /**
   * Validates all pages within a submission.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param array $form
   *   The current form.
   *
   * @throws \Exception
   */
  public function validateAllPages(WebformSubmissionInterface $webform_submission, FormStateInterface $form_state, array $form) {
    // Get outta here if we are already validating the form.
    if ($form_state->get('validating') == TRUE) {
      return;
    }
    $form_state->set('validating', TRUE);
    $errors = [];
    $current_page = $this->getCurrentPage($webform_submission);
    // Validate the submission.
    foreach ($webform_submission->getWebform()->getPages() as $page_name => $page) {
      if ($page_name != 'webform_confirmation' && $this->hasAccessToPage($page_name, $webform_submission)) {
        $form_state->set('current_page', $page_name);
        $this->logPageVisit($webform_submission, $page_name);
        $new_errors = $this->validateSubmission($webform_submission, $form, $form_state);
        if (!empty($new_errors)) {
          $errors = array_merge($errors, $new_errors);
        }
      }
    }

    // Set and log the errors.
    if (!empty($errors)) {
      foreach ($errors as $name => $error) {
        $form_state->setErrorByName($name, $error);
      }
      $this->logPageErrors($webform_submission, $form_state);
    }
    // Reset the form state to its original settings.
    $form_state->set('current_page', $current_page);
    $form_state->set('validating', FALSE);
  }

  /**
   * Validates a submission.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission.
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   An array of errors.
   */
  private function validateSubmission(WebformSubmissionInterface $webform_submission, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\webform\WebformSubmissionForm $form_object */
    $form_object = $this->entityTypeManager->getFormObject('webform_submission', 'edit');
    $form_object->copyFormValuesToEntity($webform_submission, $form, $form_state);
    $form_object->setEntity($webform_submission);

    // Create an empty form state which will be populated when the submission
    // form is submitted.
    $new_form_state = new FormState();

    // Set the triggering element to an empty element to prevent
    // errors from managed files.
    // @see \Drupal\file\Element\ManagedFile::validateManagedFile
    $new_form_state->setTriggeringElement(['#parents' => []]);

    // Get existing error messages.
    $error_messages = $this->messenger->messagesByType(MessengerInterface::TYPE_ERROR);

    // Submit the form.
    $this->formBuilder->submitForm($form_object, $new_form_state);

    // Get the errors.
    $errors = $new_form_state->getErrors();

    // Delete all form related error messages.
    $this->messenger->deleteByType(MessengerInterface::TYPE_ERROR);

    // Restore existing error message.
    foreach ($error_messages as $error_message) {
      $this->messenger->addError($error_message);
    }

    // Return the errors.
    return $errors ?? [];
  }

  /**
   * Checks to see if the current page is available.
   *
   * @param string $page
   *   The page in question.
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   The webform submission in question.
   *
   * @return bool
   *   True if the page is available.
   */
  public function hasAccessToPage(string $page, WebformSubmissionInterface $webform_submission) {
    $webform = $webform_submission->getWebform();
    $pages = $webform->getPages('edit', $webform_submission);
    return in_array($page, array_keys($pages));
  }

}
