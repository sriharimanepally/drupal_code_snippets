<?php

namespace Drupal\webomelette_ajax_table\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

class TableForm extends FormBase implements TrustedCallbackInterface {

    public function getFormId()
    {
        return 'table_form';
    }
    
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        
        $form['#id'] = $form['#id'] ?? Html::getId('test');

        $rows = [];

        for ($i=0; $i <= 3; $i++) { 
            $row = [
                $this->t($i.' Row label'),
                []
            ];
            $rows[] = $row;
        }

        for ($j=0; $j <= 3; $j++) { 
            $form['buttons'][] = [
                [
                '#type' => 'button',
                '#value' => $this->t($j.' Edit'),
                '#submit' => [
                    [$this, 'editButtonSubmit'],
                ],
                '#executes_submit_callback' => TRUE,
                // Hardcoding for now as we have only one row.
                '#edit' => $j,
                '#ajax' => [
                    'callback' => [$this, 'ajaxCallback'],
                    'wrapper' => $form['#id'],
                ]
                ],
            ];
        }

        $form['table'] = [
            '#type' => 'table',
            '#rows' => $rows,
            '#header' => [$this->t('Title'), $this->t('Operations')],
        ];

        $form['#pre_render'] = [
            [$this, 'preRenderForm'],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;

    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function editButtonSubmit(array &$form, FormStateInterface $form_state) {
        $element = $form_state->getTriggeringElement();
        $form_state->set('edit', $element['#edit']);
        $form_state->setRebuild();
    }

    /**
     * {@inheritdoc}
     */
    public function ajaxCallback(array &$form, FormStateInterface $form_state) {
        $response = new AjaxResponse();
        $response->addCommand(new OpenModalDialogCommand("Success!", 'The table has been submitted.', ['width' => 800]));
        return $response;
    }

    /**
     * Prerender callback for the form.
     *
     * Moves the buttons into the table.
     *
     * @param array $form
     *   The form.
     *
     * @return array
     *   The form.
     */
    public function preRenderForm(array $form) {
        foreach (Element::children($form['buttons']) as $child) {
        // The 1 is the cell number where we insert the button.
        $form['table']['#rows'][$child][1] = [
            'data' => $form['buttons'][$child]
        ];
        unset($form['buttons'][$child]);
        }
    
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public static function trustedCallbacks() {
        return ['preRenderForm'];
    }

}
