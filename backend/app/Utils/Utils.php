<?php

namespace App\Utils;

class Utils
{
   public static function generateImage(string $text, int $width = 480, int $height = 640): string {
      $color = str_replace('#', '', fake()->hexColor());
      $text = join('+', explode(' ', $text));
      
      return "{$width}x{$height}.png/{$color}?text={$text}";
   }
}