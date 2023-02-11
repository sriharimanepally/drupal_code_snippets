<?php

namespace Drupal\custom_user_login_register\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\user\Plugin\LanguageNegotiation\LanguageNegotiationUser;
use Drupal\user\Plugin\LanguageNegotiation\LanguageNegotiationUserAdmin;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\user\Entity\User;
use Drupal\user\RegisterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewUserRegisterForm extends NewAccountForm {

    public function __construct(EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, ModuleHandlerInterface $moduleHandler) {
        $this->setEntity(new User([], 'user'));
        $this->setModuleHandler($moduleHandler);
        parent::__construct($entity_manager, $language_manager, $entity_type_bundle_info, $time);
    }
    /**
     * @inheritdoc
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('entity.manager'),
            $container->get('language_manager'),
            $container->get('entity_type.bundle.info'),
            $container->get('datetime.time'),
            $container->get('module_handler')
        );
    }
    public function form(array $form, FormStateInterface $form_state) {
        $form = parent::form($form, $form_state);

        $form['#attached']['library'][] = 'custom_user_login_register/custom_user_login_register.custom_user_register';

        $form['test'] = [
            '#markup' => '<p>Test extended form</p>',
        ];
        return $form;
    }

}