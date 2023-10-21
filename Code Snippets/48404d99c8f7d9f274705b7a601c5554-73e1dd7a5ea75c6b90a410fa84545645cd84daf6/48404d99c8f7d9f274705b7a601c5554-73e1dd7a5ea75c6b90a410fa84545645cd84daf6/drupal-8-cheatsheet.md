# Drupal 8 Cheatsheet

## Files, Images and Media

```php
// Load file object
$file = File::load($fid);

// Get uri (public://foo/bar/baz.png)
$uri           = $file->getFileUri();

// Get path in FS (/var/www/drupal/sites/default/files/foo.txt)
$path          = drupal_realpath($file->getFileUri());  // Drupal 8.9
$path          = \Drupal::service('file_system')->realpath($file->getFileUri());  // Drupal 9

// Relative path in FS: /sytes/default/files/... (if public://)
$relative_path = file_url_transform_relative(file_create_url($uri));

// Load file content
$contents = file_get_contents($uri);
```

Create a directory in `public://` if it doesn't exist.

```php
$image_dest = 'public://article/images';                                                           
if (!is_dir($image_dest)) {                                                                      
  \Drupal::service('file_system')->mkdir($image_dest, NULL, TRUE);                               
}   
```

Create a file using `FileRepository::writeData` ([docs](https://api.drupal.org/api/drupal/core%21modules%21file%21src%21FileRepository.php/function/FileRepository%3A%3AwriteData/9.3.x)) in Drupal from image data:

```php
use Drupal\Core\File\FileSystemInterface;

$image_url = "https://example.com/images/img.png";
$image_content = file_get_contents($image_url);                                                            
$image_filename = "img.png";

if ($image_content) {
  // D9.x
  $imageFid = \Drupal::service('file.repository')
    ->writeData(
      $image_content,
      "$image_dest/$image_filename",
      FileSystemInterface::EXISTS_REPLACE
    );
  // D8.x
  file_save_data($image_content, "$image_dest/$image_filename", FileSystemInterface::EXISTS_REPLACE);
}
```

Once we have a `FileEntity` (returned by `writeData`) you can create a Media like so:

```php
$data = [
  'bundle' => 'image',
  'name' => 'Title/ALT text form image',
  'field_media_image' => ['target_id' => $imageFid],
];

$mediaEntity = \Drupal::entityManager()
  ->getStorage('media')
  ->create($data);
$mediaEntity->save();
```
### Drupal 8 -> Drupal 9 migration:

- [file_save_data, file_copy and file_move are deprecated and replaced with a service](https://www.drupal.org/node/3223520) - file_save_data, file_copy and file_move are deprecated and replaced with a service that implements \Drupal\file\FileRepositoryInterface.
- [all change records](https://www.drupal.org/list-changes/drupal) - all deprecations are listed here




## Nodes

```php
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

$node = Node::load($nid);

// or, more recomended (https://www.drupal.org/project/drupal/issues/2945539)
$node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

// Taxonmomy term
$term_item = $node->field_supplement_type->getValue();
$term = array_pop($term_item);
$tid = $term['target_id'];

// Load taxonomy term to use data
$supplement = Term::load($tid);

// or, more recomended (https://www.drupal.org/project/drupal/issues/2945539)
$supplement = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);

$item = [
  'nid'          => $nid,
  'title_prefix' => $node->field_supplement_title_prefix->value,
  'title'        => $node->title->value,
  'tid'          => $tid,
  'supplement'   => $supplement->getName(),
  'home_image'   => $this->getImagesCrop(...),
  'cover_image'  => $this->getImagesCrop(...),
  'slug'         => $supplement->field_slug->value,
  'date'         => $node->field_supplement_date->value,
];

// Body
$body_text = $node->body->value;
$body_text = $node->get('body')->value;
$body_array = $node->body->getValue();
$body_text example: '<p>Hello!</p>'
$body_array example: array(
 0 => array(
 'value' => '<p>Hello!</p>',
 'summary' => '',
 'format' => 'basic_html',
) )
$field_item_list = $

// Other alternatives

$node->get('field_slug')->getString();
$node->field_slug->getString();

// Make changes and preserve changed:
$node = Node::load($nid);
$prev_changed = $node->getChangedTime();
$node->title = "New Title";
...
$node->save()

// You need to reload and only modify changed for this to work!!
$node = Node::load($nid);
$node->setChangedTime($prev_changed);
$node->save();
```

Get the path of a node with:

Drupal 8.7 or earlier:

```
$alias = \Drupal::service('path.alias_manager')
  ->getAliasByPath("/node/$nid");
```

Drupal 8.8 or 9 you should use the `path_alias.manager` service:

```
$alias = \Drupal::service('path_alias.manager')
  ->getAliasByPath("/node/$nid");
```
For more information: https://drupal.stackexchange.com/questions/230746/get-path-alias-from-nid-or-node-object and https://www.drupal.org/node/3092086

If you have a `node` object you can use:

```
// Absolute URL
$node->toUrl()->setAbsolute()->toString()

// Relative URL
$node->toUrl()->toString();
```

To search nodes by properties:

```php
$nodes = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties([
    'type' => 'article',
    'title' => 'Some title',
  ]);
```

### Access entity reference fields

In this sample code `$article` is a content type with a `$field_author` field that is an entity reference to a content-type called `author`.

```php

if ($article->field_author->isEmpty()) {
  // No hay autores. Salimos.
  return;
}

$article_authors = [];
foreach($article->field_author as $author) {

  $author_node = $author
    ->get('entity')
    ->getTarget()
    ->getValue();

  $article_authors[] = [
    'id' => $author_node->ID(),
    'title' => $author_node->title->value,
    'field_first_name' => $author_node->field_first_name->value,
    'field_last_name' => $author_node->field_last_name->value,
    'field_slug' => $author_node->field_slug->value
  ];
}
```

or better yet:

```php
$authors = $article->get('field_author')->referencedEntities();
foreach($authos as $author) {
  $article_authors[] = [
    'id' => $author->ID(),
    // ...
  ];
}
```

### Creating nodes

```php
    // ...

    // Assuming the image is already uploaded to your server:
    $fid = '123'; // The fid of the image you are going to use.
    $image_media = Media::create([
      'bundle' => 'your_image_bundle_name_here',
      'uid' => '1',
      'langcode' => Language::LANGCODE_DEFAULT,
      'status' => Media::PUBLISHED,
      'your_image_field_name_here' => [
        'target_id' => $fid,
        'alt' => t('foo'),
        'title' => t('bar'),
      ],
    ]);
    $image_media->save();

    // Then do the same for the video media entity.
    // $video_media = ... ->save();

    $node = Node::create([
      // The node entity bundle.
      'type' => 'article',
      'langcode' => 'en',
      'created' => $created_date,
      'changed' => $created_date,
      // The user ID.
      'uid' => 1,
      'moderation_state' => 'published',
      'title' => $title,
      'field_article_section' => ['target_id' => $section_target_id],
      'field_author' => 111,
      'field_article_main_image' => ['target_id' => $image_media->id()],
      'field_article_main_video' => array(
        // ... the same as before
      ),
      'field_article_body_summary' => [
        'summary' => substr(strip_tags($text), 0, 100),
        'value' => $text,
        'format' => 'rich_text'
      ]
    ]);
    $node->save();
    $new_nid = $node->id();
```

References
- https://www.drupal.org/project/media_entity/issues/2813025
- https://codimth.com/blog/web/drupal/how-create-nodes-programmatically-drupal-8
- [Working with the Entity API | Entity API | Drupal Wiki guide on Drupal.org](https://www.drupal.org/docs/drupal-apis/entity-api/working-with-the-entity-api)

## Image styles

Geting the image style from a file or a `uri`. Taken from [this gist](https://gist.github.com/slivorezka/925dff0369e8eddc7e4ffa4801ab0240)

```php
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

// File ID.
$fid = 123;

// Load file.
$file = File::load($fid);

// Get origin image URI.
$image_uri = $file->getFileUri();

// Load image style "thumbnail".
$style = ImageStyle::load('thumbnail');

// Get URI.
$uri = $style->buildUri($image_uri);

// Get URL.
$url = $style->buildUrl($image_uri);
```

## Taxonomy

```php
// Load taxonomy tree into $tree
$tree = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadTree(self::HOME_SUPPLEMENT_LIST_VID);
```

## Entity Queries

```php

 // Sample query on Nodes
 $query = \Drupal::entityQuery('node');
 $query->condition('status', 1);
 $query->condition('type', 'supplement');
 $query->condition('field_supplement_type.entity.tid', $slug->tid);
 // or by name:
 $query->condition('field_tags.entity.name', 'tag1');
 // or by parent tid
 $query->condition('field_supplement_type.entity.parent', $slug->tid);
 // Disable access control if we are anonynous for example
 $query->accessCheck(FALSE);
 
 $now = new DrupalDateTime('now');
 $now->setTimezone(new \DateTimeZone(DATETIME_STORAGE_TIMEZONE)); // set a desired timezone (instead of the one from the site)
 $query->condition('field_date', $now->format(DATETIME_DATETIME_STORAGE_FORMAT), '>=');

 $query->sort('field_supplement_date', 'DESC');
 $query->range(0, 1);
 $entity_ids = $query->execute();

 foreach($entity_ids as $nid) {
   // Load all information for this node
   $node = Node::load($nid);
   // Do something with node and return some data
   $supplements[] = $doSomething($node);
 }
 
 // Sample query on taxonomy. Filtering by a custom column on a taxonomy
 $query = \Drupal::entityQuery('taxonomy_term');
 $query->condition('vid', 'vocubulary_name');
 $query->condition('field_custom_text', $text);
 $tids = $query->execute();
 $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);

```
- [Ver más sobre queries y fechas](https://www.webomelette.com/query-entities-using-dates-drupal-8)
- Ver [QueryInterface documentation](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21Query%21QueryInterface.php/interface/QueryInterface/8.2.x)
 
 ## Services
 
 Using services:
 
```php
$service = \Drupal::service('module.service_name');

$result = $service->methodInMyService(157);
```

Creating a service (Example taken from https://www.drupal.org/project/drupal/issues/2945539#comment-13291384):

Add a `mymodule.services.yml` to your module directory:

```yml
services:
  mymodule.myservice:
    class: Drupal\mymodule\Services\MyService
    arguments: ['@entity_type.manager']
```

Add the service in `src/Services/MyService.php` with:

```php
namespace Drupal\mymodule\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeStorageInterface;

/**
 * Class MyService.
 */
class MyService {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage =  $this->entityTypeManager->getStorage('node');
  }

  /**
   * Load a node.
   *
   * @param int $nid
   *   The ID of the node to load.
   */
  public function loadANode($nid) {
    return $this->nodeStorage->load($nid);
  }
}
```

### Dependencies

It is good practice to pass along references to popular services instead of using `\Drupal::service()`. For example: if you need an `entityTypeManager` it is preferred to inject it instead of using `\Drupal::entityTypeManager()`. It makes your code more easily testable. Some common services to inject into your Service might be:

- `entity_type.manager` - `EntityTypeManager` object to perform entity queries on
- `database` - `Connection` object to the Drupal database to perform queries on
- `current_user` - `AccountProxy` to the current user
- `date.formatter` - `DateFormatter` object which gives you access to the `DateFormat::format()` using Drupal date formats

For a full listing of Drupal's core services check [this link](https://api.drupal.org/api/drupal/core%21core.services.yml/8.2.x)


## Config

- [How to build a simple config form](https://www.drupal.org/docs/drupal-apis/form-api/configformbase-with-simple-configuration-api)

To use your config:

```php
$config = \Drupal::service('config.factory')->get('mymodule.settings');
$value = $config->get('key');
...
```

or simpler

```php
$config = \Drupal::config('mymodule.settings')->get('name_of_form_field');
```


## Custom entities

### baseFieldDefinitions

Let's take a look at a method baseFieldDefinitions and its abilities today.

Base fields are non-configurable fields that always exist on a given entity type, like the node title or created and changed dates. By definition, base fields are fields that exist for every bundle.

Entity types define their base fields in a static method baseFieldDefinitions on the entity class. The definitions returned by this function can be overridden for all bundles by hook_entity_base_field_info_alter() or overridden on a per-bundle basis via base_field_override configuration entities.
Drupal core has the following field types:

- boolean
- changed
- created
- decimal
- email
- entity_reference
- float
- integer
- language
- map
- password
- string
- string_long
- timestamp
- uri
- uuid
- comment
- datetime
- file
- image
- link
- list_float
- list_integer
- list_string
- path
- telephone
- text
- text_long
- text_with_summary

Below you can find examples with most of these types of fields.

### entity\_reference field

$fields\['user\_id'\] is an example of **entity\_reference** field.

The default widget – **entity\_reference\_autocomplete**, the default formatter – **entity\_reference\_label**. In settings, you can set the entity type for auto-complete. Also, you can set a handler (the examples of creating custom handlers you can find [here](https://fivejars.com/blog/change-entity-autocomplete-selection-rules-drupal-8)).

With setDisplayOptions config, we can set options for the field widget (display context – form) and the field formatter (display context – view).

For better understanding, see another example with a node entity:


```php
      $fields['article'] = BaseFieldDefinition::create('entity_reference')
          ->setLabel(t('Article'))
          ->setDescription(t('Article related to demo entity.'))
          ->setSetting('target_type', 'node')
          ->setSetting('handler', 'default:node')
          ->setSetting('handler_settings', [
            'target_bundles' => ['article' => 'article'],
            'auto_create' => FALSE,
          ])
          ->setRequired(TRUE)
          ->setTranslatable(FALSE)
          ->setDisplayOptions('view', [
            'label' => 'visible',
            'type' => 'string',
            'weight' => 2,
          ])
          ->setDisplayOptions('form', [
            'type' => 'entity_reference_autocomplete',
            'weight' => 2,
            'settings' => [
              'match_operator' => 'CONTAINS',
              'size' => '60',
              'placeholder' => 'Enter here article title...',
            ],
          ])
          ->setDisplayConfigurable('view', TRUE)
          ->setDisplayConfigurable('form', TRUE);
```    

Note that we can set **target\_bundles** in **handler\_settings**.

### String field

$fields\['name'\] – is an example of a string field.

This field contains a plain string value. The default widget is **string\_textfield**, and the default formatter – **string**.

### string\_long field

Also, we can create a **string\_long** field that can contain a long string value. The default widget is **string\_textarea**, the default formatter – **basic\_string**.


```php
      $fields['notes'] = BaseFieldDefinition::create('string_long')
          ->setLabel(t('Notes'))
          ->setDescription(t('Example of string_long field.'))
          ->setDefaultValue('')
          ->setRequired(FALSE)
          ->setDisplayOptions('view', [
            'label' => 'visible',
            'type' => 'basic_string',
            'weight' => 5,
          ])
          ->setDisplayOptions('form', [
            'type' => 'string_textarea',
            'weight' => 5,
            'settings' => ['rows' => 4],
          ])
          ->setDisplayConfigurable('view', TRUE)
          ->setDisplayConfigurable('form', TRUE);
```    

### boolean field

If an entity field contains a boolean value, the default widget is **boolean\_checkbox**, the default formatter – **boolean**.

```php
       $fields['status'] = BaseFieldDefinition::create('boolean')
          ->setLabel(t('Publishing status'))
          ->setDescription(t('A boolean indicating whether the Demo entity is published.'))
          ->setDefaultValue(TRUE)
          ->setSettings(['on_label' => 'Published', 'off_label' => 'Unpublished'])
          ->setDisplayOptions('view', [
            'label' => 'visible',
            'type' => 'boolean',
            'weight' => 2,
          ])
          ->setDisplayOptions('form', [
            'type' => 'boolean_checkbox',
            'weight' => 2,
          ])
          ->setDisplayConfigurable('view', TRUE)
          ->setDisplayConfigurable('form', TRUE);
```    

### list\_integer, list\_float, list\_string fields

These fields come from the options module and basically similar, so it is enough to demonstrate the use of one of them.

All these fields have **options\_select** as the default widget and **list\_default** as the default formatter. 

```php
     $fields['http_status'] = BaseFieldDefinition::create('list_integer')
          ->setLabel(t('HTTP status code'))
          ->setDescription(t('Hypertext Transfer Protocol (HTTP) response status codes.'))
          ->setDefaultValue(200)
          ->setSettings([
            'allowed_values' => [
              200 => 'OK',
              201 => 'Created',
              202 => 'Accepted',
              300 => 'Multiple Choices',
              301 => 'Moved Permanently',
              302 => 'Moved Temporarily',
              403 => 'Forbidden',
              404 => 'Not Found',
            ],
          ])
          ->setDisplayOptions('view', [
            'label' => 'visible',
            'type' => 'list_default',
            'weight' => 6,
          ])
          ->setDisplayOptions('form', [
            'type' => 'options_select',
            'weight' => 6,
          ])
          ->setDisplayConfigurable('view', TRUE)
          ->setDisplayConfigurable('form', TRUE);
```

### text, text\_long, text\_with\_summary fields

These fields store a text with a text format.

As the default formatted, all of them uses **text\_default**, for the widget – **text\_textfield**, **text\_textarea** and **text\_textarea\_with\_summary**.

```php
       $fields['text_long'] = BaseFieldDefinition::create('text_long')
          ->setLabel(t('Text (formatted, long)'))
          ->setDescription(t('Test formatted text.'))
          ->setDisplayOptions('view', [
            'label' => 'visible',
            'type' => 'text_default',
            'weight' => 6,
          ])
          ->setDisplayOptions('form', [
            'type' => 'text_textarea',
            'weight' => 6,
            'rows' => 6,
          ])
          ->setDisplayConfigurable('view', TRUE)
          ->setDisplayConfigurable('form', TRUE);
```    

### datetime field

```php
       $fields['start_date'] = BaseFieldDefinition::create('datetime')
          ->setLabel(t('Only Date'))
          ->setDescription(t('Date field example.'))
          ->setRevisionable(TRUE)
          ->setSettings([
            'datetime_type' => 'date',
          ])
          ->setDefaultValue('')
          ->setDisplayOptions('view', [
            'label' => 'above',
            'type' => 'datetime_default',
            'settings' => [
              'format_type' => 'medium',
            ],
            'weight' => -9,
          ])
          ->setDisplayOptions('form', [
            'type' => 'datetime_default',
            'weight' => -9,
          ])
          ->setDisplayConfigurable('form', TRUE)
          ->setDisplayConfigurable('view', TRUE);
```

### Widgets

```
-------------------------------------------- --------------------------------------------------------------------------------------- 
  Plugin ID                                    Plugin class                                                                           
 -------------------------------------------- --------------------------------------------------------------------------------------- 
  boolean_checkbox                             Drupal\Core\Field\Plugin\Field\FieldWidget\BooleanCheckboxWidget                       
  datetime_datelist                            Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDatelistWidget                        
  datetime_default                             Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget                         
  datetime_timestamp                           Drupal\Core\Datetime\Plugin\Field\FieldWidget\TimestampDatetimeWidget                  
  email_default                                Drupal\Core\Field\Plugin\Field\FieldWidget\EmailDefaultWidget                          
  entity_reference_autocomplete                Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget           
  entity_reference_autocomplete_tags           Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget       
  field_example_3text                          Drupal\field_example\Plugin\Field\FieldWidget\Text3Widget                              
  field_example_colorpicker                    Drupal\field_example\Plugin\Field\FieldWidget\ColorPickerWidget                        
  field_example_text                           Drupal\field_example\Plugin\Field\FieldWidget\TextWidget                               
  field_permission_example_widget              Drupal\field_permission_example\Plugin\Field\FieldWidget\TextWidget                    
  file_generic                                 Drupal\file\Plugin\Field\FieldWidget\FileWidget                                        
  image_image                                  Drupal\image\Plugin\Field\FieldWidget\ImageWidget                                      
  language_select                              Drupal\Core\Field\Plugin\Field\FieldWidget\LanguageSelectWidget                        
  link_default                                 Drupal\link\Plugin\Field\FieldWidget\LinkWidget                                        
  menu_item_extras_view_mode_selector_select   Drupal\menu_item_extras\Plugin\Field\FieldWidget\MenuItemExtrasViewModeSelectorSelect  
  moderation_state_default                     Drupal\content_moderation\Plugin\Field\FieldWidget\ModerationStateWidget               
  number                                       Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget                                
  oembed_textfield                             Drupal\media\Plugin\Field\FieldWidget\OEmbedWidget                                     
  options_buttons                              Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget                        
  options_select                               Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget                         
  path                                         Drupal\path\Plugin\Field\FieldWidget\PathWidget                                        
  string_textarea                              Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextareaWidget                        
  string_textfield                             Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget                       
  text_textarea                                Drupal\text\Plugin\Field\FieldWidget\TextareaWidget                                    
  text_textarea_with_summary                   Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget                         
  text_textfield                               Drupal\text\Plugin\Field\FieldWidget\TextfieldWidget                                   
  uri                                          Drupal\Core\Field\Plugin\Field\FieldWidget\UriWidget                                   
 -------------------------------------------- --------------------------------------------------------------------------------------- 
 ```  
    
### References:

- [ENTITY BASEFIELDDEFINITIONS FIELDS EXAMPLES IN DRUPAL 8](https://fivejars.com/blog/entity-basefielddefinitions-fields-examples-drupal-8)
- [list of supported value in entity baseFieldDefinitions setDisplayOptions type(field widget)](https://drupal.stackexchange.com/questions/271298/list-of-supported-value-in-entity-basefielddefinitions-setdisplayoptions-typefi)


 ## Logging
 
 Para logear en reemplazo de `watchdog()` se usa el siguiente código:
 
 ```php
 \Drupal::logger('my_module')->error($message);
 ```
 
 Otras opciones son:
 
 ```php
 \Drupal::logger('my_module')->emergency( $message, $vars )
 \Drupal::logger('my_module')->alert(     $message, $vars );
 \Drupal::logger('my_module')->critical(  $message, $vars );
 \Drupal::logger('my_module')->error(     $message, $vars );
 \Drupal::logger('my_module')->warning(   $message, $vars );
 \Drupal::logger('my_module')->notice(    $message, $vars );
 \Drupal::logger('my_module')->info(      $message, $vars );
 \Drupal::logger('my_module')->debug(     $message, $vars );
 ```
 
 - Para más información: https://www.drupal.org/node/2595985

 ## Normalizer and Serializer
 
 Links:
  - [Serialization API overview](https://www.drupal.org/docs/8/api/serialization-api/serialization-api-overview)
 
 ### Creating your own Normalizer
 
Create a class in `my_module/src/Normalizer/MyEntityNormalizer.php` with the normalizer code:

```php
namespace Drupal\my_module\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class MyEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {

  public function normalize($entity, $format = NULL, array $context = []) {
    $normalizer['id'] = $entity->id();
    $normalizer['field_title'] = $entity->field_title->value;
    
    // Calling normalizer on referenced entities:
    $normalized['field_xxxxxx'] = array_map(function($district) use ($format, $context) {
      return $this->serializer->normalize($district, $format, $context);
    }, $entity->field_xxxxxx->referencedEntities());
    
    return $normalizer;
  }
  
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || $format !== 'json') {
      return FALSE;
    }
    
    if ($data instanceof Entity) {
      return TRUE;
    }
    
    return FALSE;
  }
}
```

You must also add your Normalizer to `my_module.services.yml`:

```yml
services:
  my_module.entity_name.normalizer:
    class: Drupal\my_module\Normalizer\MyEntityNormalizer
    tags:
      - { name: normalizer, priority: 10 }
```

### Using serializer
 ```php
 use Drupal\node\Entity\Node;

 // Serialize a node to JSON
 $node = Node::load($nid);
 $format = 'json';
 $serialized_content = \Drupal::service('serializer')
   ->serialize($node, $format)
 );

 // Deserialize from JSON -> Node
 $node = \Drupal::service('serializer')
   ->deserialize($serialized_content, \Drupal\node\Entity\Node::class, 'json');
 ```
 
 You can serialize more complex structures like so:
 
 ```php
 <?php

use Drupal\node\Entity\Node;

$list = [
    Node::load(44130),
    Node::load(44131),
    Node::load(44130),
];

$object = [
    'total' => 3,
    'data' => $list
];

print_r(
    \Drupal::service('serializer')->serialize($object, 'json')
);
```

will output:

```json
{
    "data": [
        {
            "nid": "44130",
            "title": "Primer update de la nota"
        },
        {
            "nid": "44131",
            "title": "Un nuevo update a mi nodo"
        },
        {
            "nid": "44132",
            "title": "Tercer update de la nota"
        }
    ],
    "total": 3
}
```
 
 ## Migrate
 
 Para ejecutar una migración via código:
 
 ```php
     // Correr migración
    $manager = \Drupal::service('plugin.manager.migration');
    $plugins = $manager->createInstances([]);
    $migration = FALSE;
    foreach($plugins as $id => $plugin) {
      if ($id == '[[[migration-id]]]') {
        $migration = $plugin;
      }   
    }   

    // Implementation of MigrateMessageInterface to track migration messages.
    $log = new ReclamoLogMigrateMessage();

    // if $replace run with equivalente of --update drush flag
    if ($replace) {
      $migration->getIdMap()->prepareUpdate();
    }   
    
    // Create MigrateExecutable instance
    $executable = new MigrateExecutable($migration, $log);

    // Run migration
    $executable->import();
```

## Storing temporary data

```php

// For "mymodule_name," any unique namespace will do.
// I'd probably use "mymodule_name" most of the time.
$tempstore = \Drupal::service('user.private_tempstore')->get('mymodule_name');
$tempstore->set('my_variable_name', $some_data);

// Retrieve data
$tempstore = \Drupal::service('user.private_tempstore')->get('mymodule_name');
$some_data = $tempstore->get('my_variable_name');

// Clear data
$tempstore->set('my_variable_name', false);

```

more info here: https://atendesigngroup.com/blog/storing-session-data-drupal-8

## Rendering

- Complete example of a module with a controller + twig view: https://github.com/ericski/Drupal-8-Module-Theming-Example

To render a body field you can use `check_markup()` like so:

```php
$html = check_markup($text, $format_id, $lang);
```

`$format_id` can be `basic_html`, `full_html`, etc. The previous code is equivalente to:

```php
  $filter_types_to_skip = [...];
  $build = [
    '#type' => 'processed_text',
    '#text' => $text,
    '#format' => $format_id,
    '#filter_types_to_skip' => $filter_types_to_skip,
    '#langcode' => $langcode,
  ];
  return \Drupal::service('renderer')
    ->renderPlain($build);
```

## Caching
 
If it is about clearing the cache after the user changes settings, you can clear the node_view cache tag with `Drupal::entityManager()->getViewBuilder('node')->resetCache()`. If you want to clear the cache of a specific tag, you can pass the node ID to that method or you can use `\Drupal\Core\Cache\Cache::invalidateTags($node->getCacheTags())`.

Each thing that is rendered has one or multiple cache tags (inspect the `X-Drupal-Cache-Tags` header of a page response, I'm sure there will be development tools to better understand them in the future), by invalidating them, you can automatically clear all caches that contain them. You can enable debuggin by setting `http.response.debug_cacheability_headers: true` in your `services.yml`

- https://www.drupal.org/docs/8/api/cache-api/cache-tags
- Debugging: https://www.drupal.org/docs/8/api/responses/cacheableresponseinterface#debugging
- Cachability of REST responses: https://blog.dcycle.com/blog/2018-01-24/caching-drupal-8-rest-resource/

## Drush

When you upgrade from Drush 8.x to Drush +9.x you need to port your drush commands. Here are some pointers:

- [Porting Commands to Drush 9](https://weitzman.github.io/blog/port-to-drush9) - Nice, short blog post with the basic information.
- You can port existing commands with:

```
$ lando drush generate dcf

 Welcome to drush-command-file generator!
––––––––––––––––––––––––––––––––––––––––––

 Module machine name:
 ➤ gop_admin

 Absolute path to legacy Drush command file (optional - for porting):
 ➤ profiles/pagina12/modules/custom/gop_admin/gop_admin.drush.inc

 The following directories and files have been created or updated:
–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
 • profiles/pagina12/modules/custom/gop_admin/composer.json
 • profiles/pagina12/modules/custom/gop_admin/drush.services.yml
 • profiles/pagina12/modules/custom/gop_admin/src/Commands/GopAdminCommands.php
```
- This will generate the necesary code. You need to then migrate your commands from your `module.drush.inc` to `module/src/Commands/MyDrushCommands.php`
- To execute a Drush command within a Drush command you can do:

```php
<?php

use Drush\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;

class MyDrushCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  // Need this trait to use siteAliasManager()
  use SiteAliasManagerAwareTrait;

  public function myCommand() {
    // Run a configuration import without interaction
    $this->runDrushCommand('cim', ['-y']);
    // ...
  }

  /**
   * Execute a drush command and output results to TTY.
   */
  private function runDrushCommand($cmd, $args = []) {
    $this->processManager()
      ->drush($this->siteAliasManager()->getSelf(), $cmd, $args)
      ->setTty(true)
      ->run();
  }
}
```
- More `drush` documentation here: [Creating Custom Drush Commands](https://docs.drush.org/en/9.x/commands/)

## Composer

Some useful composer commands:

```
$ composer show -a 'drupal/layout_paragraphs'

Info from https://repo.packagist.org: #StandWithUkraine
name     : drupal/layout_paragraphs
descrip. : Layout Paragraphs
keywords : Drupal
versions : 2.0.x-dev, 2.0.2, 2.0.1, 2.0.0, 2.0.0-beta9, 2.0.0-beta8, 2.0.0-beta7, 2.0.0-beta6, 2.0.0-beta5, 2.0.0-beta4, 2.0.0-beta3, 2.0.0-beta2, 2.0.0-beta1, 2.0.0-alpha4, 2.0.0-alpha3, 2.0.0-alpha2, 2.0.0-alpha1, 1.0.x-dev, 1.0.0, 1.0.0-beta5, 1.0.0-beta4, 1.0.0-beta3, 1.0.0-beta2, 1.0.0-beta1, dev-2.0.x, dev-1.0.x
type     : drupal-module
license  : GNU General Public License v2.0 or later (GPL-2.0+) (OSI approved) https://spdx.org/licenses/GPL-2.0+.html#licenseText
homepage : https://www.drupal.org/project/layout_paragraphs
source   : [git] https://git.drupalcode.org/project/layout_paragraphs.git baa872d56cc1e741966b43258c8a0a050f8f4b6c
dist     : []  
names    : drupal/layout_paragraphs

support
source : http://cgit.drupalcode.org/layout_paragraphs
issues : https://www.drupal.org/project/issues/layout_paragraphs

requires
drupal/core ^9.2 || ^10
drupal/paragraphs ^1.6

requires (dev)
drupal/paragraphs-paragraphs_library *
drupal/block_field ~1.0
drupal/entity_usage 2.x-dev
```

will show you the available versions and other information. The `-a` option show uninstalled packages.

To install a specific version you can do:

```
$ composer require 'drupal/layout_paragraphs:2.0.0-beta9
```

## Resources

- Drupal 8 Themeing Guide: https://sqndr.github.io/d8-theming-guide/