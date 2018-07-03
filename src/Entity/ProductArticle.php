<?php

namespace Drupal\product_article\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

/**
 * Defines the Product article entity.
 *
 * @ingroup product_article
 *
 * @ContentEntityType(
 *   id = "product_article",
 *   label = @Translation("Product article"),
 *   handlers = {
 *     "storage" = "Drupal\product_article\ProductArticleStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\product_article\ProductArticleListBuilder",
 *     "views_data" = "Drupal\product_article\Entity\ProductArticleViewsData",
 *     "translation" = "Drupal\product_article\ProductArticleTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\product_article\Form\ProductArticleForm",
 *       "add" = "Drupal\product_article\Form\ProductArticleForm",
 *       "edit" = "Drupal\product_article\Form\ProductArticleForm",
 *       "delete" = "Drupal\product_article\Form\ProductArticleDeleteForm",
 *     },
 *     "access" = "Drupal\product_article\ProductArticleAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\product_article\ProductArticleHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "product_article",
 *   data_table = "product_article_field_data",
 *   revision_table = "product_article_revision",
 *   revision_data_table = "product_article_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer product article entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/product_article/{product_article}",
 *     "add-form" = "/admin/commerce/product_article/add",
 *     "edit-form" = "/admin/commerce/product_article/{product_article}/edit",
 *     "delete-form" = "/admin/commerce/product_article/{product_article}/delete",
 *     "version-history" = "/admin/commerce/product_article/{product_article}/revisions",
 *     "revision" = "/admin/commerce/product_article/{product_article}/revisions/{product_article_revision}/view",
 *     "revision_revert" = "/admin/commerce/product_article/{product_article}/revisions/{product_article_revision}/revert",
 *     "revision_delete" = "/admin/commerce/product_article/{product_article}/revisions/{product_article_revision}/delete",
 *     "translation_revert" = "/admin/commerce/product_article/{product_article}/revisions/{product_article_revision}/revert/{langcode}",
 *     "collection" = "/admin/commerce/product_article",
 *   },
 *   field_ui_base_route = "product_article.settings"
 * )
 */
class ProductArticle extends RevisionableContentEntityBase implements ProductArticleInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the product_article owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Product article.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('图片'))
      ->setCardinality(1)
      ->setRevisionable(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'file_directory' => 'commerce/product_article/image/[date:custom:Y]-[date:custom:m]',
        'file_extensions' => 'png gif jpg jpeg',
        'max_filesize' => '',
        'max_resolution' => '',
        'min_resolution' => '',
        'alt_field' => false,
        'alt_field_required' => true,
        'title_field' => false,
        'title_field_required' => false,
        'handler' => 'default:file',
        'handler_settings' => []
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'image'
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 2
      ])
      ->setDisplayConfigurable('view', true)
      ->setDisplayConfigurable('form', true);

    $fields['categories'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('分类'))
      ->setDescription(t('The categories of the Product article belong to. <a href="' . Url::fromRoute('entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'product_article_categories'])->toString() .'">管理分类</a>'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'product_article_categories'
        ]
      ])
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label'
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 2
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['content'] = BaseFieldDefinition::create('text_with_summary')
      ->setLabel(t('正文'))
      ->setDescription(t(''))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('display_summary', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea_with_summary',
        'weight' => 2
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default'
      ]);

    $fields['products'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Products'))
      ->setDescription(t('The products of the Product article reference to.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'commerce_product')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label'
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
        'weight' => 2
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Product article entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author'
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
        'weight' => 2
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Product article is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 2
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
