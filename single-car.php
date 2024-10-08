<?php get_header(); ?>

<main>
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1><?php the_title(); ?></h1>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="car-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="car-details">
                    <?php $brand = get_post_meta(get_the_ID(), 'brand', true); ?>
                    <?php if ($brand) : ?>
                        <p><strong>Brand:</strong> <?php echo esc_html($brand); ?></p>
                    <?php endif; ?>
                </div>

                <div class="car-content">
                    <?php the_content(); ?>
                </div>
            </article>

        <?php endwhile;
    else :
        echo '<p>No car found.</p>';
    endif;
    ?>
</main>

