<?php

namespace Drupal\product_article;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\product_article\Entity\ProductArticleInterface;

/**
 * Defines the storage handler class for Product article entities.
 *
 * This extends the base storage class, adding required special handling for
 * Product article entities.
 *
 * @ingroup product_article
 */
class ProductArticleStorage extends SqlContentEntityStorage implements ProductArticleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ProductArticleInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {product_article_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {product_article_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ProductArticleInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {product_article_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('product_article_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
