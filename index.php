<?php get_header(); ?>

<main>
    <h2>Cars List</h2>
    <?php
    $args = array(
        'post_type' => 'car',
        'posts_per_page' => 10,
    );
    $cars_query = new WP_Query($args);

    if ($cars_query->have_posts()) :
        while ($cars_query->have_posts()) : $cars_query->the_post();
            ?>
            <article>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php if (has_post_thumbnail()) {
                    the_post_thumbnail('medium');
                } ?>
                <?php the_excerpt(); ?>
            </article>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>No cars found.</p>';
    endif;
    ?>

    <?php echo do_shortcode('[cars_list posts_per_page="3"]'); ?>

    <h3>Body</h3>
    <?php echo do_shortcode('[cars_with_body]') ?>
</main>

<?php get_footer(); ?>
