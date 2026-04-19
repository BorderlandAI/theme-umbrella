<?php get_header(); ?>

  <section id="content" role="document">
    <div class="inner">
      <main id="mainContent" role="main">
        <?php while (have_posts()) : the_post(); ?>
        <div class="page-header">
          <h1><?php the_title(); ?></h1>
        </div>
        <article>
          <div class="text">
            <?php the_content(); ?>
          </div>
        </article>
        <?php endwhile; ?>
      </main>
    </div>
  </section>

<?php get_footer(); ?>
