<?php

/**
 * @file
 * Contains product_article.page.inc.
 *
 * Page callback for Product article entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Product article templates.
 *
 * Default template: product_article.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_product_article(array &$variables) {
  // Fetch ProductArticle Entity Object.
  $product_article = $variables['elements']['#product_article'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
