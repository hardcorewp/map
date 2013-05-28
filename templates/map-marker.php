<div itemscope="itemscope" itemtype="http://schema.org/Place">
  <a href="<?php the_permalink() ?>" itemprop="url">
    <?php the_title( '<span itemprop="name">', '</span>' ) ?>
  </a>
  <p itemprop="description"><?php the_excerpt() ?></p>
  <span itemprop="geo" itemscope="itemscope" itemtype="http://schema.org/GeoCoordinates">
    <meta itemprop="latitude" content="43.681505" />
    <meta itemprop="longitude" content="-79.294455" />
  </span>
</div>