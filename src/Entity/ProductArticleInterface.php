<?php

namespace Drupal\product_article\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Product article entities.
 *
 * @ingroup product_article
 */
interface ProductArticleInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Product article title.
   *
   * @return string
   *   Title of the Product article.
   */
  public function getTitle();

  /**
   * Sets the Product article title.
   *
   * @param string $title
   *   The Product article title.
   *
   * @return \Drupal\product_article\Entity\ProductArticleInterface
   *   The called Product article entity.
   */
  public function setTitle($title);

  /**
   * Gets the Product article creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Product article.
   */
  public function getCreatedTime();

  /**
   * Sets the Product article creation timestamp.
   *
   * @param int $timestamp
   *   The Product article creation timestamp.
   *
   * @return \Drupal\product_article\Entity\ProductArticleInterface
   *   The called Product article entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Product article published status indicator.
   *
   * Unpublished Product article are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Product article is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Product article.
   *
   * @param bool $published
   *   TRUE to set this Product article to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\product_article\Entity\ProductArticleInterface
   *   The called Product article entity.
   */
  public function setPublished($published);

  /**
   * Gets the Product article revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Product article revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\product_article\Entity\ProductArticleInterface
   *   The called Product article entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Product article revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Product article revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\product_article\Entity\ProductArticleInterface
   *   The called Product article entity.
   */
  public function setRevisionUserId($uid);

}
