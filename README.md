# mx-cpt
Demonstration of creating Custom Post Types, Taxonomies and meta-fields. Shortcodes for data output.

Task:

1. Create WordPress theme.
2. Create Custom Post Type **Car**.  
   2.1. Implement the ability to add meta-fields, for example, **Brand**.
3. Create taxonomy **Body** and link it to the CPT **Car**.
4. Templates.  
   4.1. Main page `index.php` with a list of cars.  
   4.2. Single page `single-car.php` for car details.  
5. Shortcode for displaying a list of cars.  
   5.1. Create the shortcode that displays a list of cars with the ability to filter by **Brand**.  
   5.2. The shortcode accepts a parameter to select the number of cars to display on the page.  
6. Create a shortcode using a query via `wpdb`.  
   6.1. The shortcode executes a custom *SQL-query* to the database via the `wpdb` class and displays the results on the page.  
   6.2. The shortcode should output a list of car names and the number of possible bodies associated with the taxonomy **Body**.
