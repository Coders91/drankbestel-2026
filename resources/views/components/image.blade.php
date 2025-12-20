@props([
    'id' => null,
    'size' => 'full',
    'width' => null,
    'height' => null,
    'src' => null,
    'alt' => null,
    'srcset' => null,
    'sizes' => null,
    'lazyLoading' => true,
    'fetchpriorityHigh' => false,
])


@php
  // Get image data from WordPress if id is provided
  $imageData = [];
  if ($id) {
      $imageData = [
          'src' => wp_get_attachment_image_url($id, $size),
          'alt' => get_post_meta($id, '_wp_attachment_image_alt', true),
          'srcset' => wp_get_attachment_image_srcset($id, $size),
          'sizes' => wp_get_attachment_image_sizes($id, $size),
      ];

      // Get image dimensions if width/height not provided
      if (!$width || !$height) {
          $imageMeta = wp_get_attachment_metadata($id);
          if ($imageMeta && isset($imageMeta['width'], $imageMeta['height'])) {
              $imageData['width'] = $imageMeta['width'];
              $imageData['height'] = $imageMeta['height'];
          }
      }
  }

@endphp


@php
  // Get image data from WordPress if id is provided and props aren't already set
  if ($id) {
      // Only set from WordPress if not explicitly provided
      $src = $src ?: wp_get_attachment_image_url($id, $size);
      $alt = $alt ?: get_post_meta($id, '_wp_attachment_image_alt', true);
      $srcset = $srcset ?: wp_get_attachment_image_srcset($id, $size);
      $sizes = $sizes ?: wp_get_attachment_image_sizes($id, $size);

      // Get image dimensions if width/height not provided
      if (!$width || !$height) {
          $imageMeta = wp_get_attachment_metadata($id);
          if ($imageMeta && isset($imageMeta['width'], $imageMeta['height'])) {
              $width = $width ?: $imageMeta['width'];
              $height = $height ?: $imageMeta['height'];
          }
      }
  }

  // Determine loading attributes - fetchpriorityHigh takes precedence over lazyLoading
  $loadingAttributes = '';
  if ($fetchpriorityHigh) {
      $loadingAttributes = 'fetchpriority="high"';
  } elseif ($lazyLoading) {
      $loadingAttributes = 'loading="lazy" decoding="async"';
  }
@endphp

<img
  alt="{{ $alt ?? '' }}"
  @if($width) width="{{ $width }}" @endif
  @if($height) height="{{ $height }}" @endif
  @if($src) src="{{ $src }}" @endif
  @if($srcset) srcset="{{ $srcset }}" @endif
  @if($sizes) sizes="{{ $sizes }}" @endif
  {!! $loadingAttributes !!}
  {{ $attributes->merge(['class' => 'block']) }}
/>
