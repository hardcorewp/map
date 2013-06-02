<?php
if ( !class_exists( 'Hardcore_Map_Marker_View' ) ) {

  class Hardcore_Map_Marker_View extends ScaleUp_View {

    /**
     * Callback for view's process action
     *
     * @param ScaleUp_View $view
     * @param ScaleUp_Request $request
     */
    function process( $view, $request ) {

      if ( !isset( $request->vars ) || ( isset( $request->vars ) && empty( $request->vars[ 'map_name'] ) ) ) {
        if ( is_object( $addon = $view->get( 'context' ) ) ) {
          $request->vars[ 'map_name' ] = $addon->get( 'map_name' );
        }
      }

    }

    /**
     * Callback for view's load_template_data action
     *
     * @param ScaleUp_View $view
     * @param ScaleUp_Request $request
     */
    function load_template_data( $view, $request ) {

      $request->template_data[ 'markers' ] = array();

      if ( !isset( $request->vars ) || ( isset( $request->vars ) && empty( $request->vars[ 'map_name' ] ) ) ) {
        $site = ScaleUp::get_site();
        /*** @var $map Hardcore_Map_Feature **/
        $map = $site->get_feature( 'map', $request->vars[ 'map_name' ] );
        if ( is_object( $map ) ) {
          $request->template_data[ 'markers' ] = $map->get_features( 'markers' );
        }
      }

    }

  }

}