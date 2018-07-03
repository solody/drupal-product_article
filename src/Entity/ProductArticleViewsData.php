<?php

namespace Drupal\product_article\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Product article entities.
 */
class ProductArticleViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
