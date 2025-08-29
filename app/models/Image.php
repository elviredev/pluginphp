<?php
namespace Core;

defined('ROOT') or die('Direct script access denied');

/**
 * Image class
 */
class Image
{
  /**
   * @desc Redimensionne une image en conservant ses proportions
   * et écrase le fichier original avec la version redimensionnée.
   *
   * - Gère les formats : JPEG, PNG, GIF, WebP.
   * - Si l’image est plus petite que la taille max demandée,
   *   elle n’est pas agrandie.
   * - Conserve la transparence des PNG.
   *
   * @param string $filename Chemin absolu ou relatif du fichier image
   * @param int $max_size Taille maximale (en pixels) pour la largeur
   *                      ou la hauteur (par défaut : 700)
   * @return string Retourne le chemin du fichier redimensionné (identique à l’original)
   */
  public function resize(string $filename, $max_size = 700): string
  {
    // Si l’image n’existe pas, la fonction retourne directement le chemin fourni
    if (!file_exists($filename))
      return $filename;

    // Si l'image existe, vérifier son type
    $type = mime_content_type($filename);

    // Créer une ressource image
    switch ($type)
    {
      case 'image/jpeg':
        $image = imagecreatefromjpeg($filename);
        break;
      case 'image/png':
        $image = imagecreatefrompng($filename);
          break;
      case 'image/gif':
        $image = imagecreatefromgif($filename);
        break;
      case 'image/webp':
        $image = imagecreatefromwebp($filename);
        break;
      default:
        return $filename;
        break;
    }

    // Récupérer largeur et hateur de la source de l'image
    $src_w = imagesx($image);
    $src_h = imagesy($image);

    // Calcul des nouvelles dimensions
    if ($src_w > $src_h) {
      // Limiter la largeur max
      if ($src_w < $max_size)
        $max_size = $src_w;

      // destination width devient le max
      $dst_w = $max_size;
      // Respect des proportions
      $dst_h = ($src_h / $src_w) * $max_size;
    } else {
      // Limiter la hauteur max
      if ($src_h < $max_size)
        $max_size = $src_h;

      // destination height devient le max
      $dst_h = $max_size;
      $dst_w = ($src_w / $src_h) * $max_size;
    }

    $dst_w = round($dst_w);
    $dst_h = round($dst_h);

    // Créer la nouvelle image
    $dst_image = imagecreatetruecolor($dst_w, $dst_h);
    // Si c’est un PNG, on gère la transparence
    if ($type == 'image/png') {
      imagealphablending($dst_image, false);
      imagesavealpha($dst_image, true);
    }

    // Copie et redimensionnement
    imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    imagedestroy($image);

    // Enregistrer l'image redimensionnée : on réécrit le même fichier (le fichier original est écrasé)
    switch ($type)
    {
      case 'image/jpeg':
        imagejpeg($dst_image, $filename, 90);
        break;
      case 'image/png':
        imagepng($dst_image, $filename, 9);
        break;
      case 'image/gif':
        imagegif($dst_image, $filename);
        break;
      case 'image/webp':
        imagewebp($dst_image, $filename, 90);
        break;
      default:
        return $filename;
        break;
    }

    // Libération de la mémoire
    imagedestroy($dst_image);
    return $filename;

  }

