<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_wmarka_articles_gallery
 *
 * @copyright   (C) 2026 Beibit Sakhanov <https://webmarka.kz>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Webmarka\Module\ArticlesGallery\Site\Helper\ArticlesGalleryHelper as Helper;

/**
 * @var \Joomla\Registry\Registry $params
 * @var array                     $items        articles of the current group
 * @var object|null               $juImg
 * @var string                    $overlayRgba
 */

$esc        = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$headings   = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div'];
$showIntro  = (bool) $params->get('show_introtext', 1);
$btnText    = Text::_(trim((string) $params->get('btn_text', '')) ?: 'MOD_WMARKA_ARTICLES_GALLERY_FIELD_BTN_TEXT_DEFAULT');
$microdata  = $params->get('structured_data', 'jsonld') === 'microdata';
$itemTag    = \in_array($params->get('item_heading_tag', 'h3'), $headings, true) ? $params->get('item_heading_tag', 'h3') : 'h3';
$titleClass = Helper::titleClass($params, 'uk-margin-remove', 'uk-h5');

// Caption on the tile: none, under the photo, over it, or over it on hover.
$caption   = (string) $params->get('tile_caption', 'none');
$overlayUi = (string) $params->get('tile_overlay', 'uk-overlay-primary');
// Over a photo the caption stays compact: two lines of text unless another limit is set.
$onPhoto   = $caption === 'overlay' || $caption === 'hover';
$introAttr = Helper::introAttributes($params, $onPhoto ? 'uk-margin-small-top uk-margin-remove-bottom' : 'uk-margin-small-top', $onPhoto ? 2 : 0);
$sizes     = Helper::sizes([$params->get('cols_s', 'uk-child-width-1-2@s'), $params->get('cols_m', 'uk-child-width-1-3@m'), $params->get('cols_l', 'uk-child-width-1-3@l')]);

foreach ($items as $item) :
    $picture = Helper::picture($item, $params, $juImg, $sizes, 'intro', ['img_h' => 0, 'mob_w' => 400, 'mob_h' => 0]);

    // A gallery shows photos only: articles without an image are skipped.
    if (!$picture) {
        continue;
    }

    $full  = Helper::fullImage($item);
    $intro = $showIntro && !empty($item->displayIntrotext) ? (string) $item->displayIntrotext : '';

    // The lightbox caption is HTML: it is built from plain text and escaped once, as an attribute value.
    $lightbox = '<h3 class="uk-margin-remove">' . $esc($item->title) . '</h3>';
    $text     = $intro !== '' ? Helper::plainText($intro) : '';
    $lightbox .= $text !== '' ? '<div class="uk-margin-small-top uk-text-small">' . $esc($text) . '</div>' : '';
    $lightbox .= '<div class="uk-margin-top"><a href="' . $item->link . '" class="uk-button uk-button-primary uk-button-small">' . $esc($btnText) . '</a></div>';

    $titleTag = '<' . $itemTag . ' class="' . $esc($titleClass) . '"' . ($microdata ? ' itemprop="name"' : '') . '>' . $esc($item->title) . '</' . $itemTag . '>';
    $introTag = $intro !== '' ? '<div' . $introAttr . ($microdata ? ' itemprop="caption"' : '') . '>' . $intro . '</div>' : '';
    ?>
    <div class="<?php echo Helper::filterClasses($item); ?>"<?php echo $microdata ? ' itemscope itemtype="https://schema.org/ImageObject"' : ''; ?>>
        <div class="uk-inline-clip uk-transition-toggle uk-border-rounded uk-width-1-1<?php echo $caption !== 'none' && $caption !== 'below' && $overlayUi === 'uk-overlay-primary' ? ' uk-light' : ''; ?>" tabindex="0">
            <?php echo Helper::pictureTag($picture, ['class' => 'uk-width-1-1', 'itemprop' => $microdata ? 'thumbnailUrl' : null]); ?>

            <?php if ($overlayRgba !== '') : ?>
                <div class="uk-position-cover uk-overlay uk-transition-fade" style="background-color: <?php echo $overlayRgba; ?>;"></div>
            <?php endif; ?>

            <div class="uk-position-center uk-transition-scale-up uk-transition-opaque uk-light">
                <span uk-overlay-icon></span>
            </div>

            <?php if ($caption === 'overlay' || $caption === 'hover') : ?>
                <div class="uk-overlay uk-padding-small <?php echo $esc($overlayUi); ?> uk-position-bottom<?php echo $caption === 'hover' ? ' uk-transition-slide-bottom' : ''; ?>">
                    <?php echo $titleTag . $introTag; ?>
                </div>
            <?php endif; ?>

            <a class="uk-position-cover" href="<?php echo $esc($full['src']); ?>" data-caption="<?php echo $esc($lightbox); ?>" data-alt="<?php echo $esc($picture['alt']); ?>" aria-label="<?php echo $esc($item->title); ?>"></a>
        </div>

        <?php if ($caption === 'below') : ?>
            <div class="uk-margin-small-top"><?php echo $titleTag . $introTag; ?></div>
        <?php endif; ?>

        <?php if ($microdata) : ?>
            <meta itemprop="contentUrl" content="<?php echo $esc($full['src']); ?>">
            <?php if ($caption !== 'overlay' && $caption !== 'hover' && $caption !== 'below') : ?>
                <meta itemprop="name" content="<?php echo $esc($item->title); ?>">
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
