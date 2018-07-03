<?php

namespace Drupal\product_article\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\product_article\Entity\ProductArticleInterface;

/**
 * Class ProductArticleController.
 *
 *  Returns responses for Product article routes.
 */
class ProductArticleController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Product article  revision.
   *
   * @param int $product_article_revision
   *   The Product article  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($product_article_revision) {
    $product_article = $this->entityManager()->getStorage('product_article')->loadRevision($product_article_revision);
    $view_builder = $this->entityManager()->getViewBuilder('product_article');

    return $view_builder->view($product_article);
  }

  /**
   * Page title callback for a Product article  revision.
   *
   * @param int $product_article_revision
   *   The Product article  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($product_article_revision) {
    $product_article = $this->entityManager()->getStorage('product_article')->loadRevision($product_article_revision);
    return $this->t('Revision of %title from %date', ['%title' => $product_article->label(), '%date' => format_date($product_article->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Product article .
   *
   * @param \Drupal\product_article\Entity\ProductArticleInterface $product_article
   *   A Product article  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ProductArticleInterface $product_article) {
    $account = $this->currentUser();
    $langcode = $product_article->language()->getId();
    $langname = $product_article->language()->getName();
    $languages = $product_article->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $product_article_storage = $this->entityManager()->getStorage('product_article');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $product_article->label()]) : $this->t('Revisions for %title', ['%title' => $product_article->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all product article revisions") || $account->hasPermission('administer product article entities')));
    $delete_permission = (($account->hasPermission("delete all product article revisions") || $account->hasPermission('administer product article entities')));

    $rows = [];

    $vids = $product_article_storage->revisionIds($product_article);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\product_article\ProductArticleInterface $revision */
      $revision = $product_article_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $product_article->getRevisionId()) {
          $link = $this->l($date, new Url('entity.product_article.revision', ['product_article' => $product_article->id(), 'product_article_revision' => $vid]));
        }
        else {
          $link = $product_article->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.product_article.translation_revert', ['product_article' => $product_article->id(), 'product_article_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.product_article.revision_revert', ['product_article' => $product_article->id(), 'product_article_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.product_article.revision_delete', ['product_article' => $product_article->id(), 'product_article_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['product_article_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