  /**
   * @desc Recadre une image au centre avec des dimensions fixes
   *
   * Étapes :
   *  1. Redimensionne l’image pour qu’elle ait au moins la largeur/hauteur
   *     nécessaires tout en gardant les proportions.
   *  2. Recadre (crop) au centre pour obtenir exactement
   *     $max_width x $max_height.
   *  3. Réécrit le fichier original avec la nouvelle version.
   *
   *  - Gère les formats : JPEG, PNG, GIF, WebP.
   *  - Conserve la transparence pour les PNG.
   *
   * @param string $filename Chemin de l’image à recadrer
   * @param int $max_width   Largeur cible du recadrage
   * @param int $max_height  Hauteur cible du recadrage
   * @return string Retourne le chemin du fichier recadré (identique à l’original)
   */
  public function crop(string $filename, int $max_width = 700, int $max_height = 700)
  {
    // Si l’image n’existe pas, la fonction retourne directement le chemin fourni
    if (!file_exists($filename))
      return $filename;

    // Si l'image existe, vérifier son type
    $type = mime_content_type($filename);

    // Créer une ressource image
    switch ($type)
    {
      case 'image/jpeg':
        $image = imagecreatefromjpeg($filename);
        $imageFunc = 'imagecreatefromjpeg';
        break;
      case 'image/png':
        $image = imagecreatefrompng($filename);
        $imageFunc = 'imagecreatefrompng';
        break;
      case 'image/gif':
        $image = imagecreatefromgif($filename);
        $imageFunc = 'imagecreatefromgif';
        break;
      case 'image/webp':
        $image = imagecreatefromwebp($filename);
        $imageFunc = 'imagecreatefromwebp';
        break;
      default:
        return $filename;
        break;
    }

    // Récupérer largeur et hateur de la source de l'image
    $src_w = imagesx($image);
    $src_h = imagesy($image);

    // Calcul du "max" pour le redimensionnement préalable
    if($max_width > $max_height) {
      if($src_w > $src_h) {
        $max = $max_width;
      } else {
        $max = ($src_h / $src_w) * $max_width;
      }
    } else {
      if($src_w > $src_h) {
        $max = ($src_w / $src_h) * $max_height;
      } else {
        $max = $max_height;
      }
    }

    // Redimensionner l'image
    $this->resize($filename, $max);
    // Créer une nouvelle image à partir de la version redimensionnée
    $image = $imageFunc($filename);

    // Recalcul des dimensions de la version redimensionnée pour préparr le recadrage
    $src_w = imagesx($image);
    $src_h = imagesy($image);

    // Définit ces variables à 0 pour les manipuler ensuite
    $src_x = 0;
    $src_y = 0;

    // Trouver le centre de l'image
    if($max_width > $max_height) {
      $src_y = round(($src_h - $max_height) / 2);
    } else {
      $src_x = round(($src_w - $max_width) / 2);
    }

    // Créer la nouvelle image
    $dst_image = imagecreatetruecolor($max_width, $max_height);
    // Si c’est un PNG, on gère la transparence
    if ($type == 'image/png') {
      imagealphablending($dst_image, false);
      imagesavealpha($dst_image, true);
    }

    // Copie et redimensionnement
    imagecopyresampled($dst_image, $image, 0, 0, $src_x, $src_y, $max_width, $max_height, $max_width, $max_height);
    imagedestroy($image);

    // Enregistrer l'image redimensionnée : on réécrit le même fichier (le fichier original est écrasé)
    switch ($type)
    {
      case 'image/jpeg':
        imagejpeg($dst_image, $filename, 90);
        break;
      case 'image/png':
        imagepng($dst_image, $filename, 9);
        break;
      case 'image/gif':
        imagegif($dst_image, $filename);
        break;
      case 'image/webp':
        imagewebp($dst_image, $filename, 90);
        break;
      default:
        return $filename;
        break;
    }

    // Libération de la mémoire
    imagedestroy($dst_image);
    return $filename;
  }

  /**
   * @desc Génère une miniature (thumbnail) d’une image sans modifier l’original
   *
   * @param string $filename Chemin du fichier image original.
   * @param $width          // Largeur de la miniature (par défaut 700)
   * @param $height         // Hauteur de la miniature (par défaut 700)
   * @return string         Chemin du fichier miniature généré
   */
  public function getThumbnail(string $filename, $width = 700, $height = 700): string
  {
    if (file_exists($filename)) {
      $ext = explode('.', $filename);
      $ext = end($ext);

      $dest = preg_replace("/\.$ext$/", "_thumbnail.".$ext, $filename);
      if (file_exists($dest))
        return $dest;

      copy($filename, $dest);
      $this->crop($dest, $width, $height);

      return $dest;
    }
    return $filename;
  }

}











