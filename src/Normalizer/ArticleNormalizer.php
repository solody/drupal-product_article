<?php

namespace Drupal\product_article\Normalizer;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\product_article\Entity\ProductArticleInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

class ArticleNormalizer extends ContentEntityNormalizer {

  public function supportsNormalization($data, $format = NULL) {
    $route = \Drupal::routeMatch()->getRouteName();
    return $data instanceof ProductArticleInterface && $route === 'view.api_product_articles.rest_export_1';
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $data = parent::normalize($entity, $format, $context);

    // TODO:: addCacheableDependency
    // $this->addCacheableDependency($context, $something);

    // 隐藏字段
    unset($data['content']);

    if (!empty($data['products'])) {
      foreach ($data['products'] as $index => $product) {
        unset($product['body']);
        $data['products'][$index] = $product;
      }
    }

    return $data;
  }

}
