<?php

namespace Drupal\boleta\Form;

use Drupal\Core\Form\FormBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Utility\Unicode;
use Drupal\views\Views;
use Drupal\Core\Url;


/**
 * Implements an CargarBoleta form.
 */
class CargarBoletaForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cargar_boleta_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $boletaId = 0) 
  {
    $storage = &$form_state->getStorage(); 
    
    $storage['boletaId'] = $boletaId;
    $boleta = Node::load($boletaId);

    $form['#attributes']['class'][] = 'node-form';
    
    if ($boleta->field_estado->entity->machine_name->value != 'borrador')
    {
      $this->buildRedireccion($form, $form_state, $boleta);
    }
    else
    {  
      $this->buildTitulo($form, $form_state, $boleta);
  
      $this->buildEncabezado($form, $form_state, $boleta);
  
      $this->buildDetallesAgregar($form, $form_state, $boleta);
  
      $this->buildDetallesItems($form, $form_state, $boleta);
  
      $this->buildCategoriasJornales($form, $form_state, $boleta);
    }

    return $form;
  }

  private function buildRedireccion(array &$form, FormStateInterface $form_state, $boleta) 
  {
    $usuario = User::load(\Drupal::currentUser()->id());

    $form['titulo'] = [
      '#markup' => t('<h2>La Boleta ya fue impresa, por lo cual no se puede modificar.</h2>', [
        '%empleador' => $boleta->field_empleador->entity->title->value, 
        ]),
      '#weight' => -1000,
    ];

    $form['imprimir'] = [
      '#type' => 'link',
      '#title' => t('Imprimir'), // <i class="fas fa-user-tie"></i></i> Empleados
      '#url' => Url::fromRoute("boleta.imprimir", ['boletaId' => $boleta->id()]),
      '#attributes' => [
        'class' => ['btn btn-lg btn-primary icon-before m-3'],
        'target' => '_blank',
      ],
      '#weight' => -1000,
    ];

    // Define a que vista dirije según el rol del usuario.
    $viewSufijo = ($usuario->hasRole('empleador') || $usuario->hasRole('gestor')) ? '_del_usuario' : '';

    $form['boletas'] = [
      '#type' => 'link',
      '#title' => t('Boletas'), // <i class="fas fa-user-tie"></i></i> Empleados
      '#url' => Url::fromRoute("view.listado_de_boletas_de_empleador$viewSufijo.page_1", ['arg_0' => $boleta->field_empleador->target_id]),
      '#attributes' => ['class' => ['btn btn-lg btn-secondary icon-before m-3']],
      '#weight' => -1000,
    ];
    
    $form_state->setRedirect("view.listado_de_boletas_de_empleador$viewSufijo.page_1", ['arg_0' => $boleta->field_empleador->target_id]);
  }

  private function buildTitulo(array &$form, FormStateInterface $form_state, $boleta) 
  {
    $usuario = User::load(\Drupal::currentUser()->id());

    // Define a que vista dirije según el rol del usuario.
    $viewSufijo = ($usuario->hasRole('empleador') || $usuario->hasRole('gestor')) ? '_del_usuario' : '';

    $form['boletas'] = [
      '#type' => 'link',
      '#title' => t('Boletas'), // <i class="fas fa-user-tie"></i></i> Empleados
      '#url' => Url::fromRoute("view.listado_de_boletas_de_empleador$viewSufijo.page_1", ['arg_0' => $boleta->field_empleador->target_id]),
      '#attributes' => ['class' => ['btn btn-sm btn-secondary icon-before float-right mt-2 mb-2 ml-1 mr-3']],
      '#weight' => -1010,
    ];
    
    $form['empleados'] = [
      '#type' => 'link',
      '#title' => t('Empleados'), // <i class="fas fa-user-tie"></i></i> Empleados
      '#url' => Url::fromRoute("view.listado_de_empleados_de_empleador$viewSufijo.page_1", ['arg_0' => $boleta->field_empleador->target_id]),
      '#attributes' => ['class' => ['btn btn-sm btn-secondary icon-before float-right mt-2 mb-2 ml-3']],
      '#weight' => -1010,
    ];

    $msg = 'ATENCION\n\nAl Imprimir la Boleta ya no podrá realizarle cambios posteriormente.\n\n' .
      'Si necesita continuar modificándola, cancele esta acción y elija Previsualizar.\n\n' .
      '¿Confirma que desea Imprimir la Boleta?';

    $form['imprimir'] = [
      '#type' => 'link',
      '#title' => t('Imprimir'), // <i class="fas fa-user-tie"></i></i> Empleados
      '#url' => Url::fromRoute("boleta.imprimir", ['boletaId' => $boleta->id()]),
      '#attributes' => [
        'class' => ['btn btn-sm btn-primary icon-before float-right mt-2 mb-2 ml-1'],
        'target' => '_blank',
        'onclick' => 'return (confirm("'.$msg.'"));',
      ],
      '#weight' => -1010,
    ];

    $form['previsualizar'] = [
      '#type' => 'link',
      '#title' => t('Previsualizar'), // <i class="fas fa-user-tie"></i></i> Empleados
      '#url' => Url::fromRoute("boleta.previsualizar", ['boletaId' => $boleta->id()]),
      '#attributes' => [
        'class' => ['btn btn-sm btn-primary icon-before float-right mt-2 mb-2 ml-3'],
        'target' => '_blank',
      ],
      '#weight' => -1010,
    ];

    $form['importar'] = [
      '#type' => 'link',
      '#title' => t('Importar Items'), // . '<i class="fas fa-file-upload">',
      '#url' => Url::fromRoute('node.add', ['node_type'=> 'importacion_boleta'], ['query' => ['boleta_id' => $boleta->id()]]),
      '#attributes' => ['class' => ['btn btn-sm btn-success icon-before float-right mt-2 mb-2']],
      '#weight' => -1010,
    ];

    $form['titulo'] = [
      '#markup' => t('<h2>Cargar Boleta a %empleador</h2>', [
        '%empleador' => $boleta->field_empleador->entity->title->value, 
        ]),
      '#weight' => -1000,
    ];
  }

  private function buildEncabezado(array &$form, FormStateInterface $form_state, $boleta) 
  {
    // ENCABEZADO DE BOLETA
    $form['details_head']['#type'] = 'details';
    $form['details_head']['#title'] = 'Encabezado de la Boleta';
    $form['details_head']['#weight'] = 10;
    $form['details_head']['#open'] =true;
    $form['details_head']['#prefix'] = '<div id="wrap-encabezado-items">';
    $form['details_head']['#suffix'] = '</div>';  

    $form['details_head']['row'] = [
      '#type' => 'container',
      '#weight' => 1,
      '#attributes' => ['class' => ['row']],
    ];

    $periodos = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'periodo',
      'field_estado.entity.machine_name.value' => 'abierto' 
    ]);

    $options = [];
    $default = null;
    
    foreach ($periodos as $p) 
    {   
      $options[$p->id()] = $p->label();
    }

    $form['details_head']['row']['field_periodo'] = [
      '#type' => 'select',
      '#title' => t('Período'),
      '#required' => true,
      // '#empty_option' => '- Cualquiera -',
      '#options' => $options,
      '#default_value' => $boleta->field_periodo->target_id,
      '#wrapper_attributes' => ['class' => ['col-md-2 wrap-periodo']],
      '#validate' => ['::periodoValidate'],
      '#ajax' => ['event' => 'change', 'disable-refocus' => true, 'progress' => ['type' => 'none']],
    ];

    $form['details_head']['row']['field_numero'] = [
      '#type' => 'textfield',
      '#title' => t('Número'),
      '#required' => true,
      '#default_value' => $boleta->field_numero->value,
      '#attributes' => array('readonly' => 'readonly'),
      '#wrapper_attributes' => ['class' => ['col-md-2 wrap-numero']],
    ];  

    $form['details_head']['row']['field_fecha'] = [
      '#type' => 'textfield',
      '#title' => t('Fecha'),
      '#required' => true,
      '#default_value' => $boleta->field_fecha->date->format('m/d/Y'),
      '#attributes' => array('readonly' => 'readonly'),
      '#wrapper_attributes' => ['class' => ['col-md-2 wrap-fecha']],
    ]; 

    $form['details_head']['row']['field_tipo'] = [
      '#type' => 'textfield',
      '#title' => t('Tipo'),
      '#required' => true,
      '#default_value' => $boleta->field_tipo->entity->name->value,
      '#attributes' => array('readonly' => 'readonly'),
      '#wrapper_attributes' => ['class' => ['col-md-2 wrap-tipo']],
    ]; 

    $form['details_head']['row']['field_convenio'] = [
      '#type' => 'textfield',
      '#title' => t('Convenio'),
      '#required' => true,
      '#default_value' => $boleta->field_convenio->entity->title->value,
      '#attributes' => array('readonly' => 'readonly'),
      '#wrapper_attributes' => ['class' => ['col-md-2 wrap-convenio']],
    ]; 

    // $form['details_head']['row']['field_total'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Total'),
    //   '#required' => true,
    //   '#default_value' => $boleta->field_total->value,
    //   '#attributes' => array('readonly' => 'readonly'),
    //   '#wrapper_attributes' => ['class' => ['col-md-2 wrap-total']],
    // ];  

    // $form['details_head']['row']['field_estado'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Estado'),
    //   '#required' => true,
    //   '#default_value' => $boleta->field_estado->entity->name->value,
    //   '#attributes' => array('readonly' => 'readonly'),
    //   '#wrapper_attributes' => ['class' => ['col-md-2 wrap-estado']],
    // ]; 
  }
   
  private function buildDetallesAgregar(array &$form, FormStateInterface $form_state, $boleta) 
  {
    // DETALLE DE BOLETA
    $form['details_items']['#type'] = 'details';
    $form['details_items']['#title'] = 'Items de la Boleta';
    $form['details_items']['#weight'] = 20;
    $form['details_items']['#open'] =true;
    $form['details_items']['#prefix'] = '<div id="wrap-details-items">';
    $form['details_items']['#suffix'] = '</div>';  
  
    // $form['details_items']['messages'] = [
    //   '#type' => 'status_messages',
    //   '#weight' => 1,
    // ];

    $form['details_items']['agregar'] = [
      '#type' => 'container',
      '#weight' => 1,
      '#attributes' => ['class' => ['row']],
    ];

    $options = $this->empleadosOpciones($boleta);

    // Agrego el select para elegir empleado a agregar.
    $form['details_items']['agregar']['field_agregar_empleado'] = [
      '#type' => 'select2',
      '#title' => t('Empleado a agregar'),
      '#options' => $options,
      // '#multiple' => true,
      '#default_value' => array_key_first($options),    // Hay que forzar con #value a que cambie el valor al primero disponible, el anterior ya no esta disponible.
      '#required' => false,
      '#wrapper_attributes' => ['class' => ['col-6 wrap-agregar-empleado']],
    ];

    $form['details_items']['agregar']['field_agregar_monto'] = [
      '#type' => 'textfield',
      '#title' => ($boleta->field_tipo->entity->machine_name->value == 'mensual') ? t('Total Remunerativo') : t('Cantidad de Jornales'),
      '#required' => false,
      // '#default_value' => 0,
      // '#pattern' => '[0-9]*.*[0-9][0-9]',
      '#wrapper_attributes' => ['class' => ['col-2 wrap-agregar-monto']],
    ];

    $form['details_items']['agregar']['accion_agregar_agregar'] = [
      '#type' => 'submit',
      '#name' => 'agregar',
      '#value' => t('Agregar ítem'),
      '#validate' => ['::agregarValidate'],
      '#ajax' => [
        'callback' =>  '::agregarAjax',
        'event' => 'click', 'disable-refocus' => true, 'progress' => ['type' => 'none'],
        'wrapper' => 'wrap-details-items',
      ],
      '#prefix' => '<div class="col-2 wrap-agregar-agregar">',
      '#suffix' => '</div>',
    ];
  }

  private function buildDetallesItems(array &$form, FormStateInterface $form_state, $boleta) 
  {
    $tipoBoleta = $boleta->field_tipo->entity->machine_name->value;
    // Agrego la tabla de items.
    if ($tipoBoleta == 'mensual')
    {
      // $header = ['', 'Empleado', 'Categoría', 'Modalidad', 'Total Remunerativo', 'Porcentaje', 'Subtotal'];
      $header = ['', 'Empleado', 'Categoría', 'Modalidad', 'Remunerativo'];
    }
    else
    {
      // $header = ['', 'Empleado', 'Jornales', 'Monto Diario', 'Subtotal'];
      $header = ['', 'Empleado', 'Jornales'];
    }
    
    $form['details_items']['items'] = [
      '#type' => 'table',
      '#weight' => 10,
      '#responsive' => true,
      '#attributes' => ['class' => ['table', 'table-bordered', 'field-items']],
      // '#caption' => 'Items de la boleta',
      '#header' => $header,
      '#rows' => [],
      '#empty' => t('No hay registros.'),
    ];

    // Busco los Items de la Boleta.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
     
    $query->condition('type', 'boleta_item')
      ->condition('field_boleta', $boleta->id())
      ->sort('field_empleado.entity.field_persona.entity.field_razon_social.value', 'ASC')
      ->pager(10)
      ->accessCheck(FALSE);
  
    $ids = $query->execute();

    if (!empty($ids))
    {
      $items = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids); 

      foreach ($items as $i => $item)
      {
        // Agrego el boton para eliminar el item.
        $form['details_items']['items'][$i]['accion_eliminar'] = [
          '#type' => 'submit',
          '#name' => "items-$i-accion-eliminar",
          '#value' => t('Eliminar'),
          '#attributes' => ['class' => ['btn-sm']],
          '#validate' => ['::accionValidate'],
          '#ajax' => [
            'callback' =>  '::accionAjax',
            'event' => 'click', 'disable-refocus' => true, 'progress' => ['type' => 'none'],
            'wrapper' => 'wrap-details-items',
          ],
          '#wrapper_attributes' => ['class' => ['wrap-eliminar']],
        ];

        $form['details_items']['items'][$i]['field_empleado_text'] = [
          '#type' => 'textfield',
          '#required' => true,
          '#default_value' => $item->field_empleado->entity->title->value,
          '#attributes' => array('readonly' => 'readonly'),
          '#wrapper_attributes' => ['class' => ['wrap-empleado-text']],
        ];

        if ($tipoBoleta == 'mensual')
        {
          $form['details_items']['items'][$i]['field_categoria'] = [
            '#type' => 'select',
            '#required' => true,
            // '#empty_option' => '- Cualquiera -',
            '#options' => $this->categoriasOpciones($boleta),
            '#default_value' => $item->field_categoria->target_id,
            '#validate' => ['::itemValidate'],
            '#ajax' => ['event' => 'change', 'disable-refocus' => true, 'progress' => ['type' => 'none']],
            '#wrapper_attributes' => ['class' => ['wrap-categoria']],
          ];

          $form['details_items']['items'][$i]['field_modalidad'] = [
            '#type' => 'select',
            '#required' => true,
            // '#empty_option' => '- Cualquiera -',
            '#options' => $this->modalidadesOpciones($boleta),
            '#default_value' => $item->field_modalidad->target_id,
            '#validate' => ['::itemValidate'],
            '#ajax' => ['event' => 'change', 'disable-refocus' => true, 'progress' => ['type' => 'none']],
            '#wrapper_attributes' => ['class' => ['wrap-modalidad']],
          ];

          // $form['details_items']['items'][$i]['field_porcentaje'] = [
          //   '#type' => 'textfield',
          //   '#required' => true,
          //   '#value' => $item['field_porcentaje'],
          //   '#attributes' => array('readonly' => 'readonly'),
          //   '#wrapper_attributes' => ['class' => ['wrap-porcentaje']],
          // ];
        }
        else
        {
          // $form['details_items']['items'][$i]['field_monto_diario'] = [
          //   '#type' => 'textfield',
          //   '#required' => true,
          //   '#value' => $item['field_monto_diario'],
          //   '#attributes' => array('readonly' => 'readonly'),
          //   '#wrapper_attributes' => ['class' => ['wrap-monto-diario']],
          // ];
        }

        $form['details_items']['items'][$i]['field_monto'] = [
          '#type' => 'textfield',
          '#required' => true,
          '#default_value' => $item->field_monto->value,
          // '#pattern' => $tipoBoleta == 'mensual' ? '[0-9]*.[0-9]+[0-9]+' : '[0-9]*',
          '#validate' => ['::itemValidate'],
          '#ajax' => ['event' => 'change', 'refocus' => false, 'disable-refocus' => true, 'progress' => ['type' => 'none']],
          '#wrapper_attributes' => ['class' => ['wrap-monto']],
        ]; 
      }

      $form['details_items']['pager'] = [
        '#type' => 'pager',
        // '#quantity' => 9,
        '#weight' => 15,
      ];
    }
  }

  public function periodoValidate(array &$form, FormStateInterface $form_state) 
  {
    $storage = &$form_state->getStorage(); 

    $boletaId = $storage['boletaId'];
    $boleta = Node::load($boletaId);  

    $element = $form_state->getTriggeringElement();
    $value = $element['#value'];

    if (!empty($value) && $element['#name'] == 'field_periodo')
    {
      $boleta->field_periodo->target_id = $value;

      $boleta->save();
    }
  }
  
  public function itemValidate(array &$form, FormStateInterface $form_state) 
  {
    $element = $form_state->getTriggeringElement();
    $value = $element['#value'];

    if (!empty($value))
    {
      $boletaItemId = $element["#parents"][1];
      $fieldName = $element["#parents"][2];

      $boletaItem = Node::load($boletaItemId);  

      if ($fieldName == 'field_categoria')
      {
        $boletaItem->field_categoria->target_id = $value;
      }
      elseif ($fieldName == 'field_modalidad')
      {
        $boletaItem->field_modalidad->target_id = $value;
      }
      elseif ($fieldName == 'field_monto')
      {
        $boletaItem->field_monto->value = $value;
      }

      $boletaItem->save();  
    }
  }

  public function agregarValidate(array &$form, FormStateInterface $form_state) 
  {
    $storage = &$form_state->getStorage(); 

    $boletaId = $storage['boletaId'];

    $boleta = Node::load($boletaId);

    $empleadoId = $form_state->getValue("field_agregar_empleado");
    $monto = $form_state->getValue("field_agregar_monto");

    if (!empty($empleadoId))
    {
      $empleado = Node::load($empleadoId);

      $values = [
        'type' => 'boleta_item',
        'field_boleta' => $boletaId,
        'field_empleado' => $empleadoId,
        'field_categoria' => $empleado->field_categoria->target_id,
        'field_modalidad' => $empleado->field_modalidad->target_id,
        'field_monto' => $monto,
      ];

      $boletaItem = Node::create($values);

      $boletaItem->save();  

      $form_state->setValue("field_agregar_empleado", null);

      $form_state->setRebuild();  
    }
  }

  public function agregarAjax(array &$form, FormStateInterface $form_state) 
  {
    return $form['details_items'];
  }

  public function accionValidate(array &$form, FormStateInterface $form_state) 
  {
    $element = $form_state->getTriggeringElement();
    $value = $element['#value'];

    if (!empty($value))
    {
      $boletaItemId = $element["#parents"][1];
      $accionName = $element["#parents"][2];

      $boletaItem = Node::load($boletaItemId);

      if ($accionName == 'accion_eliminar')
      {
        $boletaItem->delete();  
      }

      $form_state->setRebuild();  
    }
  }

  public function accionAjax(array &$form, FormStateInterface $form_state) 
  {
    return $form['details_items'];
  }

  private function empleadosOpciones($boleta)
  {
    // Busco todos los Empleados que estan en la Boleta.
    $items = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'boleta_item',
      'field_boleta.target_id' => $boleta->id()
    ]);

    $empleadoIdsBoleta = [];

    foreach ($items as $item)
    {
      $empleadoIdsBoleta[] = $item->field_empleado->target_id;
    }

    // Busco los Empleados del Empleador que aún no estan en la Boleta.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();

    $query->condition('type', 'empleado')
      ->condition('field_empleador', $boleta->field_empleador->target_id)
      ->condition('field_estado.entity.machine_name.value', 'activo')
      ->sort('field_persona.entity.field_razon_social.value');

    if (!empty($empleadoIdsBoleta))
    {
      $query->condition('nid', $empleadoIdsBoleta, 'NOT IN');
    }

    $ids = $query->execute();

    $entities = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);

    $options = [];

    foreach ($entities as $e) 
    {   
      $options[$e->id()] = $e->label();
    }

    return $options;
  }

  private function categoriasOpciones($boleta)
  {
    $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'categoria',
      'field_convenio' => $boleta->field_convenio->target_id,
      'field_estado.entity.machine_name.value' => 'activo' 
    ]);
    
    $options = [];
    
    foreach ($entities as $e) 
    {   
      $options[$e->id()] = $e->label();
    }

    return $options;
  }

  private function modalidadesOpciones($boleta)
  {
    $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'modalidad',
      'field_estado.entity.machine_name.value' => 'activo' 
    ]);
    
    $options = [];
    // $options[''] = '- Cualquiera -';
    $default = null;
    
    foreach ($entities as $e) 
    {   
      $options[$e->id()] = $e->label();
    }

    return $options;
  }

  private function buildCategoriasJornales(&$form, FormStateInterface $form_state, $boleta)
  {
    if ($boleta->field_tipo->entity->machine_name->value == 'mensual')
    {
      $view = Views::getView('listado_de_categorias_por_jornales');
      $view->setDisplay('block_1');
      $view->setArguments([$boleta->field_convenio->target_id]);

      $form['categorias']['#type'] = 'details';
      $form['categorias']['#title'] = 'Ejemplos de Totales Remunerativos, según Categorias y Jornales';
      $form['categorias']['#weight'] = 52;
      $form['categorias']['#open'] = false;

      $form['list']['#title'] = false;
      $form['categorias']['list'] = $view->render();
    }
  }

  /**
   * {@inheritdoc}
   */
  // public function validateForm(array &$form, FormStateInterface $form_state) {
  //   if (strlen($form_state->getValue('phone_number')) < 3) {
  //     $form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
  //   }
  // }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $this->messenger()->addStatus($this->t('Your phone number is @number', ['@number' => $form_state->getValue('phone_number')]));
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  // public function access(AccountInterface $account, $boletaId = 0) {
  //   // Check permissions and combine that with any custom access checking needed. Pass forward
  //   // parameters from the route and/or request as needed.
  //   return AccessResult::allowedIf($account->hasPermission('do example things')); // && $this->someOtherCustomCondition());
  // }
}
