<li class="marker" itemscope="itemscope" itemtype="http://schema.org/Place">
  <a href="<?php the_permalink() ?>" itemprop="url">
    <?php the_title( '<span itemprop="name">', '</span>' ) ?>
  </a>
  <img src="<?php echo wp_get_attachment_thumb_url( get_the_ID() ) ?>" itemprop="image" />
  <p itemprop="description"><?php echo strip_tags( get_the_excerpt() ) ?></p>
  <span itemprop="geo" itemscope="itemscope" itemtype="http://schema.org/GeoCoordinates">
    <meta itemprop="latitude" content="<?php the_map_marker_latitude() ?>" />
    <meta itemprop="longitude" content="<?php the_map_marker_longitude() ?>" />
  </span>
</li>