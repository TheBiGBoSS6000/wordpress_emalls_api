<?php

add_action( 'rest_api_init', 'add_emalls_api');

function add_emalls_api(){
    
    register_rest_route( 'site/api/v1', '/plugins/emalls/products', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_emalls_data',
    ));
    
}

function get_emalls_data(){
    
    if( !isset($_GET['page']) || !is_numeric($_GET['page'])
        || !isset($_GET['size']) || !is_numeric($_GET['size']) ) {
        return new WP_Error( 404, 'Not Found', array( 'status' => 404 ) );
    }
    
    $paged              = (int) $_GET['page'];
    $posts_per_page     = (int) $_GET['size'];
    
    if( $posts_per_page >= 100 ) {
        $posts_per_page = 100;
    }
    
   if( $posts_per_page <= 10 ) {
        $posts_per_page = 10;
    }
    
    $query = new WP_Query( array(
        'order'             => 'DESC',
        'orderby'           => 'modified',
        'post_type'         => 'product',
        'posts_per_page'    => $posts_per_page,
        'paged'             => $paged,
    ) );
    
    if ( $query->have_posts() ) {
        
        while ( $query->have_posts() ) {
            $query->the_post();
            $ID = get_the_ID();
            $price = get_post_meta( $ID, '_price' , TRUE );
            $regular_price = get_post_meta( $ID, '_regular_price' , TRUE );
            $stock_status = get_post_meta( $ID, '_stock_status' , TRUE );
            $result[] = [
                "id"            => $ID,
                "title"         => get_the_title(),
                "url"           => get_the_permalink(),
                "price"         => $price,
                "old_price"     => $regular_price != $price ? $regular_price : NULL,
                "is_available"  => $stock_status == "instock" ? TRUE : FALSE,
            ];
        }
        
        if( count( $result ) ) {
            return $result;
        }
        
    }
    
    return new WP_Error( 404, 'Not Found', array( 'status' => 404 ) );

}
