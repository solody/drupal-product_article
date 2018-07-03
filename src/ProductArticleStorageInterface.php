<?php

namespace Drupal\product_article;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface ProductArticleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Product article revision IDs for a specific Product article.
   *
   * @param \Drupal\product_article\Entity\ProductArticleInterface $entity
   *   The Product article entity.
   *
   * @return int[]
   *   Product article revision IDs (in ascending order).
   */
  public function revisionIds(ProductArticleInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Product article author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Product article revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\product_article\Entity\ProductArticleInterface $entity
   *   The Product article entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ProductArticleInterface $entity);

  /**
   * Unsets the language for all Product article with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
