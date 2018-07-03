<?php

namespace Drupal\product_article;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Product article entity.
 *
 * @see \Drupal\product_article\Entity\ProductArticle.
 */
class ProductArticleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\product_article\Entity\ProductArticleInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished product article entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published product article entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit product article entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete product article entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add product article entities');
  }

}
