<?php get_header(); ?>

  <section id="content" role="document">
    <div class="inner">
      <main id="mainContent" role="main">
        <div class="page-header">
          <h1>Page Not Found</h1>
        </div>
        <article>
          <div class="text">
            <p>Sorry, the page you're looking for doesn't exist. Please use the navigation above or <a href="<?php echo home_url('/contact/'); ?>">contact us</a> if you need assistance.</p>
            <p><a href="<?php echo home_url('/'); ?>" class="btn">Back to Home</a></p>
          </div>
        </article>
      </main>
    </div>
  </section>

<?php get_footer(); ?>
