<?php get_header(); ?>

  <section id="content" role="document">
    <div class="inner">
      <main id="mainContent" role="main">

        <?php while (have_posts()) : the_post(); ?>
        <article class="blog-single">

          <?php if (has_post_thumbnail()) : ?>
            <div class="blog-single-hero">
              <?php the_post_thumbnail('full', array('alt' => esc_attr(get_the_title()))); ?>
            </div>
          <?php endif; ?>

          <div class="blog-single-content">
            <div class="blog-single-meta">
              <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('F j, Y'); ?></time>
              <span class="blog-single-author">by <?php the_author(); ?></span>
            </div>

            <h1 class="blog-single-title"><?php the_title(); ?></h1>

            <div class="blog-single-body">
              <?php the_content(); ?>
            </div>

            <div class="blog-single-back">
              <a href="<?php echo home_url('/blog/'); ?>">&larr; Back to Blog</a>
            </div>
          </div>

        </article>
        <?php endwhile; ?>

      </main>
    </div>
  </section>

<?php get_footer(); ?>
