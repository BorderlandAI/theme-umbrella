<?php get_header(); ?>

  <section id="content" role="document">
    <div class="inner">
      <main id="mainContent" role="main">

        <div class="blog-listing">
          <div class="page-header">
            <h1>Blog</h1>
          </div>

          <?php if (have_posts()) : ?>
            <div class="blog-grid">
              <?php while (have_posts()) : the_post(); ?>
              <article class="blog-card">
                <?php if (has_post_thumbnail()) : ?>
                  <a href="<?php the_permalink(); ?>" class="blog-card-image">
                    <?php the_post_thumbnail('medium_large', array('alt' => esc_attr(get_the_title()), 'loading' => 'lazy')); ?>
                  </a>
                <?php endif; ?>
                <div class="blog-card-body">
                  <div class="blog-card-meta">
                    <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('F j, Y'); ?></time>
                  </div>
                  <h2 class="blog-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                  <div class="blog-card-excerpt">
                    <?php the_excerpt(); ?>
                  </div>
                  <a href="<?php the_permalink(); ?>" class="blog-card-link">Read More &rarr;</a>
                </div>
              </article>
              <?php endwhile; ?>
            </div>

            <div class="blog-pagination">
              <?php
                the_posts_pagination(array(
                  'mid_size'  => 2,
                  'prev_text' => '&laquo; Previous',
                  'next_text' => 'Next &raquo;',
                ));
              ?>
            </div>

          <?php else : ?>
            <div class="blog-empty">
              <h2>No Posts Yet</h2>
              <p>Check back soon for news, tips, and updates from Borderland Powersports.</p>
            </div>
          <?php endif; ?>
        </div>

      </main>
    </div>
  </section>

<?php get_footer(); ?>
