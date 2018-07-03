<?php

namespace Drupal\product_article\Normalizer;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\product_article\Entity\ProductArticleInterface;
use Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer;

/**
 * 展开文章的关联产品字段
 */
class ArticleProductFieldItemNormalizer extends EntityReferenceFieldItemNormalizer {

  public function supportsNormalization($data, $format = NULL) {
    if (parent::supportsNormalization($data, $format)) {
      if ($data instanceof EntityReferenceItem) {
        $entity = $data->get('entity')->getValue();
        if ($entity instanceof ProductInterface) {
          if ($data->getParent() && $data->getParent()->getParent() && $data->getParent()->getParent()->getValue() instanceof ProductArticleInterface) {
            return true;
          }
        }
      }
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    $entity = $field_item->entity;
    $data = null;
    if ($entity instanceof ProductInterface) {
      $data = $this->serializer->normalize($entity, $format, $context);
    } else {
      $data = parent::normalize($field_item, $format, $context);
    }
    return $data;
  }
}